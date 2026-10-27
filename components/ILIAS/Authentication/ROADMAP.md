# Roadmap

## Short Term

- **Purge the KeyValueStorage data of deleted users without the container.**
  `ilAuthenticationAppEventListener` reacts to `deleteUser` and reads the KeyValueStorage
  services from `global $DIC`, since `ilAppEventListener` is static and not wired through the
  component bootstrap. Once events can be contributed through the bootstrap, the listener
  should receive `ILIAS\KeyValueStorage\Services` and the subject provider of the users instead.

## Mid Term
- **Introduce clearer and more consistent status methods for** `ilAuthSession`.
  Currently, determining the actual authentication state is cumbersome for consumers.
  The distinction between `isValid()` and `isAuthenticated()` is unclear, and calling
  `isAuthenticated()` without deeper knowledge of ILIAS internals may lead to confusion,
  for example, the "Anonymous" user is also treated as authenticated.

### Improve Architecture

- Introduce repository pattern
- Improve DI handling
- Factor business logic out of UI classes

## Long Term
- Fix overall structure. There are several services dealing with diffent auth methods, but all also have dependent code inside the 
  authentication service. This should be split up into decouple the code.