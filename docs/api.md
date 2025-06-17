# TailorFit API Documentation

This document provides information about the TailorFit API, which allows developers to integrate with the TailorFit application programmatically.

## Authentication

All API requests require authentication using an API token. You can generate an API token in the TailorFit application under **Settings** > **API**.

### Authentication Header

Include your API token in the `Authorization` header of all requests:

```
Authorization: Bearer YOUR_API_TOKEN
```

### API Base URL

All API endpoints are relative to the base URL:

```
https://your-domain.com/api/v1
```

## Clients

### List Clients

```
GET /clients
```

#### Query Parameters

| Parameter | Type   | Description                                      |
|-----------|--------|--------------------------------------------------|
| page      | int    | Page number for pagination (default: 1)          |
| per_page  | int    | Number of results per page (default: 15, max: 50)|
| search    | string | Search term to filter clients by name or email   |
| sort      | string | Field to sort by (name, email, created_at)       |
| order     | string | Sort order (asc, desc)                           |

#### Response

```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "address": "123 Main St, City, Country",
      "notes": "VIP client",
      "created_at": "2023-01-01T00:00:00Z",
      "updated_at": "2023-01-01T00:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "path": "https://your-domain.com/api/v1/clients",
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

### Get Client

```
GET /clients/{id}
```

#### Response

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "address": "123 Main St, City, Country",
    "notes": "VIP client",
    "created_at": "2023-01-01T00:00:00Z",
    "updated_at": "2023-01-01T00:00:00Z"
  }
}
```

### Create Client

```
POST /clients
```

#### Request Body

```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "phone": "+1987654321",
  "address": "456 Oak St, City, Country",
  "notes": "New client"
}
```

#### Response

```json
{
  "data": {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1987654321",
    "address": "456 Oak St, City, Country",
    "notes": "New client",
    "created_at": "2023-01-02T00:00:00Z",
    "updated_at": "2023-01-02T00:00:00Z"
  }
}
```

### Update Client

```
PUT /clients/{id}
```

#### Request Body

```json
{
  "name": "Jane Smith",
  "email": "jane.updated@example.com",
  "phone": "+1987654321",
  "address": "456 Oak St, City, Country",
  "notes": "Updated notes"
}
```

#### Response

```json
{
  "data": {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane.updated@example.com",
    "phone": "+1987654321",
    "address": "456 Oak St, City, Country",
    "notes": "Updated notes",
    "created_at": "2023-01-02T00:00:00Z",
    "updated_at": "2023-01-02T01:00:00Z"
  }
}
```

### Delete Client

```
DELETE /clients/{id}
```

#### Response

```json
{
  "message": "Client deleted successfully"
}
```

## Orders

### List Orders

```
GET /orders
```

#### Query Parameters

| Parameter | Type   | Description                                      |
|-----------|--------|--------------------------------------------------|
| page      | int    | Page number for pagination (default: 1)          |
| per_page  | int    | Number of results per page (default: 15, max: 50)|
| search    | string | Search term to filter orders                     |
| status    | string | Filter by status (pending, in_progress, etc.)    |
| client_id | int    | Filter by client ID                              |
| sort      | string | Field to sort by (created_at, due_date, etc.)    |
| order     | string | Sort order (asc, desc)                           |

#### Response

```json
{
  "data": [
    {
      "id": 1,
      "order_number": "ORD-2023-001",
      "client_id": 1,
      "client": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "description": "Suit alteration",
      "status": "in_progress",
      "due_date": "2023-02-01",
      "total_amount": 250.00,
      "items": [
        {
          "description": "Jacket alteration",
          "quantity": 1,
          "unit_price": 150.00,
          "amount": 150.00
        },
        {
          "description": "Pants alteration",
          "quantity": 1,
          "unit_price": 100.00,
          "amount": 100.00
        }
      ],
      "created_at": "2023-01-01T00:00:00Z",
      "updated_at": "2023-01-01T00:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "path": "https://your-domain.com/api/v1/orders",
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

### Get Order

```
GET /orders/{id}
```

#### Response

```json
{
  "data": {
    "id": 1,
    "order_number": "ORD-2023-001",
    "client_id": 1,
    "client": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "description": "Suit alteration",
    "status": "in_progress",
    "due_date": "2023-02-01",
    "total_amount": 250.00,
    "items": [
      {
        "description": "Jacket alteration",
        "quantity": 1,
        "unit_price": 150.00,
        "amount": 150.00
      },
      {
        "description": "Pants alteration",
        "quantity": 1,
        "unit_price": 100.00,
        "amount": 100.00
      }
    ],
    "created_at": "2023-01-01T00:00:00Z",
    "updated_at": "2023-01-01T00:00:00Z"
  }
}
```

### Create Order

```
POST /orders
```

#### Request Body

```json
{
  "client_id": 1,
  "description": "Dress alteration",
  "due_date": "2023-03-01",
  "status": "pending",
  "items": [
    {
      "description": "Dress hemming",
      "quantity": 1,
      "unit_price": 75.00
    },
    {
      "description": "Dress taking in",
      "quantity": 1,
      "unit_price": 100.00
    }
  ]
}
```

#### Response

```json
{
  "data": {
    "id": 2,
    "order_number": "ORD-2023-002",
    "client_id": 1,
    "client": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "description": "Dress alteration",
    "status": "pending",
    "due_date": "2023-03-01",
    "total_amount": 175.00,
    "items": [
      {
        "description": "Dress hemming",
        "quantity": 1,
        "unit_price": 75.00,
        "amount": 75.00
      },
      {
        "description": "Dress taking in",
        "quantity": 1,
        "unit_price": 100.00,
        "amount": 100.00
      }
    ],
    "created_at": "2023-01-02T00:00:00Z",
    "updated_at": "2023-01-02T00:00:00Z"
  }
}
```

### Update Order

```
PUT /orders/{id}
```

#### Request Body

```json
{
  "client_id": 1,
  "description": "Dress alteration - updated",
  "due_date": "2023-03-15",
  "status": "in_progress",
  "items": [
    {
      "description": "Dress hemming",
      "quantity": 1,
      "unit_price": 75.00
    },
    {
      "description": "Dress taking in",
      "quantity": 1,
      "unit_price": 100.00
    },
    {
      "description": "Dress zipper replacement",
      "quantity": 1,
      "unit_price": 50.00
    }
  ]
}
```

#### Response

```json
{
  "data": {
    "id": 2,
    "order_number": "ORD-2023-002",
    "client_id": 1,
    "client": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "description": "Dress alteration - updated",
    "status": "in_progress",
    "due_date": "2023-03-15",
    "total_amount": 225.00,
    "items": [
      {
        "description": "Dress hemming",
        "quantity": 1,
        "unit_price": 75.00,
        "amount": 75.00
      },
      {
        "description": "Dress taking in",
        "quantity": 1,
        "unit_price": 100.00,
        "amount": 100.00
      },
      {
        "description": "Dress zipper replacement",
        "quantity": 1,
        "unit_price": 50.00,
        "amount": 50.00
      }
    ],
    "created_at": "2023-01-02T00:00:00Z",
    "updated_at": "2023-01-02T01:00:00Z"
  }
}
```

### Delete Order

```
DELETE /orders/{id}
```

#### Response

```json
{
  "message": "Order deleted successfully"
}
```

## Invoices

### List Invoices

```
GET /invoices
```

#### Query Parameters

| Parameter | Type   | Description                                      |
|-----------|--------|--------------------------------------------------|
| page      | int    | Page number for pagination (default: 1)          |
| per_page  | int    | Number of results per page (default: 15, max: 50)|
| search    | string | Search term to filter invoices                   |
| status    | string | Filter by status (unpaid, paid, etc.)            |
| client_id | int    | Filter by client ID                              |
| order_id  | int    | Filter by order ID                               |
| sort      | string | Field to sort by (created_at, due_date, etc.)    |
| order     | string | Sort order (asc, desc)                           |

#### Response

```json
{
  "data": [
    {
      "id": 1,
      "invoice_number": "INV-2023-001",
      "client_id": 1,
      "client": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "order_id": 1,
      "status": "unpaid",
      "invoice_date": "2023-01-15",
      "due_date": "2023-02-15",
      "subtotal": 250.00,
      "tax_rate": 10,
      "tax_amount": 25.00,
      "discount_amount": 0.00,
      "total_amount": 275.00,
      "items": [
        {
          "description": "Jacket alteration",
          "quantity": 1,
          "unit_price": 150.00,
          "amount": 150.00
        },
        {
          "description": "Pants alteration",
          "quantity": 1,
          "unit_price": 100.00,
          "amount": 100.00
        }
      ],
      "created_at": "2023-01-15T00:00:00Z",
      "updated_at": "2023-01-15T00:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "path": "https://your-domain.com/api/v1/invoices",
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

## Payments

### List Payments

```
GET /payments
```

#### Query Parameters

| Parameter  | Type   | Description                                      |
|------------|--------|--------------------------------------------------|
| page       | int    | Page number for pagination (default: 1)          |
| per_page   | int    | Number of results per page (default: 15, max: 50)|
| client_id  | int    | Filter by client ID                              |
| invoice_id | int    | Filter by invoice ID                             |
| sort       | string | Field to sort by (created_at, amount, etc.)      |
| order      | string | Sort order (asc, desc)                           |

#### Response

```json
{
  "data": [
    {
      "id": 1,
      "client_id": 1,
      "client": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "invoice_id": 1,
      "amount": 275.00,
      "payment_date": "2023-02-10",
      "payment_method": "credit_card",
      "reference": "REF123456",
      "notes": "Payment for invoice INV-2023-001",
      "created_at": "2023-02-10T00:00:00Z",
      "updated_at": "2023-02-10T00:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "path": "https://your-domain.com/api/v1/payments",
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

## Error Handling

The API uses standard HTTP status codes to indicate the success or failure of a request.

### Error Response Format

```json
{
  "error": {
    "code": "validation_error",
    "message": "The given data was invalid.",
    "details": {
      "name": [
        "The name field is required."
      ],
      "email": [
        "The email field is required.",
        "The email must be a valid email address."
      ]
    }
  }
}
```

### Common Error Codes

| Status Code | Error Code           | Description                                                  |
|-------------|----------------------|--------------------------------------------------------------|
| 400         | bad_request          | The request was malformed or invalid                         |
| 401         | unauthorized         | Authentication is required or failed                         |
| 403         | forbidden            | The authenticated user doesn't have permission               |
| 404         | not_found            | The requested resource was not found                         |
| 422         | validation_error     | The request data failed validation                           |
| 429         | too_many_requests    | Rate limit exceeded                                          |
| 500         | server_error         | An unexpected error occurred on the server                   |

## Rate Limiting

The API implements rate limiting to prevent abuse. The current limits are:

- 60 requests per minute per API token

Rate limit information is included in the response headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1609459200
```

If you exceed the rate limit, you'll receive a 429 Too Many Requests response.

## Webhooks

TailorFit can send webhook notifications for various events. You can configure webhooks in the TailorFit application under **Settings** > **API** > **Webhooks**.

### Available Events

- `client.created`: Triggered when a new client is created
- `client.updated`: Triggered when a client is updated
- `order.created`: Triggered when a new order is created
- `order.updated`: Triggered when an order is updated
- `order.status_changed`: Triggered when an order's status changes
- `invoice.created`: Triggered when a new invoice is created
- `invoice.updated`: Triggered when an invoice is updated
- `payment.created`: Triggered when a new payment is recorded

### Webhook Payload

```json
{
  "event": "order.status_changed",
  "timestamp": "2023-01-15T12:00:00Z",
  "data": {
    "id": 1,
    "order_number": "ORD-2023-001",
    "client_id": 1,
    "status": "in_progress",
    "previous_status": "pending",
    "updated_at": "2023-01-15T12:00:00Z"
  }
}
```

### Webhook Security

To verify that webhook requests are coming from TailorFit, we include a signature in the `X-TailorFit-Signature` header. The signature is a HMAC SHA-256 hash of the request body, using your webhook secret as the key.

Example code to verify the signature (PHP):

```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_TAILORFIT_SIGNATURE'];
$secret = 'your_webhook_secret';

$calculatedSignature = hash_hmac('sha256', $payload, $secret);

if (hash_equals($calculatedSignature, $signature)) {
    // Signature is valid, process the webhook
} else {
    // Signature is invalid, reject the webhook
    http_response_code(403);
    exit;
}
```
