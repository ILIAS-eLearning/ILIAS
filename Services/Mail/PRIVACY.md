# Mail Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information

- The "Mail Service" of ILIAS sends automatically generated messages and accounts can write messages manually.
- A message can be delivered internally or externally or both, depending on the settings of the respective recipient account.
- The personal selection can be made in the "Avatar > Settings > Mail Settings > Incoming Mail" setting.
- Other components in ILIAS might use the "Mail Service" to send emails to external email addresses disregarding
  that setting. Other components in ILIAS might use the channelled message delivery (internally, external, or both)
  of the internal API without considering any permission. Please check the documentation of the respective component.
- An account with "Edit Settings" permissions for "Administration > User and Roles >
  Roles > RoleTitle > Default Permissions > Administration Permission > Mail" can remove the
  permission "Internal Mail: User can use internal mail system" for all accounts of that role. Without
  this permission accounts cannot use the internal mail system.
- Additionally an account with with "Edit Settings" permissions for "Administration > User and Roles >
  Roles > RoleTitle > Default Permissions > Administration Permission > Mail" can remove the permission
  "SMTP E-Mail: User can send e-mails per SMTP to external addresses" for all accounts of that role. Without this
  permission emails with external email addresses will not be sent.
- Additionally, sending external emails can be prevented by disabling the
  "Administration > Communication > Mail > Settings > External E-Mails" setting. This is an additional safeguard
  to stop the delivery of external emails.
- The "Mail Service" is not aware of the data being passed through. It does not know anything about the
  origin (i.e. course) of the string or whether it contains user related data or not.

## Data being stored

- For each message created in the "Mail Service" the ID of the account that created the message is stored
  along with the entered message data like **subject**, **body**, **attachments**, **recipients**,
  **placeholder information** and an additional **datetime** information. For every recipient of internal mail this
  message is copied. The purpose of this data being stored is to assign the mail to the respectice accounts and present
  them in ILIAS user interface.
- The "Mail Service" distinguishes between the "user_id" (means: "Owner"), and the "sender_id" (means: "Sender"). The
  purpose is, that each party gets a copy of the message. For each copy, the "user_id" is substituted by the ID of the
  respective recipient account, so each account owns its own record.
- Until an account explicitly sends the message, the message data is temporarily stored as a shadow copy
  without any datetime information. The same applies to draft messages.
  The purpose is to temporarily store the message data in case the account navigates to other screens in
  the "Mail Service", e.g. "Search" or "Attachments" screens.
- Personal "Mail Service" related preferences are stored along with the ID of the account. This includes the
  preferred usage of the delivery channels, the setting which email addresses should be used, the configured signature 
  and finally a flag whether daily summaries of internal emails should be sent to
  the external email addresses of the user (if the corresponding cron job is enabled in the administration).
- ILIAS affords accounts with default mail folders to organize mails. Accounts can create custom folders for
  further structruring of mails. The ID of the account is used as a reference here to present the folders in the ILIAS
  user interface accordingly.
- Other componetns like the course can delegate messages to the "Mail Service". The "Mail Service" in turn
  delegates the message delivery to the [`BackgroundTasks`](../../src/BackgroundTasks/README.md) to bulk-send
  the mails. All data is passed through this asynchronous queue and thus stored temporarily.
  Please check the privacy documentation of the corresponding service for further details.

## Data being presented

- For accounts with the "Internal Mail: User can use internal mail system" mails owned by them are presented
  on the "Dashboard" and the "Mail Folder" screen: Subject, body and datetime information are presented as well
  as download links for the optional attachments.
- The "Mail Service" respects the privacy settings of name presentation of recipients. Once the message was sent, usernames
  are presented according to the "Profile and Privacy > Pubslish Profile > Visible for logged in Users" setting.

## Data being deleted

- Files that are attached to a sent message and shared by all message copies. Those files are deleted when the
  last referring message is deleted.
- If a user account is deleted from system, all "Mail Service" related data records with an "Owner Relationship" to this
  user account will be completely deleted: Messages, folders, preferences as well as all files uploaded to be attached
- In all sent internal message copies the id of the deleted account used as "sender_id" will be replaced by a "0".
- Before the deletion of the user account is completed, the last username will be inserted as sender variable
  being presented in mails. If the last username entails personal data, then this personal data is not entirely deleted
  from the system.
- In "Administration > System and Maintenance > General Settings > Cron Jobs" the cron job
  "Delete old and orphaned mails" can be activated to automatically delete messages and the related data depending
  on a threshold, optionally including the messages located in the inbox/trash folders.

## Data being exported

- Files attached to messages can be downloaded in ILIAS.
- For internal mails the "Mail Service" does not provide any kind of export in ILIAS.
- Of course emails delivered externally by "SMTP" or "Sendmail" are the responsibility of the respective email clients.
  - The "FROM" header used for sending external emails is built from the globally configured
  email address and the configured name placeholder, which may include the full name presentation, the firstname and the lastname.
  - If the "Use Gloal Reply-To" setting in "Administration > Communication > Mail > Settings > External E-Mails"
    is disabled, the user's email address and its full name are used as the "Reply-To" header in user created emails.
    Otherwise, the global reply-to address is used.
  - If a message is composed by using composite recipient strings, e.g. a group/role of users, the "BCC" header is used to
  include the email addresses of all recipients, so that email addresses are not disclosed. If there is only one final
  external recipient, the "TO" header is used instead.