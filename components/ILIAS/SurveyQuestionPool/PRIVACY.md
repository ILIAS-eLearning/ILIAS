# SurveyQuestionPool Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The SurveyQuestionPool component provides a reusable repository of survey questions for the ILIAS Survey module. It allows authors to create, manage, and organize question templates (single choice, multiple choice, matrix, metric, and text questions) that can be copied into surveys. The component stores personal data in the form of the question author's name and the question creator's user account ID.

## Integrated Components

The SurveyQuestionPool component belongs to and integrates with the following ILIAS components that maintain their own privacy documentation:

- [Survey](../Survey/PRIVACY.md)
- [MetaData](../MetaData/PRIVACY.md)

## Data being stored

- **Table `svy_question`, field `owner_fi`**: Stores the ILIAS user ID (`usr_id`) of the user who created the question. This reference is set automatically from the current user session at question creation time and is used to identify questions owned by a specific user (e.g., for purging incomplete questions belonging to a user). Accessible to administrators and users with write access to the question pool.

- **Table `svy_question`, field `author`**: Stores the full name of the question author as a free-text string. By default this is populated from `$ilUser->getFullname()` at the time of creation, but can be edited freely. This field is used to attribute questions to their creator and is shown in the question list. Accessible to all users with read access to the question pool.

- **Table `svy_category`, field `owner_fi`**: Stores the ILIAS user ID of the user who created a category (answer option set). Set automatically from the current user session at category creation time. Accessible to administrators and users with write access to the question pool.

## Data being presented

- The `author` field (question creator's full name) from the `svy_question` table is displayed in the question list table (`ilSurveyQuestionsTableGUI`) to all users with read access to the question pool. It is also available as a filter criterion when searching questions within a pool.

## Data being deleted

- When an individual question is deleted (via `SurveyQuestion::delete()`), all associated records are removed from `svy_question`, `svy_category` (where applicable), `svy_answer`, `svy_constraint`, `svy_qst_constraint`, `svy_qblk_qst`, `svy_svy_qst`, `svy_variable`, and `svy_material`. The personal data fields `owner_fi` and `author` stored in `svy_question` are deleted as part of this operation.

- When an entire question pool object is deleted (via `ilObjSurveyQuestionPool::deleteAllData()`), all questions belonging to that pool are deleted in the same manner, removing all associated personal data.

- Incomplete questions (those with `tstamp = 0`) belonging to the current user are purged via `purgeQuestions()`, which queries `svy_question` by `owner_fi` matching the current user ID and deletes the matching records.

## Data being exported

- When questions are exported to XML (via `toXML()` in question type classes such as `SurveySingleChoiceQuestion`, `SurveyTextQuestion`, etc.), the `author` field is written into the XML export file as an `<author>` element. This means the author's name is included in any XML export or ZIP archive produced by the `ilSurveyQuestionpoolExport` class and can be downloaded by users with export permission.
