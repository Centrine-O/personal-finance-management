# Personal Finance Management Database Schema

## Overview

Our database is designed to handle all aspects of personal finance management. Here's how the tables relate to each other:

## Entity Relationship Diagram (Conceptual)

```
┌─────────────┐     ┌──────────────┐     ┌─────────────┐
│    Users    │────▶│   Accounts   │────▶│Transactions │
│             │     │              │     │             │
│ - id        │     │ - id         │     │ - id        │
│ - email     │     │ - user_id    │     │ - user_id   │
│ - name      │     │ - name       │     │ - account_id│
│ - currency  │     │ - type       │     │ - amount    │
│ - settings  │     │ - balance    │     │ - date      │
└─────────────┘     └──────────────┘     └─────────────┘
       │                                        │
       │            ┌──────────────┐           │
       └───────────▶│  Categories  │◀──────────┘
                    │              │
                    │ - id         │
                    │ - user_id    │
                    │ - name       │
                    │ - type       │
                    │ - parent_id  │
                    └──────────────┘
                           │
       ┌───────────────────┼───────────────────┐
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐     ┌──────────────┐     ┌─────────────┐
│   Budgets   │     │    Bills     │     │    Goals    │
│             │     │              │     │             │
│ - id        │     │ - id         │     │ - id        │
│ - user_id   │     │ - user_id    │     │ - user_id   │
│ - period    │     │ - category_id│     │ - target    │
│ - total     │     │ - amount     │     │ - current   │
└─────────────┘     │ - due_date   │     │ - deadline  │
       │            └──────────────┘     └─────────────┘
       │
       ▼
┌─────────────┐
│Budget_Cat.  │
│             │
│ - budget_id │
│ - category_id│
│ - allocated │
│ - spent     │
└─────────────┘

┌──────────────────┐
│Recurring_Trans.  │
│                  │
│ - id             │
│ - user_id        │
│ - template_data  │
│ - frequency      │
│ - next_due       │
└──────────────────┘
```

## Table Descriptions

### 1. **Users Table**
The central table containing all user accounts and their preferences.

**Key Features:**
- Basic profile info (name, email, avatar)
- Financial preferences (currency, timezone)  
- Security settings (2FA, login tracking)
- Notification preferences
- Soft deletes for data retention

### 2. **Categories Table** 
Organizes income and expenses into meaningful groups.

**Key Features:**
- Hierarchical (parent/child relationships)
- User-specific or system-wide
- Visual customization (colors, icons)
- Income/expense/transfer types
- Budgeting integration

### 3. **Accounts Table**
Represents where money is stored (bank accounts, credit cards, cash, etc.).

**Key Features:**
- Multiple account types (checking, savings, credit, investment)
- Balance tracking with credit limits
- Bank integration support (external IDs)
- Visual customization
- Auto-sync capabilities

### 4. **Transactions Table** 
The core financial data - every income, expense, and transfer.

**Key Features:**
- Links to accounts and categories
- Detailed metadata (payee, location, notes)
- Receipt attachment support
- Recurring transaction links
- Split transaction support
- Reconciliation status
- Business/tax tracking

### 5. **Budgets Table**
Budget planning for specific time periods.

**Key Features:**
- Flexible periods (weekly, monthly, yearly)
- Income and expense planning
- Template support for reuse
- Rollover settings
- Approval workflow
- Progress tracking

### 6. **Budget_Categories Table** (Pivot)
Links budgets to categories with allocated amounts.

**Key Features:**
- Allocated vs spent tracking
- Usage percentage calculation
- Category-specific alerts
- Priority levels
- Performance analysis

### 7. **Goals Table**
Savings goals and financial objectives.

**Key Features:**
- Target amount and date
- Progress tracking
- Auto-contribution setup
- Milestone notifications
- Different goal types
- Visual progress indicators

### 8. **Bills Table**
Recurring bills and payment reminders.

**Key Features:**
- Flexible frequency patterns
- Automatic payment tracking
- Reminder notifications
- Variable amount support
- Payment history
- Due date management

### 9. **Recurring_Transactions Table**
Templates for automatically generating regular transactions.

**Key Features:**
- Flexible scheduling
- Amount variation support
- Auto-generation settings
- Occurrence limits
- Status management
- History tracking

## Database Design Principles Used

### 1. **Normalization**
- Proper foreign key relationships
- No data duplication
- Each table has a single responsibility

### 2. **Performance Optimization**
- Strategic indexing on frequently queried columns
- Composite indexes for complex queries
- Soft deletes to preserve data integrity

### 3. **Security**
- No sensitive data in plain text
- Proper constraint validations
- Foreign key cascading rules

### 4. **Flexibility**
- JSON columns for flexible metadata
- Enum values for controlled vocabularies
- Extensible category and tag systems

### 5. **Financial Accuracy**
- DECIMAL columns for precise money calculations
- Proper currency handling
- Transaction audit trails

## Common Query Patterns

### User's Monthly Spending by Category
```sql
SELECT c.name, SUM(t.amount) as total
FROM transactions t
JOIN categories c ON t.category_id = c.id
WHERE t.user_id = ? 
  AND t.type = 'expense'
  AND t.transaction_date >= ?
  AND t.transaction_date <= ?
GROUP BY c.id, c.name
ORDER BY total DESC
```

### Budget vs Actual Analysis
```sql
SELECT 
  bc.allocated_amount,
  bc.spent_amount,
  (bc.allocated_amount - bc.spent_amount) as remaining,
  c.name as category_name
FROM budget_categories bc
JOIN categories c ON bc.category_id = c.id
JOIN budgets b ON bc.budget_id = b.id
WHERE b.user_id = ? AND b.status = 'active'
```

### Net Worth Calculation
```sql
SELECT 
  SUM(CASE WHEN type IN ('checking', 'savings', 'investment') 
           THEN balance 
           ELSE -balance END) as net_worth
FROM accounts 
WHERE user_id = ? 
  AND include_in_net_worth = true 
  AND is_active = true
```

This schema provides a solid foundation for building a comprehensive personal finance management system with room for future enhancements.