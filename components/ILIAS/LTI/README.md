# LTI

The LTI component connects ILIAS with external tools and platforms via
[LTI](https://www.1edtech.org/standards/lti):

* **Platform**: ILIAS launches external tools (repository object `lti`).
* **Tool**: external platforms launch ILIAS objects (administration node `ltis`).

LTI 1.1 calls these roles consumer and provider.

It replaces the former `LTIConsumer` and `LTIProvider` components.

The keywords “MUST”, “MUST NOT”, “SHOULD” and “MAY” in this document are to be
interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**
* [Structure](#structure)
* [Where does a class go?](#where-does-a-class-go)
* [LTI Advantage security](#lti-advantage-security)
* [User interface](#user-interface)
* [Globals and request input](#globals-and-request-input)
* [Database](#database)
* [Compatibility](#compatibility)
* [Removing LTI 1.1](#removing-lti-11)

## Structure

```text
LTI/
├── LTI.php                   Component definition
├── module.xml                Object types (lti, ltiv, ltis), event listeners and cron jobs
├── LuceneObjectDefinition.xml    What the search indexes of an LTI object
├── classes/
│   ├── Administration/       Administration > LTI (ltis), shared by LTI 1.1 and LTI Advantage
│   ├── Object/               LTI object model (lti, ltiv, tools and platforms), shared by LTI 1.1 and LTI Advantage
│   │   ├── Certificate/      Certificate placeholders and settings of an LTI object
│   │   └── Verification/     Certificate verification of an LTI object (ltiv)
│   ├── Tool/                 ILIAS as the tool platforms launch: launch, login, LTI view, object releases, learning progress sent back; shared by LTI 1.1 and LTI Advantage
│   ├── LTI1p1/               LTI 1.1, kept for compatibility
│   │   ├── Consumer/
│   │   └── Provider/
│   ├── LTIAdvantage/         LTI Advantage (LTI 1.3 and its services)
│   │   ├── Common/
│   │   ├── Platform/         ILIAS launches tools: Launch, DynamicRegistration, DeepLinking, AGS, NRPS
│   │   └── Tool/             Platforms launch ILIAS: Launch, DynamicRegistration, DeepLinking, AGS, NRPS
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
* **LTIAdvantage/Common**: only what the Advantage platform and tool roles both use.
* **Administration**: the administration node (`ilObjLTIAdministration*`). Its screens serve LTI 1.1 and LTI Advantage.
* **Tool**: ILIAS as the tool a platform launches. celtic/lti checks a launch of either version by its
  data, so the launch (`ilLTILaunchReceiver`, `ilLTIDataConnector`), the login (`ilAuthProviderLTI`), the
  LTI view (`ilLTIViewGUI`), the releases of objects (`ilLTIProviderObjectSettingGUI`, `ilLTIRelease`) and
  the learning progress sent back (`ilLTIAppEventListener`, `ilLTICronOutcomeService`) are shared. Only
  what a version adds on top goes to LTI1p1 or LTIAdvantage. Several of these names are fixed by the core:
  `ilAuthProviderLTI`, `ilAuthFrontendCredentialsLTI`, `ilLTIProviderObjectSettingGUI`,
  `ilLTIAppEventListener`, `ilLTICronOutcomeService` and `$DIC['lti']`.
* **Object**: the LTI object model, that is the objects and records LTI itself owns and both LTI
  versions read and write: the repository object `lti` (`ilObjLTITool` and its GUI, access and
  list classes), its certificate verification `ltiv` in `Object/Verification`, the tools ILIAS
  launches (`ilLTITool`, table `lti_ext_provider`) and the platforms that launch ILIAS
  (`ilLTIPlatform`, table `lti2_consumer`). It holds no protocol logic and MUST NOT depend on
  LTI1p1 or LTIAdvantage, so that the model still describes the same objects when one of the two
  versions is removed.

  New code names the two counterparties as LTI Advantage does, tool and platform. The names of the
  LTI 1.1 era survive only where they are data: the tables `lti_ext_provider`, `lti_ext_consumer`
  and `lti2_consumer`, their columns, the language variables, and the ids of tabs that equal their
  language variable. In LTI1p1 consumer and provider are the terms of the LTI 1.1 standard and stay.

  The repository object is `ilObjLTITool`, the external tool it launches `ilLTITool`, as ILIAS does
  with `ilObjForum` and `ilForum`. The classes ILIAS derives from the object class carry its name:
  `ilLTIToolLP`, `ilLTIToolResult`, `ilLTIToolPlaceholderValues` and so on.

LTI1p1 and LTIAdvantage MUST NOT share classes. When both need the same logic, each keeps
its own copy, so LTI1p1 can be removed without touching LTIAdvantage. That rule is about the
protocol. The object model and the administration screens are the two areas both versions use,
because an LTI object and an administered tool are the same thing whichever version launches them.

Classes follow the usual component naming: `class.ilLTI<Area><Name>.php`, without namespace.

## LTI Advantage security

celtic/lti checks and signs every LTI Advantage message and token. ILIAS gives it the keys, the data and
the endpoints:

* **Key pair:** `ilLTIAdvantageKeyPair` (`LTIAdvantage/Common`) holds the one RSA key ILIAS signs with as
  platform and as tool. It is created when first needed and stored in the settings as `lti_1_3_privatekey`
  and `lti_1_3_kid`, the names of earlier releases, so tools and platforms configured against an updated
  installation keep verifying it. `lticerts.php` publishes it as JSON Web Key Set.
* **ILIAS as platform:** `ilLTIAdvantagePlatformConnection` sets up the library for one tool: ILIAS is the
  platform, with the client id it gave the tool and the id of the tool as deployment id, and the tool is the
  default tool of the library, with its PEM key or key set URL. A launch starts the OpenID Connect login at
  the tool, which gets the id_token from `ltiauth.php`; the login waits in the session and is used once.
  Tools often ask `ltiauth.php` with a POST from their own site, which does not carry a SameSite=Lax cookie,
  so the page that starts the launch sends the session cookie again as SameSite=None, as `ilStartUpGUI`
  does for LTI sessions. An embedded launch also offers the platform storage of LTI (`Platform::getStorageJS()`
  of celtic/lti in the page around the iframe), for tools that cannot keep a cookie inside an iframe.
  `ltitoken.php` checks the client assertion of a tool and issues access tokens for the Assignment and
  Grade Services.
* **ILIAS as tool:** `lti.php` takes the OpenID Connect login and the id_token of a platform as well as an
  LTI 1.1 launch. `ilLTIDataConnector` finds the platform by issuer, client id and deployment id, keeps the
  key the library fetched from the key set of the platform and the access tokens of its services. The
  object launched is the one the target link names (`lti.php?ref_id=N`); it must be released to the platform.
  A launch only needs a user id and a resource link id, as long as the columns they are kept in allow (250 and
  255 characters). Name, email and roles are optional, since platforms leave them out for privacy, and the
  login of a new user is built so that it is always valid.

## User interface

New screens are built with the Kitchen Sink components of `$DIC->ui()->factory()`.

The one exception is the accordion of the creation screen of an `lti` object, `ilAccordionGUI`. The
screen offers the ways of creating the object as one section each, with the first one open, and the
Kitchen Sink has nothing that does that. The class is not deprecated and other components still use
it. Any further exception MUST be explained here.

## Globals and request input

* No global variable other than the ILIAS service locator is used. `$_GET`, `$_POST`, `$_REQUEST`,
  `$_SESSION`, `$_COOKIE`, `$_FILES` and `$GLOBALS` MUST NOT be read or written, as required by the
  review criteria in `docs/development/review.md`.
* Parameters come from the HTTP service, `$DIC->http()->wrapper()->query()` and `->post()`, refined with
  `$DIC->refinery()`. A GUI reads the request wrapper in its constructor. The request body comes from
  `$DIC->http()->request()` instead of `php://input`, and session data from `ilSession`.
* `resources/lti.php` sets the command in `$_GET` and `$_POST` before ILIAS starts: ilCtrl takes it from the
  request, and a platform cannot send it. celtic/lti checks the signature against the raw body.
* `resources/ltiauth.php`, and `resources/lti.php` for an OpenID Connect login, remove `client_id` from `$_GET`
  before ILIAS starts: it is the client id ILIAS gave the tool or the platform gave ILIAS, and ILIAS would
  take it for the id of its own client. The query is read back as it came from the request URI.
* The other exception is `ilLTI1p1ProviderLaunchRequestUri`, which rewrites `$_SERVER['REQUEST_URI']`:
  `celtic/lti` builds the URL it checks the OAuth1 signature against from that value
  (`OAuth\OAuthRequest::from_request()`), so there is no API to override it. Any further exception MUST be
  explained in a comment next to the access.

## Database

All schema changes are in `classes/Setup/class.ilLTIDatabaseUpdateSteps.php`:

* Steps 1–9 come from `LTIProvider`, steps 10–29 from `LTIConsumer`, step 30 onwards belongs to LTI Advantage.
* Step 31 is the one data change: it renames the placeholder class the certificate queue stores,
  as Certificate itself does for courses and exercises.
* New steps MUST be appended and MUST check the current schema before changing it.
* The class MUST NOT be renamed and its steps MUST NOT be renumbered: `il_db_steps` stores
  the executed steps by class name, so updated installations would run them again.

## Compatibility

Installations updated from an earlier release MUST keep working:

* The object types `lti`, `ltiv` and `ltis` do not change.
* No table or column is removed or renamed.
* Public endpoints keep their URLs (`ltiresult.php`, `lti.php`, `lticerts.php`, `ltiauth.php`, `ltitoken.php`).
* The LTI Advantage key of ILIAS stays in the settings `lti_1_3_privatekey` and `lti_1_3_kid`, and the
  deployment id of a tool is still its id.
* Class names stored in the database do not change: `ilLTIDatabaseUpdateSteps` (`il_db_steps`) and
  `ilLTICronOutcomeService` (`cron_job`).
* Other components address classes of LTI by name, so renaming one means changing them too:
  `ilLTIToolLP` (ilObjectLP), `ilLTIToolResult`, `ilLTIToolActivityProgress` and
  `ilLTIToolGradingProgress` (ilLPStatusLtiOutcome), `ilLTIToolPlaceholderDescription`,
  `ilLTIToolPlaceholderValues` and `ilCertificateSettingsLTIToolFormRepository` (Certificate),
  `ilLTIAppEventListener` (SCORM), and
  the methods of `ilObjLTITool` the xAPI reports of CmiXapi call (`getInstance`, `isMixedContentType`,
  `getContentType`, `getActivityId`, `getProvider`). Earlier releases named them after `LTIConsumer`.
* An LTI Advantage platform that launches ILIAS is registered once, in `lti2_consumer` with `ref_id` 0.
  Earlier releases registered it per released object (`ref_id` > 0). Those rows MUST still be accepted
  when looking up a platform, as fallback after the registration of the platform.
* The LTI version of a tool is fixed once the tool exists. Earlier releases let the creator switch it
  when editing, which left the credentials of the other version behind; a tool for the other version is
  defined anew instead.
* Columns of `lti_consumer_settings` that `ilObjLTITool` does not know are never written, so that
  saving an object keeps the settings it came with.

## Removing LTI 1.1

LTI1p1 uses its own classes, `classes/Object` and general ILIAS services, never anything from LTIAdvantage.
To remove LTI 1.1:

1. Delete `classes/LTI1p1/`.
2. Delete `templates/default/tpl.lti1p1_*.html`.
3. Delete `resources/ltiresult.php` and its endpoint in `LTI.php`.
4. Remove the calls into LTI1p1. Search for `ilLTI1p1` and `ilLTIConsumerResultService` outside `classes/LTI1p1/`.
   On the tool side these are the key and secret an object is released with (`ilLTIProviderObjectSettingGUI`,
   which then offers only LTI Advantage platforms) and the request URI fix in `ilLTILaunchReceiver`. `ilLTIDataConnector`
   also finds a platform by its consumer key, which only LTI 1.1 uses.
5. Keep the database tables and columns until a separate, explicit migration removes them.
