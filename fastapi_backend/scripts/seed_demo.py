"""Seed a demo business/user for local testing."""
from app.core.security import get_password_hash
from app.db.session import SessionLocal, Base, engine
from app.models.models import Business, User, UserRole

Base.metadata.create_all(bind=engine)

db = SessionLocal()
if not db.query(User).filter(User.email == "demo@aiautoreview.dev").first():
    business = Business(name="Demo Coffee")
    db.add(business)
    db.flush()
    user = User(
        business_id=business.id,
        name="Demo Admin",
        email="demo@aiautoreview.dev",
        hashed_password=get_password_hash("Password123!"),
        role=UserRole.admin,
    )
    db.add(user)
    db.commit()
    print("Seeded demo user: demo@aiautoreview.dev / Password123!")
else:
    print("Demo user already exists")
db.close()
