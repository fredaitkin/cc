# Chore Manager — Feature Breakdown

## Overview

A team office chore scheduling and tracking app. Chores are assigned to members via round-robin rotation; a calendar view shows upcoming and completed instances.

---

## Navigation

Three top-level tabs in the app header:

| Tab | Component | Purpose |
|-----|-----------|---------|
| Calendar | `CalendarView` | View and complete chore instances |
| Chores | `ChoreList` | Manage chore templates |
| Team | `TeamManager` | Manage team members |

---

## Feature: Calendar View (`CalendarView.tsx`)

Displays chore instances on a calendar. Instances are generated on-demand when the calendar range is fetched.

### Behaviours
- **Month / Week toggle** — switch between month and week views via toolbar buttons
- **Navigate dates** — forward/back navigation via react-big-calendar built-in controls
- **Auto-generate instances** — fetching a date range triggers server-side instance generation for any gaps (daily chores get one instance per day; monthly chores get one per configured day-of-month)
- **Click to complete/uncomplete** — clicking any calendar event toggles its `completed_at` timestamp; completed events show strikethrough text and reduced opacity
- **Assigned member display** — each event shows the chore title and the assigned member's name beneath it
- **Category colour coding** — events are coloured by category using a deterministic pastel palette; a legend below the calendar shows which colour maps to which category

### API calls
- `GET /api/instances?start=YYYY-MM-DD&end=YYYY-MM-DD`
- `PATCH /api/instances/:id/complete`
- `PATCH /api/instances/:id/uncomplete`

---

## Feature: Chore Management (`ChoreList.tsx` + `ChoreForm.tsx`)

Manage the library of recurring chore templates.

### Behaviours
- **List chores** — table showing title, category badge, and recurrence label for all active chores
- **Category badge** — colour-coded pill matching the calendar legend colours
- **Recurrence label** — "Daily" or "Monthly on the Nth" (with ordinal suffix)
- **Add chore** — opens a modal form (`ChoreForm`) with fields:
  - Title (required)
  - Category (optional free text)
  - Recurring checkbox — toggles whether the chore repeats
  - Recurrence: Daily or Monthly (shown when recurring)
  - Assignee dropdown — select a team member to assign the chore to
- **Delete chore** — confirmation prompt, then soft-deletes the chore template (`active = 0`) and hard-deletes only pending (uncompleted) instances; completed history is preserved

### API calls
- `GET /api/chores`
- `POST /api/chores`
- `PUT /api/chores/:id` (title + category only; not exposed in UI yet)
- `DELETE /api/chores/:id`

---

## Feature: Team Management (`TeamManager.tsx`)

Manage the list of team members who are assigned chores.

### Behaviours
- **List members** — all members shown with a Remove button
- **Add member** — inline form; name must be unique (enforced by DB constraint)
- **Remove member** — confirmation prompt, then:
  - If members remain: pending instances assigned to the deleted member are redistributed round-robin across remaining members; each chore's `rotation_index` is normalised to the new member count
  - If no members remain: all pending instance assignments are set to NULL; all `rotation_index` values reset to 0

### API calls
- `GET /api/members`
- `POST /api/members`
- `DELETE /api/members/:id`

---

## Feature: Round-Robin Assignment

Chores are automatically assigned to members in rotation order.

### How it works
1. Each chore has a `rotation_index` counter stored in the DB
2. When a new instance is generated, the member at `rotation_index % memberCount` is assigned
3. `rotation_index` increments by 1 after each new instance is inserted (skipped if the instance already exists due to `INSERT IGNORE`)
4. When a member is deleted, pending assignments are redistributed and `rotation_index` is normalised

---

## Feature: On-Demand Instance Generation

Instances are not pre-generated; they are created lazily when the calendar fetches a date range.

### How it works
- `GET /api/instances` triggers `generateInstancesForRange()` before querying
- For **daily** chores: one instance per day in the range
- For **monthly** chores: one instance per month where `recur_day_of_month` falls within the range
- `INSERT IGNORE` prevents duplicates when the same range is fetched multiple times

---

## Data Model Summary

| Table | Key columns | Notes |
|-------|------------|-------|
| `team_members` | `id`, `name` (unique), `created_at` | Active members only; hard-deleted |
| `chores` | `id`, `title`, `category`, `recurrence`, `recur_day_of_month`, `rotation_index`, `active` | Soft-deleted (`active = 0`) |
| `chore_instances` | `id`, `chore_id`, `assigned_member_id`, `due_date`, `completed_at` | Unique on `(chore_id, due_date)`; FK to chores (CASCADE) and members (SET NULL) |

---

## Current Branch: `feat/chore-form-member-selector`

Work in progress to add member selection to the chore form / chore instances.
