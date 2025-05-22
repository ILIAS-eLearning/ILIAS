# DataProtection Service

## Public API

The public API of this service can be accessed in the followin way:

```php
$api = $DIC['legalDocuments']->provide(\ILIAS\DataProtection\Consumer::ID)->publicApi();
```

Additionally to the required interface the returned object is an instance of `ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\PublicApi`.
