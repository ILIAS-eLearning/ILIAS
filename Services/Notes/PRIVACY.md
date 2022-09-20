# Notes and Comments Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).


## General Information

- The component implements both the Notes and Comments feature.
- Reasoning on the configuration (activation) of the feature must be done on the institutional level and should be compliant with your terms of use.

## Configuration

**Global**

- **Notes** can be activated in the administration under "Personal Workspace" > "Notes".
- **Comments** can be activated in the administration under "Communication" > "Comments".
- **Deactivating** the Notes or Comments feature will hide existing notes and comments from the user interface but not delete the corresponding data. Reactivation will present existing data again.
- The Comments administration allows to **configure** whether authors can **delete their own comments** or not. In the second case, they may still delete the text of a comment, but not the comment itself.
- The Comments administration allows to **configure** whether **tutors** (users having "Edit Settings" permission on an object) can **delete comments of other users** assigned to an object (e.g. a course).
- The Comments administration allows to set a list of **accounts that will be notified for all comments** (including the comment and the author). This allows to monitor comments and to tackle inappropriate content.
- The Comments administration allows to enable the **export of comments together with portfolios, blogs or wikis**. These (HTML) exports can be triggered by the owners, authors, contributors and users with "Edit Settings" permission.


## Data being stored

- Each **comment or note** stores the **user ID** of the account that  created the comment or note. _Reason_: This ID is essential to present users their own notes or author information in comments.
- Each comment or note stores the **creation timestamp** and the **timestamp of the last update**. _Reason_: This data is required to sort notes and comments and to indicated updates on the content.
- Each comment or note stores the **object ID** of the referenced repository object (e.g. a course or a learning module). _Reason_: Notes and comments can be attached to these objects and should be presented together with these objects.
- Additionally a comment or note may store a **news ID** (see Services/News), if it has been attached to a news entry of an object.
- Each comment or note stores a **text*** being entered by the author. There is not history being stored for a text. If the author changes the text, the old version will no be stored.
- Older versions of the feature have stored an additional **subject** field, which has been abandoned. (DB field still exists)


## Data presentation

- ILIAS presents comments and notes listings in **various contexts of the repository**, e.g.
  courses, groups, wikis or forums.
- **Notes** are only presented **to the authors**.
- **Comments** are presented to every user having **Read** permission on an object.
- **Notes** and **Comments** both present an overview for a user of all readable notes and comments.

## Data Deletion

- **Authors** can always delete their **own notes**.
- **Authors** can always delete the **text of a comment**.
- Depending on the configuration of the Comments feature, **authors may delete their own comments**.
- Depending on the configuration of the Comments feature, users with **Edit Settings** permission on an object may delete **comments of other users** attached to the object.

## Data Export

- **Comments** may be **exportable** with HTML exports (see Configuration section).
- Users can **export** their personal **Notes** as XML in their "Profile and Privacy" settings. This data can be imported into another ILIAS installation.