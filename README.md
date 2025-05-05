# Foodics Pay Wallet Application

A Laravel-based wallet application that processes incoming bank webhooks and generates standardized XML for money transfers.

## Features

- Processing incoming webhook data from different banks (Foodics Bank, Acme Bank)
- Preventing duplicate transactions with unique identifiers
- Generate transfer XML with conditional elements
- Comprehensive testing including performance tests for large transaction volumes
- Rate limit webhook APIs to prevent abuse

## Technical Implementation

### Architecture & Design Patterns

The application follows Laravel's MVC architecture and uses several design patterns:

1. **Strategy Pattern**: Used for bank parsers, allowing different parsing strategies for different bank formats.
2. **Builder Pattern**: Used for XML generation.
3. **Factory Pattern**: Used to create appropriate parsers based on the bank name.

## Scalability and Webhook Processing Options

For handling high volumes of webhook transactions, several approaches were considered:

#### 1. Queue-Based Processing (Current Implementation)
```
┌────────┐     ┌────────┐     ┌─────────┐     ┌────────────┐
│ Webhook│ ──► │Database│ ──► │Job Queue│ ──► │Async Worker│
└────────┘     └────────┘     └─────────┘     └────────────┘
```
- **Process**: Store webhook in DB → Dispatch job to queue → Process asynchronously

#### 2. Scheduled Command Processing
```
┌────────┐     ┌────────┐              ┌───────────┐     ┌─────────┐
│ Webhook│ ──► │Database│ ◄────────────┤ Scheduled │ ──► │Processor│
└────────┘     └────────┘              │  Command  │     └─────────┘
                                       └───────────┘
```
- **Process**: Store webhook in DB → Return response immediately → Process via scheduled command

#### 3. Direct Message Queue Approach
```
┌────────┐     ┌─────────┐     ┌──────────┐     ┌────────┐
│ Webhook│ ──► │RabbitMQ │ ──► │ Consumers│ ──► │Database│
└────────┘     └─────────┘     └──────────┘     └────────┘
```
- **Process**: Send webhook directly to RabbitMQ → Process via consumers → Update DB

***I recommend this approach when we have a high-volume of webhook transactions, However, it requires complex infrastructure.***

#### 4. In-Memory Processing Queue
```
┌────────┐     ┌───────┐     ┌───────────┐     ┌────────┐
│ Webhook│ ──► │ Redis │ ──► │Background │ ──► │Database│
└────────┘     └───────┘     │ Processor │     └────────┘
                             └───────────┘
```

## Duplicate Transaction Prevention

I implemented two approaches to ensure transaction idempotency:

#### 1. Composite Unique Index Approach
```
┌────────────┐     ┌──────────────────────────────────────┐
│Transaction │ ──► │Composite Unique Index                │
│   Data     │     │(client_id, reference,                │
└────────────┘     │transaction_date, bank_name, amount)  │
                   └──────────────────────────────────────┘
```
- Database-level uniqueness constraint on multiple columns


#### 2. Unique Identifier Approach
```
┌────────────┐     ┌──────────────┐     ┌──────────────┐
│Transaction │ ──► │Generate md5  │ ──► │Unique Column │
│   Data     │     │Identifier    │     │Constraint    │
└────────────┘     └──────────────┘     └──────────────┘
```
- Generate MD5 hash from transaction attributes, store in unique column.
- **Process**: Query for existing identifiers → Filter → Insert new transactions
- see TransactionData::generateUniqueIdentifier() method

## API Endpoints

### Webhooks

- `POST /api/v1/webhooks/{bank}`: Receive webhook from a bank
- `GET /api/v1/webhooks/{webhook_id}/status`: Get webhook processing status

### Transfers

- `POST /api/v1/transfer`: Create a new transfer and generate XML message


## Installation and Setup (Sail Docker Setup)

1. Clone the repository
2. Set up environment variables:
   ```
   cp .env.example .env
   ```
3. Install dependencies:
   ```
   composer install
   ```
4. Run sail
    ```
   ./vendor/bin/sail up -d
    ```
5. Generate application key:
   ```
   ./vendor/bin/sail artisan key:generate
   ```
6. Run migrations:
   ```
   ./vendor/bin/sail artisan migrate --seed
   ```
7. Access the application at `http://localhost:6065`

## Testing

The application includes comprehensive tests:

1. Unit tests for bank parsers, XML builder, jobs, commands, and architecture test.
2. Feature tests for webhook processing and transfer money.
3. Performance tests for large transaction volumes

Run tests with:

```
./vendor/bin/sail artisan test
```

Run performance tests separately:

```
./vendor/bin/sail artisan test --group=performance
```


## Database Schema

### Clients

- `id`: Primary key
- `name`: Client name
- `balance`: Current balance
- Timestamps: `created_at`, `updated_at`

### Transactions

- `id`: Primary key
- `client_id`: Foreign key to clients table
- `reference`: Transaction reference
- `amount`: Transaction amount
- `transaction_date`: Date and time of the transaction
- `meta`: Additional transaction information (JSON)
- `bank_name`: Name of the bank that sent the transaction
- `unique_identifier`: Unique identifier to prevent duplicates
- `status`: Transaction status (pending, completed, failed)
- Timestamps: `created_at`, `updated_at`

### Webhooks

- `id`: Primary key
- `raw_data`: Original webhook payload
- `bank_name`: Name of the bank that sent the webhook
- `status`: Webhook status (pending, processing, processed, failed)
- `error_message`: Error message if processing failed
- Timestamps: `created_at`, `updated_at`

