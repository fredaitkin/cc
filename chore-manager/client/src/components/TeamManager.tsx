import React, { useState } from 'react';
import { Member } from '../types';
import * as api from '../api';

interface Props {
  members: Member[];
  onMembersChange: () => void;
}

export default function TeamManager({ members, onMembersChange }: Props) {
  const [name, setName] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  async function handleAdd(e: React.FormEvent) {
    e.preventDefault();
    if (!name.trim()) return;
    setError('');
    setLoading(true);
    try {
      await api.addMember(name.trim());
      setName('');
      onMembersChange();
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  async function handleDelete(member: Member) {
    if (!window.confirm(`Remove ${member.name}? Their pending chores will be reassigned.`)) return;
    try {
      await api.deleteMember(member.id);
      onMembersChange();
    } catch (err: any) {
      alert(err.message);
    }
  }

  return (
    <div className="panel">
      <h2>Team Members</h2>

      {members.length === 0 ? (
        <p className="empty">No team members yet. Add one below.</p>
      ) : (
        <ul className="member-list">
          {members.map(m => (
            <li key={m.id} className="member-item">
              <span>{m.name}</span>
              <button className="btn-danger" onClick={() => handleDelete(m)}>Remove</button>
            </li>
          ))}
        </ul>
      )}

      <form className="add-form" onSubmit={handleAdd}>
        <input
          type="text"
          placeholder="New member name"
          value={name}
          onChange={e => setName(e.target.value)}
          disabled={loading}
        />
        <button type="submit" className="btn-primary" disabled={loading || !name.trim()}>
          Add
        </button>
      </form>
      {error && <p className="form-error">{error}</p>}
    </div>
  );
}
