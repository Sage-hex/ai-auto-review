from functools import lru_cache
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    app_name: str = "AI Auto Review API"
    environment: str = "development"
    api_v1_prefix: str = "/api/v1"

    secret_key: str = "change-me-in-production"
    access_token_expire_minutes: int = 60 * 24

    database_url: str = "sqlite:///./fastapi_backend/aiautoreview.db"
    cors_origins: list[str] = ["http://localhost:5173"]

    model_config = SettingsConfigDict(env_file=".env", env_file_encoding="utf-8", extra="ignore")


@lru_cache
def get_settings() -> Settings:
    return Settings()
