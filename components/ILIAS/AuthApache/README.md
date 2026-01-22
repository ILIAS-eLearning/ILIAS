# Apache-based Authentication (AuthApache)

This document gives a technical, in-depth introduction to the AuthApache component
and how to extend it with custom `UsernameProvider`s.

The key words “MUST”, “MUST NOT”, “REQUIRED”, “SHALL”, “SHALL NOT”, “SHOULD”,
“SHOULD NOT”, “RECOMMENDED”, “MAY”, and “OPTIONAL” in this document are to be
interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**

## Overview

`AuthApache` is responsible for handling authentication data that is provided by Apache
(or other upstream web server components) and for producing an ILIAS username that can be used
to map or create an ILIAS account.

The username resolution is configurable (in the ILIAS "Apache Authentication" administration) with
two alternatives:

- Direct Mapping: Use a single server parameter directly as the username.
- Username Provider: Delegate resolution to the `UsernameResolver`, which evaluates registered `UsernameProvider`s in
 priority order to determine the first valid username.

### Direct Mapping

When the configuration is set to `AuthProviderApache::APACHE_AUTH_TYPE_DIRECT_MAPPING`, the username is taken
directly from a single HTTP server parameter without transformation. The parameter key is configured via the setting
`apache_auth_username_direct_mapping_fieldname`. If the configured key exists in
`ServerRequestInterface->getServerParams()` and its value is non-empty, that value is used as the
ILIAS username.

### Username Provider

The component exposes a small extension point: implementers can provide classes that
implement `ILIAS\ApacheAuth\UsernameProvider\UsernameProvider` to extract a username from the incoming
HTTP `ServerRequestInterface` in case `AuthProviderApache::APACHE_AUTH_TYPE_BY_FUNCTION` is set as mode.

The following sections document the contract, expectations, and patterns for implementing such providers 
and integrating them into ILIAS.

#### Terminology and Contract

- Request: A `Psr\Http\Message\ServerRequestInterface` instance representing the incoming HTTP request.
- UsernameProvider: A class that implements `ILIAS\ApacheAuth\UsernameProvider\UsernameProvider` and returns
 a `UsernameInterface` when invoked with a request.
- Resolved username: A `UsernameInterface` that returns a non-empty string via `asString()` and where `isEmpty()`
 returns `false`.
- Null / no-result: A `UsernameInterface` that represents the absence of a username; use `NullUsername` or
 a `UsernameInterface` implementation whose `isEmpty()` returns `true`.

Contract:
- Input: `ServerRequestInterface`.
- Output: `ILIAS\ApacheAuth\UsernameProvider\UsernameInterface`.
- Providers MUST NOT throw uncaught exceptions during normal resolution. They MUST catch errors and return
  a `NullUsername` instance to preserve system stability.

####  Architecture and Key Classes

This section describes the responsibilities of the main classes in `UsernameProvider`.

`UsernameProvider` interface

Responsibilities:
- Define two methods:
  - `getPriority(): int`: Higher values are evaluated earlier by `UsernameResolver`.
  - `getUsername(ServerRequestInterface $request): UsernameInterface`: Return a resolved `Username` or
   an empty representation.

Implementation notes:
- Classes that implement `UsernameProvider` should be stateless or at least safely
  instantiable without external dependencies if they are to be used
  with `UsernameProviderFactory::fromClassNames()`.

`UsernameInterface`, `Username`, `NullUsername`

- `UsernameInterface` is an interface and defines `asString(): string` and `isEmpty(): bool`.
- `Username` is an immutable value object that contains a non-empty username and throws on construction
  if the supplied string is empty.
- `NullUsername` is the marker object for "no username resolved" and returns an empty string
 and `isEmpty() === true`.

`UsernameProviderFactory`

Behavior:
- Accepts a list of fully-qualified class names.
- It attempts to instantiate each class with and only returns objects
 that implement `UsernameProvider`.

Implications:
- The factory intentionally keeps instantiation simple. Providers MUST be instantiable without
  constructor arguments to be used here.

`UsernameResolver`

Behavior:
- Sorts providers by `getPriority()` in descending order.
- Invokes `getUsername()` for each provider in order and returns the first non-empty `UsernameInterface`.
- If none returns a non-empty username it returns `NullUsername`.

#### Example

This example is the minimal, idiomatic approach.

```php
<?php

use ILIAS\ApacheAuth\UsernameProvider\UsernameProvider;
use ILIAS\ApacheAuth\UsernameProvider\Username;
use ILIAS\ApacheAuth\UsernameProvider\NullUsername;
use Psr\Http\Message\ServerRequestInterface;

final class HeaderUsernameProvider implements UsernameProvider
{
    public function getPriority(): int
    {
        return 100;
    }

    public function getUsername(ServerRequestInterface $request): \ILIAS\ApacheAuth\UsernameProvider\UsernameInterface
    {
        try {
            $values = $request->getHeader('REMOTE_USER');
            if (empty($values)) {
                return new NullUsername();
            }

            $candidate = trim($values[0]);
            if ($candidate === '') {
                return new NullUsername();
            }

            if (preg_match('/\s/', $candidate)) {
                return new NullUsername();
            }

            return new Username($candidate);
        } catch (\Throwable $e) {
            return new NullUsername();
        }
    }
}
```
