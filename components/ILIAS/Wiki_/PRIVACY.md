# Wiki Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Wiki component employs the following services, please consult the respective privacy.mds
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

- **Wiki**
    - The wiki settings offer options to activate the rating and the public comments feature. The rating feature can be activated for single pages.

## Data being stored

- **Contributor Grading**: The wiki allows to store grading information for wiki contributors by tutors. This stores a **status** (passed/failed/not graded), a **mark** (free text) together with the **user ID** of the user being graded and a **timestamp of the status change** in the database.

## Data being presented

The wiki presents its own data (contributor grading) and also personal related data from the integrated services. The following list includes only special presentations of this data, standard presentations are listed in the documentation of the integrated services. User information being presented to learners (e.g. in the page lists) respects the personal profile settings of the user being listed.

**Tutor Presentation** (Edit Settings Permission)
- Tutors get an overview of contributor grading for each contributing user. Since this is a tutor oriented screen, first and lastname of contributors will always be listed.

**Learner Presentation** (Read Permission)
- Learner can see their personal grading, given by a tutor, on the info page.
- The last contributor of a wiki page is printed below each wiki page.
- The wiki presents serveral page lists, which contain the **page title**, the **last change (timestamp)** and the user who **made this last change**: All Pages, New Pages.
- The wiki presents a list of recent changes. This list does not only include the last changes, but all page changes of the last month with **page title**, the **last change (timestamp)** and the user who **made this last change**.

**Notifications**
- Users can activate notifications on wiki page changes. These notifications contain the **page title**, the **last change (timestamp)** and the user who **made this last change**. Depending on the mail settins, this information is sent to the external mail address of the recipient.

## Data being deleted

...

## Data being exported

- XML exports do not contain any personal data.
- HTML exports may include the user comments, if this is enabled in the global comments settings. 
