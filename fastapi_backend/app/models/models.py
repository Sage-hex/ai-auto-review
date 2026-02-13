from datetime import datetime
from enum import Enum

from sqlalchemy import String, Integer, DateTime, ForeignKey, Text, Enum as SQLEnum
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.db.session import Base


class UserRole(str, Enum):
    admin = "admin"
    manager = "manager"
    support = "support"
    viewer = "viewer"


class ResponseStatus(str, Enum):
    pending = "pending"
    approved = "approved"
    posted = "posted"


class Business(Base):
    __tablename__ = "businesses"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, index=True)
    name: Mapped[str] = mapped_column(String(255), unique=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, nullable=False)

    users: Mapped[list["User"]] = relationship(back_populates="business")
    reviews: Mapped[list["Review"]] = relationship(back_populates="business")


class User(Base):
    __tablename__ = "users"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, index=True)
    business_id: Mapped[int] = mapped_column(ForeignKey("businesses.id"), nullable=False)
    name: Mapped[str] = mapped_column(String(255), nullable=False)
    email: Mapped[str] = mapped_column(String(255), unique=True, index=True, nullable=False)
    hashed_password: Mapped[str] = mapped_column(String(255), nullable=False)
    role: Mapped[UserRole] = mapped_column(SQLEnum(UserRole), default=UserRole.admin, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, nullable=False)

    business: Mapped[Business] = relationship(back_populates="users")


class Review(Base):
    __tablename__ = "reviews"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, index=True)
    business_id: Mapped[int] = mapped_column(ForeignKey("businesses.id"), nullable=False)
    platform: Mapped[str] = mapped_column(String(50), nullable=False)
    customer_name: Mapped[str] = mapped_column(String(255), nullable=False)
    rating: Mapped[int] = mapped_column(Integer, nullable=False)
    sentiment: Mapped[str] = mapped_column(String(30), default="neutral", nullable=False)
    content: Mapped[str] = mapped_column(Text, nullable=False)
    review_date: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, nullable=False)

    business: Mapped[Business] = relationship(back_populates="reviews")
    response: Mapped["Response"] = relationship(back_populates="review", uselist=False)


class Response(Base):
    __tablename__ = "responses"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, index=True)
    review_id: Mapped[int] = mapped_column(ForeignKey("reviews.id"), unique=True, nullable=False)
    response_text: Mapped[str] = mapped_column(Text, nullable=False)
    status: Mapped[ResponseStatus] = mapped_column(SQLEnum(ResponseStatus), default=ResponseStatus.pending, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, nullable=False)

    review: Mapped[Review] = relationship(back_populates="response")
