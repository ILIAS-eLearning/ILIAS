# CategoryReference Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The CategoryReference component (object type `catr`) provides a mechanism to place a reference (shortcut) to an existing Category object at one or more additional locations in the ILIAS repository tree. When a user accesses a CategoryReference, they are transparently redirected to the target Category. The component is a thin specialisation of the ContainerReference infrastructure: all data storage, access-control resolution, and lifecycle management are delegated to the parent `ilContainerReference` class. The CategoryReference component itself contains no additional data-processing logic beyond identifying its target type as `cat` and its own type as `catr`.

## Integrated Components

The CategoryReference component delegates all data handling to the ContainerReference component. No other ILIAS components that process personal data are directly integrated within CategoryReference itself.

## Data being stored

The CategoryReference component does not store any personal data. The mapping between a CategoryReference object and its target Category (stored in the `container_reference` table) is maintained by the ContainerReference component, which records only object identifiers — no personal data.

## Data being presented

The CategoryReference component does not present any personal data. The list GUI (`ilObjCategoryReferenceListGUI`) displays the title, description, and icon of the target Category, along with a "reference deleted" status alert when the target is no longer accessible. None of this output contains personal data. Persons with the `visible` permission can see the reference entry in the repository; persons with the `write` permission additionally see a settings command.

## Data being deleted

The CategoryReference component does not manage deletion of personal data. Deleting a CategoryReference object removes only the reference entry itself (handled by ContainerReference and the ILIAS object lifecycle). Permanent removal occurs when a person with the `delete` permission moves the object to the trash and subsequently performs a delete from trash. Deletion of the CategoryReference does not affect the target Category or any personal data stored there.

## Data being exported

The CategoryReference component supports XML export via `ilCategoryReferenceExporter` and `ilCategoryReferenceXmlWriter`, which extend the ContainerReference export infrastructure. The exported XML contains the reference object identifier and the ILIAS installation ID. No personal data is included in the export.
