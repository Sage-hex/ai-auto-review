from datetime import datetime
from typing import Optional
from pydantic import BaseModel


class ResponseData(BaseModel):
    id: int
    response_text: str
    status: str


class ReviewItem(BaseModel):
    id: int
    platform: str
    customer_name: str
    rating: int
    sentiment: str
    content: str
    review_date: datetime
    response: Optional[ResponseData] = None


class Pagination(BaseModel):
    current_page: int
    total_pages: int
    total: int


class ReviewsPayload(BaseModel):
    reviews: list[ReviewItem]
    pagination: Pagination


class ReviewsResponse(BaseModel):
    status: str = "success"
    data: ReviewsPayload


class ReviewStats(BaseModel):
    total_reviews: int
    average_rating: float
    responded_reviews: int
    pending_responses: int


class ReviewStatsResponse(BaseModel):
    status: str = "success"
    data: ReviewStats


class GenericResponse(BaseModel):
    status: str = "success"
    message: str
    data: dict | list | None = None
