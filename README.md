# BigChange API

Framework-agnostic PHP SDK for the BigChange REST API, built on Saloon 4.

The package owns OAuth 2.0 client-credentials authentication, the required
`customer-id` header, paged REST responses, the provider's 1,000-record page
limit, retries for rate limits/transient server errors, and provider response
objects. It does not know about Laravel, tenants, plans, migrations, local
models, or archive retention.

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
cover contacts, persons, jobs, users, resources and resource groups, job types,
job groups, recurrences, notes and note types, stock, vehicles, worksheets,
timesheets, sales opportunities, job children, finance collections, and
purchase-order line items.

## Testing

Run the package contract tests with `composer install` followed by
`vendor/bin/pest`.
