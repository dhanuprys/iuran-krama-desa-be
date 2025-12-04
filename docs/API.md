# REST API Documentation

## Overview
API for managing Banjar (traditional neighborhoods), Resident Statuses, Residents, Invoices, and Payments. Uses Laravel Sanctum for token authentication and admin middleware for authorization.

- Base URL: `/api`
- Auth: `Bearer <token>` (from login endpoint)
- Content-Type: `application/json`
- Date format: `YYYY-MM-DD`
- Money format: decimal with 2 digits
- Pagination: metadata available in `pagination` key

Consistent response structure:
```json
{
  "success": true,
  "message": "...",
  "data": {},
  "pagination": { }
}
```

## Authentication (Sanctum)
All management endpoints (residents, invoices) require:
- `auth:sanctum` (valid token)
- `admin` (user.role === 'admin')

Seeder creates default admin:
- email: `admin@example.com`
- password: `password`

### Register
POST `/api/register`
Body:
```json
{
  "name": "Admin User",
  "email": "admin@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

### Login
POST `/api/login`
Body:
```json
{ "email": "admin@example.com", "password": "password" }
```
Respons:
```json
{ "token": "<SANCTUM_TOKEN>" }
```
Header untuk endpoint terlindungi:
```
Authorization: Bearer <SANCTUM_TOKEN>
```

### Profile (check logged in user)
POST `/api/profile` (auth)

### Logout
POST `/api/logout` (auth)

## Middleware
- `auth:sanctum`: requires valid token
- `admin`: requires `users.role = 'admin'` (see `App\Http\Middleware\AdminMiddleware`)

## Residents API
All endpoints protected: `auth:sanctum`, `admin`

### List Residents
GET `/api/residents`
Query params (optional):
- `search`: search in `name`, `nik`, `phone`
- `banjar_id`: filter by banjar
- `resident_status_id`: filter by status
- `gender`: `L|P`
- `sort_by`: `name|nik|created_at|updated_at` (default `created_at`)
- `sort_order`: `asc|desc` (default `desc`)
- `page`: page number
- `per_page`: items per page (max 100)

Response 200:
```json
{
  "success": true,
  "data": [ResidentResource...],
  "pagination": { ... },
  "filters": {
    "banjars": [{"id":1,"name":"..."}],
    "resident_statuses": [{"id":1,"name":"..."}]
  }
}
```
ResidentResource:
```json
{
  "id": 1,
  "nik": "...",
  "name": "...",
  "gender": "L|P",
  "resident_status": {"id":1,"name":"Krama","contribution_amount":"50000.00"},
  "banjar": {"id":1,"name":"Banjar ...","address":"..."},
  "address": "...",
  "phone": "...",
  "created_at": "YYYY-MM-DD HH:mm:ss",
  "updated_at": "YYYY-MM-DD HH:mm:ss"
}
```

### Create Resident
POST `/api/residents`
Body:
```json
{
  "nik": "5103021234567890",
  "name": "I Made Surya",
  "gender": "L",
  "resident_status_id": 1,
  "banjar_id": 1,
  "address": "...",
  "phone": "0812..."
}
```
Validation (Indonesian messages):
- NIK: required, unique, max 30
- Name: required, max 255
- Gender: `L` or `P`
- Status and Banjar: must `exists`
Success response 201: `Penduduk berhasil dibuat`

### Show Resident
GET `/api/residents/{id}`

### Update Resident
PUT/PATCH `/api/residents/{id}`
- `nik` remains unique (except for own record)
Success response 200: `Penduduk berhasil diperbarui`

### Delete Resident
DELETE `/api/residents/{id}`
- Will return 422 if resident has invoices
Success response 200: `Penduduk berhasil dihapus`

## Invoices API
All endpoints protected: `auth:sanctum`, `admin`

Business rules:
- One resident can only have one invoice per calendar month (validated on create & update)
- `iuran_amount` calculated server-side from `resident.residentStatus.contribution_amount`
- `total_amount = iuran_amount + peturunan_amount + dedosan_amount`

### List Invoices
GET `/api/invoices`
Query params (optional):
- `resident_id`
- `start_date` (YYYY-MM-DD)
- `end_date` (YYYY-MM-DD)
- `search` (resident name, NIK, phone)
- `sort_by`: `invoice_date|total_amount|created_at|updated_at` (default `invoice_date`)
- `sort_order`: `asc|desc` (default `desc`)
- `page`, `per_page` (max 100)

Response 200:
```json
{
  "success": true,
  "data": [InvoiceResource...],
  "pagination": { ... }
}
```
InvoiceResource:
```json
{
  "id": 1,
  "resident": ResidentResource,
  "invoice_date": "YYYY-MM-DD",
  "iuran_amount": "50000.00",
  "peturunan_amount": "10000.00",
  "dedosan_amount": "5000.00",
  "total_amount": "65000.00",
  "user": {"id":1,"name":"Admin User"},
  "payments": [PaymentResource...],
  "created_at": "YYYY-MM-DD HH:mm:ss",
  "updated_at": "YYYY-MM-DD HH:mm:ss"
}
```

### Create Invoice
POST `/api/invoices`
Body:
```json
{
  "resident_id": 1,
  "invoice_date": "2025-10-01",
  "peturunan_amount": 10000,
  "dedosan_amount": 5000
}
```
Validation (Indonesian messages):
- `resident_id`: required|integer|exists
- `invoice_date`: required|date|before_or_equal:today
- `peturunan_amount`, `dedosan_amount`: numeric|min:0|max:999999.99
- Monthly duplicate: error `Penduduk sudah memiliki invoice untuk bulan ini`
Flow:
1. Server loads `resident` + `residentStatus`
2. Server sets `iuran_amount = contribution_amount`
3. Server checks monthly duplicate (year+month) for resident
4. Calculate `total_amount`, set `user_id` = logged in user
5. Save & return InvoiceResource
Response 201: `Invoice berhasil dibuat`

### Show Invoice
GET `/api/invoices/{id}`
Response 404: `Invoice tidak ditemukan`

### Update Invoice
PUT/PATCH `/api/invoices/{id}`
Body (partial):
```json
{
  "resident_id": 2,
  "invoice_date": "2025-10-15",
  "peturunan_amount": 12000
}
```
Notes:
- If `resident_id` changes, `iuran_amount` automatically taken from new resident status
- Monthly duplicate check still applies (excludes current invoice)
- `total_amount` recalculated when any amount changes
Response 200: `Invoice berhasil diperbarui`

### Delete Invoice
DELETE `/api/invoices/{id}`
Response 200: `Invoice berhasil dihapus`

## Payments (Model & Resource)
Currently Payment API is not exposed as separate routes, but PaymentResource contains:
```json
{
  "id": 1,
  "amount": "10000.00",
  "date": "YYYY-MM-DD",
  "method": "cash|transfer|...",
  "status": "paid|pending|invalid",
  "user": {"id":1,"name":"Admin User"},
  "created_at": "YYYY-MM-DD HH:mm:ss",
  "updated_at": "YYYY-MM-DD HH:mm:ss"
}
```

## Curl Examples

Login (get token):
```bash
curl -s -X POST http://localhost/api/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@example.com","password":"password"}'
```

List residents (admin):
```bash
curl -s -H 'Authorization: Bearer TOKEN' \
  'http://localhost/api/residents?search=Made&per_page=10'
```

Create invoice (admin):
```bash
curl -s -X POST http://localhost/api/invoices \
  -H 'Authorization: Bearer TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{"resident_id":1,"invoice_date":"2025-10-01","peturunan_amount":10000,"dedosan_amount":5000}'
```

## Architecture & Important Flows
- `admin` middleware rejects non-admin access (401 if not logged in, 403 if not admin)
- Validation uses Indonesian messages
- Invoice data integrity maintained by:
  - One-invoice-per-month validation per resident
  - `iuran_amount` taken from resident status on server
  - `total_amount` calculation done on server
- Eager loading (resident.status, resident.banjar, payments) to avoid N+1

## Key Code Files
- `App\Http\Middleware\AdminMiddleware`
- `App\Http\Controllers\Api\ResidentController`
- `App\Http\Controllers\Api\InvoiceController`
- `App\Http\Resources\ResidentResource`, `InvoiceResource`, `PaymentResource`

## Roadmap (Optional)
- Expose Payment endpoints (CRUD)
- Export OpenAPI (Swagger) / Postman collection
- Soft delete and audit log

