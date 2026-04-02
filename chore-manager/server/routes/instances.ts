import { Router, Request, Response } from 'express';
import pool from '../db';
import { Chore } from '../types';
import { RowDataPacket, ResultSetHeader } from 'mysql2';

const router = Router();

// Returns the number of days in a given month (1-indexed month, 1–12)
function daysInMonth(year: number, month: number): number {
  return new Date(year, month, 0).getDate();
}

// Format a Date as YYYY-MM-DD
function toDateStr(d: Date): string {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

function computeOccurrences(chore: Chore, startDate: Date, endDate: Date): string[] {
  const results: string[] = [];

  if (chore.recurrence === 'daily') {
    const cursor = new Date(startDate);
    cursor.setHours(0, 0, 0, 0);
    const end = new Date(endDate);
    end.setHours(0, 0, 0, 0);
    while (cursor <= end) {
      results.push(toDateStr(cursor));
      cursor.setDate(cursor.getDate() + 1);
    }
  } else if (chore.recurrence === 'monthly' && chore.recur_day_of_month != null) {
    const day = chore.recur_day_of_month;
    const start = new Date(startDate);
    start.setHours(0, 0, 0, 0);
    const end = new Date(endDate);
    end.setHours(0, 0, 0, 0);

    // Start from the first month that overlaps the range
    let year = start.getFullYear();
    let month = start.getMonth() + 1; // 1-indexed

    while (true) {
      const lastDay = daysInMonth(year, month);
      if (day <= lastDay) {
        const candidate = new Date(year, month - 1, day);
        candidate.setHours(0, 0, 0, 0);
        if (candidate > end) break;
        if (candidate >= start) {
          results.push(toDateStr(candidate));
        }
      }
      // Advance one month
      month++;
      if (month > 12) {
        month = 1;
        year++;
      }
      // Break if we've gone past the end year/month
      const firstOfMonth = new Date(year, month - 1, 1);
      if (firstOfMonth > end) break;
    }
  }

  return results;
}

async function generateInstancesForRange(startDate: Date, endDate: Date): Promise<void> {
  const [memberRows] = await pool.query<RowDataPacket[]>(
    'SELECT id FROM team_members ORDER BY id'
  );
  if (memberRows.length === 0) return;

  const [choreRows] = await pool.query<RowDataPacket[]>(
    'SELECT * FROM chores WHERE active = 1'
  );
  if (choreRows.length === 0) return;

  const conn = await pool.getConnection();
  try {
    await conn.beginTransaction();

    for (const choreRow of choreRows) {
      const chore = choreRow as Chore;
      const occurrences = computeOccurrences(chore, startDate, endDate);

      for (const dateStr of occurrences) {
        const assignedId = memberRows[chore.rotation_index % memberRows.length].id as number;

        const [result] = await conn.execute<ResultSetHeader>(
          `INSERT IGNORE INTO chore_instances (chore_id, assigned_member_id, due_date)
           VALUES (?, ?, ?)`,
          [chore.id, assignedId, dateStr]
        );

        if (result.affectedRows > 0) {
          await conn.execute(
            'UPDATE chores SET rotation_index = rotation_index + 1 WHERE id = ?',
            [chore.id]
          );
          chore.rotation_index++;
        }
      }
    }

    await conn.commit();
  } catch (err) {
    await conn.rollback();
    throw err;
  } finally {
    conn.release();
  }
}

// GET /api/instances?start=YYYY-MM-DD&end=YYYY-MM-DD
router.get('/', async (req: Request, res: Response) => {
  const { start, end } = req.query as { start?: string; end?: string };

  if (!start || !end) {
    return res.status(400).json({ error: 'start and end query params are required' });
  }

  const startDate = new Date(start);
  const endDate = new Date(end);
  if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
    return res.status(400).json({ error: 'Invalid date format. Use YYYY-MM-DD' });
  }
  if (startDate > endDate) {
    return res.status(400).json({ error: 'start must be before end' });
  }

  try {
    await generateInstancesForRange(startDate, endDate);

    const [rows] = await pool.query<RowDataPacket[]>(
      `SELECT
         ci.id,
         ci.chore_id,
         c.title  AS chore_title,
         c.category,
         c.recurrence,
         DATE_FORMAT(ci.due_date, '%Y-%m-%d') AS due_date,
         ci.completed_at,
         ci.assigned_member_id,
         tm.name AS assigned_member_name
       FROM chore_instances ci
       JOIN chores c ON c.id = ci.chore_id
       LEFT JOIN team_members tm ON tm.id = ci.assigned_member_id
       WHERE ci.due_date BETWEEN ? AND ?
       ORDER BY ci.due_date, c.title`,
      [start, end]
    );

    res.json(rows);
  } catch (err) {
    res.status(500).json({ error: 'Failed to fetch instances' });
  }
});

// PATCH /api/instances/:id/complete
router.patch('/:id/complete', async (req: Request, res: Response) => {
  const id = parseInt(req.params.id, 10);
  if (isNaN(id)) return res.status(400).json({ error: 'Invalid id' });

  try {
    const [result] = await pool.execute<ResultSetHeader>(
      'UPDATE chore_instances SET completed_at = NOW() WHERE id = ?',
      [id]
    );
    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Instance not found' });
    }
    const [rows] = await pool.query<RowDataPacket[]>(
      'SELECT id, completed_at FROM chore_instances WHERE id = ?',
      [id]
    );
    res.json(rows[0]);
  } catch (err) {
    res.status(500).json({ error: 'Failed to complete instance' });
  }
});

// PATCH /api/instances/:id/uncomplete
router.patch('/:id/uncomplete', async (req: Request, res: Response) => {
  const id = parseInt(req.params.id, 10);
  if (isNaN(id)) return res.status(400).json({ error: 'Invalid id' });

  try {
    const [result] = await pool.execute<ResultSetHeader>(
      'UPDATE chore_instances SET completed_at = NULL WHERE id = ?',
      [id]
    );
    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Instance not found' });
    }
    res.json({ id, completed_at: null });
  } catch (err) {
    res.status(500).json({ error: 'Failed to uncomplete instance' });
  }
});

export default router;
