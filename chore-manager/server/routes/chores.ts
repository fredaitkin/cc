import { Router, Request, Response } from 'express';
import pool from '../db';
import { Chore } from '../types';
import { RowDataPacket, ResultSetHeader } from 'mysql2';

const router = Router();

// GET /api/chores
router.get('/', async (_req: Request, res: Response) => {
  try {
    const [rows] = await pool.query<RowDataPacket[]>(
      `SELECT id, title, category, recurrence, recur_day_of_month, rotation_index, created_at
       FROM chores WHERE active = 1 ORDER BY id`
    );
    res.json(rows as Chore[]);
  } catch (err) {
    res.status(500).json({ error: 'Failed to fetch chores' });
  }
});

// POST /api/chores
router.post('/', async (req: Request, res: Response) => {
  const { title, category, recurrence, recur_day_of_month } = req.body as {
    title?: string;
    category?: string;
    recurrence?: string;
    recur_day_of_month?: number;
  };

  if (!title || !title.trim()) {
    return res.status(400).json({ error: 'title is required' });
  }
  if (!recurrence || !['daily', 'monthly'].includes(recurrence)) {
    return res.status(400).json({ error: 'recurrence must be "daily" or "monthly"' });
  }
  if (recurrence === 'monthly') {
    const day = Number(recur_day_of_month);
    if (!Number.isInteger(day) || day < 1 || day > 28) {
      return res.status(400).json({ error: 'recur_day_of_month must be 1–28 for monthly chores' });
    }
  }

  try {
    const [result] = await pool.execute<ResultSetHeader>(
      `INSERT INTO chores (title, category, recurrence, recur_day_of_month)
       VALUES (?, ?, ?, ?)`,
      [
        title.trim(),
        (category ?? '').trim(),
        recurrence,
        recurrence === 'monthly' ? Number(recur_day_of_month) : null,
      ]
    );
    const [rows] = await pool.query<RowDataPacket[]>(
      `SELECT id, title, category, recurrence, recur_day_of_month, rotation_index, created_at
       FROM chores WHERE id = ?`,
      [result.insertId]
    );
    res.status(201).json(rows[0]);
  } catch (err) {
    res.status(500).json({ error: 'Failed to create chore' });
  }
});

// PUT /api/chores/:id — update title and category only
router.put('/:id', async (req: Request, res: Response) => {
  const id = parseInt(req.params.id, 10);
  if (isNaN(id)) return res.status(400).json({ error: 'Invalid id' });

  const { title, category } = req.body as { title?: string; category?: string };
  if (!title || !title.trim()) {
    return res.status(400).json({ error: 'title is required' });
  }

  try {
    await pool.execute(
      'UPDATE chores SET title = ?, category = ? WHERE id = ? AND active = 1',
      [title.trim(), (category ?? '').trim(), id]
    );
    const [rows] = await pool.query<RowDataPacket[]>(
      `SELECT id, title, category, recurrence, recur_day_of_month, rotation_index, created_at
       FROM chores WHERE id = ?`,
      [id]
    );
    if ((rows as RowDataPacket[]).length === 0) {
      return res.status(404).json({ error: 'Chore not found' });
    }
    res.json(rows[0]);
  } catch (err) {
    res.status(500).json({ error: 'Failed to update chore' });
  }
});

// DELETE /api/chores/:id
router.delete('/:id', async (req: Request, res: Response) => {
  const id = parseInt(req.params.id, 10);
  if (isNaN(id)) return res.status(400).json({ error: 'Invalid id' });

  const conn = await pool.getConnection();
  try {
    await conn.beginTransaction();
    // Delete pending instances, keep completed history
    await conn.execute(
      `DELETE FROM chore_instances
       WHERE chore_id = ? AND completed_at IS NULL`,
      [id]
    );
    // Soft-delete the chore template
    await conn.execute('UPDATE chores SET active = 0 WHERE id = ?', [id]);
    await conn.commit();
    res.json({ ok: true });
  } catch (err) {
    await conn.rollback();
    res.status(500).json({ error: 'Failed to delete chore' });
  } finally {
    conn.release();
  }
});

export default router;
