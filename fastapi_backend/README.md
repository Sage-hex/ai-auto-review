# FastAPI Backend (Production-Style Starter)

This folder contains a clean FastAPI backend that replaces the legacy PHP API with a modern, layered architecture:

- `app/core`: settings/security
- `app/db`: SQLAlchemy engine and session handling
- `app/models`: ORM models
- `app/services`: business logic
- `app/api/routers`: HTTP route handlers only

## Quick start

```bash
cd fastapi_backend
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --reload --port 8000
```

API docs: `http://localhost:8000/docs`

## Environment variables

Create `.env` in the repository root (or export vars):

```env
DATABASE_URL=sqlite:///./fastapi_backend/aiautoreview.db
SECRET_KEY=replace-with-long-random-string
CORS_ORIGINS=["http://localhost:5173"]
API_V1_PREFIX=/api/v1
```

## Seed demo user

```bash
PYTHONPATH=fastapi_backend python fastapi_backend/scripts/seed_demo.py
```

Demo credentials:
- Email: `demo@aiautoreview.dev`
- Password: `Password123!`

## Main endpoints

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `GET /api/v1/reviews`
- `GET /api/v1/reviews/stats`
- `POST /api/v1/reviews/sync`
- `POST /api/v1/reviews/{review_id}/generate`
- `GET /api/v1/responses/pending`
- `PUT /api/v1/responses/{response_id}`
- `POST /api/v1/responses/{response_id}/approve`
- `POST /api/v1/responses/{response_id}/post`
