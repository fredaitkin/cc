import { Router, Request, Response } from 'express';
import pool from '../db';
import { Member } from '../types';
import { RowDataPacket, ResultSetHeader } from 'mysql2';

const router = Router();

// GET /api/members
router.get('/', async (_req: Request, res: Response) => {
  try {
    const [rows] = await pool.query<RowDataPacket[]>(
      'SELECT id, name, created_at FROM team_members ORDER BY id'
    );
    res.json(rows as Member[]);
  } catch (err) {
    res.status(500).json({ error: 'Failed to fetch members' });
  }
});

// POST /api/members
router.post('/', async (req: Request, res: Response) => {
  const { name } = req.body as { name?: string };
  if (!name || !name.trim()) {
    return res.status(400).json({ error: 'name is required' });
  }
  try {
    const [result] = await pool.execute<ResultSetHeader>(
      'INSERT INTO team_members (name) VALUES (?)',
      [name.trim()]
    );
    const [rows] = await pool.query<RowDataPacket[]>(
      'SELECT id, name, created_at FROM team_members WHERE id = ?',
      [result.insertId]
    );
    res.status(201).json(rows[0]);
  } catch (err: any) {
    if (err.code === 'ER_DUP_ENTRY') {
      return res.status(400).json({ error: 'A member with that name already exists' });
    }
    res.status(500).json({ error: 'Failed to create member' });
  }
});

// DELETE /api/members/:id
router.delete('/:id', async (req: Request, res: Response) => {
  const id = parseInt(req.params.id, 10);
  if (isNaN(id)) return res.status(400).json({ error: 'Invalid id' });

  const conn = await pool.getConnection();
  try {
    await conn.beginTransaction();

    // Get remaining members after deletion
    const [remaining] = await conn.query<RowDataPacket[]>(
      'SELECT id FROM team_members WHERE id != ? ORDER BY id',
      [id]
    );

    if (remaining.length === 0) {
      // No members left — null out all pending assignments
      await conn.execute(
        `UPDATE chore_instances
         SET assigned_member_id = NULL
         WHERE completed_at IS NULL AND due_date >= CURDATE()`
      );
      await conn.execute('UPDATE chores SET rotation_index = 0');
    } else {
      // Get all active chores
      const [chores] = await conn.query<RowDataPacket[]>(
        'SELECT id, rotation_index FROM chores WHERE active = 1'
      );

      for (const chore of chores) {
        // Find pending instances assigned to the deleted member
        const [pending] = await conn.query<RowDataPacket[]>(
          `SELECT id FROM chore_instances
           WHERE chore_id = ? AND assigned_member_id = ?
             AND due_date >= CURDATE() AND completed_at IS NULL
           ORDER BY due_date ASC`,
          [chore.id, id]
        );

        for (let i = 0; i < pending.length; i++) {
          const newMember = remaining[i % remaining.length] as RowDataPacket;
          await conn.execute(
            'UPDATE chore_instances SET assigned_member_id = ? WHERE id = ?',
            [newMember.id, pending[i].id]
          );
        }

        // Normalise rotation_index to new member count
        await conn.execute(
          'UPDATE chores SET rotation_index = rotation_index % ? WHERE id = ?',
          [remaining.length, chore.id]
        );
      }
    }

    await conn.execute('DELETE FROM team_members WHERE id = ?', [id]);
    await conn.commit();
    res.json({ ok: true });
  } catch (err) {
    await conn.rollback();
    res.status(500).json({ error: 'Failed to delete member' });
  } finally {
    conn.release();
  }
});

export default router;
