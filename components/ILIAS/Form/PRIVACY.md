# Form Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Form component provides legacy form input elements (text fields, file uploads, password inputs,
email inputs, date selectors, location pickers, etc.) used throughout ILIAS to collect user input.
It is a UI framework component and does **not** store personal data in database tables of its own.
All data entered through forms is persisted by the calling components, not by the Form component
itself.

This component is **deprecated** and scheduled for removal in ILIAS 12. It is being replaced by the
UI Input framework in the UI component.

There are two mechanisms within the Form component that temporarily handle user data:

1. **Temporary file uploads**: When a form containing file uploads fails validation, the Form
   component moves uploaded files to a temporary directory on the filesystem. The temporary file
   names include the user's **session ID**. These files are restored on the next form submission
   attempt and are not persisted beyond the session lifecycle.
2. **Session-based form value serialization**: Individual form property values can be serialized
   into the PHP session (via `writeToSession()` / `readFromSession()`). This data is transient
   and is automatically removed when the session expires.

## Integrated Components

- The Form component employs the following components, please consult the respective PRIVACY.md
  files:
    - [AccessControl](../AccessControl/PRIVACY.md) — the location input element
      (`ilLocationInputGUI`) checks RBAC permissions to determine whether to display a geolocation
      configuration hint.
    - [User](../User/PRIVACY.md) — the login input element (`ilUserLoginInputGUI`) queries the
      User component to verify login name uniqueness. Several form elements reference the current
      user object for locale-dependent formatting.
    - [FileUpload](../FileUpload/PRIVACY.md) — the file input element (`ilFileInputGUI`) uses the
      FileUpload service for processing uploaded files.
    - [HTTP](../HTTP/PRIVACY.md) — form elements use the HTTP service to read request parameters
      (POST and query values).
    - [Refinery](../Refinery/PRIVACY.md) — form elements use the Refinery component for input
      transformation and sanitization.
    - UI — the file input element uses the UI upload limit resolver for
      determining maximum upload sizes.
    - Authentication — the password input element
      (`ilPasswordInputGUI`) queries authentication mode settings to determine whether password
      modification is allowed.

## Data being stored

The Form component does **not** write personal data to any database table. It does not own any
database tables.

The following transient data is handled:

- **Session ID in temporary file names**: When a form with file uploads fails validation, uploaded
  files are temporarily stored in the ILIAS data directory under `data/temp/`. The file names
  include the user's **session ID**, a hash value, the form field name, and the original file name.
  This data exists only on the filesystem and is tied to the session lifecycle.
- **Serialized form values in the PHP session**: Form property values may be serialized into the
  PHP session for persistence across page loads (e.g., for table filters). This data is transient
  and disappears when the session ends.

Since no data is written to database tables, the Form component itself does not create persistent
personal data records. The consuming components are responsible for persisting and documenting the
data they collect through forms.

## Data being presented

The Form component does not present personal data on its own. It renders form input fields that
are populated by the calling component. The data displayed within form fields (e.g., a user's
email address in an email input field, or a username in a login field) is provided and controlled
by the consuming component.

The only permission check within the Form component is in `ilLocationInputGUI`: **persons with
the "Visible" permission on the System Folder** are shown a configuration hint when the
geolocation service is not configured. This hint does not contain personal data.

## Data being deleted

The Form component does not delete personal data from database tables, as it does not own any.

- **Temporary upload files** stored in `data/temp/` during failed form validations are removed
  when the form is successfully resubmitted, or they become orphaned when the session expires. The
  ILIAS cron job for cleaning temporary files handles the removal of orphaned temporary files.
- **Session-serialized form values** are cleared via `clearFromSession()` by the calling component,
  or automatically when the PHP session expires.

## Data being exported

The Form component does not export any personal data. It is a UI framework component that provides
input elements to other components. Any export of data collected through forms is handled by the
respective consuming components.
