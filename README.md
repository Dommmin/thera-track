# Symfony Learning Path: From Junior to Senior

This repository contains a comprehensive learning path for Symfony framework, guiding you from junior to senior level development. The project is built using Symfony 7.3 and includes Docker configuration for easy development.

## Prerequisites

- Docker and Docker Compose
- Git
- Basic PHP knowledge
- Basic understanding of OOP principles

## Initial Setup

1. Clone the repository
2. Copy `.env.example` to `.env` and adjust the values
3. Run the Docker containers:
```bash
docker compose up -d
```

## Learning Path

### 1. Understanding Symfony Structure and Configuration

#### Basic Directory Structure
- `config/` - Configuration files
- `src/` - Application source code
- `templates/` - Twig templates
- `public/` - Publicly accessible files
- `var/` - Cache, logs, and other temporary files
- `tests/` - Test files

#### Configuration Files
- `config/services.yaml` - Service definitions
- `config/packages/` - Package configurations
- `config/routes.yaml` - Route definitions

### 2. Dependency Injection

Learn about:
- Service Container
- Service Configuration
- Service Tags
- Service Autowiring
- Service Aliases

Commands:
```bash
# View all services
php bin/console debug:container

# View specific service
php bin/console debug:container app.service_name
```

### 3. Doctrine ORM and Database Management

#### Entity Management
```bash
# Create User entity
php bin/console make:user

# Create other entities
php bin/console make:entity

# Create migrations
php bin/console make:migration

# Run migrations
php bin/console doctrine:migrations:migrate
```

#### Advanced Doctrine Features
- Entity Relationships (OneToOne, OneToMany, ManyToMany)
- Entity Validation
- Custom Repository Classes
- DQL and Query Builder
- Doctrine Events

### 4. Data Fixtures

```bash
# Install DoctrineFixturesBundle
composer require --dev orm-fixtures

# Create fixtures
php bin/console make:fixture

# Load fixtures
php bin/console doctrine:fixtures:load
```

### 5. Symfony Messenger

```bash
# Install Messenger
composer require symfony/messenger

# Create message
php bin/console make:message

# Create message handler
php bin/console make:message-handler
```

Learn about:
- Message Bus
- Message Handlers
- Transport Configuration (Redis, RabbitMQ)
- Message Serialization
- Retry Strategies

### 6. Events and Event Listeners

```bash
# Create event listener
php bin/console make:listener

# Create event subscriber
php bin/console make:subscriber
```

Learn about:
- Event Dispatcher
- Event Subscribers
- Event Listeners
- Custom Events

### 7. Mailer

```bash
# Install Mailer
composer require symfony/mailer
```

Learn about:
- Email Configuration
- HTML and Text Emails
- Attachments
- Email Templates
- Mailer Events

### 8. API Development

```bash
# Install Serializer Component
composer require symfony/serializer

# Install JWT Authentication (optional)
composer require lexik/jwt-authentication-bundle
```

Learn about:
- REST API Design Principles
- Controller Response Types:
  - JSON Response
  - XML Response
  - Custom Response Types
- Serialization:
  - JSON Serialization
  - XML Serialization
  - Custom Normalizers
  - Serialization Groups
  - Serialization Context
- API Versioning
- API Documentation (OpenAPI/Swagger)
- API Security:
  - JWT Authentication
  - API Keys
  - OAuth2
- Error Handling:
  - Custom Exception Listeners
  - API Error Responses
  - Validation Error Formatting
- API Testing:
  - Functional Tests
  - API Client Testing
  - Response Assertions

Example API Controller:
```php
#[Route('/api/v1/products', name: 'api_products_list', methods: ['GET'])]
public function list(Request $request): JsonResponse
{
    $products = $this->productRepository->findAll();

    return $this->json(
        $products,
        Response::HTTP_OK,
        [],
        ['groups' => 'product:list']
    );
}
```

### 9. Security and Voters

```bash
# Install Security Bundle
composer require symfony/security-bundle

# Create voter
php bin/console make:voter
```

Learn about:
- Authentication
- Authorization
- Voters
- Access Control
- Security Events

### 10. Caching

```bash
# Install Cache Component
composer require symfony/cache
```

Learn about:
- Cache Configuration
- Cache Pools
- Cache Tags
- Cache Invalidation
- Cache Warmers

### 11. DTOs and Request Data Mapping

Learn about the new Symfony 6.3+ feature for mapping request data to typed objects:
- `#[MapRequestPayload]` attribute
- `#[MapQueryString]` attribute
- Validation integration
- Custom value resolvers

### 12. UUID and DDD Concepts

```bash
# Install UUID Component
composer require symfony/uid
```

Learn about:
- UUID Generation
- Domain-Driven Design basics
- CQRS patterns
- Event Sourcing basics

### 13. Authentication and Registration

```bash
# Create login form
php bin/console make:auth

# Create registration form
php bin/console make:registration-form
```

Learn about:
- Form Security
- CSRF Protection
- Password Hashing
- Remember Me Functionality
- Two-Factor Authentication

### 14. Advanced Doctrine

```bash
# Create custom repository
php bin/console make:repository

# Create custom DQL function
php bin/console make:dql-function
```

Learn about:
- Custom Repository Methods:
  - Complex Query Builder
  - DQL Functions
  - Native SQL Queries
- Query Optimization:
  - N+1 Problem Solutions
  - Eager vs Lazy Loading
  - Query Caching
  - Index Optimization
- Doctrine Events:
  - Lifecycle Events (prePersist, preUpdate, etc.)
  - Custom Event Listeners
  - Event Subscribers
- Custom Types:
  - Custom Doctrine Types
  - Type Conversion
  - Value Objects
- Batch Processing:
  - Large Dataset Handling
  - Memory Management
  - Batch Updates
  - Chunk Processing

### 15. Advanced Forms

```bash
# Create custom form type
php bin/console make:form

# Create form extension
php bin/console make:form-extension
```

Learn about:
- Custom Form Types:
  - Form Type Extension
  - Data Transformers
  - Form Type Guessing
- Form Events:
  - PRE_SET_DATA
  - PRE_SUBMIT
  - SUBMIT
  - POST_SUBMIT
- Dynamic Forms:
  - Form Modifiers
  - Dynamic Field Types
  - Conditional Fields
- File Upload:
  - File Constraints
  - File Validation
  - File Storage Strategies
- Validation:
  - Custom Constraints
  - Validation Groups
  - Dynamic Validation
  - Cross-field Validation

### 16. Performance and Optimization

```bash
# Install Blackfire for profiling
composer require blackfire/php-sdk

# Install APCu for caching
composer require symfony/cache
```

Learn about:
- Profiling:
  - Symfony Profiler
  - Blackfire Profiling
  - Memory Profiling
  - Database Profiling
- Query Optimization:
  - Query Analysis
  - Index Usage
  - Query Caching
  - Result Caching
- Loading Strategies:
  - Eager Loading
  - Lazy Loading
  - Partial Loading
  - Custom Loading
- Cache Management:
  - Cache Invalidation
  - Cache Tags
  - Cache Warmers
  - Cache Pools

### 17. Command Line Interface

```bash
# Create custom command
php bin/console make:command

# Create scheduled task
php bin/console make:cron
```

Learn about:
- Custom Commands:
  - Command Structure
  - Input/Output Handling
  - Command Testing
- Scheduled Tasks:
  - Cron Jobs
  - Task Scheduling
  - Task Monitoring
- Progress Bars:
  - Progress Tracking
  - Custom Progress Bars
  - Progress Callbacks
- Interactive Commands:
  - User Input
  - Command Questions
  - Command Confirmation
- Batch Processing:
  - Memory Management
  - Chunk Processing
  - Progress Tracking
  - Error Handling

### 18. Error Handling and Logging

```bash
# Create custom exception listener
php bin/console make:exception-listener

# Create custom error page
php bin/console make:error-page
```

Learn about:
- Exception Handling:
  - Custom Exception Classes
  - Exception Listeners
  - Error Pages
  - Error Responses
- Logging:
  - Log Levels
  - Log Handlers
  - Log Processors
  - Log Rotation
- Debugging:
  - Debug Tools
  - Stack Traces
  - Error Context
  - Debug Bar

### 19. File Management System

```bash
# Create file manager service
php bin/console make:service FileManager

# Create file upload handler
php bin/console make:service FileUploadHandler
```

Learn about:
- File Upload:
  - File Validation
  - File Storage
  - File Types
  - File Size Limits
- File Management:
  - File Organization
  - File Metadata
  - File Permissions
  - File Versioning
- Storage Strategies:
  - Local Storage
  - Cloud Storage
  - CDN Integration
  - File Caching
- Security:
  - File Access Control
  - File Encryption
  - Virus Scanning
  - File Quarantine

## Development Tools

### Profiler
```bash
# Install Profiler
composer require --dev symfony/profiler-pack
```

### Testing
```bash
# Install Test Pack
composer require --dev symfony/test-pack

# Run tests
php bin/phpunit
```

### Logging
```bash
# Install Monolog
composer require symfony/monolog-bundle
```

## Best Practices

1. Always use type hints and return types
2. Follow PSR standards
3. Write tests for your code
4. Use dependency injection
5. Keep controllers thin
6. Use services for business logic
7. Document your code
8. Use proper naming conventions

## Resources

- [Symfony Documentation](https://symfony.com/doc/current/index.html)
- [Symfony Best Practices](https://symfony.com/doc/current/best_practices.html)
- [Doctrine Documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/current/index.html)
- [API Platform Documentation](https://api-platform.com/docs)

## Contributing

Feel free to contribute to this learning path by:
1. Forking the repository
2. Creating a feature branch
3. Submitting a pull request

## License

This project is licensed under the MIT License.