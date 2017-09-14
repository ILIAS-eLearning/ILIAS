# ILIAS Unit Testing investigation
## Definition
Unit testing is the process of testing the smallest testable part of an application, called units.
Units are usually one method of a class or one logical concept of the method.

Unit tests fulfil the following criteria:
- Able to be **fully automated**
- **Isolated** (Dependencies of the class under test are mocked)
- Runnable in **any order**, if the test is part of many other tests
- **In memory** (no db or filesystem access)
- **Consistent** results (Should always return the same, no random numbers)
- Runs **fast** (Test should at most take 1min to finish)
- Tests a **single logical unit**
- **Readable**
- **Maintainable**
- **Trustworthy** (The result of the test must be correct)

### Role in ILIAS

All unit tests in ILIAS are automated and help to verify that all units behave as intended.
Furthermore, unit tests enable the community to tackle bugs much faster and in an earlier state of development.
Due to a faster handling of side effects and other bugs, more time is left to actually refactor and improve the current code base. 
 
//describe what unit tests are and how they improve ILIAS. Also refer to the goals set by the community as well as the CI server
## Tools
### PHP Unit
PHP Unit is a collection of tools (PHP classes and executables) which makes not only testing easy, but also helps to gain 
insight into the test results and how much of the code base remains untested.
 
### Mockery
Mockery is a lightweight and flexible mocking object framework which is used for unit testing with PHP Unit and other unit testing frameworks.
It it designed as a drop in replacement for the PHP Unit mock functionality, but can also work alongside with the PHP Unit mock objects.

#### Mock Objects
In unit tests mock objects are used to simulate a specific behaviour of real objects. The primary usage of mock objects is to isolate the
object under test. However there are also other use cases for example some times there is no actual implementation of classes which are required
for the class under test.

The benefit of mocking frameworks are the dynamic creation of such mock objects and stubs. They enable developers to describe the behaviour 
of the mock objects with a flexible API. The API also aims to be as close as possible to natural language descriptions to make the test code
even more expressive.

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

[ILIAS CI Server](https://ci.ilias.de/)

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