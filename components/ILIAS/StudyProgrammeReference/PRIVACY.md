# StudyProgrammeReference Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The StudyProgrammeReference component provides a reference (alias) object of type `prgr` that points to an existing Study Programme (`prg`) object in the ILIAS repository tree. It acts as a shortcut or soft link allowing the same Study Programme to appear in multiple locations without duplicating it. The component does not introduce its own data storage and delegates all substantive behaviour — including any data handling — to the referenced Study Programme and the base ContainerReference infrastructure.

## Integrated Components

The StudyProgrammeReference component delegates all data handling to the following components, which have their own privacy documentation:

- [StudyProgramme](../StudyProgramme/PRIVACY.md)
- [ContainerReference](../ContainerReference/PRIVACY.md)

## Data being stored

The StudyProgrammeReference component does not store any personal data.

## Data being presented

The StudyProgrammeReference component does not present any personal data.

## Data being deleted

The StudyProgrammeReference component does not delete any personal data.

## Data being exported

The StudyProgrammeReference component does not export any personal data.
