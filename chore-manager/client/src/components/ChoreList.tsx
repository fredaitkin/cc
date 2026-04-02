import React, { useState } from 'react';
import { Chore } from '../types';
import * as api from '../api';
import ChoreForm from './ChoreForm';

interface Props {
  chores: Chore[];
  onChoresChange: () => void;
}

function recurrenceLabel(chore: Chore): string {
  if (chore.recurrence === 'daily') return 'Daily';
  const day = chore.recur_day_of_month;
  if (!day) return 'Monthly';
  const suffix = day === 1 ? 'st' : day === 2 ? 'nd' : day === 3 ? 'rd' : 'th';
  return `Monthly on the ${day}${suffix}`;
}

// Deterministic pastel color from a string
const PALETTE = [
  '#f9c6c6', '#f9e0c6', '#f9f5c6', '#c6f9cc',
  '#c6f0f9', '#c6d0f9', '#e0c6f9', '#f9c6ec',
  '#d4f9c6', '#f9d4c6',
];
export function categoryColor(cat: string): string {
  if (!cat) return '#e0e0e0';
  const hash = Array.from(cat).reduce((acc, ch) => acc + ch.charCodeAt(0), 0);
  return PALETTE[hash % PALETTE.length];
}

export default function ChoreList({ chores, onChoresChange }: Props) {
  const [showForm, setShowForm] = useState(false);

  async function handleDelete(chore: Chore) {
    if (!window.confirm(`Delete "${chore.title}"? Pending instances will be removed.`)) return;
    try {
      await api.deleteChore(chore.id);
      onChoresChange();
    } catch (err: any) {
      alert(err.message);
    }
  }

  return (
    <div className="panel">
      <div className="panel-header">
        <h2>Chores</h2>
        <button className="btn-primary" onClick={() => setShowForm(true)}>+ Add Chore</button>
      </div>

      {chores.length === 0 ? (
        <p className="empty">No chores yet. Add one to get started.</p>
      ) : (
        <table className="chore-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Category</th>
              <th>Recurrence</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {chores.map(chore => (
              <tr key={chore.id}>
                <td>{chore.title}</td>
                <td>
                  <span
                    className="category-badge"
                    style={{ backgroundColor: categoryColor(chore.category) }}
                  >
                    {chore.category || '—'}
                  </span>
                </td>
                <td>{recurrenceLabel(chore)}</td>
                <td>
                  <button className="btn-danger" onClick={() => handleDelete(chore)}>
                    Delete
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      {showForm && (
        <ChoreForm
          onSaved={() => { setShowForm(false); onChoresChange(); }}
          onCancel={() => setShowForm(false)}
        />
      )}
    </div>
  );
}
