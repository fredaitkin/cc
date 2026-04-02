import React, { useState } from 'react';
import * as api from '../api';
import { Chore } from '../types';

interface Props {
  onSaved: (chore: Chore) => void;
  onCancel: () => void;
}

export default function ChoreForm({ onSaved, onCancel }: Props) {
  const [title, setTitle] = useState('');
  const [category, setCategory] = useState('');
  const [recurrence, setRecurrence] = useState<'daily' | 'monthly'>('daily');
  const [dayOfMonth, setDayOfMonth] = useState(1);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');

    if (!title.trim()) {
      setError('Title is required');
      return;
    }
    if (recurrence === 'monthly' && (dayOfMonth < 1 || dayOfMonth > 28)) {
      setError('Day of month must be between 1 and 28');
      return;
    }

    setLoading(true);
    try {
      const chore = await api.createChore({
        title: title.trim(),
        category: category.trim(),
        recurrence,
        ...(recurrence === 'monthly' ? { recur_day_of_month: dayOfMonth } : {}),
      });
      onSaved(chore);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="modal-overlay" onClick={onCancel}>
      <div className="modal" onClick={e => e.stopPropagation()}>
        <h2>Add Chore</h2>
        <form onSubmit={handleSubmit}>
          <label>
            Title *
            <input
              type="text"
              value={title}
              onChange={e => setTitle(e.target.value)}
              placeholder="e.g. Empty dishwasher"
              autoFocus
            />
          </label>

          <label>
            Category
            <input
              type="text"
              value={category}
              onChange={e => setCategory(e.target.value)}
              placeholder="e.g. Kitchen"
            />
          </label>

          <label>
            Recurrence
            <select
              value={recurrence}
              onChange={e => setRecurrence(e.target.value as 'daily' | 'monthly')}
            >
              <option value="daily">Daily</option>
              <option value="monthly">Monthly</option>
            </select>
          </label>

          {recurrence === 'monthly' && (
            <label>
              Day of month (1–28)
              <input
                type="number"
                min={1}
                max={28}
                value={dayOfMonth}
                onChange={e => setDayOfMonth(parseInt(e.target.value, 10))}
              />
            </label>
          )}

          {error && <p className="form-error">{error}</p>}

          <div className="modal-actions">
            <button type="button" className="btn-secondary" onClick={onCancel} disabled={loading}>
              Cancel
            </button>
            <button type="submit" className="btn-primary" disabled={loading}>
              {loading ? 'Saving…' : 'Add Chore'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
