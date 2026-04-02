# Architectural Patterns

## 1. Centralized API Client with Generic Request Helper

All client HTTP calls go through `client/src/api.ts`. A single generic `request<T>` function (lines 5–10) handles fetch + JSON parsing + error throwing; all exported functions call it and return typed results.

**Why**: Avoids duplicated fetch boilerplate and centralises error handling.

## 2. Callback-Based Data Refresh

Parent `App.tsx` owns all data state. Child components receive mutation callbacks (`onChoresChange`, `onMembersChange`) and call them after writes to trigger a parent re-fetch.

- Callbacks wired: `App.tsx:19–26`
- Called from children: `ChoreList.tsx:57`, `TeamManager.tsx:23`

**Why**: Keeps data ownership in one place without a state library.

## 3. Lazy Chore Instance Generation

Chore instances are not pre-created on a schedule. The `GET /api/instances?start=&end=` endpoint generates any missing instances for the requested date range on demand, then returns all in that range.

- Range logic: `server/routes/instances.ts:69–114`
- Occurrence helpers: `computeOccurrences()` at `instances.ts:21–67`

**Why**: Avoids a background scheduler; instances stay correct even if the server is offline for a period.

## 4. Round-Robin Rotation via `rotation_index`

The `chores` table has a `rotation_index` column. When assigning a new instance, the backend picks `members[rotation_index % memberCount]`, then increments and persists `rotation_index`.

- Assignment: `server/routes/instances.ts:88–103`

**Why**: Stateless assignment — no need to inspect history to know who is next.

## 5. Atomic Transactions for Multi-Table Mutations

Any operation touching multiple tables uses explicit `BEGIN` / `COMMIT` / `ROLLBACK`:

- Member deletion (reassign rotation): `server/routes/members.ts:49–107`
- Chore deletion (clean up instances): `server/routes/chores.ts:99–117`

**Why**: Prevents partial state (e.g., a member removed but rotation index left invalid).

## 6. Auto-Migration on Startup

`server/db.ts:16–32` reads `schema.sql`, splits on `;`, and runs each statement against the pool on startup before the HTTP server begins accepting requests.

Called from `server/index.ts:17–26`.

**Why**: Single source of truth for schema; no separate migration runner needed for this scale.

## 7. Soft Delete for Chores

Deleting a chore sets `active = 0` (`server/routes/chores.ts:109`) rather than removing the row. Completed instances are preserved in `chore_instances` for history.

Schema field: `schema.sql:14` — `active TINYINT(1) DEFAULT 1`.

**Why**: Preserves audit history; past completions remain visible in the calendar.

## 8. Deterministic Category Color Hashing

`ChoreList.tsx:20–29` maps a category string to a color by summing character codes modulo the palette length. Same category always gets the same color across renders and sessions.

**Why**: No need to persist color assignments; purely derived from the string.

## 9. Vite Dev Proxy for API Calls

`client/vite.config.ts:6–9` proxies all `/api/*` requests to `http://localhost:3001`, so the client code uses relative `/api/` URLs and never hardcodes the backend host.

**Why**: Same URL structure works in both dev (proxy) and production (same-origin or a reverse proxy).
