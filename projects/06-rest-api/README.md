# REST API

JSON REST API for task management with proper HTTP status codes.

## Features

- CRUD endpoints
- JSON request/response
- Pagination
- Status filtering
- Input validation
- Proper HTTP status codes
- Error handling

## Setup

```bash
composer install
```

## Endpoints

### Create Task
```
POST /api.php
Content-Type: application/json

{
  "title": "New task",
  "description": "Task description",
  "status": "pending"
}

Response: 201 Created
```

### Get All Tasks
```
GET /api.php?page=1&limit=20&status=pending

Response: 200 OK
{
  "data": [...],
  "total": 100,
  "page": 1,
  "limit": 20
}
```

### Get Single Task
```
GET /api.php?id=1

Response: 200 OK
```

### Update Task
```
PUT /api.php?id=1
Content-Type: application/json

{
  "status": "completed"
}

Response: 200 OK
```

### Delete Task
```
DELETE /api.php?id=1

Response: 204 No Content
```

## Start Server

```bash
php -S localhost:8000 -t public
```

## Testing

```bash
vendor/bin/phpunit

# Manual testing with curl
curl -X POST http://localhost:8000/api.php \
  -H "Content-Type: application/json" \
  -d '{"title":"Test task"}'
```

## HTTP Status Codes

- 200 OK - Successful GET/PUT
- 201 Created - Successful POST
- 204 No Content - Successful DELETE
- 400 Bad Request - Validation error
- 404 Not Found - Resource not found
- 405 Method Not Allowed - Invalid HTTP method
- 500 Internal Server Error - Server error

## Security

- Input validation
- PDO prepared statements
- JSON escaping
- Request size limits (handled by web server)

## Limitations

- No authentication
- No rate limiting
- No API versioning
- No CORS configuration
- No OpenAPI/Swagger docs
