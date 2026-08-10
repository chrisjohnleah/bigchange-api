# BigChange API

Framework-agnostic PHP SDK for the BigChange REST API and optional JobWatch
attachment service, built on Saloon 4.

```shell
composer require chrisjohnleah/bigchange-api
```

The package owns OAuth 2.0 client-credentials authentication, the required
`customer-id` header, paged REST responses, the provider's page limits, retries
for rate limits/transient server errors, and provider response objects. It does
not know about Laravel, tenants, plans, migrations, local models, or archive
retention.

```php
use ChrisJohnLeah\BigChange\BigChangeClient;
use ChrisJohnLeah\BigChange\Data\BigChangeCredentials;

$client = BigChangeClient::fromClientCredentials(new BigChangeCredentials(
    clientId: $clientId,
    clientSecret: $clientSecret,
    customerId: $customerId,
));

$page = $client->jobs(pageNumber: 1, pageSize: 1000);

foreach ($page->items as $job) {
    // Provider data only: map it in the consuming application.
}
```

Use `get()` and `page()` for newly documented resources. Convenience helpers
cover contact groups, contacts, persons, jobs, users, resources and resource
groups, job types, job groups, recurrences, notes and note types, stock,
vehicles, worksheets, timesheets, sales opportunities, department codes, flags,
nominal codes, VAT codes, job line items, status history, constraints, flag
history, active flags, finance collections, and purchase-order line items.
The `assetManagementClient()` exposes BigChange's separate Asset Management API
base URL, including assets, asset image metadata, and service schedules, with
its documented 2,000-record page limit.

The optional `BigChangeJobWatchClient` is deliberately separate from the OAuth
REST client. It uses JobWatch Basic Auth plus the company-key header for the
documented `JobAddAttachments` operation and should only be enabled when the
customer's BigChange account has that service licensed and provisioned.

## Testing

Run the package contract tests with `composer install` followed by
`vendor/bin/pest`.
