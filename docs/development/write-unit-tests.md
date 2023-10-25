# Writing Unit Tests

ILIAS supports unit testing with the PHPUnit testing framework. We highly recommend their [excellent documentation](https://phpunit.de/) that explains the basic ideas of unit testing, how PHPUnit works and how it is installed.

## Configuration
After installing PHPUnit on your machine you need to configure ILIAS to work with it. The test cases are performed with an authenticated user in your ILIAS installation. The configuration determines which user is used to perform the test cases. You will find a configuration file template at:

`Services/PHPUnit/config/cfg.phpunit.template.php`

Make a copy at:

`Services/PHPUnit/config/cfg.phpunit.php`

Now change the file and provide a valid client ID, account ID, and username.

>*Please activate the [developer mode](https://docu.ilias.de/goto_docu_pg_1082_42.html) in your `client.ini.php` when running PHPUnit tests.*

## Test Cases
Test classes should be located in a subdirectory test of your module or service directory.

`[Services/Modules]/[ComponentName]/test`

E.g. the test classes for the Administration service are located at `Services/Administration/test`. The names for test class files should usually be derived from the application class they are written for.

`[ApplicationClassName]Test.php`

E.g. if you write test cases for a class ilSetting the test class file should be named `ilSettingTest.php`.

The class should contain a method starting with "test" for each test case.

```php
<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
[...]
    +-----------------------------------------------------------------------------+
*/
 
class ilSettingTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = FALSE;
 
    protected function setUp()
    {
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
    }
 
    public function testSetGetSettings()
    {
        $set = new ilSetting("test_module");
        $set->set("foo", "bar");
        $value = $set->get("foo");
 
        $this->assertEquals("bar", $value);
    }
[...]
}
?>
```

- Your test class must be **derived from class PHPUnit_Framework_TestCase**.
- Your test must be executable with **PHPUnit 5.7**.
- If your test needs an ILIAS Installation, it must be annotated with **@group needsInstalledILIAS**, and...
  - you must set **$backupGlobals to false**, otherwise, your test cases will not work.
  - the setUp() method should contain the **ilUnitUtil::performInitialisation();** call. ilUnitUtil can be found in Services/PHPUnit/classes.
- If your test does not need an ILIAS Installation, it must not be annotated with @group needsInstalledILIAS and also should not use ilUnitUtil::performInitialisation().

To run your test class you simply call phpunit in your ILIAS root directory with the local path of your test class (omit the .php suffix):

`> phpunit Services/Administration/test/ilSettingTest`


>1. *Please write your test cases in a way that the* ***requirements to run them are minimal***. *The test cases should run on a usual system. They should not require that certain conditions are given on the test system, e.g. an empty repository.*
>2. ***Clean up the data*** *that is written during the test case. If possible, a test run should not leave any data created during the test in the system.*

## Test Suites
Test suites allow to perform aggregated tests. They should be provided on the component level, one test suite for each service and module. The file name must follow the format:
`il[Services/Modules][ComponentName]Suite.php`

E.g. the test suite class for the Administration service is located at:
`Services/Administration/test/ilServicesAdministrationSuite.php`

The class is named:
`ilServicesAdministrationSuite`

```php
<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
[...]
    +-----------------------------------------------------------------------------+
*/
 
class ilServicesAdministrationSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesAdministrationSuite();
 
        // add each test class of the component     
        include_once("./Services/Administration/test/ilSettingTest.php");
        $suite->addTestSuite("ilSettingTest");
        [...]
 
        return $suite;
    }
}
?>
```

The example outlines the basic structure of a test suite class. To run the test suite, simply pass the class name to phpunit in the ILIAS main directory:
`> phpunit Services/Administration/test/ilServicesAdministrationSuite`

## The Global Test Suite
The global test suite scans all Services and Modules directory automatically for component level test suites and aggregates them to one big test suite. The suite is located in the PHPUnit Service. You can run the suite by typing:
`> phpunit Services/PHPUnit/test/ilGlobalSuite`

The global suite will first list all component suites that are included in execution. If your suite is not listed, you should check whether your suite file and class is following the naming conventions as written in the previous section.