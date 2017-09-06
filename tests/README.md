# ILIAS Unit Testing investigation
## Definition
//describe what unit tests are and how they improve ILIAS. Also refer to the goals set by the community as well as the CI server
## Tools
### PHP Unit
### Mockery
//describe mockery and php unit
## Setup test environment
### Setup
//Show how to setup the tools described in the previous chapter
### Run tests with PHPStorm
### Run tests with CLI
## Guidelines
//what should be tested and how
### Naming
The filename of the test class should always be named like *\<class name of the implementation\>Test.php*. 
Furthermore, the test class should always be named as the class which is tested by the unit test class.
For example the real class is called *Car* the corresponding test class would be named *CarTest* and the filename
 *CarTest.php*.

### Directory structure
#### Old parts
Each module / service has its own test folder which should have the same structure as the classes directory.
For example:
```
WAC/
|
-----> classes/
|        |
|        ------> path/
|                  |
|                  -----> SecurePath
|        
-----> test/
         |
         ------> path/
                   |
                   -----> SecurePath
```
#### New parts (/src)
The test source for the new parts of ILIAS are located in the *tests* directory, 
which is located in the web root directory of ILIAS. The structure should be the same 
than the *src* directory where the actual implementation lives.
```
<webroot>/
|
-->  src
|     |
|     ------> <your service>/
|     |             |
|     |             --------> <your folders structur>/
|     |                                |
|     |                                ------> <your class>
|     |
|     ------> <other services>/
|
--> tests
      |
      ------> <your service>/
      |             |
      |             --------> <your folders structur>/
      |                                |
      |                                ------> <your test class>
      |
      ------> <other services>/
```


 
## Test Examples
//show different test scenarios and how they are looking in ILIAS
//filesystem test
//mocking tests (normal, fat legacy classes, disable constructor, fluent interfaces)
//DI erwähnen
## External documentation
[PHP Unit Documentation](https://phpunit.de/manual/5.7/en/index.html)

[Mockery Documentation](http://docs.mockery.io/en/latest/)
## Old Documentation
[Old ILIAS Unit Test Guide](https://www.ilias.de/docu/goto_docu_pg_29759_42.html)

//Refer to obsolete documentation

## FAQ
### What needs to be tested ?
### How is a local test environment set up ?
### How do I start the ILIAS unit tests in PHPStorm ? 
### How do I start the ILIAS unit tests in CLI ?
### Where do I put my unit tests ?
### What is the ILIAS CI-Server and how can I benefit from it in terms of unit tests ?
### What do I need to consider concerning unit tests before pushing code to the ILIAS repo ?
//talk about testability