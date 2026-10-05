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
├── classes/                The addresses ILIAS gives the other side (ilLTIEndpoint), the session cookie of LTI flows and the log of celtic/lti
│   ├── Administration/       Administration > LTI (ltis), shared by LTI 1.1 and LTI Advantage
│   ├── Object/               LTI object model (lti, ltiv, tools and platforms), shared by LTI 1.1 and LTI Advantage
│   │   ├── Certificate/      Certificate placeholders and settings of an LTI object
│   │   └── Verification/     Certificate verification of an LTI object (ltiv)
│   ├── Tool/                 ILIAS as the tool platforms launch: launch, login, LTI view, object releases, learning progress sent back; shared by LTI 1.1 and LTI Advantage
│   ├── LTI1p1/               LTI 1.1, kept for compatibility
│   │   ├── Consumer/
│   │   └── Provider/
│   ├── LTIAdvantage/         LTI Advantage (LTI 1.3 and its services)
│   │   ├── Common/           Key pair, responses of the endpoints, requests waiting in the session
│   │   ├── Platform/         ILIAS launches tools: Launch, DynamicRegistration, DeepLinking, AGS, NRPS
│   │   └── Tool/             Platforms launch ILIAS: DynamicRegistration, DeepLinking, NRPS
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
  Grade Services and the Names and Role Provisioning Services. Tools set up for earlier releases may leave out
  what the library requires: `ltiauth.php` always answers by form post without prompting, and takes the client
  id as `id` too; in a client assertion the issuer and the subject have to be the client id and the signature
  has to verify, while `jti`, `iat`, `exp` and `aud` are only checked when present (a `jti` is used once, the
  audience must be on the host of ILIAS).
* **Addresses:** `ilLTIEndpoint` builds every URL ILIAS gives the other side, from the base URL of the request,
  which is also the issuer of ILIAS, and the instance guid of earlier releases (client id, path and host). Only the
  URLs of `ltiservices.php` come from the configured HTTP path, because under that script the base URL of the
  request ends in the script and its path. `ilLTIAdvantageResponse` ends the requests of the endpoints and the pages
  that post a message to the other side, and `ilLTIAdvantagePendingRequests` keeps in the session what waits to be
  answered: a login, a Deep Linking request, a Dynamic Registration.
* **ILIAS as tool:** `lti.php` takes the OpenID Connect login and the id_token of a platform as well as an
  LTI 1.1 launch. `ilLTIDataConnector` finds the platform by issuer, client id and deployment id, keeps the
  key the library fetched from the key set of the platform and the access tokens of its services. The
  object launched is the one the target link names (`lti.php?ref_id=N`); it must be released to the platform.
  The OpenID Connect login goes back to the platform by GET, as in earlier releases, so that it carries the
  cookies SameSite=Lax holds back from a POST. A platform that registers no token URL for its services may send
  it as the custom parameter `oauth2_access_token_url`. Of the LTI 1.1 platforms that share a consumer key, the
  last one wins, as before. An object keeps the LTI version of its own release when the platform has releases of
  both versions. The roles of a launch map to the local roles of the release as in earlier releases, institution
  roles (faculty, student) and sub-roles included.
  A launch only needs a user id and a resource link id, as long as the columns they are kept in allow (250 and
  255 characters). Name, email and roles are optional, since platforms leave them out for privacy, and the
  login of a new user is built so that it is always valid.
* **Dynamic Registration, ILIAS as platform:** `ilLTIAdvantagePlatformRegistration` (`Platform/DynamicRegistration`).
  A user who may define own tools enters the registration URL of a tool on the creation screen of an `lti`
  object. It opens in an iframe with the OpenID configuration of ILIAS (`lticonfig.php`) and a registration
  token: a JWT signed with the key of ILIAS, valid for an hour, that names the user and the client id the tool
  gets. The tool posts its configuration to `ltiregistration.php` from its server, without a session, so the
  token is all that tells ILIAS who registers. The configuration is checked before it is stored as an own tool of
  that user, and the token is used up with it, since no second tool gets its client id. Errors follow RFC 7591.
  When the tool posts `org.imsglobal.lti.close` from the iframe, or the user says the registration is done,
  the object is created for the tool the session started to register.
* **Dynamic Registration, ILIAS as tool:** `ilLTIAdvantageToolRegistration` (`Tool/DynamicRegistration`), which
  leaves the protocol to celtic/lti. The form of an LTI Advantage platform in the administration shows a
  registration URL (`ltitoolregistration.php`) with a token that names the platform, valid for a day. The platform
  opens it, celtic/lti checks its OpenID configuration and registers ILIAS, and the answer becomes the registration
  of the platform. The registration keeps the id of the token, so each URL registers once; the form shows a new
  one each time. A registration another platform already has, by issuer, client id and deployment id, is refused here and in the form,
  since a launch could not tell the two apart. No session is needed, because the platform opens the URL on its own site, where a session
  cookie of ILIAS is not sent.
* **Deep Linking, ILIAS as platform:** `ilLTIAdvantagePlatformDeepLinking` (`Platform/DeepLinking`). When the
  tool of a new `lti` object offers Deep Linking, the user picks its content in the tool, in an iframe of the
  creation screen, or later from the tool settings of an object for new objects next to it. The request waits in
  the session of the user for that container and is used once: its state goes to the tool as `data` and has to
  come back. celtic/lti checks the signature, expiry and nonce of the response, `ilLTIAdvantagePlatformConnection`
  that it comes from the tool for ILIAS and its deployment. Each resource link becomes an object; its target link
  is kept as the custom parameter `target_link_uri`, as earlier releases did, and the launch uses it. The nonces of
  ILIAS as platform are kept in `lti2_nonce` under `consumer_pk` 0, since ILIAS has no record of its own there.
* **Deep Linking, ILIAS as tool:** `ilLTIAdvantageToolDeepLinking` (`Tool/DeepLinking`). The request arrives at
  `lti.php` like a launch and is checked by celtic/lti. An instructor or administrator of the platform picks among
  the objects released to it, and each goes back as a resource link to `lti.php?ref_id=N`. Nobody is logged in:
  the page with the objects posts them to `lti.php` with a token ILIAS signed that names the platform and the
  return URL, valid for an hour, so it works in an iframe without a session cookie of ILIAS. A request ILIAS
  cannot answer goes back to the platform as an error.
* **Assignment and Grade Services, ILIAS as platform:** `ilLTIAdvantagePlatformGradeService` (`Platform/AGS`),
  served by `ltiservices.php` under the URLs of earlier releases, since tools keep the line item URL of a launch.
  Each `lti` object of a tool that reports grades is one line item, which the launch names; a change of the line
  item changes the title and the maximum score of the object, and it cannot be deleted. A tool may also create
  line items of its own in a course or group where it has objects (`ilLTIAdvantagePlatformLineItemRepository`,
  table `lti_consumer_lineitems` of earlier releases), with the optional fields of the service; their URLs carry
  the id with a minus sign, as before, and they only keep the scores. Everything is done with an access token of
  `ltitoken.php`, for the tool itself only: the object has to be of the tool and the user one who launched it. A
  score is kept in `lti_consumer_grades`; unless a later one is already kept, a fully graded score of an object
  becomes the result and the learning progress is updated. Lists are paged when the tool gives a limit. As in
  earlier releases, a score may give its numbers as strings and its timestamp without time zone, the object may
  have moved out of the context of the URL, and a user is found under the privacy setting of any of their launches.
* **Names and Role Provisioning Services, ILIAS as platform:** `ilLTIAdvantagePlatformMembershipService`
  (`Platform/NRPS`), served by `ltiservices.php` as well (`/membership/{context}/{object}`). Only a tool with the
  option of the service gets it: the launch of its objects names the URL, and the tool reads the members of the
  course or group with their roles (admins and tutors are instructors, members learners), under the privacy settings
  of the tool. The URL names the object because a random user id is one per object; with it, only members who
  launched the object are listed. The list is paged when the tool gives a limit. What the services share, the
  access token, the object and the pages, is in `ilLTIAdvantagePlatformServiceRequest`.
* **Names and Role Provisioning Services, ILIAS as tool:** `ilLTIAdvantageToolMembership` (`Tool/NRPS`). When an
  instructor or administrator of a platform launches ILIAS and the launch names the service, ILIAS reads the members of
  the context and gives every active member the account and the roles of the release, as their own launch would.
  celtic/lti sends the requests and follows the pages; the members are read by ILIAS because the library drops their
  status. Members the platform reports as inactive or deleted lose the roles of the release; members that are not
  in the list keep them, because the same object may be linked from other contexts of the platform. A platform that
  does not answer only leaves a warning in the log. ILIAS asks for the scope when it registers through Dynamic Registration.
* **Assignment and Grade Services, ILIAS as tool:** `ilLTIAppEventListener` sends the learning progress of a user
  of a platform as a score, through celtic/lti, when the launch named a line item and granted the score scope.
* Tokens ILIAS gives out and takes back itself, such as the registration tokens, are signed and checked by
  `ilLTIAdvantageKeyPair` and name what they are for, so that no other token of ILIAS is taken for them.

## Logging

Everything LTI logs goes to the log of the component `lti`, so that the log of an installation tells what happened
in a request without anything else. Its level is set in Administration > Logging.

* **Info:** each step of each flow, with what identifies it on both sides: the launch of an object and the launch
  ILIAS takes (platform, registration, client id, deployment id, resource link, context and user id of the
  platform, nonce), the id_token, the access tokens, every request to the services and the scores, the members
  read from a platform, the accounts created, the outcomes sent, Deep Linking and Dynamic Registration.
* **Warning:** every request ILIAS refuses, with its reason and the same ids, and every call the other side did
  not answer.
* **Debug:** celtic/lti logs every request it gets, every message it sends and every call with their bodies
  and headers (`ilLTILibraryLogger`), and ILIAS the time each call took (`ilLTIHttpClient`). Each request
  starts with the versions of ILIAS, celtic/lti and PHP.

Tokens, id_tokens, client assertions, signatures, secrets and cookies never reach the log
(`ilLTILibraryLogger::hideSecrets()`). To find out what went wrong, set the level of `lti` to Debug, repeat what
failed and take the log. The lines of one request start with the same part of the session id, which changes
once when a launch logs the user in.

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

* Steps 1–9 come from `LTIProvider`, steps 10–29 from `LTIConsumer`, step 30 onwards belongs to LTI Advantage
  (32: the option of a tool to read the members, 33: the optional fields of the line items tools create).
* Step 31 is the one data change: it renames the placeholder class the certificate queue stores,
  as Certificate itself does for courses and exercises.
* New steps MUST be appended and MUST check the current schema before changing it.
* The class MUST NOT be renamed and its steps MUST NOT be renumbered: `il_db_steps` stores
  the executed steps by class name, so updated installations would run them again.

## Compatibility

Installations updated from an earlier release MUST keep working:

* The object types `lti`, `ltiv` and `ltis` do not change.
* No table or column is removed or renamed.
* Public endpoints keep their URLs (`ltiresult.php`, `lti.php`, `lticerts.php`, `ltiauth.php`, `ltitoken.php`,
  `lticonfig.php` and `ltiregistration.php` of Dynamic Registration, and `ltiservices.php` of the Assignment and
  Grade Services and of the Names and Role Provisioning Services). `ltiregstart.php` and `ltiregend.php` of
  earlier releases are gone: only the registration running in the browser used them.
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
* LTI Advantage launches carry what earlier releases sent: the instance guid of the client, path and host, the
  custom parameters under the names they are configured with (`custom_x` stays `custom_x`), a dash for a name the
  privacy settings keep back, and the context label of type and id. The roles follow earlier releases as well.

### Notes for updated installations

What administrators of an updated installation may notice:

* The database update steps 32 and 33 have to run (`setup update`).
* The tools defined before the update do not get the Names and Role Provisioning Services: the option is off
  until it is switched on in the tool, or a tool asks for the scope when it registers again.
* The line items tools created themselves are served again, under their earlier URLs.
* `ltiregstart.php` and `ltiregend.php` are gone; Dynamic Registration uses `lticonfig.php` and `ltiregistration.php`.

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
