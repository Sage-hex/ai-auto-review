from pydantic import BaseModel, EmailStr, Field


class RegisterRequest(BaseModel):
    business_name: str = Field(min_length=2, max_length=255)
    name: str = Field(min_length=2, max_length=255)
    email: EmailStr
    password: str = Field(min_length=8, max_length=128)


class LoginRequest(BaseModel):
    email: EmailStr
    password: str = Field(min_length=8, max_length=128)


class UserOut(BaseModel):
    id: int
    name: str
    email: EmailStr
    role: str
    business_id: int


class BusinessOut(BaseModel):
    id: int
    name: str


class AuthPayload(BaseModel):
    user: UserOut
    business: BusinessOut
    token: str


class APIResponse(BaseModel):
    status: str = "success"
    message: str
    data: AuthPayload
