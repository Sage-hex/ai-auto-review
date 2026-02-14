from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session

from app.db.session import get_db
from app.schemas.auth import RegisterRequest, LoginRequest
from app.services.auth_service import register_user, login_user

router = APIRouter(prefix="/auth", tags=["auth"])


def _auth_response(message: str, user, business, token):
    return {
        "status": "success",
        "message": message,
        "data": {
            "user": {
                "id": user.id,
                "name": user.name,
                "email": user.email,
                "role": user.role.value if hasattr(user.role, "value") else user.role,
                "business_id": user.business_id,
            },
            "business": {"id": business.id, "name": business.name},
            "token": token,
        },
    }


@router.post("/register")
def register(payload: RegisterRequest, db: Session = Depends(get_db)):
    user, business, token = register_user(db, payload)
    return _auth_response("Registration successful", user, business, token)


@router.post("/login")
def login(payload: LoginRequest, db: Session = Depends(get_db)):
    user, business, token = login_user(db, payload)
    return _auth_response("Login successful", user, business, token)
