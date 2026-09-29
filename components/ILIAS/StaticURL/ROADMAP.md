# Roadmap

## Short Term

### Give the handlers their dependencies through `Context`
Handlers are contributed via `$contribute[Handler::class]` and therefore constructed at build time, when
`$DIC` is still empty. Several of them work around this by reading `$DIC` lazily in private methods instead
of their constructor:

- `Authentication\StaticUrlHandler`: language
- `Blog\PermanentLink\StaticUrlHandler`: the blog's internal service
- `ilFileStaticURLHandler`: access, ctrl, database, http and the URI builder
- `User\StaticURLHandler`: legal documents, the current user and the user's `LocalDIC`

`Context` is passed to `handle()` and already carries http, refinery, access, ctrl, lng and the URI builder, so
most of these can be taken from there. What is left (database, component-internal services) should be wired
into the handler by its own component once that component is migrated.

### Replace the legacy proxies in `Context`
`src/Legacy/` holds proxies for user, repository tree, language, main template, ctrl and settings. They go away
one by one as their components are migrated. `lng()`, `ctrl()` and `mainTemplate()` return the legacy objects
because they are public contract towards the handlers; changing that is a breaking change for all handlers
and should be done together with the previous point.

## Mid Term

...

## Long Term

...
