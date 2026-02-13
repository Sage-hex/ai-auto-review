from fastapi import APIRouter, Depends, HTTPException, Query
from sqlalchemy.orm import Session

from app.api.deps import get_current_user
from app.db.session import get_db
from app.models.models import User
from app.services.review_service import list_reviews, get_review_stats, sync_reviews, generate_response

router = APIRouter(prefix="/reviews", tags=["reviews"])


@router.get("")
def get_reviews(
    page: int = Query(default=1, ge=1),
    platform: str | None = None,
    rating: int | None = Query(default=None, ge=1, le=5),
    sentiment: str | None = None,
    response_status: str | None = None,
    db: Session = Depends(get_db),
    user: User = Depends(get_current_user),
):
    return {"status": "success", "data": list_reviews(db, user, page, platform, rating, sentiment, response_status)}


@router.get("/stats")
def stats(db: Session = Depends(get_db), user: User = Depends(get_current_user)):
    return {"status": "success", "data": get_review_stats(db, user)}


@router.post("/sync")
def sync(db: Session = Depends(get_db), user: User = Depends(get_current_user)):
    data = sync_reviews(db, user)
    return {"status": "success", "message": "Reviews synced", "data": data}


@router.post("/{review_id}/generate")
def generate(review_id: int, payload: dict, db: Session = Depends(get_db), user: User = Depends(get_current_user)):
    try:
        data = generate_response(db, user, review_id, payload.get("business_name"))
        return {"status": "success", "message": "Response generated", "data": data}
    except ValueError as exc:
        raise HTTPException(status_code=404, detail=str(exc))
