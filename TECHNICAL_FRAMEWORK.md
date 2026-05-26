# Technical Framework: Core Logistics Engine

## 1. Architecture Overview
The system follows an Event-Driven Architecture to decouple data ingestion from the user experience.

### Data Flow
`Courier Webhook` $\rightarrow$ `WebhookController` $\rightarrow$ `Redis Queue` $\rightarrow$ `ProcessWebhookJob` $\rightarrow$ `PostgreSQL (JSONB)`

## 2. Core Implementation Standards

### Data Layer (PostgreSQL + JSONB)
- **Flexibility**: All courier-specific data must be stored in the `metadata` JSONB column of the `shipments` table.
- **Performance**: Any field within `metadata` that becomes a primary filter for the dashboard must be analyzed for GIN indexing.
- **Querying**: Use Laravel's `->` operator for JSONB queries (e.g., `where('metadata->status', 'delivered')`).

### Background Processing (Redis)
- **Resilience**: All jobs must implement `tries` and `backoff` to handle transient API or database failures.
- **Atomicity**: Use `updateOrCreate` to ensure shipments are not duplicated during webhook retries.

### Visualization (Frontend)
- **N+1 Prevention**: Always use `with()` (Eager Loading) when retrieving shipments and their carriers.
- **Pagination**: Use `paginate()` to ensure the dashboard remains performant as the dataset grows.

## 3. Guide for Adding New Functionality

### Adding a New Courier
1. **Identify Fields**: Map the courier's webhook payload to the internal `metadata` structure.
2. **Strategy Pattern (Future)**: When adding multiple couriers, implement a `CarrierParserInterface` to isolate parsing logic per provider.
3. **Testing**: Use a tool like Postman to send a sample payload to `/api/webhooks/{carrier_code}`.

### Scaling the Database
- If `metadata` searches become slow, consider adding a specific B-tree index on a virtual column for the most queried JSON field.
