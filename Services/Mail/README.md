# Mail

ILIAS provides several classes to create and
send Emails/Messages for different purposes.

The key words “MUST”, “MUST NOT”, “REQUIRED”, “SHALL”, “SHALL NOT”, “SHOULD”,
“SHOULD NOT”, “RECOMMENDED”, “MAY”, and “OPTIONAL” in this document are to be
interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**
* [General](#general)
  * [Delivery Channels](#delivery-channels)
  * [External Emails: HTML Frame](#external-emails-html-frame)
* [ilMail](#ilmail)
  * [Recipients](#recipients)
      * [Supported Recipient String Examples](#supported-recipient-string-examples)
      * [Syntax Rules](#syntax-rules)
      * [Semantics](#semantics)
  * [Subject and Body](#subject-and-body)
* [ilMimeMail](#ilmimemail)
  * [Sender](#sender)
  * [Transport](#transport)
* [ilMailNotification](#ilmailnotification)
* [ilSystemNotification](#ilsystemnotification)
* [ilAccountMail](#ilaccountmail)
* [Manual Mail Templates](#manual-mail-templates)
  * [Context Registration](#context-registration)
  * [Context Usage Example](#context-usage-example)

## General

The following chapters will describe and explain which purposes
each class has and how to use them.

All of the following described classes and code snippets
rely on a valid email server configuration and meaningful
settings in the global ILIAS email administration.

A short introduction of some base features/concepts
will be given before the respective classes are
substantiated.

### Delivery Channels

The delivery channel for messages MAY be based
on individual settings by each user. These settings
can be configured for each user via the ILIAS GUI.

There are two possible recipient channels:
* internal - Users on the system will be addressed.
  Internal messages will be delivered to the internal
  inbox, which is accessible via the ILIAS user
  interface.
* external - The email will be sent to an external address
  outside of the ILIAS context.

Both channels can be combined, so the user
will receive messages on both channels.

### External Emails: HTML Frame

With ILIAS 5.1.x the [HTML Mails with Skin](https://www.ilias.de/docu/goto_docu_wiki_wpage_3506_1357.html)
were introduced.

Text emails sent to an **external** email address
can be displayed in a HTML frame. The HTML template
is part of the skin:

    ./Services/Mail/templates/default/tpl.html_mail_template.html

There are no advanced formatting options, except
the global format given in the HTML file itself.
HTML templates as a frame can only be used when a
skin was set/configured as *default system-style* in
`Administration » Layout and Styles » System Styles`.
The configuration is stored and read from
your `client.ini.php` file located in your internal
data directory ('./data/<CLIENT_ID/').

Example:
```
[layout]
skin = "default"
style = "delos"
```

Emails are sent without HTML frame and as raw plain/text
when no template can be determined.

Per Skin, one HTML template can be defined and has to be
stored in the following location:

    ./Customizing/global/skin/<NAME>/Services/Mail/tpl.html_mail_template.html.

The HTML frame template concept consists of the HTML
markup file itself and some optional attachments.
Images of type \*.jp(e)g MUST be stored in a sub-folder *img*.
It is possible to attach several image attachments.
Images are packed into the email as inline images,
regardless of their use.
The source of the images is prefixed with a *cid*.
The *cid* of an image with the file name "foo.jpg"
will be *img/foo.jpg*.

Other file types (like \*.png, \*.gif, \*.bmp, etc.) will
be silently ignored. This is not considered to be a bug,
but a design limitation ([http://www.ilias.de/mantis/view.php?id=18878](http://www.ilias.de/mantis/view.php?id=18878)).

This feature does not apply for ILIAS-internal messages at all.

## ilMail

`\ilMail` is the central class of *Services/Mail* and acts as
some kind of reduced and **medium level** notification system
dealing only with internal and external emails.
It does neither care about low-level transport of messages
(e.g. like sending external emails via SMTP), nor does it
act like a centralized notification system dealing with
any kind/type of notification in ILIAS.

The constructor of class `\ilMail` requires the
internal ILIAS id of the sender's user account.
For *System Emails* you MUST use the global
constant **ANONYMOUS_USER_ID**.
In the context of the mail service the
term *System Emails* describes every email
communication that is initiated and completed
without manual editing.
A *System Email* is not one that can be directly
sent to one or more users by an interaction in the
user interface.

Examples:

* User joined course
* User created forum posting 
* ...

The intended public API for sending messages is
the `sendMail()` method.
Other methods like `sendMimeMail()` are public
as well but not intended to be used by consumers.

Simple Example:

```php
global $DIC;

$senderUserId = $DIC->user()->getId();

$to = 'root';
$cc = 'john.doe';
$bc = 'max.mustermann';
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
    [array("normal")](#recipients)
);
```

### Recipients

The `$to`, `$cc` and `$bcc` recipient arguments
MUST be passed as string primitives.
Multiple recipients for TO, CC and BCC MUST be
concatenated with a `,` character.

```php
$to = 'john.doe@ilias.de, root, #il_role_1000';
```

The following recipient address types are supported:

* Email addresses
* Usernames
* String representations of mailing lists
* Group repository object titles (as long as they are globally unique)
* String representations of local roles in courses and groups
* String representations of roles in general

Class `\ilMail` respects the configured transport channels for each evaluated
user account parsed from the recipient strings.

ILIAS is enabled to use standards compliant email addresses. `\ilMail`
and the underlying address parsers support RFC 822 compliant address
lists as specified in [RFC0822.txt](http://www.ietf.org/rfc/rfc0822.txt).

The address parser below *./Services/Mail/classes/Address* could
be considered as a separate service. To get an address parser you
can simply use an instance of `ilMailRfc822AddressParserFactory`
and pass a comma separated string of recipients.

#### Supported Recipient String Examples

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
* Examples: john.doe / john.doe@iliasjohn.doe

Role object mailbox address:
* The local part denotes the title of an ILIAS role.
* The domain denotes the title of an ILIAS repository object.
* The local part MUST start with a "#" character.
* If the local part starts with "#il_role_" its remaining characters directly specify the object id of the role. For example "#il_role_1234 identifies the role with object id "1234".
* If the object title identifies an object that is an ILIAS role, then the local-part is ignored.
* If the object title identifies an object that is not an ILIAS role, then the local-part is used to identify a local role for that object.
* The local part can be a substring of the role name. For example, "#member" can be used instead of "#il_crs_member_1234".
* Examples: \#il_role_1000 / \#il_crs_member_998 / \#member@\[French Course\]
* Such addresses can be created by `\ilRoleMailboxAddress`

External email address:
* The local part MUST NOT start with a "#" character.
* The domain MUST be specified and it MUST not have the value "ilias".
* Examples: \#il_ml_4711

Mailing list:
* The local part denotes the mailing list.
* The local part MUST start with a "#" character, followed by the character sequence "il_ml_" and the internal id of the mailing list.
* The domain MUST be omitted.
* Examples: John Doe <jd@mail.com> / john.doe@ilias

After recipient strings being parsed by a `RFC822`
address parser, the corresponding user id resolvers
are determined in `\ilMailAddressTypeFactory::getByPrefix`.

1. If the first character of the parsed RFC822 compliant
address does **not** equal `#` **and** the first two characters
do **not** equal `"\#`, the address is supposed to be an external
email address or an ILIAS username.
2. If the first seven characters of the parsed RFC822 compliant
address equal `#il_ml_`, the address is supposed to be a
mailing list and the assigned user accounts are used as
recipients, if you as the sender are the owner of this list.
3. If the the parsed RFC822 compliant address is a
valid group name, the address is supposed to be a
Group and the respective members will be used as recipients.
4. In all other cases the parsed RFC822 compliant address
is supposed to be a role representation and the resolver
fetches the assigned users as recipients for the email.

### Subject and Body

Beside the recipients the API accepts subject
and body of the message, both being of type *plain/text*.
The consumer MUST ensure that the message does
not contain any HTML.
Line breaks MUST be provided by a line feed (LF) character.
Violations against this rule may raise exceptions in
future ILIAS releases.

Currently the mail system tries to magically detect
whether or not the message body passed by consumers
contains any HTML when sending **external**
[emails with an HTML frame and a plain/text alternative](#external-emails:-html-frame).
This is done in `\ilMimeMail::buildBodyParts`.
If no HTML is included at all, or only a few inline
elements (\<b\>, \<u\>, \<i\>, \<a\>) are given, a
[`nl2br()`](http://php.net/manual/en/function.nl2br.php)
is applied on the body and used for the HTML version
of the email. The originally passed body is used as
plain/text alternative.
If HTML is detected in the body string passed by the
consumer, the original body is used for the HTML email.
For the plain/text alternative, `<br>` elements are replaced
with a `\n` (LF) character and
[`strip_tags'](http://php.net/manual/en/function.strip-tags.php)
is applied afterwards on the originally passed message body.

This behaviour is not specified at all and also tries
to handle/fix misusage by consumers.
The rule is: You MUST NOT pass any HTML.

For **internal** messages HTML is completely escaped
on the UI endpoint via `\ilUtil::htmlencodePlainString`.

### Attachments

The `$attachments` can be passed as an array of file names.
Each file MUST have been assigned to the sending user account
in a prior step.
This can be done as described in the following examples:

```php
$attachment = new \ilFileDataMail($senderUserId);

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

// or $attachment->unlinkFiles(['/temp/hello.jpg']);
$attachment->unlinkFile('/temp/hello.jpg');
```

As outlined above attachments have to be removed
manually by the consumer after the transport of an
email has been delegated to the mail system.

### Type

The type string MUST be one of the following options:

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
an email to external email addresses and you don't want to
use one of the existing wrappers described below.
This class is mainly used for external procedures, e.g. if 
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
When preparing the email transport `\ilMimeMail` wraps an HTML structure around
the message body (see: [HTML Mails with Skin](https://www.ilias.de/docu/goto_docu_wiki_wpage_3506_1357.html)) 
passed by the consumer. It automatically transforms newline characters (LF/CR)
to HTML linebreak elements.
The original message body is used as *text/plain* alternative, the processed
body is used as *text/html* alternative.

### Sender

Since ILIAS 5.3.x the `$sender` cannot be passed as a primitive
data type anymore. A sender MUST implement `\ilMailMimeSender`.
ILIAS currently provides two different implementations:

1. `\ilMailMimeSenderSystem`
2. `\ilMailMimeSenderUser`

Both instances can be retrieved by the globally available `\ilMailMimeSenderFactory`,
registered in the dependency container (`$DIC["mail.mime.sender.factory"]`).

The first MUST be used whenever an email is sent with the purpose of a *System Email*.
The second type MUST be used for a *User-to-User Email*.

### Transport

The actual transport is decoupled from `\ilMimeMail`. `\ilMimeMail` uses
the determined transport strategy given by the
`\ilMailMimeTransportFactory` instance which is available in
the dependency injection container (`$DIC["mail.mime.transport.factory"]`).

A transport strategy MUST follow the definition of
the `\ilMailMimeTransport` interface. A different transport could be
globally set via `\ilMimeMail::setDefaultTransport`, or just for
the next call of `Send()` by passing an instance of `\ilMailMimeTransport` as
argument:

```php
$mailer->Send($transport);
```

## ilMailNotification

`\ilMailNotification` is a **higher level** abstraction
wrapping `\ilMail`. It can be extended to create a custom
email for a specific purpose of your component or plugin.

Every service/module/plugin in ILIAS MAY create a specialized class
for creating and sending emails. Therefore it MUST use inheritance
and derive from `\ilMailNotification`.  

```php
class MyMailNotification extends \ilMailNotification
{
    // [...]
    public function send()
    {
        try
        {
            $this->setSubject('Welcome to ILIAS');
            $this->setBody('Hello World!');
            $this->appendBody(\ilMail::_getInstallationSignature());

            $this->sendMail($this->getRecipients(), array('system'));
        }
        catch(\ilMailException $e)
        {
            // Error handling
        }
    }
    // [...]
}
```

The usage could look like this:

```php
$myMailNotification = new \MyMailNotification();
$myMailNotification->setRecipients(
    [
        4711, 666
    ]
);
$myMailNotification->send();
```

If the recipients could only be/are already
provided as usernames or email addresses,
you can use the third parameter of `sendMail()`
and pass a boolean `false`.
If you pass `true` as third argument or don't pass
a third argument at all, the elements of the first
parameter array are considered to be the internal
user ids of the recipients and the corresponding
usernames will be determined automatically when
`sendMail()` is called.

```php
class MyMailNotification extends \ilMailNotification
{
    // [...]
    public function send()
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
    // [...]
}
```

The usage could look like this:

```php
$myMailNotification = new \MyMailNotification();
$myMailNotification->setRecipients(
    [
        'root', 'dummy@gmail.com'
    ]
);
$myMailNotification->send();
``` 

If you explicitly need to send an external email you
can use/extend from `\ilMimeMailNotification`. This class itself
also derives from `\ilMailNotification`.

Recipients can be provided as:

* Instances of \ilObjUser
* The internal user id
* Email address

```php
class ilRegistrationMailNotification extends \ilMimeMailNotification
{
    // [...]
    public function send()
    {
        try
        {
            foreach ($this->getRecipients() as $rcp) {
                $this->initMail();
                $this->initLanguage($rcp);
                $this->setSubject('Welcome to ILIAS');
                $this->setBody('Hello World!');
                $this->appendBody(\ilMail::_getInstallationSignature());

                $this->getMail()->appendInstallationSignature(true);
                $this->sendMail(array($rcp),array('system'));
            }
        }
        catch(\ilMailException $e)
        {
            // Error handling
        }
    }
    // [...]
}
```

The usage could look like this:

```php
$myMailNotification = new \ilRegistrationMailNotification();
$myMailNotification->setRecipients([
    6,
    new ilObjUser(6),
    'dummy@gmail.com'
]);
$myMailNotification->send();
``` 

## ilSystemNotification

`\ilSystemNotification` is a commonly used implementation
of the previous explained `\ilMailNotification`.

This class is used to create an email sent by the ILIAS
system with a more or less given structure and text layout.
The implementation is used by several modules/services
to create their own emails.

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

## ilAccountMail

An instance of `\ilAccountMail` MUST be used to sent
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
* \[ADMIN_MAIL\]: Email address of Administrator
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

This will create a default message with the look
of a registration email.

To enable the language variable fallback the
following mutator has to be called with a
boolean true as it's argument:
```php
$accountMail->useLangVariablesAsFallback(true);
```
If a raw password should be included, it MUST be
explicitly set via:
```php
$accountMail->setUserPassword($rawPassword);
 ```

Internally `\ilAccountMail` makes use of `\ilMimeMail`.

## Manual Mail Templates

The concept of ['Manual Mail Templates'](https://www.ilias.de/docu/goto_docu_wiki_wpage_2703_1357.html) is best described
as a feature which enables services/modules to
provide text templates for a 'User-to-User Email' in a
specific context.

Often tutors / admins send the emails with the same purpose
and texts to course members, e.g. to ask them if they
have problems with the course because they have not
used the course yet.

### Context Registration

A module or service MAY announce its email template
contexts to the system by adding them to their
respective *module.xml* or *service.xml*.
The template context id has to be globally unique.
An optional path can be added if the module/service
directory layout differs from the ILIAS standard,
where most files are located in a `./classes`
directory.

```xml
<?xml version = "1.0" encoding = "UTF-8"?>
<module xmlns="http://www.w3.org" version="$Id$" id="crs">
    ...
    <mailtemplates>
        <context id="crs_context_manual" class="ilCourseMailTemplateContext" />
    </mailtemplates>
</module>
```

If registered once, every email template context
class defined in a *module.xml* or *service.xml*
has to extend the base class `\ilMailTemplateContext`.
All abstract methods MUST be implemented to make
a template context usable.

* getId(): string
* getTitle(): string
* getDescription(): string
* getSpecificPlaceholders(): array
* resolveSpecificPlaceholder(string $placeholderId, array $contextParameters, \ilObjUser $recipient = null, $htmlMarkup = false): string

A collection of context specific placeholders
can be returned by a simple array definition.
The key of each element should be a unique
placeholder id.
Each placeholder contains (beside its id) a
placeholder string and a label which is used
in the user interfaced.

```php
return array(
    'crs_title' => array(
        'placeholder' => 'CRS_TITLE',
        'label' => $lng->txt('crs_title')
    ),
    'crs_link' => array(
        'placeholder' => 'CRS_LINK',
        'label' => $lng->txt('crs_mail_permanent_link')
    )
);
```

Supposing the context registration succeeded and you
properly derived a context PHP class providing all
necessary data and placeholders, you are now
able to use your registered context in your component
and build hyperlinks to the mail system, transporting
your context information.

### Context Usage Example

Given you created a context named `crs_context_tutor_manual` ...

```php
global $DIC;

class ilCourseMailTemplateTutorContext extends \ilMailTemplateContext
{
    // [...]
    const ID = 'crs_context_tutor_manual';
    // [...]
}
```

... you can provide a hyperlink to the mail system (or
more precisely to `\ilMailFormGUI`) as follows:

```php
$DIC->ctrl()->redirectToUrl(
    \ilMailFormCall::getRedirectTarget(
        $this, // The referring ILIAS controller aka. GUI when redirecting back to the referrer
        'participants', // The desired command aka. the method to be called when redirecting back to the referrer
        array(), // Key/Value array for parameters important for the ilCtrl/GUI context when when redirecting back to the referrer, e.g. a ref_id
        array(
           'type' => 'new', // Could also be 'reply' with an additional 'mail_id' paremter provided here
        ),
        array(
            \ilMailFormCall::CONTEXT_KEY => \ilCourseMailTemplateTutorContext::ID, // don't forget this!
            'ref_id' => $courseRefId,
            'ts'     => time(),
            // further parameters which will be later automatically passed to your context class 
        )
    )
);
```

Parameters required by your mail context class
MUST be provided as a key/value pair array in the
fifth parameter of `\ilMailFormCall::getRedirectTarget`.
These parameters will be passed back to
your context class when the mail system uses
`\ilMailTemplateContext::resolveSpecificPlaceholder(...)`
as callback when an email is actually sent and included
placeholders should be replaced.
You also MUST add a key `\ilMailFormCall::CONTEXT_KEY`
with your context id as value to this array.
