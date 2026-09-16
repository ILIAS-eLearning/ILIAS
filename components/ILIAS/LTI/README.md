# LTI

The LTI component connects ILIAS with external tools and platforms via
[LTI](https://www.1edtech.org/standards/lti):

* **Consumer**: ILIAS launches external tools (repository object `lti`).
* **Provider**: external platforms launch ILIAS objects (administration node `ltis`).

It replaces `LTIConsumer` and `LTIProvider`. Until the migration is finished, those
components still register the objects, events and cron job, and contain code that
has not been reworked yet.

The key words “MUST”, “MUST NOT”, “SHOULD” and “MAY” in this document are to be
interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**
* [Structure](#structure)
* [Where does a class go?](#where-does-a-class-go)
* [Database](#database)
* [Compatibility](#compatibility)
* [Removing LTI 1.1](#removing-lti-11)

## Structure

```text
LTI/
├── LTI.php                   Component definition
├── classes/
│   ├── LTI1p1/               LTI 1.1, kept for compatibility
│   │   ├── Consumer/
│   │   └── Provider/
│   ├── LTIAdvantage/         LTI Advantage (LTI 1.3 and its services)
│   │   ├── Common/
│   │   ├── Consumer/         Launch, DynamicRegistration, DeepLinking, AGS, NRPS
│   │   └── Provider/         Launch, DynamicRegistration, DeepLinking, AGS, NRPS
│   └── Setup/                Setup agent and database update steps
├── resources/                Endpoints
└── templates/default/        Templates of LTI 1.1 are prefixed with tpl.lti1p1_
```

## Where does a class go?

* **LTI1p1**: code that only serves LTI 1.1 (launch with OAuth1 signature, Basic Outcomes).
  No new features are added here.
* **LTIAdvantage**: code that only serves LTI Advantage. Inside each role the code is
  split by service (`Launch`, `DynamicRegistration`, `DeepLinking`, `AGS`, `NRPS`).
  Protocol handling MUST be delegated to the `celtic/lti` library instead of being reimplemented.
* **LTIAdvantage/Common**: only what the Advantage consumer and provider both use.

LTI1p1 and LTIAdvantage MUST NOT share classes. When both need the same logic, each keeps
its own copy, so LTI1p1 can be removed without touching LTIAdvantage.

Classes follow the usual component naming: `class.ilLTI<Area><Name>.php`, without namespace.

## Database

All schema changes are in `classes/Setup/class.ilLTIDatabaseUpdateSteps.php`:

* Steps 1–9 come from `LTIProvider`, steps 10–29 from `LTIConsumer`, step 30 onwards belongs to LTI Advantage.
* New steps MUST be appended and MUST check the current schema before changing it.
* The class MUST NOT be renamed and its steps MUST NOT be renumbered: `il_db_steps` stores
  the executed steps by class name, so installations updated from ILIAS 11 would run them again.

## Compatibility

Installations updated from ILIAS 11 MUST keep working:

* The object types `lti`, `ltiv` and `ltis` do not change.
* No table or column is removed or renamed.
* Public endpoints keep their URLs (e.g. `ltiresult.php`, `lti.php`).
* Class names stored in the database or used by other components do not change,
  e.g. `ilLTIDatabaseUpdateSteps`, `ilLTICronOutcomeService` and `ilLTIConsumerResult`.

## Removing LTI 1.1

LTI1p1 only uses its own classes and the LTI object model (`ilObjLTIConsumer`, `ilLTIConsumeProvider`,
`ilLTIPlatform`). To remove LTI 1.1:

1. Delete `classes/LTI1p1/`.
2. Delete `templates/default/tpl.lti1p1_*.html`.
3. Delete `resources/ltiresult.php` and its endpoint in `LTI.php`.
4. Remove the calls into LTI1p1. Search for `ilLTI1p1` and `ilLTIConsumerResultService` outside `classes/LTI1p1/`.
5. Keep the database tables and columns until a separate, explicit migration removes them.
