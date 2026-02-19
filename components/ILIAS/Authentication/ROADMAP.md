# Roadmap

## Short Term

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