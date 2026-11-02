# Privacy Data Types

Privacy Data Types make personal data visible at the PHP type level. A
wrapped value knows **where it came from** (its `Source`) and hands out
the raw value only through `resolve()`, stating **what the code needs it
for** (a `TechnicalRequirement`: store it, present it, export it, hand it
to another component, process it). `resolve()` call sites are found by
static analysis wherever PHPStan knows the type of the wrapper.

The privacy documentation of a component is generated from their use: every
`resolve()` call states where the value is presented, to whom and what for
(or why it is stored, exported or handed on), and becomes a sentence in a
`PRIVACY_DATA.md` with the sections of a PRIVACY.md, see
[Generating the privacy documentation](#generating-the-privacy-documentation).
When the code changes, the documentation changes with it.

This is what the types are for:

- **Type safety.** Personal data no longer travels as anonymous `string`s.
  A `PostalAddress` cannot be mixed up with a `string`, the raw value is
  only available through `resolve()`, and PHPStan knows the type of the
  released value. An opt-in rule flags wrappers passed directly to
  `json_encode()`, `var_dump()` and similar functions.
- **Documentation that follows the code.** The privacy documentation is
  derived from the `resolve()` calls, so nobody has to keep it in sync by
  hand.

This is what they are **not**:

- They are not a legal instrument. A `TechnicalRequirement` describes what
  the code does with a value. It is not the purpose or the legal basis of
  processing in the sense of the GDPR, and nothing here claims compliance
  with it. What a requirement states with `$in_order_to` is a plain
  description for the privacy documentation.
- They do not record anything. `resolve()` returns the value and nothing
  else happens at runtime; there is no hook to log accesses.

**Table of Contents**
* [Concept](#concept)
* [Scope](#scope)
* [Available types](#available-types)
* [Sources](#sources)
* [Technical requirements](#technical-requirements)
* [Usage](#usage)
  * [Creating types, sources and requirements](#creating-types-sources-and-requirements)
  * [Wrapping values (repositories)](#wrapping-values-repositories)
  * [Resolving values (consumers)](#resolving-values-consumers)
  * [Transforming without resolving](#transforming-without-resolving)
  * [Testing](#testing)
* [Bundles](#bundles)
* [Defining the personal data of a component](#defining-the-personal-data-of-a-component)
* [Static analysis](#static-analysis)
* [Cost](#cost)
* [Migrating a component](#migrating-a-component)
* [Generating the privacy documentation](#generating-the-privacy-documentation)

## Concept

```
PrivacyDataType<T>
    ├── getSource(): Source        where does the value come from?
    ├── resolve(TechnicalRequirement): T   release the raw value
    └── __toString(): string       masked, e.g. "…\PostalAddress(***) from usr_data.(street,…)"
```

Two properties fall out of this design:

1. **Static analysability.** A PHPStan rule records every
   `resolve()` call site, its technical requirement with its phrases, and
   its component. The generator renders the documentation from it.
2. **Leak protection.** `__toString()` masks the value; the PHPStan rules
   in `privacy.neon` (opt-in, see [Static analysis](#static-analysis))
   flag passing a wrapper to `var_dump()`, `print_r()`, `var_export()`,
   `json_encode()` and `serialize()`.

What the code does with a raw value after `resolve()` is not visible to
the analysis (e.g. writing it to a log): resolve as late as possible, so
the requirement describes the actual use.

Passing a wrapper around needs nothing — only *releasing the raw value*
requires a technical requirement. Prefer handing the wrapper itself to other
services and resolving as late as possible.

## Scope

The generic types in this component cover the personal data fields of a
user profile (`usr_data`: address, email, phone, name fields, birthday,
matriculation, ...). The types available so far are listed below. Whether
and when further fields follow is decided field by field; types for data
that only one component has are that component's decision (see
[Defining the personal data of a component](#defining-the-personal-data-of-a-component)).
User IDs in particular
are referenced almost everywhere and are out of scope until there is a
separate decision for them.

Explicitly **not** wrapped: passwords and authentication secrets — those
must never travel through generic value objects at all.

## Available types

| Type | Wrapped value | `Factory` method | Catalogue getter |
|---|---|---|---|
| `Types\PostalAddress` | `Types\PostalAddressValue` (street, city, zipcode, country) | `postalAddress()` | `$sources->user()->postalAddress()` |
| `Types\PersonName` | `Types\PersonNameValue` (firstname, lastname, title) | `personName()` | `$sources->user()->personName()` |
| `Types\Affiliation` | `Types\AffiliationValue` (institution, department) | `affiliation()` | `$sources->user()->affiliation()` |
| `Types\GeoLocation` | `Types\GeoLocationValue` (latitude and longitude as strings, zoom from `loc_zoom`) | `geoLocation()` | `$sources->user()->geoLocation()` |
| `Types\Login` | `string` | `login()` | `$sources->user()->login()` |

Further types (`EmailAddress`, `PhoneNumber`, ...) are added one at a
time, when a component needs them. A type is worth adding when it
completes the documentation of a component.

## Sources

A `Source\Source` describes the origin of a value:

| Source | Use when the value comes from | `describe()` |
|---|---|---|
| `DbTableColumn` | a single database column | `usr_data.street` |
| `DbTableColumns` | several columns of one table forming one compound value | `usr_data.(street,city,zipcode,country)` |
| `UserInput` | a form or other direct user input | `user_input:profile_form` |
| `ExternalApi` | an external system (LDAP, Shibboleth, XML import, ...) | `api:shibboleth.homePostalAddress` |
| `LegacySource` | an unmigrated write path where the origin is unknown — migration TODO | `legacy:profile_data_wither` |

Do **not** instantiate `DbTableColumn`/`DbTableColumns` with string
literals in consuming code. Use the named getters of a source catalogue,
so that every personal data column is registered in exactly one place:
`$sources->user()` for the user account (`usr_data`), or the
catalogue of the component, see
[Defining the personal data of a component](#defining-the-personal-data-of-a-component).
The PHPStan rule `PreferKnownSourcesRule` checks this when
`scripts/PHPStan/privacy.neon` is included in a
PHPStan run; the escape hatch for genuinely undocumented columns is a
`@privacy-undocumented` annotation.

`DbTableColumn` and `DbTableColumns` implement `Source\DbTarget` and can
therefore also act as the target of a `StoreInTable` requirement.

## Technical requirements

A `TechnicalRequirement\TechnicalRequirement` states, at the point of
access, what the code does with the raw value. It is deliberately not
called "purpose": it describes the code, not the reason an institution
processes the data.

Every requirement carries the phrases the generated documentation needs.
They are required, and they must be literal strings or constants so the
analysis can read them:

| Factory method | Use when | Rendered in the documentation as |
|---|---|---|
| `storeInTable($target, $in_order_to, $condition)` | persisting to a database table (target from a catalogue) | "If <condition>, <data> is stored to <in_order_to>." |
| `displayToUser($where, $to, $in_order_to, $condition)` | rendering in the UI | "If <condition>, <data> is presented <where> to <to>, to <in_order_to>." |
| `export($where, $by, $in_order_to, $condition)` | a file persons download (table export, vCard) | "If <condition>, <by> can export <data> <where>, to <in_order_to>." |
| `passToComponent($component, $in_order_to)` | handing the raw value to another component (prefer passing the wrapper) | "<Component>: receives <data> to <in_order_to>" |
| `technicalProcessing($in_order_to)` | hashing, comparison, condition checks — no output | listed in the notes for authors |

`LegacyAccess` marks an unmigrated read path whose requirement is unknown;
it is a migration TODO, never used in new code.

Writing phrases that read well:

- `$where` includes its preposition: "in the marker of a person on the
  map", "on the public profile of the person".
- `$to` and `$by` name persons by permission, setting or relation, not by
  role: "persons who can see the public profile", "persons with the
  \"Read\" permission".
- `$in_order_to` continues "to": "show who is where".
- `$condition` continues "If": "the person has published it in their
  profile"; leave it empty if there is none.
- `<data>` is the label of the type (`PostalAddress::label()`: "the postal
  address"). Calls with the same phrases are merged into one sentence, so
  reuse phrases, e.g. as class constants, where the same place shows
  several types.

## Usage

### Creating types, sources and requirements

`Factory` (privacy data types), `Source\Sources` (sources, including the
`user()` catalogue) and `TechnicalRequirement\TechnicalRequirements` are
stateless factories of value objects. Create
them with `new` where they are needed or inject them; there is no service
in the bootstrap or in the legacy container:

```php
use ILIAS\Data\Privacy\Factory;
use ILIAS\Data\Privacy\Source\Sources;
use ILIAS\Data\Privacy\TechnicalRequirement\TechnicalRequirements;

$factory = new Factory();
$sources = new Sources();
$requirements = new TechnicalRequirements();
```

`Sources` offers the ad-hoc constructors (`userInput()`,
`externalApi()`) and the catalogue of the
user account (`$sources->user()->postalAddress()`).
`TechnicalRequirements` offers `storeInTable()`, `displayToUser()`,
`export()`, `passToComponent()` and `technicalProcessing()`.
`LegacySource` and `LegacyAccess` are only for deprecated delegates (see
[Migrating a component](#migrating-a-component)) and are created with `new`
there, so that they stand out.

### Wrapping values (repositories)

Wrap at the point where the value enters PHP, typically when reading
from the database:

```php
$address = $this->factory->postalAddress(
    new PostalAddressValue(
        $row->street ?? '',
        $row->city ?? '',
        $row->zipcode ?? '',
        $row->country ?? ''
    ),
    $this->sources->user()->postalAddress()
);
```

Values entering from a form or an external system use the corresponding
source:

```php
$address = $factory->postalAddress(
    new PostalAddressValue(street: $input),
    $sources->userInput('profile_form')
);
```

### Resolving values (consumers)

Create the requirement inline in the `resolve()` call, with string
literals or catalogue getters as arguments, so the analysis can read it. A
requirement held in a variable is recognised without its arguments; any
other expression is listed as "Unclassified".

```php
// Persisting — the target column(s) show up in the notes for authors:
$value = $address->resolve($requirements->storeInTable(
    $sources->user()->postalAddress(),
    'keep it as part of the profile of the user account'
));
$fields['street'] = [\ilDBConstants::T_TEXT, $value->street];

// Rendering:
$street = $address->resolve($requirements->displayToUser(
    'on the public profile of the person',
    'persons who can see the public profile',
    'show the profile the person has published',
    'the person has published it in their profile'
))->street;

// Technical processing, no output:
$hash = hash('sha256', serialize(
    $address->resolve($requirements->technicalProcessing('pseudonymise the person in statistics'))
));
```

### Transforming without resolving

Changing a value is not disclosure. `PostalAddress` offers withers
(`withStreet()`, ..., `withFieldsOf()`) that derive a new instance; the
given source replaces the previous one. The other types have no withers
yet.

```php
$changed = $address->withStreet('Sidestreet 7', $sources->userInput('profile_form'));
```

### Testing

`components/ILIAS/Data/tests/Privacy/Fixtures` provides the
`PrivacyDataTypeAssertions` trait and `UnitTestSource` /
`UnitTestTechnicalRequirement` for constructing and resolving instances in
tests (never use these in production code — and never use `LegacySource` /
`LegacyAccess` in tests, those mark unmigrated production paths):

```php
$address = new Factory()->postalAddress($value, new UnitTestSource());

$this->assertResolvesTo($address, $value, new UnitTestTechnicalRequirement());
$this->assertToStringDoesNotExposeValue($address, 'Mainstreet 5');
```

## Bundles

A compound type such as `PostalAddress` is one value from one source,
resolved as a whole. A `PrivacyDataBundle` groups personal data that
belongs together but comes from different places; it has no value of its
own and is never resolved, its parts are:

```php
interface PrivacyDataBundle
{
    /** @return iterable<string, PrivacyDataType<mixed>|PrivacyDataBundle> */
    public function parts(): iterable;
}
```

The profile of a user (`ILIAS\User\Profile\Data`) is such a bundle of its
typed fields. Bundles can contain bundles, up to all personal data about
one person; such a bundle does not exist yet, it is what an export of the
data of a person would build on.

## Defining the personal data of a component

A component that handles personal data:

1. **Names its tables and columns in a source catalogue**, a class
   implementing `Source\SourceCatalogue` in the component itself, e.g.
   `ILIAS\Certificate\Privacy\CertificateSources`. Only catalogues may
   name tables and columns as literals (`PreferKnownSourcesRule`). The
   columns of the user account, which all components share, are in
   `Source\Known\UserSources`.
2. **Uses the generic types** in `ILIAS\Data\Privacy\Types` where they
   fit, and may define its own types for data that only it has, created
   with `new` (the `Factory` covers the generic types only).
3. **States the phrases at every `resolve()` call**; the generated
   documentation of the component follows from them.

A catalogue is a marker interface; it must be constructible without
arguments and return sources built from constants. An own type only needs
its label for the documentation; the value can also be a scalar:

```php
// components/ILIAS/Rating/src/Privacy/RatingSources.php
readonly class RatingSources implements SourceCatalogue
{
    public function rating(): DbTableColumn
    {
        return new DbTableColumn('il_rating', 'rating');
    }
}

// components/ILIAS/Rating/src/Privacy/Rating.php
/** @extends AbstractPrivacyDataType<int> */
readonly class Rating extends AbstractPrivacyDataType
{
    public static function label(): string
    {
        return 'the rating';
    }
}

// where the value is read
$rating = new Rating((int) $row['rating'], new RatingSources()->rating());
```

## Static analysis

The PHPStan code lives in
`scripts/PHPStan` (with the other PHPStan rules of ILIAS) and provides:

- `NoRawValueAccessRule` — a `PrivacyDataType` must not be passed to
  `var_dump`, `print_r`, `var_export`, `json_encode`, `serialize`;
- `PreferKnownSourcesRule` — `new DbTableColumn('lit', 'lit')` outside
  a `SourceCatalogue` is flagged (`@privacy-undocumented` escape
  hatch);
- `PrivacyResolveReportRule` — reports every `resolve()` call site for the
  documentation generator.

`resolve()` is typed as the wrapped `T` by PHPStan's native generics
resolution — no extension needed.

The rules are opt-in; include them in a run like this:

```
vendor/composer/vendor/bin/phpstan analyse \
    -c scripts/PHPStan/privacy.neon \
    --autoload-file=vendor/composer/vendor/autoload.php \
    components/ILIAS/<Component>
```

`privacy-analysis.neon` is only for the documentation generator, never
for linting: it reports every `resolve()` call as an "error".

## Cost

Wrapping and resolving a value is a few object constructions and
nothing else; no I/O, no logging. Tables such as the MyStaff user list
only resolve the address when an address column is selected.

## Migrating a component

1. Wrap the value in the component's repository/DTO where it is read
   (source: a catalogue) and written (`StoreInTable`).
2. Keep existing getters/setters working as `@deprecated` delegates that
   resolve with `LegacyAccess` (getters) and write with `LegacySource`
   (setters) — nothing breaks. The delegate shows up once under
   "Not yet migrated" in the notes for authors; its callers, including
   those in other components, are not listed, so the documentation is
   incomplete for that data until the accessor is removed.
3. Migrate consumers call site by call site to real requirements — one
   pull request per type and consuming component.
4. When no `LegacyAccess` entries remain for a field, remove the
   deprecated accessors.
5. Run the generator; the new uses appear in `PRIVACY_DATA.md`.

## Generating the privacy documentation

```
php scripts/Privacy/generate-privacy-docs.php --run-phpstan [--component=Maps] [--dry-run | --check]
```

`--phpstan-output=<file>` keeps the analysis; without `--run-phpstan`, the
generator reads an earlier analysis from that file.

- Every component that resolves privacy data types gets a
  `PRIVACY_DATA.md` next to its handwritten `PRIVACY.md`, with the
  sections of a PRIVACY.md (see
  [How to write a PRIVACY.md](../../../../../docs/development/how-to-write-a-privacy.md)).
  "Integrated components", "Data being stored", "Data being presented" and
  "Data being exported" are generated from the requirements; a section
  without any says that it has not been documented here yet and points to
  the handwritten file. "Data being deleted" and "General information"
  have no counterpart in the code yet.
- Notes for the authors follow in an HTML comment at the end, visible in
  the file and in reviews but not in the rendered documentation: every
  call with its code location and table, phrases that are not literals,
  technical processing, unclassified calls and deprecated accessors.
- Only data handled through privacy data types appears; the file grows
  with every type and every migrated call, and is meant to replace the
  handwritten `PRIVACY.md` once it covers everything. Until the authority
  of a component switches, the handwritten file stays authoritative; the
  generator never writes it.
- The file contains no date and no line numbers, so it only changes when
  what the code does with personal data changes. `--check` writes nothing
  and exits with 1 if a report is missing or outdated, and, for an
  analysis of all components, if a report is orphaned.
