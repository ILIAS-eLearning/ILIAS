# Context-related possibilities

ILIAS can be used in several contexts, with the web-application being the first and foremost usage. But there are several other ways to access ILIAS ressources: SOAP, CLI/Shell, RSS, and so on. As all protocols have their respective limitations ilContext has been introduced with ILIAS 4.3 to handle those limits in a generic way.

## ilContext - basic usage
Every script - as in "entry point" - in ILIAS should set a specific context, e.g. `CONTEXT_WEB` or `CONTEXT_CRON`. This has to be done **before** calling `ilInitalisation` (either directly or through `inc.header.php`). If no specific context is set, `CONTEXT_WEB` is assumed.

```php
include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_CRON);
```

The basic methods used to check against protocol-imposed limitations are as follows (in no specific order):

- `bool supportsRedirects()` Returns true if redirects are currently possible.
- `bool hasUser()` The context does not necessarily have a specific user, e.g. CONTEXT_RSS.
- `bool usesHTTP()` This can be used to find out if GET/POST/SERVER-Variables are available, especially relevant for CONTEXT_CRON.
- `bool hasHTML()` This is meant as "is the output to be delivered as HTML?" as opposed to ASCII, e.g. CONTEXT_CRON.
- `bool usesTemplate()` This decides if ilTemplate is available in the current context. But as the template engine and the main template are completely mixed up, there are very few cases where it can be omitted.
- `bool initClient()` Without the client only the very basic core system will be initialised. See `ilInitialisation::initILIAS()`.
- `bool doAuthentication()` The authentication can be completely bypassed, use at your own risk.
- `bool getType()` Checks should not be done against the type of context but against features. This is for those very rare exceptions where it is needed.

### Example

It is mostly the very low-level parts of ILIAS that benefit the most of the abstraction made possible by ilContext. The following code snippet kind of sums up what is currently possible.

```php
if(ilContext::supportsRedirects())
{
   ilUtil::redirect($a_target);
}     
else
{        
   // user-directed linked message
   if(ilContext::usesHTTP() && ilContext::hasHTML())
   {                       
      $mess = $a_message_details.
         ' Please <a href="'.$a_target.'">click here</a> to continue.';             
   }
   // plain text 
   else
   {                          
      // not much we can do here
      $mess = $a_message_details;      
 
      if(!trim($mess))
      {
         $mess = 'Redirect not supported by context ('.$a_target.')';               
      }
   }
 
   self::abortAndDie($mess);        
}
```

## Future prospects

The main scenario would be to enable AJAX-requests that deliver JSON in a controlled manner (aka "no exits"). This would need an overhaul of ilTemplate and probably ilCtrl, too. For now the basic HTML-based page delivery is assumed unless stopped (aka "exit").

Something like ilContext could be enhanced to not only supply information about the general context of the request but more dynamic and detailed data as

- output format
- input data
- and so on.

By no means this is intended as the new global object or object registry.