# TestQuestionPool Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).**

## General information

The module TestQuestionPool and the module Test are still tied together in most intricate ways. The primary component of concern in regards to privacy related evaluations is the Test. As the lines between these components are blurred, it is advised to never look at only one of the components but always at both.

## Integrated components

The TestQuestionPool component employs the following services, please consult the respective privacy.mds.

  - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md): Is used for permission handling and is able to present personal data.
  - [COPage](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/COPage/PRIVACY.md): Is used for content creation/presentation and is able to store, present and delete personal data.
  - Export: Is used for export creation and is able to export personal data.
  - [InfoScreen](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/InfoScreen/PRIVACY.md): Is used as in any repository object and is able to present personal data.
  - [Metadata](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/MetaData/Privacy.md): Stores the full name of the author of the test.
  - [Notes](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Notes/Privacy.md): Is used to create, edit and present comments for questions and the question pool.
  - [Object](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/ILIASObject): Stores the account which created the object as it's owner and creation and update timestamps for the object.
  - [Skill (Competence) Service](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Skill/PRIVACY.md)
  - [Taxonomy](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Taxonomy/PRIVACY.md)
  - [Test](tbd)

## Data being stored

- At the creation process of questions the field 'Author' is prefilled with the full name of the account, which is creating the question. If this value is not changed, the name of the account is stored.
- At the editing of questions the field 'Author' contains the previous saved value. If the value is changed and personal data is entered, it will be stored.
- By storing (and presenting) the value for 'Author' it is possible to contact the account, if there are problems with the question or the configuration of it. In addition it supports the collaborative development of questions.
- Ownership of Questions: Owners of questions are stored in the TestQuestionPool as reference to the 'User ID'. The data is required to manage detailed access and permissions on usage and editing of the question.

## Data being presented

- At the overview of the questions of a question pool, the values of the field 'Author' for all questions are shown, which may contain personal data.
- At the 'Statistics' of a question, the 'Author' of tests is displayed at the table 'This question is used in the following tests', if the question is used in tests. This information originates from the metadata service (see above).

## Data being deleted

It is possible to delete questions. Within this the personal data at the field 'Author' and the ownership of the question is deleted.

## Data being exported

- The XML export of the question pool contains the personal data 'Author' of the questions and of the question pool itself within the metadata.
- It's purpose is to being imported in ILIAS again, although the contained personal data is easily accesible. Including this information ensures that the authorship of the question is not lost after import.
