# Roadmap

## Short Term

...

## Mid Term

### Own Entry Point for the WOPI Endpoint

`resources/index.php` still enters ILIAS through the "ILIAS Legacy Initialisation Adapter" and builds the
`RequestHandler` from `$DIC`. A WOPI request only needs a small part of ILIAS: the HTTP state, the data signer for
the access token, the resource storage, a few settings and the WOPI tables. The goal is an own `EntryPoint`
contributed in `WOPI.php` which receives its dependencies through the component bootstrap and does not initialise
the rest of ILIAS.

The following services are already available through the bootstrap: `GlobalHttpState`, `DataSigning`,
`IRSSServices`, `PhpUploadLimit`, `LoggerFactoryInterface` and `HttpPathProvider`. Still missing are:

- **Database**: the Database component provides `Connection` only as a lazy proxy on the legacy `$DIC`, so the
  resource storage and the lock repository still depend on the legacy initialisation.
- **Settings**: `wopi_activated`, `saving_interval`, `wopi_discovery_url` and the proof keys are read from
  `ilSetting`. There is no settings or key-value service in the bootstrap yet.
- **Legacy code in the save path**: `ilObjFileStakeholder` and the user name in `GetFileInfoResponse` use
  `global $DIC`.

As an intermediate step, the entry point can be introduced like the one of WebDAV: an own `EntryPoint` with its
dependencies wired in `WOPI.php`, which still calls the legacy initialisation internally until the database and
the settings are available through the bootstrap.

Permissions are deliberately not checked again on every WOPI request, as this would require the whole access
control stack (RBAC, tree, object definitions, conditions). Access is granted when the access token is issued and
ends with the lifetime of the token.

## Long Term

...
