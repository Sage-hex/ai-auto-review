from datetime import datetime, timedelta
from random import choice, randint

from sqlalchemy import func
from sqlalchemy.orm import Session, joinedload

from app.models.models import Review, Response, ResponseStatus, User


SAMPLE_REVIEWS = [
    "Amazing service and very friendly staff!",
    "The experience was okay, room for improvement.",
    "I had a disappointing experience with the support team.",
    "Fast service, clean location, and fair price.",
    "Great quality and excellent communication.",
]


def _serialize_review(review: Review) -> dict:
    response = None
    if review.response:
        response = {
            "id": review.response.id,
            "response_text": review.response.response_text,
            "status": review.response.status.value,
        }

    return {
        "id": review.id,
        "platform": review.platform,
        "customer_name": review.customer_name,
        "rating": review.rating,
        "sentiment": review.sentiment,
        "content": review.content,
        "review_date": review.review_date,
        "response": response,
    }


def list_reviews(db: Session, user: User, page: int, platform: str | None, rating: int | None, sentiment: str | None, response_status: str | None):
    query = db.query(Review).options(joinedload(Review.response)).filter(Review.business_id == user.business_id)

    if platform:
        query = query.filter(Review.platform == platform)
    if rating:
        query = query.filter(Review.rating == rating)
    if sentiment:
        query = query.filter(Review.sentiment == sentiment)
    if response_status:
        query = query.join(Response, isouter=True).filter(Response.status == response_status)

    per_page = 10
    total = query.count()
    reviews = (
        query.order_by(Review.review_date.desc())
        .offset((page - 1) * per_page)
        .limit(per_page)
        .all()
    )

    return {
        "reviews": [_serialize_review(r) for r in reviews],
        "pagination": {
            "current_page": page,
            "total_pages": max((total + per_page - 1) // per_page, 1),
            "total": total,
        },
    }


def get_review_stats(db: Session, user: User):
    total_reviews = db.query(func.count(Review.id)).filter(Review.business_id == user.business_id).scalar() or 0
    avg_rating = db.query(func.avg(Review.rating)).filter(Review.business_id == user.business_id).scalar() or 0

    responded_reviews = (
        db.query(func.count(Response.id))
        .join(Review, Review.id == Response.review_id)
        .filter(Review.business_id == user.business_id, Response.status.in_([ResponseStatus.approved, ResponseStatus.posted]))
        .scalar()
        or 0
    )

    pending_responses = (
        db.query(func.count(Response.id))
        .join(Review, Review.id == Response.review_id)
        .filter(Review.business_id == user.business_id, Response.status == ResponseStatus.pending)
        .scalar()
        or 0
    )

    return {
        "total_reviews": total_reviews,
        "average_rating": float(avg_rating),
        "responded_reviews": responded_reviews,
        "pending_responses": pending_responses,
    }


def sync_reviews(db: Session, user: User):
    for i in range(5):
        rating = randint(1, 5)
        sentiment = "positive" if rating >= 4 else "neutral" if rating == 3 else "negative"
        review = Review(
            business_id=user.business_id,
            platform=choice(["google", "facebook", "yelp"]),
            customer_name=f"Customer {randint(100, 999)}",
            rating=rating,
            sentiment=sentiment,
            content=choice(SAMPLE_REVIEWS),
            review_date=datetime.utcnow() - timedelta(hours=i),
        )
        db.add(review)

    db.commit()
    return {"synced": 5}


def generate_response(db: Session, user: User, review_id: int, business_name: str | None):
    review = db.query(Review).filter(Review.id == review_id, Review.business_id == user.business_id).first()
    if not review:
        raise ValueError("Review not found")

    response_text = (
        f"Thank you for your feedback, {review.customer_name}. "
        f"We appreciate you reviewing {business_name or 'our business'} and will keep improving your experience."
    )
    existing = db.query(Response).filter(Response.review_id == review.id).first()
    if existing:
        existing.response_text = response_text
        existing.status = ResponseStatus.pending
        response = existing
    else:
        response = Response(review_id=review.id, response_text=response_text, status=ResponseStatus.pending)
        db.add(response)

    db.commit()
    db.refresh(response)
    return {"id": response.id, "response_text": response.response_text, "status": response.status.value}


def pending_responses(db: Session, user: User):
    rows = (
        db.query(Response)
        .join(Review, Review.id == Response.review_id)
        .options(joinedload(Response.review))
        .filter(Review.business_id == user.business_id, Response.status == ResponseStatus.pending)
        .all()
    )
    return [
        {
            "id": r.id,
            "response_text": r.response_text,
            "status": r.status.value,
            "review": {
                "id": r.review.id,
                "platform": r.review.platform,
                "customer_name": r.review.customer_name,
                "rating": r.review.rating,
            },
        }
        for r in rows
    ]


def update_response_status(db: Session, user: User, response_id: int, action: str, response_text: str | None = None):
    response = (
        db.query(Response)
        .join(Review, Review.id == Response.review_id)
        .filter(Response.id == response_id, Review.business_id == user.business_id)
        .first()
    )
    if not response:
        raise ValueError("Response not found")

    if response_text is not None:
        response.response_text = response_text
    if action == "approve":
        response.status = ResponseStatus.approved
    elif action == "post":
        response.status = ResponseStatus.posted

    db.commit()
    db.refresh(response)
    return {"id": response.id, "status": response.status.value, "response_text": response.response_text}
