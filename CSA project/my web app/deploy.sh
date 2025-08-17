#!/bin/bash

# Secure Web Application Deployment Script
# This script automates the deployment process for the secure web application

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to generate secure password
generate_password() {
    openssl rand -base64 32 | tr -d "=+/" | cut -c1-25
}

# Function to check prerequisites
check_prerequisites() {
    print_status "Checking prerequisites..."
    
    if ! command_exists docker; then
        print_error "Docker is not installed. Please install Docker first."
        exit 1
    fi
    
    if ! command_exists docker-compose; then
        print_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi
    
    if ! docker info >/dev/null 2>&1; then
        print_error "Docker is not running. Please start Docker first."
        exit 1
    fi
    
    print_success "Prerequisites check passed"
}

# Function to setup environment
setup_environment() {
    print_status "Setting up environment..."
    
    if [ ! -f .env ]; then
        if [ -f env.example ]; then
            cp env.example .env
            print_success "Created .env file from template"
        else
            print_error "env.example file not found"
            exit 1
        fi
    else
        print_warning ".env file already exists. Skipping creation."
    fi
    
    # Generate secure passwords if not set
    if grep -q "your_secure_root_password_here" .env; then
        MYSQL_ROOT_PASSWORD=$(generate_password)
        sed -i "s/your_secure_root_password_here/$MYSQL_ROOT_PASSWORD/" .env
        print_success "Generated secure MySQL root password"
    fi
    
    if grep -q "your_secure_password_here" .env; then
        MYSQL_PASSWORD=$(generate_password)
        sed -i "s/your_secure_password_here/$MYSQL_PASSWORD/" .env
        print_success "Generated secure MySQL user password"
    fi
    
    if grep -q "your_secure_redis_password_here" .env; then
        REDIS_PASSWORD=$(generate_password)
        sed -i "s/your_secure_redis_password_here/$REDIS_PASSWORD/" .env
        print_success "Generated secure Redis password"
    fi
    
    if grep -q "your_session_secret_here" .env; then
        SESSION_SECRET=$(generate_password)
        sed -i "s/your_session_secret_here/$SESSION_SECRET/" .env
        print_success "Generated secure session secret"
    fi
    
    if grep -q "your_csrf_secret_here" .env; then
        CSRF_SECRET=$(generate_password)
        sed -i "s/your_csrf_secret_here/$CSRF_SECRET/" .env
        print_success "Generated secure CSRF secret"
    fi
}

# Function to create necessary directories
create_directories() {
    print_status "Creating necessary directories..."
    
    mkdir -p logs
    mkdir -p uploads
    mkdir -p ssl
    
    # Set proper permissions
    chmod 755 logs uploads ssl
    chmod 644 .env
    
    print_success "Directories created with proper permissions"
}

# Function to build and start containers
deploy_containers() {
    print_status "Building and starting containers..."
    
    # Stop any existing containers
    docker-compose down 2>/dev/null || true
    
    # Build and start containers
    docker-compose up -d --build
    
    print_success "Containers started successfully"
}

# Function to wait for services to be ready
wait_for_services() {
    print_status "Waiting for services to be ready..."
    
    # Wait for MySQL
    print_status "Waiting for MySQL..."
    timeout=60
    while [ $timeout -gt 0 ]; do
        if docker-compose exec -T mysql mysqladmin ping -h localhost --silent 2>/dev/null; then
            print_success "MySQL is ready"
            break
        fi
        sleep 2
        timeout=$((timeout - 2))
    done
    
    if [ $timeout -le 0 ]; then
        print_error "MySQL failed to start within timeout"
        exit 1
    fi
    
    # Wait for Redis
    print_status "Waiting for Redis..."
    timeout=30
    while [ $timeout -gt 0 ]; do
        if docker-compose exec -T redis redis-cli ping 2>/dev/null | grep -q "PONG"; then
            print_success "Redis is ready"
            break
        fi
        sleep 1
        timeout=$((timeout - 1))
    done
    
    if [ $timeout -le 0 ]; then
        print_error "Redis failed to start within timeout"
        exit 1
    fi
    
    # Wait for web application
    print_status "Waiting for web application..."
    timeout=60
    while [ $timeout -gt 0 ]; do
        if curl -f http://localhost/ >/dev/null 2>&1; then
            print_success "Web application is ready"
            break
        fi
        sleep 2
        timeout=$((timeout - 2))
    done
    
    if [ $timeout -le 0 ]; then
        print_error "Web application failed to start within timeout"
        exit 1
    fi
}

# Function to display deployment information
display_info() {
    print_success "Deployment completed successfully!"
    echo
    echo "=========================================="
    echo "           DEPLOYMENT SUMMARY"
    echo "=========================================="
    echo
    echo "🌐 Application URL: http://localhost"
    echo "🔒 Admin Dashboard: http://localhost/admin/security-dashboard.php"
    echo
    echo "👤 Default Admin Credentials:"
    echo "   Username: admin"
    echo "   Password: Admin@123"
    echo
    echo "⚠️  IMPORTANT: Change the default admin password immediately!"
    echo
    echo "📊 Service Status:"
    docker-compose ps
    echo
    echo "📝 Useful Commands:"
    echo "   View logs: docker-compose logs -f"
    echo "   Stop services: docker-compose down"
    echo "   Restart services: docker-compose restart"
    echo "   Update application: ./deploy.sh --update"
    echo
    echo "🔧 Configuration:"
    echo "   Environment file: .env"
    echo "   Database: localhost:3306"
    echo "   Redis: localhost:6379"
    echo
    echo "📚 Documentation: README.md"
    echo "=========================================="
}

# Function to update application
update_application() {
    print_status "Updating application..."
    
    # Pull latest changes
    git pull origin main 2>/dev/null || print_warning "Could not pull latest changes"
    
    # Rebuild and restart containers
    docker-compose down
    docker-compose up -d --build
    
    print_success "Application updated successfully"
}

# Function to show help
show_help() {
    echo "Secure Web Application Deployment Script"
    echo
    echo "Usage: $0 [OPTIONS]"
    echo
    echo "Options:"
    echo "  --help, -h     Show this help message"
    echo "  --update, -u   Update existing deployment"
    echo "  --clean, -c    Clean deployment (remove all containers and volumes)"
    echo
    echo "Examples:"
    echo "  $0              # Deploy application"
    echo "  $0 --update     # Update existing deployment"
    echo "  $0 --clean      # Clean deployment"
}

# Function to clean deployment
clean_deployment() {
    print_warning "This will remove all containers, volumes, and data!"
    read -p "Are you sure you want to continue? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        print_status "Cleaning deployment..."
        docker-compose down -v --remove-orphans
        docker system prune -f
        print_success "Deployment cleaned successfully"
    else
        print_status "Clean operation cancelled"
    fi
}

# Main script logic
main() {
    echo "=========================================="
    echo "    Secure Web Application Deployment"
    echo "=========================================="
    echo
    
    # Parse command line arguments
    case "${1:-}" in
        --help|-h)
            show_help
            exit 0
            ;;
        --update|-u)
            update_application
            exit 0
            ;;
        --clean|-c)
            clean_deployment
            exit 0
            ;;
        "")
            # Default deployment
            ;;
        *)
            print_error "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
    
    # Run deployment steps
    check_prerequisites
    setup_environment
    create_directories
    deploy_containers
    wait_for_services
    display_info
}

# Run main function with all arguments
main "$@"
