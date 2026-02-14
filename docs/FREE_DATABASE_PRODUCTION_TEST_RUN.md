# Free Database + Production-like Test Run

This is the fastest beginner path to run this project with a **free hosted PostgreSQL database**.

## Recommended free options

- **Neon** (very beginner-friendly)
- **Supabase** (also easy, includes dashboard)

Both provide a PostgreSQL connection string you can paste into `DATABASE_URL`.

---

## 1) Create a free PostgreSQL DB (Neon example)

1. Create account at Neon.
2. Create a new project.
3. Copy the connection string from the Neon dashboard.
4. Convert/ensure format for SQLAlchemy + psycopg:

```env
DATABASE_URL=postgresql+psycopg://USER:PASSWORD@HOST/DBNAME?sslmode=require
```

> Keep `sslmode=require` for hosted DB providers.

---

## 2) Configure environment

In repo root:

```bash
cp .env.example .env
```

Set:

```env
VITE_API_BASE_URL=http://localhost:8000/api/v1
SECRET_KEY=put-a-long-random-secret-here
CORS_ORIGINS=["http://localhost:5173"]
DATABASE_URL=postgresql+psycopg://USER:PASSWORD@HOST/DBNAME?sslmode=require
```

---

## 3) Install backend dependencies

```bash
cd fastapi_backend
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

---

## 4) Start API and validate DB connectivity

```bash
uvicorn app.main:app --host 0.0.0.0 --port 8000
```

If DB connection works, app starts and you can open:
- `http://localhost:8000/health`
- `http://localhost:8000/docs`

Optional seed user:

```bash
PYTHONPATH=fastapi_backend python fastapi_backend/scripts/seed_demo.py
```

Demo login:
- `demo@aiautoreview.dev`
- `Password123!`

---

## 5) Start frontend

From repo root in another terminal:

```bash
npm run dev
```

Open `http://localhost:5173`.

---

## 6) Production-like local test checklist

Use this before deploying:

1. Build frontend:
   ```bash
   npm run build
   ```
2. Backend import/syntax check:
   ```bash
   python -m compileall fastapi_backend/app fastapi_backend/scripts
   ```
3. Run backend with production-ish command:
   ```bash
   uvicorn app.main:app --host 0.0.0.0 --port 8000 --workers 2
   ```
4. Smoke test API:
   ```bash
   curl -s http://localhost:8000/health
   ```

Expected response:

```json
{"status":"ok"}
```

---

## 7) Deploy target suggestion (free-ish starter)

- **Backend:** Render / Railway / Fly.io
- **Frontend:** Vercel / Netlify
- **Database:** Neon / Supabase Postgres

On your backend host, set env vars exactly as in `.env` (especially `DATABASE_URL`, `SECRET_KEY`, and `CORS_ORIGINS`).

---

## Common issues

- `ModuleNotFoundError: psycopg` -> reinstall requirements in backend venv.
- `password authentication failed` -> wrong DB user/password.
- `server closed the connection unexpectedly` -> usually SSL/connection-string issue; add `?sslmode=require`.
- CORS errors in browser -> include frontend origin in `CORS_ORIGINS`.
