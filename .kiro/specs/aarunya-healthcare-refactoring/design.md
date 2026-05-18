# Design Document: Aarunya Healthcare System Refactoring

## Overview

This document outlines the comprehensive refactoring design for the Aarunya Maternal Healthcare Management System. The refactoring transforms the existing system into a clean, production-ready architecture with unified database management, centralized authentication, strict validation, enhanced security, optimized performance, and maintainable code structure while preserving all existing functionality and user interface design.

## Architecture Overview

### System Architecture

The refactored Aarunya system follows a layered architecture pattern with clear separation of concerns:

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                        │
├─────────────────┬─────────────────┬─────────────────────────┤
│   Client UI     │   Admin UI      │    Doctor UI            │
│   (Patient)     │                 │                         │
└─────────────────┴─────────────────┴─────────────────────────┘
┌─────────────────────────────────────────────────────────────┐
│                     API Gateway                             │
│              (RESTful API Endpoints)                        │
└─────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────┐
│                   Business Logic Layer                      │
├─────────────────┬─────────────────┬─────────────────────────┤
│ Authentication  │   Validation    │   File Management       │
│    Service      │    Engine       │      Service            │
├─────────────────┼─────────────────┼─────────────────────────┤
│   Security      │   Performance   │   Error Handling        │
│  Framework      │    Monitor      │      Service            │
└─────────────────┴─────────────────┴─────────────────────────┘
┌─────────────────────────────────────────────────────────────┐
│                   Data Access Layer                         │
│                  (Database Layer)                           │
└─────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────┐
│                    Database Layer                           │
│                 (MySQL Database)                            │
└─────────────────────────────────────────────────────────────┘
```

### Technology Stack

- **Backend**: PHP 8.1+ with PSR-4 autoloading
- **Database**: MySQL 8.0+ with InnoDB engine
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Security**: HTTPS, CSRF protection, XSS prevention
- **Caching**: Redis for session and data caching
- **File Storage**: Structured file system with virus scanning
- **Containerization**: Docker for deployment
- **Testing**: PHPUnit for unit and integration testing

## Core Components

### 1. Database Architecture

#### Unified Database Layer

The refactored system implements a centralized database layer with the following components:

**Database Connection Manager**
```php
namespace Aarunya\Database;

class ConnectionManager {
    private static $instance = null;
    private $connection;
    private $config;
    
    public static function getInstance(): self
    public function getConnection(): PDO
    public function beginTransaction(): void
    public function commit(): void
    public function rollback(): void
}
```

**Query Builder**
```php
namespace Aarunya\Database;

class QueryBuilder {
    public function select(array $columns = ['*']): self
    public function from(string $table): self
    public function where(string $column, $operator, $value): self
    public function join(string $table, string $condition): self
    public function execute(): array
}
```

**Migration System**
```php
namespace Aarunya\Database;

class MigrationManager {
    public function runMigrations(): void
    public function rollbackMigration(string $version): void
    public function createMigration(string $name): string
}
```

#### Database Schema Optimization

**Enhanced Table Structure**
- All tables use InnoDB engine for ACID compliance
- Proper foreign key constraints with cascading rules
- Optimized indexes for frequently queried columns
- UTF-8MB4 character set for full Unicode support

**Key Relationships**
```sql
-- Users table (patients)
users (id) -> appointments (user_id)
users (id) -> health_records (user_id)
users (id) -> emergency_requests (user_id)

-- Doctors table
doctors (id) -> appointments (doctor_id)
doctors (id) -> consultations (doctor_id)

-- Admins table
admins (id) -> health_metrics (recorded_by)
admins (id) -> emergency_requests (resolved_by)
```

### 2. Authentication & Authorization System

#### Centralized Authentication Service

**Authentication Manager**
```php
namespace Aarunya\Auth;

class AuthenticationManager {
    public function authenticate(string $email, string $password, string $role): AuthResult
    public function generateSession(User $user): SessionToken
    public function validateSession(string $token): bool
    public function refreshSession(string $token): SessionToken
    public function logout(string $token): void
}
```

**Role-Based Access Control (RBAC)**
```php
namespace Aarunya\Auth;

class AuthorizationManager {
    public function hasPermission(User $user, string $resource, string $action): bool
    public function assignRole(User $user, Role $role): void
    public function checkResourceAccess(User $user, Resource $resource): bool
}
```

**Session Management**
```php
namespace Aarunya\Auth;

class SessionManager {
    public function createSession(User $user): Session
    public function validateSession(string $sessionId): Session|null
    public function regenerateSession(Session $session): Session
    public function destroySession(string $sessionId): void
    public function cleanupExpiredSessions(): void
}
```

#### Security Features

- **Password Security**: bcrypt hashing with configurable cost
- **Session Security**: Secure tokens with automatic regeneration
- **CSRF Protection**: Token-based CSRF protection for all forms
- **Rate Limiting**: Configurable rate limiting for authentication attempts
- **Audit Logging**: Comprehensive logging of all authentication events

### 3. Input Validation & Security Framework

#### Validation Engine

**Input Validator**
```php
namespace Aarunya\Validation;

class InputValidator {
    public function validate(array $data, array $rules): ValidationResult
    public function sanitize(mixed $input, string $type): mixed
    public function validateFile(UploadedFile $file): FileValidationResult
}
```

**Validation Rules**
```php
namespace Aarunya\Validation\Rules;

interface ValidationRule {
    public function validate($value, array $parameters = []): bool
    public function getMessage(): string
}

class EmailRule implements ValidationRule
class RequiredRule implements ValidationRule
class LengthRule implements ValidationRule
class FileTypeRule implements ValidationRule
```

#### Security Framework

**XSS Prevention**
```php
namespace Aarunya\Security;

class XSSProtection {
    public function sanitizeOutput(string $content): string
    public function sanitizeHtml(string $html, array $allowedTags = []): string
    public function escapeForJavaScript(string $content): string
}
```

**SQL Injection Prevention**
- All database queries use prepared statements
- Input validation before database operations
- Parameterized queries for all user inputs

**CSRF Protection**
```php
namespace Aarunya\Security;

class CSRFProtection {
    public function generateToken(): string
    public function validateToken(string $token): bool
    public function injectTokenIntoForm(string $html): string
}
```

### 4. File Management System

#### File Manager

**File Upload Handler**
```php
namespace Aarunya\FileManager;

class FileUploadHandler {
    public function uploadFile(UploadedFile $file, string $category): UploadResult
    public function validateFile(UploadedFile $file): ValidationResult
    public function scanForViruses(string $filePath): ScanResult
    public function generateUniqueFilename(string $originalName): string
}
```

**File Organization Structure**
```
uploads/
├── profile_photos/
│   ├── users/
│   │   └── {user_id}/
│   └── doctors/
│       └── {doctor_id}/
├── documents/
│   ├── medical_records/
│   │   └── {user_id}/
│   └── doctor_credentials/
│       └── {doctor_id}/
└── temp/
    └── {session_id}/
```

**File Security Features**
- Virus scanning for all uploaded files
- File type validation based on MIME type and extension
- Size limits per file type and user role
- Automatic cleanup of temporary and orphaned files
- Secure file serving with access control

### 5. Performance Optimization

#### Caching Strategy

**Cache Manager**
```php
namespace Aarunya\Cache;

class CacheManager {
    public function get(string $key): mixed
    public function set(string $key, mixed $value, int $ttl = 3600): void
    public function delete(string $key): void
    public function flush(): void
    public function tags(array $tags): self
}
```

**Caching Layers**
- **Database Query Cache**: Frequently accessed data
- **Session Cache**: User session data in Redis
- **Template Cache**: Compiled template cache
- **Asset Cache**: Static asset caching with versioning

#### Database Optimization

**Index Strategy**
```sql
-- User lookup optimization
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);

-- Appointment queries
CREATE INDEX idx_appointments_date_doctor ON appointments(appointment_date, doctor_id);
CREATE INDEX idx_appointments_user_status ON appointments(user_id, status);

-- Health records timeline
CREATE INDEX idx_health_records_user_date ON health_records(user_id, recorded_at);

-- Emergency requests priority
CREATE INDEX idx_emergency_priority_status ON emergency_requests(priority, status, created_at);
```

**Query Optimization**
- Prepared statement caching
- Connection pooling
- Lazy loading for large datasets
- Pagination for all list views

### 6. API Design

#### RESTful API Structure

**API Endpoints**
```
/api/v1/
├── auth/
│   ├── POST /login
│   ├── POST /logout
│   ├── POST /refresh
│   └── GET /profile
├── users/
│   ├── GET /users
│   ├── POST /users
│   ├── GET /users/{id}
│   ├── PUT /users/{id}
│   └── DELETE /users/{id}
├── appointments/
│   ├── GET /appointments
│   ├── POST /appointments
│   ├── GET /appointments/{id}
│   ├── PUT /appointments/{id}
│   └── DELETE /appointments/{id}
└── health-records/
    ├── GET /health-records
    ├── POST /health-records
    └── GET /health-records/{id}
```

**API Response Format**
```json
{
  "success": true,
  "data": {
    // Response data
  },
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 100,
      "total_pages": 5
    }
  },
  "errors": []
}
```

#### API Security

**Authentication & Authorization**
- JWT tokens for API authentication
- Role-based access control for endpoints
- Rate limiting per user and IP
- CORS configuration for cross-origin requests

**Input Validation**
- JSON schema validation for all payloads
- Sanitization of all input data
- File upload validation for multipart requests

### 7. Error Handling & Logging

#### Error Handler

**Exception Hierarchy**
```php
namespace Aarunya\Exceptions;

abstract class AarunyaException extends Exception
class ValidationException extends AarunyaException
class AuthenticationException extends AarunyaException
class AuthorizationException extends AarunyaException
class DatabaseException extends AarunyaException
class FileUploadException extends AarunyaException
```

**Error Logger**
```php
namespace Aarunya\Logging;

class ErrorLogger {
    public function logError(Throwable $exception, array $context = []): void
    public function logSecurityEvent(string $event, array $data = []): void
    public function logPerformanceMetric(string $metric, float $value): void
}
```

#### Logging Strategy

**Log Levels**
- **DEBUG**: Development debugging information
- **INFO**: General application information
- **WARNING**: Warning conditions
- **ERROR**: Error conditions
- **CRITICAL**: Critical conditions requiring immediate attention

**Log Categories**
- **Security**: Authentication, authorization, security events
- **Performance**: Slow queries, high memory usage, response times
- **Business**: User actions, appointments, health records
- **System**: Database connections, file operations, cache operations

### 8. Configuration Management

#### Environment Configuration

**Configuration Structure**
```php
namespace Aarunya\Config;

class ConfigManager {
    public function get(string $key, mixed $default = null): mixed
    public function set(string $key, mixed $value): void
    public function load(string $environment): void
    public function validate(): ValidationResult
}
```

**Environment Files**
```
config/
├── .env.example
├── .env.local
├── .env.development
├── .env.testing
├── .env.staging
└── .env.production
```

**Configuration Categories**
- **Database**: Connection parameters, pool settings
- **Cache**: Redis configuration, TTL settings
- **Security**: Encryption keys, CSRF settings, rate limits
- **File Upload**: Size limits, allowed types, storage paths
- **Email**: SMTP settings, templates
- **Logging**: Log levels, file paths, rotation settings

## Data Models

### Core Entities

#### User Entity
```php
namespace Aarunya\Models;

class User extends BaseModel {
    protected $table = 'users';
    protected $fillable = [
        'name', 'email', 'phone', 'age', 'lmp_date', 
        'pregnancy_week', 'due_date', 'blood_group'
    ];
    protected $hidden = ['password'];
    protected $casts = [
        'lmp_date' => 'date',
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    public function appointments(): HasMany
    public function healthRecords(): HasMany
    public function emergencyRequests(): HasMany
}
```

#### Doctor Entity
```php
namespace Aarunya\Models;

class Doctor extends BaseModel {
    protected $table = 'doctors';
    protected $fillable = [
        'name', 'email', 'phone', 'specialization', 
        'experience', 'qualification', 'availability'
    ];
    
    public function appointments(): HasMany
    public function consultations(): HasMany
}
```

#### Appointment Entity
```php
namespace Aarunya\Models;

class Appointment extends BaseModel {
    protected $table = 'appointments';
    protected $fillable = [
        'user_id', 'doctor_id', 'appointment_date', 
        'appointment_time', 'status', 'notes'
    ];
    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'time'
    ];
    
    public function user(): BelongsTo
    public function doctor(): BelongsTo
}
```

### Data Relationships

**Entity Relationship Diagram**
```
Users ||--o{ Appointments : books
Users ||--o{ HealthRecords : has
Users ||--o{ EmergencyRequests : creates

Doctors ||--o{ Appointments : attends
Doctors ||--o{ Consultations : provides

Admins ||--o{ HealthMetrics : records
Admins ||--o{ EmergencyRequests : resolves
```

## Security Implementation

### Data Protection

#### Encryption Strategy
- **At Rest**: AES-256 encryption for sensitive patient data
- **In Transit**: TLS 1.3 for all communications
- **Database**: Encrypted columns for PII data
- **Files**: Encrypted file storage for medical documents

#### Access Control Matrix

| Role    | Users | Doctors | Appointments | Health Records | Admin Functions |
|---------|-------|---------|--------------|----------------|-----------------|
| Patient | Own   | Read    | Own          | Own            | None            |
| Doctor  | Read  | Own     | Assigned     | Assigned       | None            |
| Admin   | All   | All     | All          | All            | All             |

### Compliance Features

#### HIPAA Compliance
- Audit logging for all data access
- Data encryption at rest and in transit
- Access controls based on minimum necessary principle
- Secure data backup and recovery procedures

#### Data Privacy
- User consent management
- Data retention policies
- Right to data portability
- Secure data deletion procedures

## Performance Specifications

### Response Time Requirements

| Operation Type | Target Response Time | Maximum Response Time |
|----------------|---------------------|----------------------|
| Page Load      | < 1.5 seconds       | < 3 seconds          |
| API Calls      | < 500ms             | < 1 second           |
| Database Query | < 100ms             | < 500ms              |
| File Upload    | < 2 seconds         | < 5 seconds          |

### Scalability Targets

- **Concurrent Users**: 1,000 simultaneous users
- **Database**: 100,000+ patient records
- **File Storage**: 10TB+ medical documents
- **API Throughput**: 1,000 requests/minute per endpoint

### Monitoring & Metrics

#### Performance Metrics
- Response time percentiles (50th, 95th, 99th)
- Database query performance
- Memory usage and garbage collection
- File system I/O performance

#### Business Metrics
- User registration rate
- Appointment booking success rate
- System availability (99.9% uptime target)
- Error rate (< 0.1% target)

## Deployment Architecture

### Containerization Strategy

**Docker Configuration**
```dockerfile
# Application Container
FROM php:8.1-fpm-alpine
RUN docker-php-ext-install pdo pdo_mysql
COPY . /var/www/html
WORKDIR /var/www/html

# Database Container
FROM mysql:8.0
ENV MYSQL_DATABASE=aarunya_db
VOLUME /var/lib/mysql

# Cache Container
FROM redis:7-alpine
VOLUME /data
```

**Docker Compose Structure**
```yaml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "80:80"
    depends_on:
      - database
      - cache
  
  database:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: aarunya_db
    volumes:
      - db_data:/var/lib/mysql
  
  cache:
    image: redis:7-alpine
    volumes:
      - cache_data:/data
```

### Environment Strategy

#### Development Environment
- Local Docker containers
- Hot reloading for development
- Debug logging enabled
- Test data seeding

#### Staging Environment
- Production-like configuration
- Automated testing pipeline
- Performance monitoring
- Security scanning

#### Production Environment
- High availability setup
- Load balancing
- Automated backups
- Monitoring and alerting

## Testing Strategy

### Test Pyramid

#### Unit Tests (70%)
- Business logic components
- Validation rules
- Data models
- Utility functions

#### Integration Tests (20%)
- Database operations
- API endpoints
- File upload functionality
- Authentication flows

#### End-to-End Tests (10%)
- User workflows
- Cross-browser compatibility
- Performance testing
- Security testing

### Test Implementation

**PHPUnit Configuration**
```php
namespace Tests\Unit;

class UserServiceTest extends TestCase {
    public function testUserRegistration(): void
    public function testPasswordHashing(): void
    public function testUserAuthentication(): void
}
```

**API Testing**
```php
namespace Tests\Integration;

class AppointmentApiTest extends TestCase {
    public function testCreateAppointment(): void
    public function testGetAppointments(): void
    public function testUpdateAppointment(): void
}
```

## Migration Strategy

### Phase 1: Infrastructure Setup
1. Set up new database schema with proper constraints
2. Implement centralized configuration management
3. Create unified authentication system
4. Set up logging and monitoring infrastructure

### Phase 2: Core Refactoring
1. Refactor database layer with connection pooling
2. Implement input validation and security framework
3. Create file management system
4. Set up caching infrastructure

### Phase 3: API Development
1. Develop RESTful API endpoints
2. Implement API authentication and authorization
3. Add rate limiting and throttling
4. Create comprehensive API documentation

### Phase 4: Testing & Optimization
1. Implement comprehensive test suite
2. Performance optimization and tuning
3. Security audit and penetration testing
4. Load testing and scalability validation

### Phase 5: Deployment & Monitoring
1. Set up containerized deployment
2. Implement CI/CD pipeline
3. Configure monitoring and alerting
4. Production deployment and validation

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Database Query Security

*For any* user input passed to database queries, the system SHALL use prepared statements to prevent SQL injection attacks.

**Validates: Requirements 1.3, 4.4**

### Property 2: Authentication Consistency

*For any* user role (patient, doctor, admin), the authentication system SHALL handle login through a single unified interface with consistent security measures.

**Validates: Requirements 2.1, 2.2, 2.3**

### Property 3: Input Validation Universality

*For any* user input received by the system, the validation engine SHALL validate and sanitize the input before processing to prevent XSS and injection attacks.

**Validates: Requirements 3.1, 3.2, 4.5**

### Property 4: Session Security

*For any* user login attempt, the session manager SHALL generate secure session tokens with proper expiration and automatic regeneration.

**Validates: Requirements 2.3, 2.7**

### Property 5: CSRF Protection Coverage

*For any* state-changing operation in the system, CSRF protection SHALL be implemented to prevent cross-site request forgery attacks.

**Validates: Requirements 4.1**

### Property 6: File Upload Security

*For any* file upload operation, the file manager SHALL validate file type, size, scan for viruses, and generate unique filenames to prevent conflicts and security threats.

**Validates: Requirements 3.6, 7.1, 7.3, 7.5, 7.6**

### Property 7: Error Handling Consistency

*For any* error that occurs in the system, the error handler SHALL log the error with appropriate severity level and provide user-friendly error messages.

**Validates: Requirements 8.1, 8.2, 8.3, 8.4**

### Property 8: API Response Standardization

*For any* API request made to the system, the response SHALL follow RESTful conventions with consistent JSON format and standardized HTTP status codes.

**Validates: Requirements 9.1, 9.2, 9.3**

### Property 9: Access Control Enforcement

*For any* user attempting to access system resources, the authorization system SHALL enforce role-based access controls and proper permission checking.

**Validates: Requirements 2.5, 14.3**

### Property 10: Data Encryption Compliance

*For any* sensitive patient information handled by the system, the security framework SHALL implement proper encryption for data at rest and in transit.

**Validates: Requirements 4.3, 14.1, 14.4**

### Property 11: Audit Logging Completeness

*For any* data access or modification operation, the system SHALL maintain comprehensive audit logs for compliance and security monitoring.

**Validates: Requirements 2.6, 4.7, 14.2**

### Property 12: Configuration Security

*For any* configuration setting in the system, sensitive values SHALL be stored securely using environment variables and proper encryption.

**Validates: Requirements 2.4, 10.1, 10.3, 10.6**

### Property 13: Performance Response Time

*For any* page load or API request, the system SHALL respond within the specified performance targets (pages < 2 seconds, APIs < 1 second).

**Validates: Requirements 5.3**

### Property 14: Database Constraint Integrity

*For any* data operation on the database, the system SHALL enforce proper foreign key relationships and validation constraints at the database level.

**Validates: Requirements 1.6, 13.1, 13.3**

### Property 15: Functionality Preservation

*For any* existing feature in the current system, the refactored system SHALL preserve all functionality while improving the underlying architecture.

**Validates: Requirements 12.1, 12.2, 12.3, 12.4, 12.5, 12.6, 12.7, 12.8**

## Conclusion

This design document provides a comprehensive blueprint for refactoring the Aarunya Maternal Healthcare Management System into a modern, secure, and scalable architecture. The design emphasizes security, performance, maintainability, and compliance while preserving all existing functionality. The implementation will follow industry best practices and modern development standards to ensure a robust and future-proof healthcare management platform.

The modular architecture allows for incremental implementation and testing, reducing risk during the refactoring process. Each component is designed to be independently testable and maintainable, supporting long-term system evolution and enhancement.