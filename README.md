# PetroApp - Station Transfer Ingestion System

A robust, idempotent, and concurrency-safe system for ingesting fuel station transfer events. Developed as a candidate take-home assignment.

---

## 🚀 Tech Stack + Requirements

- **PHP**: ^8.2 (Leveraging modern syntax and typing)
- **Framework**: [Laravel 12 (Skeleton)](https://laravel.com/) - Core business logic and API routing.
- **Database**: MySQL 8.0 (Ensuring data integrity and unique constraints).
- **Architecture**: Service-Repository pattern with DTOs for type-safe data transfer and clean separation of concerns.
- **Containerization**: Docker & Docker Compose.
- **Testing**: PHPUnit 11+ for automated quality assurance.

---

## 🏗️ Design Notes

### Idempotency Strategy
The system ensures that each `event_id` is processed exactly once:
1.  **Application-level Check**: Before insertion, the system verifies if the `event_id` already exists in storage.
2.  **Database-level Enforcement**: A `UNIQUE` index on `event_id` serves as the final source of truth.
3.  **Exception Handling**: Concurrent requests that pass the application check simultaneously will trigger a `DuplicateEventException` at the database level, which is caught and handled to record a "duplicate" count instead of failing the request.

### Concurrency Strategy
Relying on database-level atomic guarantees and unique constraints allows the system to remain safe under high concurrency without requiring heavy application-level locks (e.g., Redis locks), which simplifies the architecture while maintaining correctness.

### Decisions & Tradeoffs
-   **Partial Accept (Default Strategy)**: The system implements a "partial accept" strategy for batch ingestion. Valid events are stored, and invalid ones are reported with their index and error details in the response. This maximizes data throughput from external systems.
-   **Fail-Fast Alternative**: A "fail-fast" mode is available via configuration (`config/event_transfers.php`) which rejects the entire batch if a single validation error occurs.
-   **Events Count Calculation**: The summary endpoint returns the count of *all* stored events for a station (all statuses), while the total amount sums *only* approved events. This provides a clear distinction between "total throughput" and "reconciled amount".

---

## 🔧 Installation & Running

### 🐳 Docker
You can run the entire environment (Nginx + PHP-FPM + MySQL) using Docker:

1.  **Build and Run**:
    ```bash
    docker compose up --build -d
    ```
    The application will be accessible at `http://localhost:8000`.

---

## 🧪 Testing

The project includes a comprehensive test suite covering:
1.  Batch insert counts (inserted vs duplicates).
2.  Idempotency verification (totals unchanged by duplicates).
3.  Out-of-order event arrivals.
4.  Concurrent ingestion safety.
5.  Summary calculation accuracy.
6.  Validation strategy behavior.

### Run Tests Locally:
```bash
php artisan test
```

### Run Tests in Docker:
```bash
docker compose exec app php artisan test
```

---

## 📡 API Reference

### 1. Ingest Transfers
`POST /api/transfers`

**Sample Payload:**
```json
{
  "events": [
    {
      "event_id": "uuid-1234",
      "station_id": 1,
      "amount": 100.5,
      "status": "approved",
      "created_at": "2026-02-19T10:00:00Z"
    }
  ]
}
```

**Response (Partial Accept):**
```json
{
  "inserted": 1,
  "duplicates": 0,
  "validation_failed_items": []
}
```

**Curl Example:**

```bash
curl -X POST http://localhost:8000/api/transfers \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "events": [
      {
        "event_id": "uuid-1234",
        "station_id": 1,
        "amount": 100.5,
        "status": "approved",
        "created_at": "2026-02-19T10:00:00Z"
      }
    ]
  }'
```

### 2. Station Summary
`GET /api/stations/{station_id}/summary`

**Response:**
```json
{
  "station_id": 1,
  "total_approved_amount": 100.5,
  "events_count": 1
}
```

**Curl Example:**

```bash
curl -X GET "http://localhost:8000/api/stations/1/summary" \
  -H "Accept: application/json"
```

---

## 📦 Deliverables Note
- **Postman Collection**: Found in the root directory as `Event_Transfer_Collection.json`.
- **Commit History**: Readable history showing iterative progress and refactors.
- **Logging**: Basic level logging implemented for critical ingestion failures or duplicate detections at the repository level.
