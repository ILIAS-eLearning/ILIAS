# Mail

ILIAS provides several classes to create and
sent Mails for different purposes.

The key words “MUST”, “MUST NOT”, “REQUIRED”, “SHALL”, “SHALL NOT”, “SHOULD”,
“SHOULD NOT”, “RECOMMENDED”, “MAY”, and “OPTIONAL” in this document are to be
interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**
* [General](#general)
* [ilMail](#ilmail)
* [ilMimeMail](#ilmimemail)
* [ilMailNotification](#ilmailnotification)
* [ilSystemNotification](#ilsystemnotification)

## General

Use different mail abstractions for different 
purposes.

All of the following described classes MUST have
a valid mail server configuration in the global
ILIAS administration for testing purposes.

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

An instance  of the `ìlMail` is only used for
internal mailing based on recipients input
channels(mail box, external mail etc.).

To identify the recipient the instance needs
the recipients login name and the senders user ID(see also `usr_id`).

```php
$senderUserId = $user->getId();

$recipientLoginName = 'ilyas_bar';
$cc = '';
$bc = '';
$subject = 'ilMail is great';
$message = 'Look at this awesome mail';
$attachment = '';

$mail = new ilMail($senderUserId);
$mail->sendMail(
	$recipientLoginName,
	$cc,
	$bc,
	$subject,
	$message,
	$attachment,
	array("system")
);
```

The example above will create a mail and sent
the mail to an existing user.
The sender and the recipient MUST both be existing
users of the ILIAS system.

This class is used for internal purposes in which
the user is involved e.g.
forum notifications.
The input channels like internal mailbox or external mail
will be used automatically based on the configuration in
the user.

## ilMimeMail

The `ilMimeMail` creates and sends an external
mail in the 
[Multipurpose Internet Mail Extensions(MIME)](https://en.wikipedia.org/wiki/MIME)
format.

```php
$senderMail = 'me@your.ilias.org';
$recepientMail = 'someone@ilias.org';
$mailSubject = 'This is MIME Mail';
$mailBody = 'Hello World!';

$mail = new ilMimeMail();
$mail->From($senderMail);
$mail->To($recepientMail);
$mail->Subject($mailSubject);
$mail->Body($mailBody);
$mail->send();
```

The example will create the mail and
send it to the defined recipient.

A `ilMimeMail` will be used to send external mails.
The according method to sent a mail need the
recipient and the sender to be entered as mail addresses
instead of user IDs like in [ilMail](#ilmail).

The sender and the recipient SHOULD
both be contactable via the entered email addresses.

This class is e.g. used for external procedures, because
the user e.g. has no access to the ILIAS system like in the
registration process.

## ilMailNotification

`ilMailNotification` is an abstract class that can be implemented to 
create a custom mail classes.

Every Service/Module/Plugin in ILIAS can create a specialized class
for creating and sending mails.

```php
class MyMailNotification extends ilMailNotification
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

`ilSystemNotification` is a common used implementation
of the previous explained `ilMailNotification`.

This class is used to create a mail sent by the ILIAS
system.
The implementation is used by several modules/services
to create it own mails.

```php
$mail = new ilSystemNotification();
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
other implementations of `ilMailNotification`.

This class will send a mail to an internal
user on the ILIAS system.

## ilAccountMail

An object of `ilAccountMail` is used to sent user
specific data to an given mail address.

This mail is often used in registration processes.

```php
$accountMail = new ilAccountMail();
$accountMail->useLangVariablesAsFallback(true);
$accountMail->setUser($user);

$accountMail->send();
```

This will create a default mail with the look
of a registration mail.
The line:
```php
$accountMail->useLangVariablesAsFallback(true);
```

Defines the subject and the body in the ILIAS
default form.
