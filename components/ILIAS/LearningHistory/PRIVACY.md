# LearningHistory Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The LearningHistory component provides a timeline-based view of a user's learning achievements.
It does not store personal data itself. Instead, it acts as an aggregation and presentation
service that collects entries from provider components (Badge, Certificate, Tracking/Learning
Progress, Course, Skill) and displays them in chronological order. Additionally, the component
adds a "first login" entry by reading the user's first login date from the User component.

The entire service can be disabled via the global administration setting "enable_learning_history".
When disabled, the main menu entry is hidden and no learning history data is collected or presented.
The learning history can also be embedded as a page content element in Portfolio pages, in which
case it displays the portfolio owner's achievements to visitors of that portfolio.

## Integrated Components

- The LearningHistory component employs the following components, please consult the respective
  PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/release_11/components/ILIAS/AccessControl/PRIVACY.md) – used for checking "read" permissions on
      referenced repository objects when rendering timeline entries as clickable links.
    - Badge – provides learning history entries for badge awards via
      `ilBadgeLearningHistoryProvider`.
    - [Certificate](https://github.com/ILIAS-eLearning/ILIAS/blob/release_11/components/ILIAS/Certificate/PRIVACY.md) – provides learning history entries for earned
      certificates via `ilCertificateLearningHistoryProvider`.
    - [COPage](https://github.com/ILIAS-eLearning/ILIAS/blob/release_11/components/ILIAS/COPage/PRIVACY.md) – the LearningHistory component registers as a page
      content type ("lhist"), allowing learning history timelines to be embedded in pages.
    - Course – provides learning history entries for completed course learning objectives via
      `ilCourseLearningHistoryProvider`.
    - Dashboard – the learning history is accessible through
      the Achievements section of the Dashboard.
    - [Portfolio](https://github.com/ILIAS-eLearning/ILIAS/blob/release_11/components/ILIAS/Portfolio/PRIVACY.md) – learning history can be embedded in Portfolio
      pages, displaying the portfolio owner's timeline to portfolio visitors.
    - [Skill](https://github.com/ILIAS-eLearning/ILIAS/blob/release_11/components/ILIAS/Skill/PRIVACY.md) – provides learning history entries for skill achievements
      and profile completions via `ilSkillLearningHistoryProvider`.
    - Tracking – provides learning history entries for completed learning progress objects via
      `ilTrackingLearningHistoryProvider`.
    - User – the first login date of a user is read to generate the
      "first login" learning history entry.

## Data being stored

This component does not store any personal data itself. It does not own any database tables and
does not write user preferences. All personal data displayed in the learning history is read at
runtime from the respective provider components (Badge, Certificate, Tracking, Course, Skill,
User). Those components are responsible for the storage and lifecycle of their own data.

The only data persisted by this component is the **global activation setting**
("enable_learning_history"), which is a system configuration value and does not constitute
personal data.

## Data being presented

- **Each user** can view their own learning history through the Achievements section in the
  Dashboard. The timeline displays entries from all active providers, including:
    - badge awards (title, parent object, date),
    - certificate acquisitions (title, date),
    - learning progress completions (object title, date),
    - course learning objective completions (objective title, course, date),
    - skill achievements and profile completions (skill/profile title, level, date),
    - the date of the user's first login.
- When a timeline entry references a repository object for which the user has the "Read"
  permission, the entry is rendered as a clickable link to that object. Objects without "Read"
  permission are displayed as plain text without a link.
- **Portfolio visitors** can view the learning history of the portfolio owner when a
  LearningHistory page content element is embedded in a Portfolio page. The visibility of the
  portfolio itself is controlled by the Portfolio component's sharing settings.
- **Persons with the "Write" permission on the LearningHistory Administration** can enable or
  disable the learning history service globally. They do not gain access to any user's learning
  history data.
- **Persons with the "Read" permission on the LearningHistory Administration** can view the
  current activation setting.

## Data being deleted

This component does not store personal data, so there is no personal data to delete within
this component. The data presented in the learning history is owned and managed by the
respective provider components:

- **When a user account is deleted**: The provider components handle the deletion of their own
  data (e.g., badge assignments, learning progress records, certificates). Once deleted, those
  entries will no longer appear in the learning history.
- **When a provider's source data is deleted** (e.g., a badge is revoked, a course is deleted
  from trash): The respective provider will no longer return entries for deleted data. The
  LearningHistory component itself does not retain any residual data.
- **When a Portfolio page containing a LearningHistory element is deleted**: Only the page
  content configuration (date range, selected provider classes) stored by the COPage component
  is removed. No personal data is affected.

As noted in the component's README, the service will keep entries even if the referenced objects
are deleted, as long as the consuming components historise the necessary data. However, this
historisation responsibility lies with the provider components, not with LearningHistory itself.

## Data being exported

The LearningHistory component does not provide any dedicated export functionality. Personal achievement data displayed in the timeline cannot be directly exported by users through this component; any export of the underlying data (e.g., certificates, badges) is handled by the respective source components.

When the learning history is embedded as a page content element, its configuration (selected date range and provider classes) is stored by the COPage component as part of the page's XML structure. This configuration data is therefore included when a COPage or Portfolio page is exported through the respective export mechanisms of those components. No personal achievement data is included in such exports.
