# Accordion Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Accordion component provides a reusable UI widget that renders collapsible content panels in either vertical or horizontal orientation. Panels can be expanded or collapsed interactively by the user. When session storage is enabled (via `setUseSessionStorage(true)`), the component tracks which tabs the current user has opened and restores that state on subsequent page loads within the same session. This tracking behaviour is opt-in and controlled by the component that embeds the Accordion — the Accordion itself does not activate it unconditionally.

## Integrated Components

The Accordion component does not delegate to other ILIAS components that handle personal data. It accesses the ILIAS User object (`ilObjUser`) only to read the current user's ID for use as a session-storage key, and it uses `ilSession` for transient storage. No other component with a dedicated PRIVACY.md is integrated.

## Data being stored

When session storage is enabled, the Accordion stores the set of currently opened tab numbers per accordion instance and per user in the PHP session (key: `accordion`, sub-keyed by accordion ID and user ID). The stored value is a semicolon-separated string of tab numbers (e.g. `"1;3"`).

- **Data type**: User ID (integer) used as an index key; opened tab numbers (integers) as the associated value.
- **Storage location**: PHP session only — `ilSession::set("accordion", ...)` / `ilSession::get("accordion")`. No database table is written.
- **Purpose**: Restoring the user's accordion panel state (which panels are open) across page loads within the same session.
- **Persistence**: Data exists only for the duration of the PHP session. It is cleared automatically when the session ends or is destroyed.

When session storage is not enabled, no personal data is stored by this component at all.

## Data being presented

No personal data is rendered to the user by the Accordion component itself. The component displays only the content items (headers and body content) that are passed to it by the embedding component. No user names, email addresses, or other personal attributes are output by the Accordion.

## Data being deleted

Session-stored accordion state is automatically discarded when the user's PHP session ends (logout or session timeout). There is no explicit user-initiated or administrator-initiated deletion mechanism within the Accordion component. Because no data is written to the database, there is no account-deletion hook or trash-based deletion.

## Data being exported

The Accordion component does not provide any export functionality and does not expose any personal data through export mechanisms.
