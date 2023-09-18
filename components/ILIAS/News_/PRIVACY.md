# News Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).


## Configuration 

**Global**

- The **News** service can be **activated** in the global administration.
- The **RSS** service can be **activated** in the global administration and it can be configured, if the feed is presented to the internet or only to registered users.
- A global setting controls the **default access** of single news entries, authenticated users or public via RSS.

**Repository Objects**

- The News service can be **activated** on the level of repository objects using the service, e.g. courses.
- The **default access** of single news entries, authenticated users or public via RSS, can be set on the repository object level. This overwrites the global setting.

**Single New Entries**

- The **access** of single news entries, authenticated users or public via RSS, can be set for each entry.


## Data being stored

- Each **news entry** stores the **user ID** of the account that originally created the news entry along with a **creation timestamp**, the **user ID** of the account that last updated the news entry  along with the **timestamp of the last update**.
  These are not neccessarily the same and could be different accounts.  _Reason_:
  This data is required to be able to list news only after a specific date or to be
  able to adress authors of news for collaboration or reference.
- For **each user** and **each news entry**, ILIAS stores, whether the **news has
  been presented ("is read")** to the user or not. No timestamp is included in this
  data. _Reason_: Unread news entries should be visually distinguishable from read
  entries.


## Data presentation

- ILIAS presents news listings in **various contexts of the repository**, e.g.
  courses, groups, wikis or forums. This information is usually shown to all users
  having **Read** permission in this context.
- Accounts having **Edit Settings** permission in a context are usually able to
  create and update news. News are automatically created e.g. when a new file is
  uploaded. These automatically created news cannot be updated.
- Additionally news are presented in an **personal context in an aggregated form**
  for users. All news of their favourite repository objects are presented in these
  views. However only news are presented, that are also accessible in the repository
  contexts directly (meaning read permission to the context is needed).
- Optionally news can be **configured** as being readable to the **public as an open
  RSS webfeed**. These feeds include all public news entries of a context and are
  accessible via an URL without any authentication.


## Data Deletion

- News related to an object are deleted on final object deletion. Users need the
  **Delete** permission for this action.


## Data Export

- News entries are currently not exportable. If the context objects (e.g. courses)
  are exported as XML, news data is not included. These object related exports
  explicitly should not container personal data.
