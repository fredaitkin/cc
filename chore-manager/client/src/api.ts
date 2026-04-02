import { Member, Chore, ChoreInstanceRow } from './types';

const BASE = '/api';

async function request<T>(path: string, options?: RequestInit): Promise<T> {
  const res = await fetch(`${BASE}${path}`, options);
  const data = await res.json();
  if (!res.ok) throw new Error(data.error ?? 'Request failed');
  return data as T;
}

// Members
export const getMembers = () => request<Member[]>('/members');

export const addMember = (name: string) =>
  request<Member>('/members', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name }),
  });

export const deleteMember = (id: number) =>
  request<{ ok: boolean }>(`/members/${id}`, { method: 'DELETE' });

// Chores
export const getChores = () => request<Chore[]>('/chores');

export const createChore = (data: {
  title: string;
  category: string;
  recurrence: 'daily' | 'monthly';
  recur_day_of_month?: number;
}) =>
  request<Chore>('/chores', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });

export const updateChore = (id: number, data: { title: string; category: string }) =>
  request<Chore>(`/chores/${id}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });

export const deleteChore = (id: number) =>
  request<{ ok: boolean }>(`/chores/${id}`, { method: 'DELETE' });

// Instances
export const getInstances = (start: string, end: string) =>
  request<ChoreInstanceRow[]>(`/instances?start=${start}&end=${end}`);

export const completeInstance = (id: number) =>
  request<{ id: number; completed_at: string }>(`/instances/${id}/complete`, { method: 'PATCH' });

export const uncompleteInstance = (id: number) =>
  request<{ id: number; completed_at: null }>(`/instances/${id}/uncomplete`, { method: 'PATCH' });

export const toggleComplete = (instance: ChoreInstanceRow) =>
  instance.completed_at ? uncompleteInstance(instance.id) : completeInstance(instance.id);
