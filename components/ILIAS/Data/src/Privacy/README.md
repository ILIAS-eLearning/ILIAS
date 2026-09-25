# Privacy Data Types

Privacy Data Types make personal data machine-readable at the PHP type
level. A wrapped value knows **where it came from** (its `Source`) and
hands out the raw value only through `resolve()`, stating **why it is
being accessed** (a `Purpose`). Every resolve call is reported to the
audit logger and is statically collectable, so per-component privacy
documentation can be generated from the code itself.

This complements the handwritten `PRIVACY.md` files of the components: it
answers, from the code, which components store, display, and forward
personal data — the basis for GDPR documentation and Art. 15 access
requests.

**Table of Contents**
* [Concept](#concept)
* [Scope](#scope)
* [Available types](#available-types)
* [Sources](#sources)
* [Purposes](#purposes)
* [Usage](#usage)
  * [Obtaining the service](#obtaining-the-service)
  * [Wrapping values (repositories)](#wrapping-values-repositories)
  * [Resolving values (consumers)](#resolving-values-consumers)
  * [Transforming without resolving](#transforming-without-resolving)
  * [Testing](#testing)
* [Contributing a logger backend](#contributing-a-logger-backend)
* [Static analysis and generated documentation](#static-analysis-and-generated-documentation)
* [Migrating a component](#migrating-a-component)

## Concept

```
PrivacyDataType<T>
    ├── getSource(): Source        where does the value come from?
    ├── resolve(Purpose): T        release the raw value (logged)
    └── __toString(): string       masked, e.g. "…\PostalAddress(***) from usr_data.(street,…)"
```

Three properties fall out of this design:

1. **Audit trail.** Every `resolve()` call is passed to the configured
   `PrivacyLogger` with type, source, and purpose — never the raw value.
2. **Static analysability.** A PHPStan collector records every
   `resolve()` call site, its purpose, and its component. A generator
   turns this into per-component privacy documentation.
3. **Leak protection.** `__toString()` masks the value; PHPStan rules
   forbid passing a wrapper to `var_dump()`, `json_encode()`,
   `serialize()` and friends.

Passing a wrapper around is free and unlogged — only *releasing the raw
value* requires a purpose. Prefer handing the wrapper itself to other
services and resolving as late as possible.

## Scope

Wrapped: personal data (address, email, phone, name fields, IDs, ...).

Explicitly **not** wrapped: passwords and authentication secrets — those
must never travel through generic value objects at all.

## Available types

| Type | Wrapped value | Notes |
|---|---|---|
| `Types\PostalAddress` | `Types\PostalAddressValue` (street, city, zipcode, country) | The residential address of a user; compound value over four `usr_data` columns |

Further types (`UserId`, `EmailAddress`, `PhoneNumber`, ...) will be
added as the respective data fields are migrated. The rollout
deliberately starts small — see the feature wiki entry for the staged
migration strategy.

## Sources

A `Source\Source` describes the origin of a value:

| Source | Use when the value comes from | `describe()` |
|---|---|---|
| `DbTableColumn` | a single database column | `usr_data.street` |
| `DbTableColumns` | several columns of one table forming one compound value | `usr_data.(street,city,zipcode,country)` |
| `UserInput` | a form or other direct user input | `user_input:profile_form` |
| `ExternalApi` | an external system (LDAP, Shibboleth, XML import, ...) | `api:shibboleth.homePostalAddress` |
| `SessionData` | the current session | `session:user_id` |
| `LegacySource` | an unmigrated write path where the origin is unknown — migration TODO | `legacy:ilObjUser::setStreet` |

Do **not** instantiate `DbTableColumn`/`DbTableColumns` with string
literals in consuming code. Use the named getters of the
`Source\Known\KnownSources` catalogue (`$privacy->sources()`), so that
every known personal data column is registered in exactly one place.
This is enforced by a PHPStan rule; the escape hatch for genuinely
undocumented columns is a `@privacy-undocumented` annotation.

`DbTableColumn` and `DbTableColumns` implement `Source\DbTarget` and can
therefore also act as the target of a `StoreInTable` purpose.

## Purposes

A `Purpose\Purpose` states why the raw value is needed, at the point of
access:

| Purpose | Use when | `describe()` |
|---|---|---|
| `StoreInTable` | persisting to a database table (target column(s) required) | `store_in:usr_data.(street,…)` |
| `DisplayToUser` | rendering in the UI | `display_to_user:public_profile` |
| `PassToComponent` | handing the raw value to another component (prefer passing the wrapper instead!) | `pass_to:Mail (signature)` |
| `TechnicalProcessing` | hashing, comparison, condition checks — no output | `technical:pseudonymisation` |
| `LegacyAccess` | an unmigrated read path where the purpose is unknown — migration TODO, never use in new code | `legacy:profile_data_getter` |

## Usage

### Obtaining the service

The entry point is the `ILIAS\Data\Privacy\Services` interface. It is
defined and implemented by the Data component bootstrap.

Modern component (component bootstrap):

```php
// MyComponent.php
$internal[MyRepository::class] = static fn() =>
    new MyRepository(
        $use[\ILIAS\Data\Privacy\Services::class],
        // ...
    );
```

The source and purpose factories are also available individually —
via `$pull` in a component bootstrap or via their FQN keys in the
legacy container:

```php
// MyComponent.php
$internal[MyRepository::class] = static fn() =>
    new MyRepository(
        $pull[\ILIAS\Data\Privacy\Source\Sources::class],
        $pull[\ILIAS\Data\Privacy\Purpose\Purposes::class],
        // ...
    );

// legacy code
$sources = $DIC[\ILIAS\Data\Privacy\Source\Sources::class];
$purposes = $DIC[\ILIAS\Data\Privacy\Purpose\Purposes::class];
```

`Sources` offers the ad-hoc constructors (`userInput()`,
`externalApi()`, `sessionData()`, `legacy()`) and inherits the
`KnownSources` catalogue (`$sources->user()->postalAddress()`).
`Purposes` offers `storeInTable()`, `displayToUser()`,
`passToComponent()`, `technicalProcessing()`, and `legacyAccess()`.
Prefer the factories over `new` at call sites; direct instantiation
remains supported (and is used by detached value objects such as
`Profile\Data` for their legacy markers).

Legacy code (service locator):

```php
global $DIC;
$privacy = $DIC[\ILIAS\Data\Privacy\Services::class];
```

Never construct concrete privacy types with `new` in production code —
only the factory binds the audit logger. (Tests may construct directly.)

### Wrapping values (repositories)

Wrap at the point where the value enters PHP, typically when reading
from the database:

```php
$address = $this->privacy->factory()->postalAddress(
    new PostalAddressValue(
        $row->street ?? '',
        $row->city ?? '',
        $row->zipcode ?? '',
        $row->country ?? ''
    ),
    $this->privacy->sources()->user()->postalAddress()
);
```

Values entering from a form or an external system use the corresponding
source:

```php
$address = $privacy->factory()->postalAddress(
    new PostalAddressValue(street: $input),
    $privacy->sources()->userInput('profile_form')
);
```

### Resolving values (consumers)

Passing the wrapper around needs no purpose. Only release the raw value
where it is actually used:

```php
// Persisting — the target column(s) become part of the audit trail:
$value = $data->getPostalAddress()->resolve(
    $privacy->purposes()->storeInTable($privacy->sources()->user()->postalAddress())
);
$fields['street'] = [\ilDBConstants::T_TEXT, $value->street];

// Rendering:
$street = $address->resolve($privacy->purposes()->displayToUser('public_profile'))->street;

// Technical processing, no output:
$hash = hash('sha256', serialize(
    $address->resolve($privacy->purposes()->technicalProcessing('pseudonymisation'))
));
```

### Transforming without resolving

Changing a value is not disclosure. The withers derive a new instance
without logging; the given source replaces the previous one:

```php
$changed = $address->withStreet('Sidestreet 7', $sources->userInput('profile_form'));
```

### Testing

`components/ILIAS/Data/tests/Privacy/Fixtures` provides an
`InMemoryPrivacyLogger` with assertion helpers, the
`PrivacyDataTypeAssertions` trait, and `UnitTestSource` /
`UnitTestPurpose` for constructing and resolving instances in tests
(never use these in production code — and never use `LegacySource` /
`LegacyAccess` in tests, those mark unmigrated production paths):

```php
$logger = new InMemoryPrivacyLogger();
$factory = new Factory($logger);

$address = $factory->postalAddress($value, new UserInput('test'));
$address->resolve(new DisplayToUser('test_context'));

$logger->assertLoggedOnce();
$logger->assertLastPurposeIs('display_to_user:test_context');
```

## Contributing a logger backend

The audit trail backend is deliberately pluggable and **no backend is
shipped yet** (storage and retention are open community decisions). The
Data component collects all contributed backends into a composite; with
none contributed, logging is a no-op.

A component provides a backend via its bootstrap:

```php
// MyComponent.php
$contribute[\ILIAS\Data\Privacy\Logger\PrivacyLogger::class] = static fn() =>
    new MyPrivacyLoggerBackend(/* lazy dependencies only! */);
```

Rules for backends:

- record **metadata only** (type, source, purpose, timestamp, acting
  user) — never the raw value;
- the constructor is executed at bootstrap build time: no DB, no DIC, no
  HTTP access there — inject closures and evaluate them at `log()` time.

## Static analysis and generated documentation

The PHPStan extension lives in
`components/ILIAS/Data/PHPStan/Privacy` and provides:

- `NoRawValueAccessRule` — a `PrivacyDataType` must not be passed to
  `var_dump`, `print_r`, `var_export`, `json_encode`, `serialize`;
- `StoreInTableTargetRule` — `new StoreInTable(...)` requires a
  `DbTarget` object;
- `PreferKnownSourcesRule` — `new DbTableColumn('lit', 'lit')` outside
  the `Known` catalogue is flagged (`@privacy-undocumented` escape
  hatch);
- `PrivacyResolveCollector` — records every `resolve()` call site.

`resolve()` is typed as the wrapped `T` by PHPStan's native generics
resolution — no extension needed.

The generator turns the collector output into per-component
documentation:

```
php scripts/Privacy/generate-privacy-docs.php --run-phpstan [--component=User] [--dry-run]
```

By default it writes `PRIVACY_DATA.md` next to each component's
handwritten `PRIVACY.md`; `--target=privacy-md` overwrites `PRIVACY.md`
itself (to be used once the community decides to replace the handwritten
files). Resolves through `LegacyAccess`/`LegacySource` are listed in a
separate "Unmigrated (legacy access)" section, making migration progress
visible per component.

## Migrating a component

1. Wrap the value in the component's repository/DTO where it is read
   (source: `KnownSources` catalogue) and written (`StoreInTable`).
2. Keep existing getters/setters working as `@deprecated` delegates that
   resolve with `LegacyAccess` (getters) and write with `LegacySource`
   (setters) — nothing breaks, every unmigrated access shows up in the
   audit trail and the generated report.
3. Migrate consumers call site by call site to real purposes — one
   commit per consuming component.
4. When no `LegacyAccess` entries remain for a field, remove the
   deprecated accessors.
