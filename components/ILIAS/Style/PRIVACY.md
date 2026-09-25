# Style Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Style component manages two distinct areas of visual presentation in ILIAS: **Content Styles** (CSS-based formatting rules applied to content objects such as pages, wikis, and portfolios) and **System Styles** (skin/theme selection for the overall ILIAS interface). Content styles are stored as objects in the repository and define characteristics, parameters, colors, templates, and media queries. System styles control which skin is active globally or per repository category. Users can personalize their interface by selecting a preferred skin and style from the available activated options; this preference is stored per user via the User component's preference mechanism (`usr_pref` table, keys `skin` and `style`).

## Integrated Components

The Style component delegates user preference persistence to the User component via `ilObjUser::setPref()` and `ilObjUser::getPref()`. Access control checks are performed through the RBAC system but no personal data from that system is stored by Style itself.

## Data being stored

The Style component's own database tables (`style_char`, `style_parameter`, `style_setting`, `style_template`, `style_template_class`, `sty_media_query`, `style_folder_styles`, `style_data`, `syst_style_cat`, `settings_deactivated_s`) store only style definitions, template structures, category-to-substyle mappings, and activation state — none of which contain personal data.

One item of personal data is stored indirectly: each user's preferred skin and style identifiers are written to the `usr_pref` table (managed by the User component) using the keys `skin` and `style`. This happens when a user saves their personal settings. The purpose is to restore the user's chosen visual theme on subsequent logins.

## Data being presented

The style preference (skin and style name) selected by a user is displayed back to that user on the personal settings page ("Main Settings" section). No personal data of other users is presented through this component. A person with the `write` or `sty_write_content` permission on a style object can manage content style definitions, but these definitions do not contain personal data.

## Data being deleted

The style preference entries in `usr_pref` (keys `skin` and `style`) are reset to system defaults when the user actively reverts their personal style setting. They are removed as part of user account deletion, which is handled by the User component. Content style objects stored in the repository are deleted permanently when a person with the appropriate repository permission deletes the object from trash. System style category assignments (`syst_style_cat`) are deleted when the assignment is explicitly removed by a person with administration permission.

## Data being exported

The `ilStyleDataSet` and `ilStyleExporter` classes support XML export of content style objects. Exported data covers style characteristics, parameters, colors, templates, template class assignments, media queries, and style settings — all of which are style definition data and contain no personal data. User style preferences are not included in any export.
