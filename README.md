# Core Logistics Engine (MVP)

Core Logistics Engine is a high-performance system designed to solve bottlenecks in cross-border logistics management. It focuses on decoupling data ingestion from the user experience, allowing the system to handle massive amounts of carrier updates (webhooks) without impacting performance.

## 🚀 Overview

In logistics, operators often face server overloads when thousands of package updates arrive simultaneously. This project solves that by implementing an event-driven architecture where data is ingested instantly and processed asynchronously.

### Key Features
- **Asynchronous Ingestion**: Webhooks are received and immediately queued in Redis, ensuring a fast `202 Accepted` response to couriers.
- **Flexible Data Schema**: Uses PostgreSQL **JSONB** to store variable shipment attributes (dimensions, customs data, etc.) without needing constant database migrations.
- **High-Performance Search**: Implements **GIN Indexes** on JSONB columns for ultra-fast filtering and searching of dynamic shipment metadata.
- **Optimized Dashboard**: Uses **Eager Loading** to eliminate the N+1 query problem, ensuring the operator's view loads instantly regardless of the number of carriers.

---

## 🛠 Tech Stack

- **Backend**: PHP 8.3 + Laravel 11
- **Database**: PostgreSQL 15+ (JSONB & GIN Indexing)
- **Queue/Cache**: Redis
- **Frontend**: Tailwind CSS + Laravel Breeze (Blade)
- **Infrastructure**: Docker & Docker Compose

---

## 🏗 Architecture

### Data Flow
`Carrier Webhook` $\rightarrow$ `WebhookController` $\rightarrow$ `Redis Queue` $\rightarrow$ `ProcessWebhookJob` $\rightarrow$ `PostgreSQL (JSONB)`

1. **Ingestion**: The `WebhookController` receives the POST request, validates the payload, and dispatches a `ProcessWebhookJob`.
2. **Processing**: The Worker picks up the job from Redis, determines the carrier, and uses `updateOrCreate` to maintain shipment records.
3. **Visualization**: The `ShipmentController` retrieves data using optimized queries and renders it in a paginated dashboard.

---

## 💻 Local Deployment Guide

Follow these steps to get the project running on your machine.

### Prerequisites
- Docker & Docker Compose
- Git
- Composer (optional, handled by Docker)
- Node.js & NPM (for frontend assets)

### Step-by-Step Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd core-logistics-engine
   ```

2. **Start the Infrastructure**
   ```bash
   docker compose up -d
   ```

3. **Install Dependencies & Initialize**
   ```bash
   docker compose exec app composer install
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate
   ```

4. **Setup Frontend Assets**
   ```bash
   npm install
   npm run build
   ```

5. **Start the Queue Worker** (Open a new terminal)
   ```bash
   docker compose exec app php artisan queue:work
   ```

6. **Access the Application**
   - Dashboard: `http://localhost:8080/shipments`
   - Register a user: `http://localhost:8080/register`

---

## 🧪 Testing & Simulation

### Manual Simulation
The project includes a Python simulation script to test the system under load:
```bash
# Install dependencies
pip install -r simulations/requirements.txt

# Run simulation
python3 simulations/simulate_webhooks.py
```

### Automated Tests
To run the professional test suite (Unit & Feature tests):
```bash
docker compose exec app php artisan test
```

---

## 📂 Project Structure

- `app/Http/Controllers`: Webhook and Dashboard logic.
- `app/Jobs`: Asynchronous processing logic.
- `database/migrations`: DB schema including JSONB and GIN index definitions.
- `simulations/`: Python scripts for load and flow simulation.
- `TECHNICAL_FRAMEWORK.md`: Detailed architectural guide for scaling.
