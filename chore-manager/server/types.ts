export interface Member {
  id: number;
  name: string;
  created_at: string;
}

export interface Chore {
  id: number;
  title: string;
  category: string;
  recurrence: 'daily' | 'monthly';
  recur_day_of_month: number | null;
  rotation_index: number;
  active: number;
  created_at: string;
}

export interface ChoreInstance {
  id: number;
  chore_id: number;
  assigned_member_id: number | null;
  due_date: string;
  completed_at: string | null;
  created_at: string;
}

export interface ChoreInstanceRow {
  id: number;
  chore_id: number;
  chore_title: string;
  category: string;
  recurrence: 'daily' | 'monthly';
  due_date: string;
  completed_at: string | null;
  assigned_member_id: number | null;
  assigned_member_name: string | null;
}
