import React, { useState, useEffect, useCallback } from 'react';
import { Member, Chore } from './types';
import * as api from './api';
import CalendarView from './components/CalendarView';
import ChoreList from './components/ChoreList';
import TeamManager from './components/TeamManager';

type Tab = 'calendar' | 'chores' | 'team';

export default function App() {
  const [tab, setTab] = useState<Tab>('calendar');
  const [members, setMembers] = useState<Member[]>([]);
  const [chores, setChores] = useState<Chore[]>([]);

  const loadMembers = useCallback(async () => {
    try { setMembers(await api.getMembers()); } catch {}
  }, []);

  const loadChores = useCallback(async () => {
    try { setChores(await api.getChores()); } catch {}
  }, []);

  useEffect(() => {
    loadMembers();
    loadChores();
  }, [loadMembers, loadChores]);

  return (
    <div className="app">
      <header className="app-header">
        <h1>Office Chore Manager</h1>
        <nav className="app-nav">
          <button
            className={tab === 'calendar' ? 'nav-btn active' : 'nav-btn'}
            onClick={() => setTab('calendar')}
          >
            Calendar
          </button>
          <button
            className={tab === 'chores' ? 'nav-btn active' : 'nav-btn'}
            onClick={() => setTab('chores')}
          >
            Chores
          </button>
          <button
            className={tab === 'team' ? 'nav-btn active' : 'nav-btn'}
            onClick={() => setTab('team')}
          >
            Team
          </button>
        </nav>
      </header>

      <main className="app-main">
        {tab === 'calendar' && <CalendarView members={members} />}
        {tab === 'chores' && (
          <ChoreList chores={chores} onChoresChange={loadChores} />
        )}
        {tab === 'team' && (
          <TeamManager members={members} onMembersChange={loadMembers} />
        )}
      </main>
    </div>
  );
}
