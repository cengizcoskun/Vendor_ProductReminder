# Product Reminder Module for Magento 2 - Elryan

## Overview

The Product Reminder module allows customers in Magento 2 e-commerce stores to track specific products and create reminders about them. With this module, customers can set up email reminders about a product for a specific date in the future.

Developer: **Yasin Cengiz Coşkun - Elryan**

## Table of Contents
1. [Features](#features)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [API Reference](#api-reference)
6. [Cron Jobs](#cron-jobs)
7. [Technical Details](#technical-details)
   - [Database Schema](#database-schema)
8. [Development Approach](#development-approach)
9. [Support](#support)

## Features

- Create reminders for products
- Automatic email notifications on specified dates
- REST API endpoints for managing reminders (GET, INSERT, UPDATE, DELETE)
- Reminder settings on the admin panel
- Secure API access control
- Customizable email templates
- Observer for handling product reminder deletions after product deletions
- Custom database table for storing reminders
- Advance notice reminders (one week before the actual reminder date)
- Extra: Verification of Customer ID and Product ID sent from API

## Requirements

- Magento 2.4.x or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Magento_Customer module
- Magento_Catalog module

## Installation

### Installation via Composer

```bash
composer require vendor/module-product-reminder
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:clean
```

### Manual Installation

1. Copy the module code to the `app/code/Vendor/ProductReminder` directory
2. Run the following commands:

```bash
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:clean
```

## Configuration

Module configuration can be accessed from **Stores > Configuration > Vendor Extensions > Product Reminders** menu.

Available settings:

- **Enabled**: Enable or disable the module
- **Email Sender**: Sender identity for reminder emails
- **Default Reminder Message**: Default reminder email message


## API Reference

API endpoints are available through the REST API:

### Create Reminder

```
POST /rest/V1/product-reminder
```

Input parameters:
```json
{
  "customerId": 1,
  "productId": 42,
  "reminderDate": "2024-12-31",
  "status": "pending" // Optional, default: "pending"
}
```

### Get Customer Reminders

```
GET /rest/V1/product-reminder/:customerId
```

### Delete Reminder

```
DELETE /rest/V1/product-reminder/:id
```

### Update Reminder

```
PUT /rest/V1/product-reminder/:id
```

Input parameters:
```json
{
  "customerId": 1,
  "productId": 42,
  "reminderDate": "2024-12-31",
  "status": "pending" // Optional
}
```

## API Authentication

The API endpoints support two authentication methods:

1. **Token-based Authentication**: Use the `Authorization: Bearer [token]` header for API access
2. **Customer Session Authentication**: For frontend access when customers are logged in

API security ensures customers can only access their own data.

## Cron Jobs

The module includes the following cron jobs:

- **send_reminders**: Runs daily, sends reminder emails scheduled for that day
- **send_advance_notice**: Runs daily, sends advance notices for upcoming reminders

Cron job configuration is done in the `etc/crontab.xml` file.

## Technical Details

### Database Schema

The module creates a table named `vendor_product_reminder`:

| Column          | Type          | Description                                   |
|-----------------|---------------|-----------------------------------------------|
| reminder_id     | int (auto)    | Primary key                                   |
| customer_id     | int           | Customer ID                                   |
| product_id      | int           | Product ID                                    |
| reminder_date   | date          | Reminder date                                 |
| created_at      | timestamp     | Creation date                                 |
| updated_at      | timestamp     | Last update date                              |
| status          | varchar       | Status (pending, sent, cancelled)             |


## Development Approach

This module was developed with the following principles in mind:

1. **Magento Best Practices**: Following Magento 2 development guidelines, including proper use of dependency injection, avoiding the use of ObjectManager, and implementing thorough PHPDoc comments.

2. **Extensibility**: The module is designed to be easily extended with new features.

3. **Security**: API endpoints implement proper authorization and validation to ensure that customers can only access their own data.

4. **Maintainability**: The code is organized in a clear structure with appropriate separation of concerns.

5. **Performance**: The module is designed to efficiently handle large numbers of reminders.

## Support

For any issues or questions, please contact [y.cengiz.coskun@gmail.com](mailto:y.cengiz.coskun@gmail.com).