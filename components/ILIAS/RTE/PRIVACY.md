# RTE Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The RTE (Rich Text Editor) component integrates TinyMCE as a browser-based WYSIWYG editor into ILIAS. It provides editor configuration and rendering support for rich-text input fields used across various ILIAS components (e.g. forum posts, exercise assignments, general content). The component manages system-wide settings that control which HTML tags are permitted per context module (general, assessment, forum posts), and tracks a per-user preference for whether the rich text editor interface is shown. The component itself does not perform any independent data processing beyond delegating the user preference to the User component and storing system configuration via `ilSetting`.

## Integrated Components

The RTE component delegates storage of the per-user editor preference (`show_rte`) to the User component via `ilObjUser::writePref()`. Media object references embedded in rich-text content are resolved through the Media Objects component (`ilObjMediaObject`), but media object storage is not managed by RTE itself.

## Data being stored

The RTE component does not write personal data directly to database tables. However, it indirectly stores one item of personal data:

- **User editor preference (`show_rte`)**: A per-user preference indicating whether the rich text editor is displayed or hidden (value `0` or `1`). This is written via `ilRTESettings::setRichTextEditorUserState()` which calls `ilObjUser::writePref('show_rte', ...)`, causing the value to be stored in the `usr_pref` table (managed by the User component). The preference is set automatically when the editor detects a mobile browser (forces value `0`) or when a user manually toggles the editor state.

System-wide editor configuration (allowed HTML tags per module, selected editor type) is stored in the `settings` table under the `advanced_editing` namespace via `ilSetting`. These settings contain no personal data.

## Data being presented

The `show_rte` user preference value is read by `ilRTESettings::getRichTextEditorUserState()` to determine whether to render the TinyMCE editor for the currently authenticated user. This affects only the user's own editor view; no personal data from this preference is displayed to other users. The session cookie value is passed to TinyMCE JavaScript templates (`SESSION_ID` variable) for authenticated media upload requests, but is not rendered visibly in the UI.

## Data being deleted

The `show_rte` preference stored in `usr_pref` is deleted as part of the standard user account deletion process, which is handled entirely by the User component. The RTE component provides no dedicated deletion mechanism of its own.

## Data being exported

The RTE component does not provide any data export functionality. No personal data managed by this component can be exported through the RTE component itself.
