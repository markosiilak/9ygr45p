#!/bin/bash

# Ticket Discovery Portal - Setup Script
# This script sets up the entire project with one command

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_header() {
    echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

# Check if Docker is installed
check_docker() {
    print_info "Checking Docker installation..."
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed. Please install Docker first."
        print_info "Visit: https://docs.docker.com/get-docker/"
        exit 1
    fi
    print_success "Docker is installed ($(docker --version))"
}

# Check if Docker Compose is installed
check_docker_compose() {
    print_info "Checking Docker Compose installation..."
    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        print_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi
    
    # Check which version is available
    if docker compose version &> /dev/null; then
        print_success "Docker Compose is installed ($(docker compose version | head -n 1))"
        DOCKER_COMPOSE_CMD="docker compose"
    else
        print_success "Docker Compose is installed ($(docker-compose --version))"
        DOCKER_COMPOSE_CMD="docker-compose"
    fi
}

# Check if Docker daemon is running
check_docker_daemon() {
    print_info "Checking if Docker daemon is running..."
    if ! docker info &> /dev/null; then
        print_error "Docker daemon is not running. Please start Docker Desktop or Docker daemon."
        exit 1
    fi
    print_success "Docker daemon is running"
}

# Setup environment file
setup_env() {
    print_info "Setting up environment file..."
    if [ ! -f .env ]; then
        if [ -f .env.example ]; then
            cp .env.example .env
            print_success "Created .env file from .env.example"
            print_warning "Please review and update .env file if needed"
        else
            print_error ".env.example file not found!"
            exit 1
        fi
    else
        print_info ".env file already exists, skipping..."
    fi
}

# Build and start Docker containers
start_containers() {
    print_info "Building and starting Docker containers..."
    print_info "This may take a few minutes on first run..."
    
    if $DOCKER_COMPOSE_CMD up -d --build; then
        print_success "Docker containers started successfully"
    else
        print_error "Failed to start Docker containers"
        exit 1
    fi
}

# Wait for services to be ready
wait_for_services() {
    print_info "Waiting for services to be ready..."
    sleep 5
    
    # Wait for backend to be ready
    print_info "Waiting for backend service..."
    local max_attempts=30
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if docker exec piletitasku-backend-1 php artisan --version &> /dev/null 2>&1; then
            print_success "Backend service is ready"
            break
        fi
        attempt=$((attempt + 1))
        sleep 2
    done
    
    if [ $attempt -eq $max_attempts ]; then
        print_warning "Backend service may not be fully ready, but continuing..."
    fi
}

# Run database migrations
run_migrations() {
    print_info "Running database migrations..."
    if $DOCKER_COMPOSE_CMD exec -T backend php artisan migrate --force; then
        print_success "Database migrations completed"
    else
        print_error "Failed to run migrations"
        exit 1
    fi
}

# Seed the database
seed_database() {
    print_info "Seeding database with initial data..."
    if $DOCKER_COMPOSE_CMD exec -T backend php artisan db:seed --force; then
        print_success "Database seeded successfully"
    else
        print_warning "Database seeding failed or already seeded"
    fi
}

# Display final information
display_info() {
    print_header "Setup Complete!"
    
    # Read URLs from .env file
    FRONTEND_PORT=$(grep "^FRONTEND_PORT=" .env 2>/dev/null | cut -d '=' -f2 || echo "3000")
    BACKEND_PORT=$(grep "^BACKEND_PORT=" .env 2>/dev/null | cut -d '=' -f2 || echo "8000")
    BACKEND_PUBLIC_URL=$(grep "^BACKEND_PUBLIC_URL=" .env 2>/dev/null | cut -d '=' -f2 || echo "http://localhost:8000")
    
    echo -e "${GREEN}Your application is now running!${NC}\n"
    echo -e "Frontend:     ${BLUE}http://localhost:${FRONTEND_PORT}${NC}"
    echo -e "Backend API:  ${BLUE}${BACKEND_PUBLIC_URL}${NC}"
    echo -e "API Docs:     ${BLUE}${BACKEND_PUBLIC_URL}/api${NC}\n"
    
    echo -e "${YELLOW}Test Accounts:${NC}"
    echo -e "  Admin:     admin@example.com / password"
    echo -e "  Organizer: organizer@example.com / password"
    echo -e "  Customer:  customer@example.com / password\n"
    
    echo -e "${YELLOW}Useful Commands:${NC}"
    echo -e "  View logs:        ${BLUE}$DOCKER_COMPOSE_CMD logs -f${NC}"
    echo -e "  Stop services:    ${BLUE}$DOCKER_COMPOSE_CMD down${NC}"
    echo -e "  Restart services: ${BLUE}$DOCKER_COMPOSE_CMD restart${NC}\n"
}

# Main execution
main() {
    print_header "Ticket Discovery Portal - Setup"
    
    check_docker
    check_docker_compose
    check_docker_daemon
    setup_env
    start_containers
    wait_for_services
    run_migrations
    seed_database
    display_info
    
    print_success "Setup completed successfully! 🎉"
}

# Run main function
main

