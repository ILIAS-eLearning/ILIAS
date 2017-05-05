# Filesystem Service
## Conceptual Summary
To eliminate security issues like path traversal, we would like to introduce
a new Filesystem Service which streamlines the filesystem access for ILIAS.
The service provides a modular way for extension, which enables the ILIAS
community to seamless extend the service with additional supported filesystem
types.

There are four directories which are accessed via the service:
* Data directory within the ILIAS webroot
* ILIAS data directory
* Customizing directory
* Temporary directory

## ILIAS DI integration
To use the new filesystem service a new key is introduced into the DIC named "filesystem".
It's possible to access the 4 storage locations via the methods described bellow.
Each of the 4 Methods return a filesystem object which satisfies the Filesystem interface.
 
```php
<?php

//new filesystem service key
$DIC["filesystem"];
 
//access the 4 predefined storage locations
$DIC["filesystem"]->web();                //Data directory within the ILIAS web root
$DIC["filesystem"]->storage();            //ILIAS data directory
$DIC["filesystem"]->customizing();        //The Customizing directory within the ILIAS web root
$DIC["filesystem"]->temp();               //Temporary directory
```

## Authors

* **Nicolas Schaefli** - *interface definition* - [d3r1w](https://github.com/d3r1w)

## Versioning

We use [SemVer](http://semver.org/) for versioning. 

## Acknowledgments

* [keep a changelog](http://keepachangelog.com/) The guide used to create and update the service changelog.
