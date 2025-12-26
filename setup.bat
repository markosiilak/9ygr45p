@echo off
REM Ticket Discovery Portal - Setup Script for Windows
REM This script sets up the entire project with one command

setlocal enabledelayedexpansion

echo.
echo ========================================
echo   Ticket Discovery Portal - Setup
echo ========================================
echo.

REM Check if Docker is installed
echo [INFO] Checking Docker installation...
docker --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker is not installed. Please install Docker Desktop first.
    echo [INFO] Visit: https://docs.docker.com/get-docker/
    exit /b 1
)
echo [OK] Docker is installed

REM Check if Docker Compose is available
echo [INFO] Checking Docker Compose installation...
docker compose version >nul 2>&1
if errorlevel 1 (
    docker-compose --version >nul 2>&1
    if errorlevel 1 (
        echo [ERROR] Docker Compose is not installed. Please install Docker Compose first.
        exit /b 1
    )
    set DOCKER_COMPOSE_CMD=docker-compose
) else (
    set DOCKER_COMPOSE_CMD=docker compose
)
echo [OK] Docker Compose is installed

REM Check if Docker daemon is running
echo [INFO] Checking if Docker daemon is running...
docker info >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker daemon is not running. Please start Docker Desktop.
    exit /b 1
)
echo [OK] Docker daemon is running

REM Setup environment file
echo [INFO] Setting up environment file...
if not exist .env (
    if exist .env.example (
        copy .env.example .env >nul
        echo [OK] Created .env file from .env.example
        echo [WARNING] Please review and update .env file if needed
    ) else (
        echo [ERROR] .env.example file not found!
        exit /b 1
    )
) else (
    echo [INFO] .env file already exists, skipping...
)

REM Build and start Docker containers
echo.
echo [INFO] Building and starting Docker containers...
echo [INFO] This may take a few minutes on first run...
%DOCKER_COMPOSE_CMD% up -d --build
if errorlevel 1 (
    echo [ERROR] Failed to start Docker containers
    exit /b 1
)
echo [OK] Docker containers started successfully

REM Wait for services to be ready
echo [INFO] Waiting for services to be ready...
timeout /t 5 /nobreak >nul

REM Run database migrations
echo [INFO] Running database migrations...
%DOCKER_COMPOSE_CMD% exec -T backend php artisan migrate --force
if errorlevel 1 (
    echo [ERROR] Failed to run migrations
    exit /b 1
)
echo [OK] Database migrations completed

REM Seed the database
echo [INFO] Seeding database with initial data...
%DOCKER_COMPOSE_CMD% exec -T backend php artisan db:seed --force
if errorlevel 1 (
    echo [WARNING] Database seeding failed or already seeded
) else (
    echo [OK] Database seeded successfully
)

REM Display final information
echo.
echo ========================================
echo   Setup Complete!
echo ========================================
echo.
echo Your application is now running!
echo.
echo Frontend:     http://localhost:3000
echo Backend API:  http://localhost:8000
echo API Docs:     http://localhost:8000/api
echo.
echo Test Accounts:
echo   Admin:     admin@example.com / password
echo   Organizer: organizer@example.com / password
echo   Customer:  customer@example.com / password
echo.
echo Useful Commands:
echo   View logs:        %DOCKER_COMPOSE_CMD% logs -f
echo   Stop services:    %DOCKER_COMPOSE_CMD% down
echo   Restart services: %DOCKER_COMPOSE_CMD% restart
echo.
echo Setup completed successfully!
echo.

endlocal

