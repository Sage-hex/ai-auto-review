# AI Auto Review

A clean, two-part codebase:
- **Frontend:** React + Vite (`src/`)
- **Backend:** FastAPI (`fastapi_backend/`)

Legacy PHP artifacts were removed to keep the repository focused and easier to maintain.

## Project Structure

```txt
.
├── src/                    # React application
├── public/                 # Static frontend assets
├── fastapi_backend/        # FastAPI app (routers/services/models/db/core)
├── docs/                   # Project documentation
├── package.json
└── .env.example
```

## Frontend (React)

```bash
npm install
npm run dev
```

Build:

```bash
npm run build
```

## Backend (FastAPI)

```bash
cd fastapi_backend
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --reload --port 8000
```

- Health check: `http://localhost:8000/health`
- Swagger docs: `http://localhost:8000/docs`

## Environment

Copy and edit:

```bash
cp .env.example .env
```

Set at least:

```env
VITE_API_BASE_URL=http://localhost:8000/api/v1
DATABASE_URL=sqlite:///./fastapi_backend/aiautoreview.db
SECRET_KEY=replace_with_random_long_secret
```

## Database Help

If you're new to databases, use:
- `docs/DATABASE_CONNECTION_GUIDE.md`

It explains SQLite (easy local) and PostgreSQL (production) step by step.


## Free database + production-like run

If you want a free hosted DB (Neon/Supabase) and a step-by-step production-style test run, follow:

- `docs/FREE_DATABASE_PRODUCTION_TEST_RUN.md`

