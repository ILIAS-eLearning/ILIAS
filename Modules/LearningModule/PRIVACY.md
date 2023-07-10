# ILIAS Learning module Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The ILIAS Learning Module Module component employs the following services, please consult the respective privacy.mds
    - The **Learning Progress** service manages data on access time specifically last time, number of accesses and the progress status specifically in progress, completed for each user accessing the object.
    - The **Object** service stores the account which created the
      object as it's owner and creation and update timestamps for the
      object.
    - [AccessControl](../../Services/AccessControl/PRIVACY.md)
    - [Info Screen Service](../../Services/InfoScreen/PRIVACY.md)
    - [News Service](../../Services/News/Privacy.md)
    - [Page Editor Service](../../Services/COPage/Privacy.md)
    - [Notes/Comments Service](../../Services/Notes/Privacy.md)
    - [Rating Service](../../Services/Rating/Privacy.md)


## Configuration

- **Global**
    - The global learning module administration allows to enable the page history for editing. If enabled older version (and their authors) of pages is being saved, see [Page Editor Service](../../Services/COPage/Privacy.md).

- **Learning Module**
    - The learning module offers an option to activate the storage of learners question results (passed or failed). If activated ILIAS stores for each question and learner the status including the ID of the learner and the questions.

## Data being stored

- The learning module stores the last access, number of accesses (read count) and (estimated) spent seconds for each page for a learning module and learner to enable features like the restricted forward navigation, the re-start at the last visited page when re-entering the learning module and the different learning progress modes (e.g. minimum time spent per chapter).

## Data being presented

**Tutor Presentation** (View Learning Progres Permission)
- If the last access, access number and time spent is presented to tutors on the learning progress screens is configured by the learnining progress administration settings (activation of each field). If deactivated only the overall status derived from the data (passed/not passed) is presented.

## Data being deleted

...

## Data being exported

- XML Exports of Learnind Modules do not contain any personal data.
