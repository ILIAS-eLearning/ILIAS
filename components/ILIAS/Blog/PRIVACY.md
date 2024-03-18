# Blog Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Blog component employs the following services, please consult the respective privacy.mds
    - The **Object** service stores the account which created the
      object as it's owner and creation and update timestamps for the
      object.
    - [COPage](../COPage/PRIVACY.md)
    - [Notes/Comments](../Notes/PRIVACY.md)
    - The **Mail/Contacts** service implements a user search for finding users to share blogs with.
    - [AccessControl](../AccessControl/PRIVACY.md)
    - [Info Screen Service](../InfoScreen/PRIVACY.md)

## General Information

The main purpose of blogs is to frequently share information on certain topics or personal developments to others. This data entered by the creator of a blog may include personal data being stored and presented.

## Configuration

**Global**

- Personal blogs are activated in the "Personal Resources" administration (Administration > Personal Resources). Collaborative blogs are part of the repository and configured by RBAC.

**Blog**

- The blog settings contain a setting to show the personal profile picture in the header of the blog.
- The blog settings contain a setting "Authors" which controls, if a list of authors is presented as side block in the blog that leads to author specific posting lists.

## Data being stored

Single blog postings are stored using the [COPage](../../Services/COPage/PRIVACY.md) service. The author may include any personal content in these pages. The pages are not structured with any personal data related scheme (like e.g. the user service storing birthday, name or address information).

The blog component additionally store the following personal data for each posting:
- A **timestamp "created"** that stores the creation date of the blog posting.
- The **user ID** of the author of the posting.
- A **timestamp** for the last withdrawal of the blog posting.

## Data being presented

If configured in the settings, a side block will list all authors with their first- and lastname, if activated in the personal profile. Otherwise the account name will be presented.

Blog postings may contain any personal information that the author puts directly into these pages.

Blog postings present the creation date and the author with first- and lastname if activated in the personal profile. Otherwise the account name will be presented.

If activated public comments will be listed under each portfolio page.

For personal blogs all data of the blog (incl. comments) is visible to all users that are defined in the "Share" tab of the blog (see Configuration above).

The sharing screens of personal blogs allow to search for other users, the presentation of users, their account/login names, first and last names are controlled by the Mail/Contacts components. Usually first and last name are not presented, if the user has the personal profile deactivated.

## Data being deleted

- The author can remove blog pages anytime. This will remove it from the presentation to other users. However the information will be part of the history of that particular page. The history is not presented to other users having only the read permission.
- Deleting a page will delete any personal data stored directly within the page.
- Deleting a blog will delete all pages included.
- Deleting user accounts of authors will not delete the corresponding blog postings. Postings will present a "Deleted Account" as author information.

## Data being exported

- Blogs can be exported as a zipped folder of HTML files.
- A print view can be used to convert a blog to PDF by the browser.
