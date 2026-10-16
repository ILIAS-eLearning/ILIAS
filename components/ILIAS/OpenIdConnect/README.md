# OpenID Connect

The key words “MUST”, “MUST NOT”, “REQUIRED”, “SHALL”, “SHALL NOT”, “SHOULD”,
“SHOULD NOT”, “RECOMMENDED”, “MAY”, and “OPTIONAL” in this document are to be
interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**
* [Role Assignment Rules](#role-assignment-rules)
  * [Configuration](#configuration)
  * [Mapping Format](#mapping-format)
  * [Claim Matching](#claim-matching)
  * [Assignment on Login](#assignment-on-login)
  * [Default Role](#default-role)
  * [Update on Subsequent Logins](#update-on-subsequent-logins)
  * [Limitations](#limitations)

## Role Assignment Rules

Role assignment rules define how global ILIAS roles are assigned to user
accounts that authenticate via OpenID Connect. Evaluation happens during
user synchronisation on every successful OpenID Connect login
(`ilOpenIdConnectUserSync`).

Administrators configure the rules in
`Administration » Users and Roles » Authentication and Registration » OpenID Connect » Role Assignment`.
Developers implement and extend the matching and assignment logic in
`ilOpenIdConnectUserSync::parseRoleAssignments()`.

### Configuration

* Only **global** roles can be configured as mapping targets. The anonymous
  role MUST NOT be offered for selection.
* Local roles MUST NOT be configured and are not supported by the current
  synchronisation.
* Each global role MAY have:
  * a mapping value in the form `claim::value` (see [Mapping Format](#mapping-format)), and
  * an option that restricts synchronisation to the first login
    (see [Update on Subsequent Logins](#update-on-subsequent-logins)).
* An empty mapping value means that the respective role is ignored by the
  synchronisation.

### Mapping Format

A role mapping value MUST use the following format:

```
<claim>::<value>
```

Example:

```
roles::employee
```

* `<claim>` is the name of a claim in the OpenID Connect user info response.
* `<value>` is the expected claim value that triggers assignment of the
  mapped ILIAS role.
* Leading and trailing whitespace around claim name and value is ignored.
* Values that do not contain exactly one `::` separator are rejected when
  saving the configuration.

### Claim Matching

For each configured mapping, ILIAS evaluates the corresponding claim from the
provider user info object:

* If the claim is a **string**, it matches when its trimmed value equals the
  configured `<value>`.
* If the claim is an **array**, it matches when the configured `<value>` is
  contained in the list of claim entries. Supported array entry types are:
  * plain strings, and
  * objects that expose an `id` or `value` property (the property value is
    compared).
* Unsupported array entry types MUST cause synchronisation to fail with a
  domain error, so that unexpected provider payloads are not silently ignored.
* If the claim is missing, or the configured value is not present, the mapping
  does **not** match.

### Assignment on Login

When a mapping matches, ILIAS assigns the corresponding global role to the
user account by writing a user import `Role` element with
`Action="Assign"` and `Type="Global"`.

* Multiple mappings MAY match for the same login. In that case, all matching
  roles are assigned.
* Role assignment is additive with respect to already existing role memberships
  of the account.

### Default Role

A default global role is configured in the OpenID Connect user synchronisation
settings and is used as a fallback for **newly created** accounts only:

* If user creation is allowed and **no** role mapping matches during the first
  login, the default global role MUST be assigned.
* If at least one role mapping matches during account creation, the default
  role is **not** assigned by the fallback path.
* The default role MUST NOT be used as a fallback for already existing accounts.

### Update on Subsequent Logins

Each role mapping MAY be restricted so that it is synchronised only the first
time a user logs in:

* If the option *Only Synchronise Automatically the First Time a User Logs In*
  is enabled for a mapping, that mapping is evaluated only while the account is
  being created. Later logins MUST NOT assign the role through that mapping.
* If the option is disabled, a matching mapping assigns the role on every
  successful OpenID Connect login.

### Limitations

The following behaviour is **not** implemented:

* ILIAS MUST NOT de-assign (detach) a mapped global role when a previously
  matching claim is missing or no longer contains the configured value on a
  later login.
* Role synchronisation is therefore **assign-only**. Once a mapped global role
  has been assigned, losing the corresponding provider claim does **not** remove
  that role membership automatically.
* There is no separate administration setting that enables or disables
  automatic role de-assignment for OpenID Connect.

Administrators who need to revoke mapped roles after claims change MUST do so
manually (for example in user administration) or by other processes outside
OpenID Connect synchronisation.
