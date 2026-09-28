# Learning History Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Learning History component aggregates learning achievement entries from other components and presents them in a timeline. The entries can contain **achievement or completion events**, related object titles and **timestamps**, including a user's **first login date**.

The component does not store these achievement records in its own database tables. It reads them at runtime for the user whose history is being displayed.

## Integrated Components

- [AccessControl](../AccessControl/PRIVACY.md) checks whether referenced repository objects can be linked for the user whose history is displayed.
- [Badge](../Badge/PRIVACY.md) provides badge-award entries.
- [Certificate](../Certificate/PRIVACY.md) provides entries for acquired certificates.
- [COPage](../COPage/PRIVACY.md) stores the configuration of Learning History page content elements and renders them in pages.
- [Course](../Course/PRIVACY.md) provides course learning-objective completion entries.
- [Dashboard](../Dashboard/PRIVACY.md) provides the Achievements area through which a user's own learning history can be accessed.
- [Portfolio](../Portfolio/PRIVACY.md) can embed the history of the portfolio owner in a portfolio page.
- [Skill](../Skill/PRIVACY.md) provides skill-achievement and self-evaluation entries.
- The **Tracking** service provides learning-progress completion entries.
- The **User** service provides the first-login date used for the corresponding entry.

## Configuration

- The global `enable_learning_history` setting controls whether the Learning History service is active and whether its main navigation entry is available. Disabling it does not delete achievement data stored by the provider components.
- A Learning History page content element can store a **date range** and a selection of achievement providers in the page XML. These settings control which personal achievement entries are presented; the configuration is stored and managed by the [COPage](../COPage/PRIVACY.md) component.

## Data being stored

The Learning History component itself does not store any personal data. It reads achievement entries and a user's **first login date** at runtime from the integrated provider components; those components remain responsible for storing the data.

The component's page content element stores only presentation configuration, such as the selected date range and provider classes, in the COPage page XML. The global activation setting is system configuration and is not personal data.

## Data being presented

- A user can view their own timeline in the Achievements area. Depending on the active providers, it can show badge awards, certificate acquisitions, learning-progress completions, course learning-objective completions, skill achievements and self-evaluations, and the user's **first login date**.
- Entries can present related object or achievement titles and the **date and time** of the event. Certificate entries can include a link to download the user's certificate.
- A related repository object is linked only when the history subject has **Read** permission for a reference to that object; otherwise the entry is shown without that object link.
- When a Learning History page content element is displayed in a Portfolio, it presents the **portfolio owner's** history. Portfolio sharing and access rules determine which visitors can see the page.
- The page content element can restrict the displayed period and provider types according to its stored configuration.

## Data being deleted

The Learning History component does not store personal achievement data and has no personal-data deletion operation of its own.

- Deletion and retention of achievement records are handled by the provider components. Learning History reads the provider results and does not create a separate copy that it can delete.
- The service does not remove entries merely because a referenced object is deleted. The component documentation states that entries can remain available when a provider historises the data needed for older entries.
- Deleting a page containing a Learning History element removes its page configuration through the [COPage](../COPage/PRIVACY.md) component; it does not delete the underlying achievement records.
- Deleting a user account or provider data affects the timeline only according to the deletion behavior of the User and provider components.

## Data being exported

- The Learning History component has no dedicated export function.
- When a Learning History element is included in a COPage XML export, its presentation configuration is exported by [COPage](../COPage/PRIVACY.md); the provider achievement records are not stored in that configuration.
- Portfolio HTML and print exports render embedded Learning History elements in offline mode for the portfolio owner. Those exports can therefore contain the owner's displayed achievement entries, related titles and timestamps, subject to the Portfolio export and sharing context.
