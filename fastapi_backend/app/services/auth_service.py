from fastapi import HTTPException, status
from sqlalchemy.orm import Session

from app.core.security import get_password_hash, verify_password, create_access_token
from app.models.models import User, Business, UserRole
from app.schemas.auth import RegisterRequest, LoginRequest


def register_user(db: Session, payload: RegisterRequest):
    existing_user = db.query(User).filter(User.email == payload.email).first()
    if existing_user:
        raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail="Email already in use")

    business = Business(name=payload.business_name)
    db.add(business)
    db.flush()

    user = User(
        business_id=business.id,
        name=payload.name,
        email=payload.email,
        hashed_password=get_password_hash(payload.password),
        role=UserRole.admin,
    )
    db.add(user)
    db.commit()
    db.refresh(user)
    db.refresh(business)

    token = create_access_token(str(user.id))
    return user, business, token


def login_user(db: Session, payload: LoginRequest):
    user = db.query(User).filter(User.email == payload.email).first()
    if not user or not verify_password(payload.password, user.hashed_password):
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid credentials")

    business = db.get(Business, user.business_id)
    token = create_access_token(str(user.id))
    return user, business, token
