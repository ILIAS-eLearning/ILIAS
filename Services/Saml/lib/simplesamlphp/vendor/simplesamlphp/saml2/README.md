SimpleSAMLphp SAML2 library
===========================
[![Build Status](https://travis-ci.org/simplesamlphp/saml2.png?branch=feature/fix-build)]
(https://travis-ci.org/simplesamlphp/saml2) [![Coverage Status](https://img.shields.io/coveralls/simplesamlphp/saml2.svg)]
(https://coveralls.io/r/simplesamlphp/saml2)


A PHP library for SAML2 related functionality. Extracted from [SimpleSAMLphp](http://www.simplesamlphp.org),
used by [OpenConext](http://www.openconext.org).
This library is a collaboration between [UNINETT](http://uninett.no) and [SURFnet](http://surfnet.nl).


Before you use it
-----------------
**DO NOT USE THIS LIBRARY UNLESS YOU ARE INTIMATELY FAMILIAR WITH THE SAML2 SPECIFICATION.**

If you are not familiar with the SAML2 specification and are simply looking to connect your application using SAML2,
you should probably use [SimpleSAMLphp](http://www.simplesamlphp.org).

While this library is tagged as stable it is currently not very developer friendly and it's API is likely to change
significantly in the future. It is however a starting point for collaboration between parties.
So let us know what you would like to see in a PHP SAML2 library.

Note that the **HTTP Artifact Binding and SOAP client not work** outside of SimpleSAMLphp.


Usage
-----

* Install with [Composer](http://getcomposer.org/doc/00-intro.md), add the following in your composer.json:

```json
{
    "require": {
        "simplesamlphp/saml2": "0.1.*"
    }
}
```

Then run ```composer update```.

* Provide the required external dependencies by extending and implementing the ```SAML2_Compat_AbstractContainer```
  then injecting it in the ContainerSingleton (see example below).

* Use at will.
Example:
```php
    // Use Composers autoloading
    require 'vendor/autoload.php';

    // Implement the Container interface (out of scope for example)
    require 'container.php';
    SAML2_Compat_ContainerSingleton::setContainer($container);

    // Set up an AuthnRequest
    $request = new SAML2_AuthnRequest();
    $request->setId(SAML2_Utils::generateId());
    $request->setIssuer('https://sp.example.edu');
    $request->setDestination('https://idp.example.edu');

    // Send it off using the HTTP-Redirect binding
    $binding = new SAML2_HTTPRedirect();
    $binding->send($request);
```

License
-------
This library is licensed under the LGPL license version 2.1.
For more details see [LICENSE](https://raw.github.com/simplesamlphp/saml2/master/LICENSE).
