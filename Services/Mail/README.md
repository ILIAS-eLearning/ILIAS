# Mail

ILIAS provides several classes to create and
sent Mails for different purposes.

The key words “MUST”, “MUST NOT”, “REQUIRED”, “SHALL”, “SHALL NOT”, “SHOULD”,
“SHOULD NOT”, “RECOMMENDED”, “MAY”, and “OPTIONAL” in this document are to be
interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**
* [General](#general)
* [ilMail](#ilmail)
  * [Recipients](#recipients)
      * [Examples](#examples)
      * [Syntax Rules](#syntax-rules)
      * [Semantics](#semantics)
  * [Subject and Body](#subject-and-body)
* [ilMimeMail](#ilmimemail)
  * [Sender](#sender)
  * [Transport](#transport)
* [ilMailNotification](#ilmailnotification)
* [ilSystemNotification](#ilsystemnotification)
* [ilAccountMail](#ilaccountmail)

## General

All of the following described classes rely on
a valid mail server configuration in the global
ILIAS administration.

The templates of each default mail can be found
in `Services/Mail/templates/default`.

The mailing MAY base on individual settings by
each user.
These settings can configured for each user via
the ILIAS GUI.

The described classes differ in their usage and
their purposes.
There are two possible recipient channels:
* internal - Users on the system will be addressed.
  Internal mails will be delivered to the internal
  mail box, which is accessible via the ILIAS user
  interface.
  The mail classes that are used for internal purposes,
  uses user IDs to identify recipient and/or the sender
* external - The mail will be sent to an external address
  outside of the ILIAS context.
  These addresses are defined by external input or data fields
  in ILIAS e.g. the `mail`-field in ILIAS.
  The `mail` field MUST be configured in
  the user configuration.

These both channels can be combined, so the user
will receive mails on both channels.

The following chapters will describe and explain which purposes
each class has and how to use them.

## ilMail

`\ilMail` is the central class of *Services/Mail* and acts as
some kind of reduced and **medium level** notification system.

The constructor of class `\ilMail` requires the
internal ILIAS id of the sender's user account. For *System Emails*
you MUST use the global constanct **ANONYMOUS_USER_ID**. 

The intended public API for sending messages is the `sendMail()` method.

```php
global $DIC;

$senderUserId = $DIC->user()->getId();

$to = 'root';
$cc = 'mjansen';
$bc = 'ntheen';
$subject = 'Make ILIAS great again!';
$message = "Lorem ipsum dolor sit amet,\nconsetetur sadipscing elitr,\nsed diam nonumy eirmod tempor.";
$attachments = [];

$mail = new \ilMail($senderUserId);
$mail->sendMail(
    $to,
    $cc,
    $bc,
    $subject,
    $message,
    $attachments,
    array("system")
);
```

### Recipients

The TO, CC and BCC recipients must be passed as strings.

Class `\ilMail` respects the configured transport channels for each evaluated
user account parsed from the recipient strings.

ILIAS is enabled to use standards compliant email addresses. `\ilMail`
and the underlying address parsers support RFC 822 compliant address
lists as specified in [RFC0822.txt](http://www.ietf.org/rfc/rfc0822.txt).

#### Examples

The following mailbox addresses work for sending an email to the user with the
login john.doe and email address jd@mail.com. 
The user is member of the course "French Course".
The member role of the course object has the name "il_crs_member_998".
Furthermore the user was assigned to a mailing list with the internal
id "4711".


* john.doe
* John Doe <john.doe>
* john.doe@ilias
* \#member@\[French Course\]
* \#il_crs_member_998
* \#il_role_1000
* jd@mail.com
* John Doe <jd@mail.com>
* \#il_ml_4711

#### Syntax Rules

The following excerpt from chapter 6.1 "Syntax" of RFC 822 is relevant for
the semantics described below:

    addr-spec = local-part [ "@", domain ]

#### Semantics

User account mailbox address:
* The local part denotes the login of an ILIAS user account.
* The domain denotes the current ILIAS client.
* The local part MUST NOT start with a "#" character.
* The domain MUST be omitted or MUST have the value "ilias".

Role object mailbox address:
* The local part denotes the title of an ILIAS role.
* The domain denotes the title of an ILIAS repository object.
* The local part MUST start with a "#" character.
* If the local part starts with "#il_role_" its remaining characters directly specify the object id of the role. For example "#il_role_1234 identifies the role with object id "1234".
* If the object title identifies an object that is an ILIAS role, then the local-part is ignored.
* If the object title identifies an object that is not an ILIAS role, then the local-part is used to identify a local role for that object.
* The local part can be a substring of the role name. For example, "#member" can be used instead of "#il_crs_member_1234".

External email address:
* The local part MUST NOT start with a "#" character.
* The domain MUST be specified and it MUST not have the value "ilias".

Mailing list:
* The local part denotes the mailing list
* The local part MUST start with a "#" character, followed by the character sequence "il_ml_" and the internal id of the mailing list.
* The domain MUST be omitted.

### Subject and Body

Beside the recipients the API accepts subject and body of the message,
both being of type *plain/text*. The consumer MUST ensure that the
message does not contain any HTML. Line breaks MUST be provided by a
line feed (LF) character.

### Attachments

The `$attachments` can be passed as an array of file names.
Each file MUST have been assigned to sending user account in a prior step.
This can be done as described in the following examples:

```php
$attachment = new ilFileDataMail($senderUserId);

$attachment->storeAsAttachment(
    'appointment.ics', $someIcalString
);
$attachment->copyAttachmentFile(
    '/temp/hello.jpg', 'HelloWorld.jpg'
);

$mail = new \ilMail($senderUserId);
$mail->sendMail(
    $to,
    $cc,
    $bc,
    $subject,
    $message,
    [
        'appointment.ics',
        'HelloWorld.jpg'
    ],
    array("system")
);
```

### Type

The type string must be one of the following options:

* normal
* system (displayed in a separate block at the *Personal Desktop*)

Messages marked as *system* will not be delivered to the recipient if he/she
did not accept the *Terms of Service*, or he/she has an expired user account.

## ilMimeMail

`\ilMimeMail` is a **low level** class to create and send
an external email in the 
[Multipurpose Internet Mail Extensions(MIME)](https://en.wikipedia.org/wiki/MIME)
format.

It SHOULD be used whenever you want to explicitly send
an email to external email addresses. This class is
mainly used for external procedures, e.g. if 
user has not yet access to ILIAS, e.g. in the
registration process.

```php
global $DIC;

/** @var \ilMailMimeSenderFactory $senderFactory */
$senderFactory = $DIC["mail.mime.sender.factory"];
$sender        = $senderFactory->system();

$mailer = new \ilMimeMail();
$mailer->From($sender);

$mailer->To(explode(',', [
    'dummy1@gmail.com',
    'dummy2@web.de',
]));
$mailer->Cc(explode(',', [
    'dummy3@yahoo.com'
]));
$mailer->Bcc(explode(',', [
    'dummy4@aol.com'
]));

$mailer->Subject($subject);
$mailer->Body($plainText);

$mailer->Attach(
    '/srv/www/ilias5/data/defaultclient/ilFile/4/file_400/001/composer.json',
    'application/json',
    'inline',
    'Composer.json'
);
$mailer->Attach(
    '/srv/www/ilias5/data/defaultclient/ilFile/4/file_401/001/composer.json',
    'application/json',
    'inline',
    'AnotherComposer.json'
);

$mailer->Send();
```

Subject and body MUST be of type plain text and MUST NOT contain any HTML fragments.
`\ilMimeMail` wraps an HTML structure around the message body (see: [HTML Mails with Skin](https://www.ilias.de/docu/goto_docu_wiki_wpage_3506_1357.html)) 
passed by the consumer when preparing the email transport.
It automatically transforms newline characters (LF/CR) to HTML linebreak elements.
The original message body is used as *text/plain* alternative,
the processed body is used as *text/html* alternative.

### Sender

Since ILIAS 5.3.x the `$sender` cannot be passed as a primitive
data type anymore. A sender must implement `\ilMailMimeSender`.
ILIAS currently provides two different implementations:

1. ilMailMimeSenderSystem
2. ilMailMimeSenderUser

Both instances can be retrieved by the globally available `\ilMailMimeSenderFactory`,
registered in the dependency container (`$DIC["mail.mime.sender.factory"]`).

The first MUST be used whenever an email is sent with the purpose of a *System Email*.
The second type MUST be used for a *User-to-User Email*.

### Transport

The actual transport is decoupled from `\ilMimeMail`. `\ilMimeMail` uses
the determined transport strategy given by the
`\ilMailMimeTransportFactory` instance which is available in
the dependency injection container (`$DIC["mail.mime.transport.factory"]`).

A transport strategy must follow the definition of
the `\ilMailMimeTransport` interface. A different transport could be
globally set via `\ilMimeMail::setDefaultTransport`, or just for
the next call of `Send()` by passing an instance of `\ilMailMimeTransport` as
argument:

```php
$mailer->Send($transport);
```

## ilMailNotification

`\ilMailNotification` is an abstract class that can be implemented to 
create a custom mail classes.

Every Service/Module/Plugin in ILIAS can create a specialized class
for creating and sending mails.

```php
class MyMailNotification extends \ilMailNotification
{
	public function sendMail()
	{
		try
		{
			$this->setSubject('Welcome to ILIAS');
			$this->setBody('Hello World!');
			$this->appendBody(\ilMail::_getInstallationSignature());

			$this->sendMail($this->getRecipients(), array('system'), false);
		}
		catch(\ilMailException $e)
		{
			// Error handling
		}
	}
}
```

The use of the implementation could look like this:

```php
$myMailNotification = new MyMailNotification();
$this->serRecipients(array('somone@somewhere.com', 'somebody@ilias.org'));
```

The sender of the mail will be the ILIAS system
that will be configured via the setup of ILIAS.

An implementation of this mail class MAY be used to
sent external and/or internal mails.

## ilSystemNotification

`\ilSystemNotification` is a common used implementation
of the previous explained `\ilMailNotification`.

This class is used to create a mail sent by the ILIAS
system.
The implementation is used by several modules/services
to create it own mails.

```php
$mail = new \ilSystemNotification();
$mail->setLangModules(array('user'));
$mail->setRefId($refId);
$mail->setChangedByUserId($user->getId());
$mail->setSubjectLangId('my_subject');
$mail->setIntroductionLangId('my_body');
$mail->addAdditionalInfo('additional_info', 'The title');
$mail->setGotoLangId('exc_team_notification_link');
$mail->setReasonLangId('my_reason');
$mail->sendMail(array($targetUser->getId()));
```

The class create a system specific mail like
other implementations of `\ilMailNotification`.

This class will send a mail to an internal
user on the ILIAS system.

## ilAccountMail

An instance of `ilAccountMail` MUST be used to sent
external emails whenever a user account was created
in ILIAS. It's main purpose is to provide user
account information in the self registration process.

The contents of this email can be configured in
**Administration » User Management » New Account Mail**.
Subject and body can be defined for each installed
language. Furthermore placeholders can be used, being
replaced with the user's account data when the email
is sent:

* \[MAIL_SALUTATION\]: Salutation
* \[FIRST_NAME\]: First Name
* \[LAST_NAME\]: Last Name
* \[EMAIL\]: E-Mail
* \[LOGIN\]: Login Account
* \[PASSWORD\]: Password
* \[IF_PASSWORD\]...\[/IF_PASSWORD\]: This text block is only included, if the new user account has been created including a password.
* \[IF_NO_PASSWORD\]...\[/IF_NO_PASSWORD\]: This text block is only included, if the new user account has been created without a password.
* \[ADMIN_MAIL\]: Mail address of Administrator
* \[ILIAS_URL\]: URL of ILIAS system
* \[CLIENT_NAME\]: Client Name
* \[TARGET\]: URL of target item, e.g. a linked course that is passed to ILIAS from outside.
* \[TARGET_TITLE\]: Title of target item, e.g. course title.
* \[TARGET_TYPE\]: Type of target item, e.g. ‘Course’ for a course item.
* \[IF_TARGET\]...\[/IF_TARGET\]: This text is only included, if a target item is provided.
* \[IF_TIMELIMIT\]...\[/IF_TIMELIMIT\]: This text is only included, if the user has a limited access period.
* \[TIMELIMIT\]: User access period

The object tries to determine subject and body based
on the user's language. If this fails, the system
language is used as a fallback for determination. 
If this fails again, there is an optional second
fallback by using the following language variables:

* reg_mail_body_salutation
* reg_mail_body_text1
* reg_mail_body_text2
* reg_mail_body_text3

```php
global $DIC;

$user = $DIC->user();

$accountMail = new \ilAccountMail();
$accountMail->setUser($user);

$accountMail->send();
```

This will create a default mail with the look
of a registration mail.

To enable the language variable fallback the
following mutator has to be called with a
boolean true as it's argument:
```php
$accountMail->useLangVariablesAsFallback(true);
```
If a raw password should be included, it must be
explicitly set via:
```php
 $acc_mail->setUserPassword($rawPassword);
 ```

Internally `\ilAccountMail` makes use of `\ilMimeMail`.