# COPage (Page Editor) Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).


## Data being stored

- Each page stores the **content** together with the **user ID** of the page
  **creator**, the **last user who changed** the page and **timestamps** of the
  **creation** and **last change**.
- The service stores not only the current page state, but **stores historic versions
  of pages with each change** being made.
- Some container (e.g. Wikis) allow to add **user links** within the content. 


## Data presentation

- **User links** within the content: Depending on the profile publishing settings
  of the user, the user is presented with a **clickable "lastname, firstname,
  account name"** link which leads to the personal profile of the user. If the
  profile is not published, nothing will be presented.
- The container of the page (e.g. a Wiki or a Learning module) controls the
  **read permission** to the content of pages. Each user granted with this permission
  can access the **content**, which may include **profile links**. Also the **content
  itself may include personal data**, if other users have entered this data. Some
  container (e.g. Wikis) present the **timestamp of the last change together with
  the authoring user** (profile link as above).
- The container of the page also controls **write access** (e.g. write or edit
  content permission) to the page. This gives user access to the full **page
  history** including **timestamps and users that made these changes**. Depending
  on the container it also enables users to add user links to any user, if the
  account name is known. The internal link feature offers also a **search for
  users with activated public profiles** for this purpose. Search strings must
  have a minimum of three characters. If this string matches firstname, lastname
  or account name a user will be listed.


## Data Deletion

- **Users cannot delete** entries in the page **history**.
- **Pages and their history are deleted**, if the **single page or** the **container
  object** is **deleted** and **removed from the trash**.
- If a **user is deleted** the **content** authored by the user is **not deleted**.
  However **no account name, firstname or lastname will be presented** in the "last
  edited" information, the page history or as user links **anymore**.


## Data being exported

- XML exports of pages do *not* contain the historic information (older versions including creation user and timestamp).
- Personal data is only exported, if the page content itself contains personal information.
