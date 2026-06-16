# Learning History Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

The Learning History component aggregates data from various ILIAS services. Please consult the respective privacy documentation:

- The **Tracking** service provides information about **completed objects** (Learning Progress).
- The **Badge** service provides information about **awarded badges**.
- [Certificate](../Certificate/PRIVACY.md) provides information about **issued certificates**.
- [Skill](../Skill/PRIVACY.md) provides information about **achieved skill levels**.
- The **Course** service provides information about **course completions**.
- The **User** service provides the **first login date** and handles **user identification**.
- [COPage](../COPage/PRIVACY.md) is used to embed the learning history as a page element.
- [Portfolio](../Portfolio/PRIVACY.md) uses the component to display the owner's history in their portfolios.
- [AccessControl](../AccessControl/PRIVACY.md) is used to check permissions for displayed objects.
- The **Object** service is used to retrieve object **titles**, **types**, and **icons**.
- The **Tree** service is used for checking the repository structure (e.g., determining parent courses).

## General Information

The Learning History component provides a chronological overview of a user's learning achievements. It serves as an aggregator and does not persist achievement data in its own database tables. Instead, it queries other services and presents the results in a unified timeline.

## Configuration

- **Global Settings**: Administrators can enable or disable the learning history feature globally.
- **Embedded Settings**: When embedded in a page (via **COPage**), the **date range** and **selected achievement types** (providers) are stored within the page's XML structure.

## Data being stored

The Learning History component itself does not persist personal data in its own database tables. It relies on:

- **global toggle**: The activation status of the feature is stored in the `il_setting` table.
- **page configuration**: Embedding settings are stored as part of the page content in the **COPage** service.

## Data being presented

The component presents the following data in a timeline format:

- **Achievement Title**: A description of what was achieved (e.g., "Course completed", "Badge awarded").
- **Context Object**: The **title** of the object the achievement is related to.
- **Timestamp**: The **date** and **time** when the achievement was recorded.
- **Icon**: A visual representation of the achievement or object type.

When used within a **Portfolio**, the component presents the learning history of the portfolio's **owner** to anyone who has access to that portfolio page.

## Data being deleted

As the component does not store personal data itself, there is no specific data deletion logic. Data removal is handled by the respective source services:

- **User Deletion**: When a user account is deleted, their achievements are removed by the source services (Tracking, Badge, etc.), which automatically removes them from the learning history.
- **Object Deletion**: Deleting an object containing an embedded learning history removes the associated configuration.

## Data being exported

- **Page Export**: If the learning history is embedded in a page, its configuration (but not the actual achievement data) is included in the **COPage** XML export.
- **Portfolio Export**: When a portfolio is exported, the embedded learning history element is included.
