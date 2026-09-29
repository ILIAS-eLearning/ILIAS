# Roadmap

## Short Term

### Remove `RBACServicesLegacyProxy`
`Container::rbac()` falls back to `RBACServicesLegacyProxy` when `RBACServices` is not bound. The fallback
exists because a good number of tests build a container with single rbac mocks (`rbacreview`, `rbacsystem`,
`rbacadmin`) instead of the bootstrapped service. Once those tests bind `RBACServices` themselves, the proxy
and the fallback can be removed.

### Let `RBACServices` depend on interfaces
`RBACServices` hands out the three legacy classes `ilRbacReview`, `ilRbacSystem` and `ilRbacAdmin`, and
`ILIAS\AccessControl\PublicInterface\RBAC` is still an empty interface. As soon as AccessControl defines a
public surface for permission checks, `RBACServices` should expose that, and components like `FileServices`
(`UploadRestrictionBypassLegacyProxy`) can use it instead of the container.

## Mid Term

...

## Long Term

...
