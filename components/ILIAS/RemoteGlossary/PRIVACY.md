# RemoteGlossary Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The RemoteGlossary component (type `rglo`) represents a glossary resource imported from a partner institution via the ECS campus-connect interface. It does not host glossary content locally; instead it acts as a reference object that redirects users to the remote glossary on the originating institution's ILIAS instance. The object stores only its own online/offline availability status (`availability_type` in the `rglo_settings` table). Access to the object is controlled by the RBAC system: the object is visible and accessible to all users when set to online; persons with `write` permission can view it regardless of its online status.

## Integrated Components

The RemoteGlossary component delegates core persistence and ECS synchronisation logic to the base classes provided by the ECS Interface component. It uses `ilAdvancedMDSubstitution` for optional advanced metadata display in the repository list view. Neither of these integrated components stores personal data on behalf of RemoteGlossary.

## Data being stored

The RemoteGlossary component does not store any personal data. The only database table written by this component is `rglo_settings`, which holds `obj_id` (the ILIAS repository object ID) and `availability_type` (an integer flag indicating online or offline status). No user identifiers, names, e-mail addresses, IP addresses, or other personal data are written to this table.

## Data being presented

The RemoteGlossary component does not present personal data. The repository list view (`ilObjRemoteGlossaryListGUI`) displays the object's title, an optional organisation name sourced from the ECS metadata, and the online/offline status. None of these values are personal data. A person with `read` permission can view the object and follow the link to the remote institution; a person with `write` permission can additionally edit the object settings.

## Data being deleted

When the RemoteGlossary object is deleted from the repository and subsequently removed from the trash ("delete from trash"), the corresponding row in `rglo_settings` is removed by the inherited object deletion logic of `ilRemoteObjectBase`. No personal data is deleted in this process, as none is stored.

## Data being exported

The RemoteGlossary component does not provide an export of personal data, as none is stored.
