# Category Reference Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Category Reference component creates references to existing categories so that a category can be accessed from another position in the repository.

## Integrated Components

The Category Reference component employs the following services and components:

- The **Object** service stores the user ID of the account that created the reference as its owner and the creation and update timestamps.
- [AccessControl](../AccessControl/PRIVACY.md) controls which accounts may see or modify the reference and access the referenced category.
- [Container](../Container/PRIVACY.md) provides the container-reference functionality used by this component.
- The **Category** component provides the referenced category and its contents. Personal data handled by the category is documented by that component.
- [Container Reference](../ContainerReference/PRIVACY.md) provides the shared reference implementation and deletion handling.

## Data being stored

The Category Reference component itself does not store any personal data.

## Data being presented

The Category Reference component itself does not present any personal data.

## Data being deleted

**Reference deletion**: Deleting a category reference removes its entry from the `container_reference` table. The associated object data, including owner and timestamps, is handled by the **Object** service.

## Data being exported

An XML export of the category reference is provided. It does not contain any personal data.