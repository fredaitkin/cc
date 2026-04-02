# Chore Manager

A team office chore scheduling and tracking app. Members are assigned recurring chores via round-robin rotation; a calendar view shows upcoming and completed instances.

## Tech Stack

- **Backend**: Node.js + Express + TypeScript, MySQL2 (connection pool), tsx (dev runner)
- **Frontend**: React 18 + TypeScript + Vite, react-big-calendar, date-fns
- **Monorepo**: npm workspaces (`server/`, `client/`)

## Key Directories

| Path | Purpose |
|------|---------|
| `server/index.ts` | Entry point — registers routes, runs migrations, starts server |
| `server/db.ts` | MySQL pool + auto-migration from `schema.sql` on startup |
| `server/schema.sql` | Source-of-truth DDL for `team_members`, `chores`, `chore_instances` |
| `server/routes/` | REST endpoints: `members.ts`, `chores.ts`, `instances.ts` |
| `server/types.ts` | Shared TypeScript interfaces for DB rows |
| `client/src/api.ts` | All HTTP calls; typed via generic `request<T>` helper |
| `client/src/App.tsx` | Root component — tab state, data fetching, callback wiring |
| `client/src/components/` | `CalendarView`, `ChoreList`, `ChoreForm`, `TeamManager` |
| `client/src/types.ts` | Shared TypeScript interfaces for API responses |

## Commands

```bash
# Development (runs server on :3001 + Vite client in parallel)
npm run dev

# Client only
npm run dev --workspace=client

# Server only
npm run dev --workspace=server

# Production build (client)
npm run build --workspace=client
```

## Environment Setup

Copy `server/.env.example` → `server/.env` and set:
`DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`

## Additional Documentation

- [Architectural Patterns](.claude/docs/architectural_patterns.md) — design decisions, data flow conventions, and recurring implementation patterns
