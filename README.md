# AI Auto Review - Review Response Automation SaaS Platform

AI Auto Review is a modern, advanced AI-powered Review Response Automation SaaS platform designed for business administrators and their teams. The platform supports multiple businesses with multi-tenant architecture, subscription plans with free trials, role-based access control, and integrations with popular review platforms (Google, Yelp, Facebook).

## Features

- **Multi-tenant Architecture**: Support for multiple businesses with isolated data
- **User Role Management**: Admin, Manager, Support, and Viewer roles with appropriate permissions
- **Subscription Plans**: Free trial, Basic, and Professional plans with feature gating
- **Review Platform Integrations**: Connect with Google, Yelp, and Facebook
- **AI-Generated Responses**: Automatically generate professional responses to reviews using OpenAI GPT
- **Response Approval Workflow**: Review, edit, and approve AI-generated responses before posting
- **Analytics Dashboard**: Track review metrics and sentiment analysis
- **Scheduled Automation**: Automatically fetch new reviews and generate responses

## Tech Stack

### Frontend
- React + Vite
- React Router for navigation
- TailwindCSS for styling
- Axios for API requests
- Heroicons for icons

### Backend
- Vanilla PHP REST API
- MySQL Database
- JWT Authentication
- OpenAI GPT Integration

## Project Structure

### Frontend
```
src/
├── assets/         # Static assets
├── components/     # React components
│   ├── auth/       # Authentication components
│   ├── common/     # Shared components
│   ├── dashboard/  # Dashboard components
│   ├── layout/     # Layout components
│   ├── platforms/  # Platform integration components
│   ├── reviews/    # Review management components
│   ├── subscription/ # Subscription management components
│   └── users/      # User management components
├── contexts/       # React context providers
├── hooks/          # Custom React hooks
├── pages/          # Page components
├── services/       # API services
└── utils/          # Utility functions
```

### Backend
```
backend/
├── api/            # API endpoints
│   └── endpoints/  # Endpoint handlers
├── config/         # Configuration files
├── core/           # Core functionality
├── cron/           # Scheduled tasks
├── database/       # Database schema and migrations
├── models/         # Data models
├── services/       # Business logic services
└── utils/          # Utility functions
```

## Getting Started

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Node.js 16 or higher
- XAMPP or similar local server environment

### Installation

1. Clone the repository
```bash
git clone https://github.com/yourusername/AiAutoReview.git
cd AiAutoReview
```

2. Install frontend dependencies
```bash
npm install
```

3. Create MySQL database
```sql
CREATE DATABASE ai_auto_review;
```

4. Import database schema
```bash
mysql -u root -p ai_auto_review < backend/database/schema.sql
```

5. Configure backend
   - Update database credentials in `backend/config/database.php`
   - Set your OpenAI API key in `backend/config/config.php`

6. Start development server
```bash
npm run dev
```

7. Set up cron jobs for automation (optional)
```bash
# Hourly review sync
0 * * * * php /path/to/AiAutoReview/backend/cron/sync_reviews.php

# Response generation every 2 hours
0 */2 * * * php /path/to/AiAutoReview/backend/cron/generate_responses.php
```

## API Documentation

The backend provides a RESTful API for the frontend to interact with. Key endpoints include:

- **Authentication**
  - `POST /api/login` - User login
  - `POST /api/register` - User and business registration

- **Business Management**
  - `GET /api/business` - Get business profile
  - `PUT /api/business` - Update business profile

- **User Management**
  - `GET /api/users` - List users
  - `POST /api/users` - Create user
  - `PUT /api/users/{id}` - Update user
  - `DELETE /api/users/{id}` - Delete user

- **Review Management**
  - `GET /api/reviews` - List reviews
  - `GET /api/reviews/{id}` - Get review details
  - `POST /api/reviews/sync` - Sync reviews from platforms
  - `POST /api/reviews/{id}/generate` - Generate AI response

- **Response Management**
  - `PUT /api/responses/{id}` - Update response
  - `POST /api/responses/{id}/approve` - Approve response
  - `POST /api/responses/{id}/post` - Post response to platform

- **Subscription Management**
  - `GET /api/subscription` - Get subscription details
  - `POST /api/subscription/upgrade` - Upgrade subscription plan

- **Platform Integration**
  - `GET /api/platforms` - Get platform integrations
  - `POST /api/platforms/{platform}` - Connect platform
  - `DELETE /api/platforms/{platform}` - Disconnect platform

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgements

- [OpenAI](https://openai.com/) for the GPT API
- [React](https://reactjs.org/) and [Vite](https://vitejs.dev/) for the frontend framework
- [TailwindCSS](https://tailwindcss.com/) for the styling framework

---

## FastAPI Backend (New)

A production-style FastAPI backend is now included in `fastapi_backend/` with proper separation of concerns (routers/services/models/db/core).

### Run FastAPI backend

```bash
cd fastapi_backend
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --reload --port 8000
```

### Frontend API base URL

Set this in `.env` (repo root) so React uses FastAPI:

```env
VITE_API_BASE_URL=http://localhost:8000/api/v1
```

### Database setup help

See beginner guide: `docs/DATABASE_CONNECTION_GUIDE.md`.
