# Ticket Discovery Portal

A full-stack ticket discovery portal built with **Nuxt.js 4** (Frontend) and **Laravel 12** (Backend).

## Project Requirements

Build a simple ticket discovery portal with a frontend and backend.

### Frontend
- Must be built with Nuxt.js
- Use TypeScript where possible
- Display a list of events from the backend
- Each event must have an image and be clickable, opening an event detail page
- Event page must show:
  - Title
  - Image (loads from URL)
  - Description
  - Ticket options with prices
  - "Buy" button (non-functional)

### Backend
- Use any backend framework (Laravel chosen)
- Provide a well-documented API endpoint to list events
- Events must include:
  - Title
  - Image URL
  - Description
  - One or more ticket types with prices

### Technical Requirements
- The system must run in Docker
- Make setup simple, ideally a single `docker compose up` command
- Follow Git best practices

### Notes
- No authentication or payment flow required
- Prioritize clarity and clean code over feature quantity

---

## Getting Started

### Prerequisites

- Docker & Docker Compose

### Quick Setup

```bash
docker compose up --build
```

Or use the setup script for a guided setup:
```bash
npm run setup
```

### Access URLs

- **Frontend:** http://localhost:3000
- **Backend API:** http://localhost:8000/api

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/events` | List all events |
| GET | `/api/events/{id}` | Get event details |

---

## Extra Features (Beyond Requirements)

The following features were implemented beyond the original requirements to demonstrate full-stack capabilities:

### Authentication System
- User registration and login
- JWT token-based API authentication via Laravel Sanctum
- Session management with encrypted cookies
- Login/Register pages with form validation

### Test Accounts

After seeding, these accounts are available:

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@example.com` | `password` |
| Organizer | `organizer@example.com` | `password` |
| Customer | `customer@example.com` | `password` |

### Extended API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register new user |
| POST | `/api/login` | Login user |
| GET | `/api/user` | Get current user (auth required) |
| POST | `/api/logout` | Logout user (auth required) |

### Security Hardening
- **Rate Limiting** - API and login endpoint throttling
- **CORS Protection** - Configurable allowed origins
- **Security Headers** - X-Content-Type-Options, X-Frame-Options, HSTS, etc.
- **Token Expiration** - Configurable Sanctum token lifetime (default: 24 hours)
- **Session Encryption** - Encrypted session data

### Internationalization (i18n)
- English and Estonian language support
- Language switcher in header
- Persisted language preference

### Image Optimization
- On-the-fly image resizing
- Responsive images with srcset
- Lazy loading with NuxtImg

---

## Tech Stack

### Frontend
- **Nuxt.js 4** with TypeScript
- **Nuxt UI 4** component library
- **Tailwind CSS 4** for styling
- **Swiper** for image carousels

### Backend
- **Laravel 12** (PHP 8.2+)
- **Laravel Sanctum** for API authentication
- **SQLite** database
- **Docker** containerization

---

## Project Structure

```
piletitasku/
├── frontend/          # Nuxt.js application
│   ├── app/
│   │   ├── components/  # Vue components
│   │   ├── composables/ # Vue composables (useAuth, useApi, etc.)
│   │   ├── layouts/     # Page layouts
│   │   ├── pages/       # File-based routing
│   │   ├── locales/     # Translation files (en, et)
│   │   └── utils/       # Utility functions
│   └── server/          # Nuxt server API routes
├── backend/           # Laravel application
│   ├── app/
│   │   ├── Http/Controllers/  # API controllers
│   │   ├── Http/Middleware/   # Security middleware
│   │   └── Models/            # Eloquent models
│   ├── database/              # Migrations & seeders
│   └── routes/                # API routes
├── docker-compose.yml   # Docker orchestration
├── package.json         # Root npm scripts
├── setup.sh             # Linux/macOS setup script
└── setup.bat            # Windows setup script
```

---

## NPM Scripts

| Script | Description |
|--------|-------------|
| `npm run setup` | Run full setup (Linux/macOS) |
| `npm run setup:windows` | Run full setup (Windows) |
| `npm run dev:frontend` | Start frontend dev server |
| `npm run dev:backend` | Start backend dev server |
| `npm run docker:up` | Start Docker containers |
| `npm run docker:down` | Stop Docker containers |
| `npm run docker:logs` | View Docker logs |
| `npm run migrate` | Run database migrations |
| `npm run seed` | Run database seeder |
| `npm run migrate:fresh` | Fresh migration with seeding |

---

## Docker Services

| Service | Technology | Port |
|---------|------------|------|
| `backend` | Laravel + PHP 8.4 + SQLite | 8000 |
| `frontend` | Nuxt.js + Node.js 20 | 3000 |

Both services have hot-reload enabled via volume mounts.

---

## Environment Variables

### Docker Configuration
| Variable | Description | Default |
|----------|-------------|---------|
| `BACKEND_PORT` | Backend service port | 8000 |
| `FRONTEND_PORT` | Frontend service port | 3000 |
| `BACKEND_PUBLIC_URL` | Public backend URL | http://localhost:8000 |
| `FRONTEND_API_BASE_URL` | API URL for client-side | http://localhost:8000 |

### Security Configuration
| Variable | Description | Default |
|----------|-------------|---------|
| `CORS_ALLOWED_ORIGINS` | Allowed CORS origins | http://localhost:3000 |
| `SANCTUM_TOKEN_EXPIRATION` | Token lifetime (minutes) | 1440 (24 hours) |
| `SESSION_ENCRYPT` | Encrypt sessions | true |

---

## Author

**Marko Siilak** - [marko@siilak.com](mailto:marko@siilak.com)

## License

ISC


## Live Demo

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000/api
- **API Documentation**: See routes/api.php for endpoint details

## Architecture

### Backend (Laravel 12)
- RESTful API with resource controllers
- SQLite database for development
- Sanctum authentication
- Role-based access control (RBAC)
- Image upload handling

### Frontend (Nuxt 4)
- Server-side rendering (SSR)
- TypeScript for type safety
- Tailwind CSS for styling
- Composable-based state management
- Responsive design with mobile-first approach

---

Built with ❤️ by Marko Siilak
