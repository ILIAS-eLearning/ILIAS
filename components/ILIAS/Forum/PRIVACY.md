# Forum Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## General Information

The forum is an ILIAS repository object in which users can hold discussions. A forum is organized in different topics (threads), to which contributions (posts) are written. As long as a forum is not deleted, posts can be viewed for years after they have been published.

At least one person is responsible for moderation in all forums: They can censor and overwrite posts and close topics. Moderators can be added and removed by a person with the “Edit Settings” permission on the “Moderators” tab.

## Configuration 

Various privacy-related settings are made under “Administration > Repository and Objects > Forum”:

**Enable Statistics in Forum** (default: off) sets for the installation whether a statistics function can be activated in the settings of individual forums.

**Allow posting with pseudonym** (default setting: off) sets whether pseudonymization can be activated in individual forums. Registered users can then post in this forum using an alias without stating their name. 

**Attachment** (default setting: on) sets whether file attachments are possible for posts in all forums or whether this function must be activated in individual forums.

**Notifications** (default setting: on, without file attachments) sets whether users can be informed by e-mail about new posts in forums and whether these e-mails can also contain file attachments. The notification can be sent immediately or once a day if the cron job “Send Forum Notifications via Cron Job” is activated.

**Drafts** (default setting: off) sets whether it is possible to save draft posts in the forum and at what intervals drafts are automatically saved.

The following privacy-related settings can be made in an individual forum under “Settings”:

### Basic Settings

**User Functions / Thread Rating** (default: off) enables users to assign a 5-star rating to threads. The average rating and number of ratings is displayed to all users.

**Moderator Functions / Approve Postings** (default: off) enables posts in the forum to be published only after they have been read and approved by moderators.

**Privacy / Enable Statistics** (default: off) enables all forum users to see who has published how many posts.

**Privacy / Posting with pseudonym** (default setting: off) allows users to publish posts in the forum anonymously or using a pseudonym. Forum posts that have already been anonymized remain anonymous even after a changeover.

### Notifications

For forums within courses and groups, you can set here whether users of the forum automatically receive notifications by e-mail, whether they have to activate it manually or whether they can deactivate it individually (default setting: manual activation).

### News settings

If RSS is activated in ILIAS under “Administration > News and Web Feeds”, you can set here whether an RSS feed is offered for the forum. The “Public Notification” setting enables this feed to contain forum posts without authentication (default setting: off).

## Data being stored

The **ID of the creating user** and the time of creation are saved for the entire forum. If the forum settings are changed, the **ID of the user making the change** and the time of the change are saved.

The **title, description** and a manually designed **start page** of the forum may contain personal information provided by the creator.

The **titles and texts of the posts** and topics in the forum are created by the forum participants themselves and may contain personal information, e.g. the names of other forum participants. Posts may also contain images and file attachments.

With every post or draft of a post, the **ID** of the user who created it and the **time** of creation and publication are saved. When changes are made (including censorship or re-release), the time and ID of the user making the change are saved.

If anonymization is set in the forum, the ID of the user and the **pseudonym** entered are saved internally with a post.

The **moderator** function of a user is saved via the ILIAS access control system by the moderator role they belong to. For **censored posts**, the time and comment are saved and are linked to the ID of the censor.

**Notification settings** for the forum or individual topics are saved for users (notification of new topics, modification or censorship of posts, deletion of posts or topics). The settings are linked to the **IDs** of the user who made them and to whom they apply.

The **read status** of posts and the **sorting** of topics is saved for individual users and used to customize the display.

If forum topics can be given a 5-star rating, **individual ratings** are saved by the rating component of ILIAS.

## Data being presented

The title, description, start page of the forum as well as the titles of the topics and the posts may contain **manually created personal information**.

If a post was written without pseudonymization, the **ILIAS username** and the **date of creation** are visible to other users with read access to the forum. In the case of highlighted posts by moderators, a **Moderation** appears. In the case of subsequent changes, the user name and the date of the **change** are visible.

If a user has published a personal profile, the **first name** and **surname** are also displayed. Depending on the profile settings, a **portrait** or **letter avatar** is displayed. If a forum is enabled for anonymous access via the access control system, this data is only displayed for anonymous users if the profile has been made publicly visible.

If a post has been written with a **pseudonym**, the pseudonym entered appears instead of the username and is recognizable as such.

If **statistics** are activated in the forum, a table is available for all users with read access, in which at least the **username** and the **number of posts** are listed for users with posts. If the personal profile is enabled, the first name and surname are also displayed. If a **learning progress** is activated in the forum and you have the authorization to see the learning progress of other users, it will be displayed here.

If the **notification settings** of a forum allow the individal deactivation of an automatic notification, a list of all course or group members with username, first and last name, as well as the permission is displayed.

## Data being deleted

When a **post or topic** is deleted, all associated personal data is deleted. If a daily notification via cron job is set in the ILIAS administration, this data is stored temporarily until the notifications are sent.

After deleting a **user account**, the user's topics and posts remain in the forum. Their data records still contain the ID and, in the case of non-pseudonymized posts, also the username. However, "Deleted" is displayed as the name of the creator. The user account no longer appears in the forum statistics.

If an **entire forum** is deleted and the ILIAS "Trash" is active, all personal data is initially retained.  When the forum object is finally deleted from "Trash", all forum data (incl. user related data) is deleted as well.

## Data being exported

If a **notification about new or changed posts** is set, it will be sent via the ILIAS mail system. For users who have set forwarding to an external e-mail address, the notification will be forwarded to this address. The notification contains the date, title and content of the post, as well as the username or pseudonym of the creator. Depending on the setting, file attachments are also forwarded.

New posts can be subscribed to via an **RSS feed** in suitable programs. In the forum, you can set whether this feed is password-protected or public. A public feed contains the title of the forum, the **title, content, creation date** and a link to the post in ILIAS for each post. If the forum itself is not public, you will be taken to the ILIAS login page when you use the link.

To support the **full-text search**, a separate server with the Apache Lucene search engine can be operated for ILIAS. When creating the full-text index for a forum, the content of a manually created start page as well as the title, content and author ID of all topics and posts are retrieved by this server and stored in the index.

ILIAS users can create a **print view** in a forum topic and print it out via the browser. The personal data contained on it corresponds to the normal browser view of the forum topic. ILIAS users with the "Edit Settings" permission for a forum can export an **HTML page** with the content of all forum topics. The personal data contained in it corresponds to the normal browser views of the forum topics.

A forum can be exported from ILIAS in **XML format** by users with the "Edit Settings" permission. This creates a ZIP archive that can then be downloaded and imported into another ILIAS platform. The archive contains all content of the forum, the user IDs of the moderators and authors of posts, as well as the usernames or pseudonyms of the authors.