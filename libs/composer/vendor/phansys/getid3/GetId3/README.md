GetId3
======
[![Build Status](https://secure.travis-ci.org/phansys/GetId3.png?branch=master)](http://travis-ci.org/phansys/GetId3)

This version of GetId3 library **only works in PHP >= 5.3** and updates to [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) CS and makes it Symfony2 installable by deps or composer mechanisms.

* [Main site] http://www.getid3.org
* [Support] http://support.getid3.org/

License
-------

For license info please read [Resources/doc/license.txt](https://github.com/phansys/GetId3/tree/master/Resources/doc/license.txt)

For commercial license read [Resources/doc/license.commercial.txt](https://github.com/phansys/GetId3/tree/master/Resources/doc/license.commercial.txt)

## Installation
(You can choose deps or composer install mechanisms)

### deps

##### Step 1: Download GetId3

Add following lines to your `deps` file:

```
[GetId3]
    git=https://github.com/phansys/GetId3.git
    target=/phansys/getid3/GetId3

```
Now, run the vendors script to download the library:

``` bash
$ php bin/vendors install
```

##### Step 2: Configure the Autoloader

Add the `GetId3` namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerPrefixes(array(
    // ...
        'GetId3' => __DIR__.'/../vendor/phansys/getid3/GetId3',
        ));
```
___

### [composer] (http://getcomposer.org/)

##### Step 1: Edit composer.json

Add following lines to your `composer.json` `"require"` definitions:

``` json
"phansys/getid3": "v2.0.0.x-dev"
```

##### Step 2: Run composer

Now, run the composer script to download the library:

``` bash
$ php composer.phar install
```


Quick use example reading audio properties
------------------------------------------

``` php
<?php
namespace My\Project;

use \GetId3\GetId3Core as GetId3;

class MyClass
{
    // ...
    private function MyMethod()
    {
        $getId3 = new GetId3();
        $getId3->option_md5_data        = true;
        $getId3->option_md5_data_source = true;
        $getId3->encoding               = 'UTF-8';		
        $mp3File = '/path/to/my/mp3file.mp3';	
        $audio = $getId3->analyze($mp3File);	
        if (isset($audio['error'])) 
        {
            throw new \RuntimeException('Error at reading audio properties with GetId3 : ' . $mp3File);
        }			
        $this->setLength(isset($audio['playtime_seconds']) ? $audio['playtime_seconds'] : '');
        // var_dump($audio);
    }
}
```
