 
# Personal Finance Management System

  A comprehensive Laravel-based web application for managing personal finances, built to demonstrate enterprise-grade development skills and FinTech domain knowledge. Fully containerized with Docker for
  easy setup and deployment.

  ## 🚀 Features
  Contact: [centyanita@gmail.com]

  ### Core Functionality
  - **Budget Planning & Tracking** - Create monthly/yearly budgets with category-wise allocation
  - **Expense Management** - Record and categorize daily expenses with receipt uploads
  - **Income Tracking** - Multiple income sources with recurring income support
  - **Financial Goals** - Set and track savings goals with progress visualization
  - **Financial Reports** - Comprehensive analytics and insights dashboards
  - **Enterprise features** they'd expect in their products
  ### Advanced Features
  - **Multi-Account Support** - Bank accounts, credit cards, savings accounts
  - **Bill Reminders** - Automated notifications for upcoming bills
  - **Investment Tracking** - Basic portfolio tracking and performance metrics
  - **Export/Import** - CSV/Excel data import/export functionality
  - **Mobile Responsive** - PWA-ready responsive designd more professional. 

  ## 🛠️ Tech Stack

  ### Backend
  - **Framework**: Laravel 10.x for Personal Finance Management System…
  - **Database**: MySQL 8.0
  - **Cache/Queue**: Redis
  - **Authentication**: Laravel                                                           │
  - **API**: RESTful API with comprehensive documentation
  - **Testing**: PHPUnit with Feature & Unit tests                                                                              
  ### Frontend
  - **Framework**: Vue.js 3 with Composition API
  - **UI Components**: Tailwind CSS + Headless UI
  - **Charts**: Chart.js for financial visualizations
  - **State Management**: Pinia
  - **Build Tool**: Vite

  ### DevOps & Infrastructure
  - **Containerization**: Docker & Docker Compose
  - **Web Server**: Nginx
  - **Process Manager**: Supervisor
  - **Code Quality**: PHP CS Fixer, ESLint, Prettier
  - **Documentation**: API documentation with Swagger/OpenAPI

  ## 📋 Prerequisites

  **Only Docker is required!** No need to install PHP, Node.js, MySQL, or any other dependencies locally.

  - **Docker** 20.0+
  - **Docker Compose** 2.0+
  - **Git**

  ## 🚦 Quick Start

  ### 1. Clone Repository
  ```bash
  git clone https://github.com/Centrine-O/personal-finance-manager.git
  cd personal-finance-manager

  2. Environment Setup

  # Copy environment file
  cp .env.example .env

  # Generate application key
  docker-compose run --rm app php artisan key:generate

  3. Start Application

  # Build and start all services
  docker-compose up -d

  # Run database migrations and seeders
  docker-compose exec app php artisan migrate --seed

  # Create storage symlink
  docker-compose exec app php artisan storage:link

  4. Access Application

  - Application: http://localhost:8000
  - API Documentation: http://localhost:8000/api/documentation
  - PHPMyAdmin: http://localhost:8080
  - Mailhog (Email Testing): http://localhost:8025

  🐳 Docker Services

  The application runs with the following services:

  | Service    | Description                           | Port |
  |------------|---------------------------------------|------|
  | app        | Laravel application (PHP 8.2 + Nginx) | 8000 |
  | mysql      | MySQL 8.0 database                    | 3306 |
  | redis      | Redis for caching and queues          | 6379 |
  | phpmyadmin | Database administration               | 8080 |
  | mailhog    | Email testing tool                    | 8025 |
  | node       | Node.js for frontend assets           | -    |

  🧰 Development Commands

  Application Management

  # Start services
  docker-compose up -d

  # Stop services
  docker-compose down

  # View logs
  docker-compose logs -f app

  # Restart specific service
  docker-compose restart app

  Laravel Commands

  # Run artisan commands
  docker-compose exec app php artisan migrate
  docker-compose exec app php artisan make:model Transaction

  # Install PHP packages
  docker-compose exec app composer install

  # Clear caches
  docker-compose exec app php artisan optimize:clear

  Frontend Development

  # Install npm packages
  docker-compose run --rm node npm install

  # Build assets for development
  docker-compose run --rm node npm run dev

  # Build for production
  docker-compose run --rm node npm run build

  # Watch for changes (development)
  docker-compose run --rm node npm run dev -- --watch

  🧪 Testing

  # Run all tests
  docker-compose exec app php artisan test

  # Run with coverage
  docker-compose exec app php artisan test --coverage

  # Run specific test suite
  docker-compose exec app php artisan test --filter=BudgetTest

  # Run tests in parallel
  docker-compose exec app php artisan test --parallel

  🗄️ Database Management

  # Run migrations
  docker-compose exec app php artisan migrate

  # Rollback migrations
  docker-compose exec app php artisan migrate:rollback

  # Seed database
  docker-compose exec app php artisan db:seed

  # Fresh migration with seeding
  docker-compose exec app php artisan migrate:fresh --seed

  # Database backup
  docker-compose exec mysql mysqldump -u root -ppassword finance_db > backup.sql

  📊 Monitoring & Debugging

  Container Health

  # Check running containers
  docker-compose ps

  # Monitor resource usage
  docker stats

  # Access container shell
  docker-compose exec app bash
  docker-compose exec mysql bash

  Application Logs

  # Laravel logs
  docker-compose exec app tail -f storage/logs/laravel.log

  # Nginx access logs
  docker-compose logs nginx

  # All service logs
  docker-compose logs -f

  🚀 Production Deployment

  Build Production Images

  # Build optimized production image
  docker-compose -f docker-compose.prod.yml build

  # Start production services
  docker-compose -f docker-compose.prod.yml up -d

  Production Optimizations

  # Inside container
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  composer install --optimize-autoloader --no-dev
  npm run build

  🔧 Development Workflow

  Hot Reloading

  The development setup includes:
  - PHP: Changes reflected immediately (no container rebuild needed)
  - Frontend: Vite hot module replacement for Vue.js
  - Database: Persistent volumes for data retention

  Code Quality

  # PHP CS Fixer
  docker-compose exec app ./vendor/bin/php-cs-fixer fix

  # ESLint
  docker-compose run --rm node npm run lint

  # Run all quality checks
  docker-compose exec app composer run-script quality

  📁 Docker Structure

  ├── docker/
  │   ├── app/
  │   │   ├── Dockerfile           # Main PHP application
  │   │   └── supervisord.conf     # Process management
  │   ├── nginx/
  │   │   └── default.conf         # Nginx configuration
  │   └── mysql/
  │       └── init.sql             # Database initialization
  ├── docker-compose.yml           # Development environment
  ├── docker-compose.prod.yml      # Production environment
  └── .dockerignore               # Docker ignore rules

  🔧 Customization

  Environment Variables

  Key variables in .env:
  DB_HOST=mysql
  REDIS_HOST=redis
  MAIL_HOST=mailhog
  APP_URL=http://localhost:8000

  Port Configuration

  To change default ports, update docker-compose.yml:
  services:
    app:
      ports:
        - "8080:80"  # Change from 8000 to 8080

  🤝 Contributing

  Setup for Contributors

  git clone <repo>
  cd personal-finance-manager
  cp .env.example .env
  docker-compose up -d
  docker-compose exec app php artisan migrate --seed

  📄 Troubleshooting

  Common Issues

  1. Port conflicts: Change ports in docker-compose.yml
  2. Permission issues: Run docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
  3. Database connection: Ensure MySQL container is running
  4. Composer issues: Run docker-compose exec app composer install

  Reset Everything

  docker-compose down -v
  docker-compose up -d --build
  docker-compose exec app php artisan migrate:fresh --seed

  👨‍💻 About

  Built by [Centrine] as a demonstration of full-stack Laravel development skills with enterprise-grade Docker containerization, focusing on FinTech applications and production-ready deployment practices.

  Key Learning Outcomes:
  - Docker & Container Orchestration - Production-ready containerization
  - Laravel Enterprise Patterns - Service layers, repositories, clean architecture
  - FinTech Domain Modeling - Complex financial calculations and data structures
  - API Design - RESTful APIs with comprehensive documentation
  - Testing Strategy - Unit, feature, and integration testing
  - DevOps Practices - CI/CD ready, environment management

  ---
  
  🎯 Alignment

  This project demonstrates skills directly relevant to:
  - ✅ Laravel Expertise - Advanced framework usage with best practices
  - ✅ Enterprise Architecture - Scalable, maintainable code structure
  - ✅ FinTech Domain - Financial calculations and data management
  - ✅ Database Design - Complex relationships and optimization
  - ✅ Testing Framework - PHPUnit implementation
  - ✅ Modern DevOps - Docker containerization for enterprise deployment

