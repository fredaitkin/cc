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
  created_at: string;
}

export interface ChoreInstanceRow {
  id: number;
  chore_id: number;
  chore_title: string;
  category: string;
  recurrence: 'daily' | 'monthly';
  due_date: string; // YYYY-MM-DD
  completed_at: string | null;
  assigned_member_id: number | null;
  assigned_member_name: string | null;
}
