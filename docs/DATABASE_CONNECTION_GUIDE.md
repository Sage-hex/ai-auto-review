# Database Connection Guide (Beginner Friendly)

If databases feel new, follow this exactly.

## 1) Pick a database

For easiest local setup, start with **SQLite** (no install, one file).
For production, use **PostgreSQL**.

## 2) Configure the backend connection

The FastAPI backend reads this environment variable:

- `DATABASE_URL`

### Option A: SQLite (recommended for first run)

```env
DATABASE_URL=sqlite:///./fastapi_backend/aiautoreview.db
```

What this means:
- `sqlite:///` = use SQLite
- `./fastapi_backend/aiautoreview.db` = file path where data is stored

### Option B: PostgreSQL (recommended for real deployment)

```env
DATABASE_URL=postgresql+psycopg2://postgres:YOUR_PASSWORD@localhost:5432/aiautoreview
```

Parts explained:
- `postgresql+psycopg2` = Postgres driver
- `postgres` = DB username
- `YOUR_PASSWORD` = DB password
- `localhost` = DB server host
- `5432` = default Postgres port
- `aiautoreview` = database name

## 3) Create `.env` file

In repository root:

```bash
cp .env.example .env
```

Then add/update:

```env
SECRET_KEY=replace-with-a-long-random-secret
DATABASE_URL=sqlite:///./fastapi_backend/aiautoreview.db
CORS_ORIGINS=["http://localhost:5173"]
VITE_API_BASE_URL=http://localhost:8000/api/v1
```

## 4) Start backend and frontend

Backend:

```bash
cd fastapi_backend
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --reload --port 8000
```

Frontend (new terminal):

```bash
npm run dev
```

## 5) Verify connection

Open:
- Backend health: `http://localhost:8000/health`
- API docs: `http://localhost:8000/docs`

If `/health` returns `{"status":"ok"}`, your app is connected.

## Common errors and fixes

- **`connection refused`**: DB server not running (for Postgres/MySQL).
- **`password authentication failed`**: username/password incorrect.
- **`no such table`**: first startup issue; restart backend so tables are created.
- **Frontend still calling old API**: ensure `VITE_API_BASE_URL` is set correctly.

## Next step for production

Move from SQLite to PostgreSQL and use migrations (Alembic) for schema changes.
