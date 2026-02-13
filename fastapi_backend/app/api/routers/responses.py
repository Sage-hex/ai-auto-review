from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session

from app.api.deps import get_current_user
from app.db.session import get_db
from app.models.models import User
from app.services.review_service import pending_responses, update_response_status

router = APIRouter(prefix="/responses", tags=["responses"])


@router.get("/pending")
def get_pending(db: Session = Depends(get_db), user: User = Depends(get_current_user)):
    return {"status": "success", "data": pending_responses(db, user)}


@router.put("/{response_id}")
def update_response(response_id: int, payload: dict, db: Session = Depends(get_db), user: User = Depends(get_current_user)):
    try:
        data = update_response_status(db, user, response_id, action="update", response_text=payload.get("response_text"))
        return {"status": "success", "message": "Response updated", "data": data}
    except ValueError as exc:
        raise HTTPException(status_code=404, detail=str(exc))


@router.post("/{response_id}/approve")
def approve_response(response_id: int, db: Session = Depends(get_db), user: User = Depends(get_current_user)):
    try:
        data = update_response_status(db, user, response_id, action="approve")
        return {"status": "success", "message": "Response approved", "data": data}
    except ValueError as exc:
        raise HTTPException(status_code=404, detail=str(exc))


@router.post("/{response_id}/post")
def post_response(response_id: int, db: Session = Depends(get_db), user: User = Depends(get_current_user)):
    try:
        data = update_response_status(db, user, response_id, action="post")
        return {"status": "success", "message": "Response posted", "data": data}
    except ValueError as exc:
        raise HTTPException(status_code=404, detail=str(exc))
