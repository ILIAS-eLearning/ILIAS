# ILIAS Unit Testing investigation

<!-- MarkdownTOC depth=3 autolink="true" bracket="round" autoanchor="true" style="ordered" indent="   " -->

1. [Definition](#definition)
   1. [Role in ILIAS](#role-in-ilias)
1. [Tools](#tools)
   1. [PHP Unit](#php-unit)
   1. [Mockery](#mockery)
      1. [Mock Objects](#mock-objects)
1. [Setup test environment](#setup-test-environment)
   1. [Setup composer](#setup-composer)
   1. [Setup ILIAS](#setup-ilias)
   1. [Setup xDebug \(test coverage\)](#setup-xdebug-test-coverage)
      1. [Setup for Ubuntu](#setup-for-ubuntu)
      1. [Configuration](#configuration)
   1. [Run tests with PHPStorm](#run-tests-with-phpstorm)
      1. [Configure Testframework](#configure-testframework)
      1. [Create run configuration](#create-run-configuration)
      1. [Run the tests](#run-the-tests)
      1. [Explanation ILIAS installation bound tests](#explanation-ilias-installation-bound-tests)
      1. [Configure ILIAS installation bound tests](#configure-ilias-installation-bound-tests)
      1. [Exclude ILIAS installation bound tests](#exclude-ilias-installation-bound-tests)
      1. [Run only ILIAS installation bound tests](#run-only-ilias-installation-bound-tests)
   1. [Run tests with CLI](#run-tests-with-cli)
      1. [Execute ILIAS installation bound tests](#execute-ilias-installation-bound-tests)
      1. [Execute all tests](#execute-all-tests)
      1. [Execute only installation unbound tests](#execute-only-installation-unbound-tests)
1. [Guidelines](#guidelines)
   1. [Foreword](#foreword)
   1. [Naming](#naming)
      1. [Namespace](#namespace)
      1. [Class](#class)
      1. [Unit-Test \(Method\)](#unit-test-method)
      1. [Further improvements](#further-improvements)
   1. [Directory structure](#directory-structure)
      1. [Old parts](#old-parts)
      1. [New parts \(/src\)](#new-parts-src)
   1. [Unit-Test structure](#unit-test-structure)
   1. [Good tests are FIRST](#good-tests-are-first)
      1. [Fast](#fast)
      1. [Isolated](#isolated)
      1. [Repeatable](#repeatable)
      1. [Self-Validating](#self-validating)
      1. [Timely](#timely)
   1. [Write CORRECT tests](#write-correct-tests)
      1. [Conformance](#conformance)
      1. [Ordering](#ordering)
      1. [Range](#range)
      1. [Reference](#reference)
      1. [Existence](#existence)
      1. [Cardinality](#cardinality)
      1. [Time](#time)
1. [Test Examples](#test-examples)
   1. [Negative Examples](#negative-examples)
      1. [Wrong Location and Bloated](#wrong-location-and-bloated)
      1. [Testing PHP behaviour / Wrong class](#testing-php-behaviour--wrong-class)
      1. [Useless test / Generic naming](#useless-test--generic-naming)
      1. [Test regression](#test-regression)
   1. [Test Examples](#test-examples-1)
      1. [Template](#template)
      1. [Normal test](#normal-test)
      1. [Fat legacy class with parent](#fat-legacy-class-with-parent)
      1. [Mock static calls](#mock-static-calls)
      1. [Fluent interfaces](#fluent-interfaces)
   1. [Rise testability](#rise-testability)
      1. [SOLID](#solid)
      1. [A SOLID way to use the DIC](#a-solid-way-to-use-the-dic)
      1. [Distinction between unit and integration tests](#distinction-between-unit-and-integration-tests)
1. [Continues integration](#continues-integration)
   1. [Test suites](#test-suites)
   1. [Benefits](#benefits)
   1. [Current problems](#current-problems)
      1. [External PHPUnit version](#external-phpunit-version)
      1. [Risky prebuild step](#risky-prebuild-step)
1. [External Documentation](#external-documentation)
1. [FAQ](#faq)
   1. [What needs to be tested ?](#what-needs-to-be-tested-)
   1. [How is a local test environment set up ?](#how-is-a-local-test-environment-set-up-)
   1. [How do I start the ILIAS unit tests in PHPStorm ?](#how-do-i-start-the-ilias-unit-tests-in-phpstorm-)
   1. [How do I start the ILIAS unit tests in CLI ?](#how-do-i-start-the-ilias-unit-tests-in-cli-)
   1. [Where do I put my unit tests ?](#where-do-i-put-my-unit-tests-)
   1. [What is the ILIAS CI-Server and how can I benefit from it in terms of unit tests ?](#what-is-the-ilias-ci-server-and-how-can-i-benefit-from-it-in-terms-of-unit-tests-)
   1. [What do I need to consider concerning unit tests before pushing code to the ILIAS repo ?](#what-do-i-need-to-consider-concerning-unit-tests-before-pushing-code-to-the-ilias-repo-)
1. [Glossary](#glossary)
1. [Sources](#sources)

<!-- /MarkdownTOC -->


<a name="definition"></a>
## Definition
Unit testing is the process of testing the smallest testable part of an 
application, called units. Units are usually one behaviour of a class 
or one logical concept of a method.

Unit tests fulfil the following criteria: [7]
- Able to be **fully automated**
- **Isolated** (Dependencies of the class under test are mocked)
- Runnable in **any order**, if the test is part of many other tests
- **In memory** (no db or filesystem access)
- **Consistent** results (Should always return the same, no random numbers)
- Runs **fast** (Test should at most take 1 seconds to finish)
- Tests a **single logical unit**
- **Readable**
- **Maintainable**
- **Trustworthy** (The result of the test must be correct)

<a name="role-in-ilias"></a>
### Role in ILIAS

All unit tests in ILIAS are fully automated with the goal to verify that all 
units behave as intended. Furthermore, unit tests enable the community to tackle 
bugs much faster and in an earlier state of development. Due to a faster handling 
of side effects and other bugs, more time is left to actually refactor and improve 
the current code base.



We're not there yet with UnitTests in ILIAS! It has to become a habit to concern 
about UnitTests in ILIAS Development:
- if you are a developer: write them
- if you are a service provider: offer them
- if you are a customer: fund them

<a name="tools"></a>
## Tools
<a name="php-unit"></a>
### PHP Unit
PHP Unit is a collection of tools (PHP classes and executables) which makes not 
only testing easy, but also helps to gain insight into the test results and 
how much of the code base remains untested. [8]

<a name="mockery"></a>
### Mockery
Mockery is a lightweight and flexible mocking object framework which is used for 
unit testing with PHP Unit and other unit testing frameworks. It is designed 
as a drop in replacement for the PHP Unit mock functionality, but can also work 
alongside with the PHP Unit mock objects. [2]
Before using Mockery please consider that you add an additional dependency to
your code. Mockery has functionality that allows testing older ILIAS-code which
is not provided by PHPUnit. Many similar functionalities are also native to
PHPUnit. It is recommended to only use Mockery if required and prefer PHPUnit
otherwise.


<a name="mock-objects"></a>
#### Mock Objects
In unit tests mock objects are used to simulate a specific behaviour of real 
objects. The primary usage of mock objects is to isolate the object under test. 
However there are also other use cases, for example some times no implementation 
of a class is present at test time, this missing implementation can be replaced 
by a mock object.

The benefit of mocking frameworks are the dynamic creation of such mock objects 
and stubs. They enable developers to describe the behaviour
of the mock objects with a flexible API. The API also aims to be as close as 
possible to natural language descriptions to make the test code even more 
expressive. [3]

<a name="setup-test-environment"></a>
## Setup test environment
<a name="setup-composer"></a>
### Setup composer
Composer is a dependency manager for PHP packages / libraries.
The composer [installation guide](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)
provides all necessary steps for the setup.

<a name="setup-ilias"></a>
### Setup ILIAS
Install ILIAS on your favourite operation system with the provided [installation guide](docs/configuration/install.md).
Make sure that all dev and prod dependencies are installed with composer.  
```
composer show
```
Verify that the phpunit packages are showing up in the displayed list.
If the packages are not listed run the following command to install them.  
```
composer install
```

<a name="setup-xdebug-test-coverage"></a>
### Setup xDebug (test coverage)
XDebug is only used for the generation of the test coverage.
If no test coverage is required please skip this installation step because 
xDebug will slow down your unit test quite a bit.

<a name="setup-for-ubuntu"></a>
#### Setup for Ubuntu
- Run the following command or use your own package manager to install the 
following package.

<a name="trusty"></a>
##### Trusty
```
sudo apt-get install php5-xdebug
```

<a name="xenial"></a>
##### Xenial
```
sudo apt-get install php-xdebug
```

<a name="configuration"></a>
#### Configuration
Please make sure to add the xdebug extension to one of your php.ini files.

<a name="run-tests-with-phpstorm"></a>
### Run tests with PHPStorm

<a name="configure-testframework"></a>
#### Configure Testframework
- Enter the PHPStorm settings.
- Navigate to "Language & Frameworks -> PHP -> Testframeworks"
- Select your interpreter
- Select composer within the PHPUnit library section
- Enter the path to the composer 
autoload.php -> {ILIAS root}/libs/composer/vendor/autoload.php
- Hit the Ok button to save the changes.

<a name="create-run-configuration"></a>
#### Create run configuration
- Navigate to "Run -> Edit Configurations..."
- Hit the plus button and select PHPUnit to create a new configuration
- Name it properly like global test suite.
- Select the test scope radio option -> Defined in Configuration file
- Tick use alternative configuration file
- Enter the path -> {ILIAS root}/Services/PHPUnit/config/PhpUnitConfig.xml
- Set the path custom working directory to the ILIAS root.
- Hit the OK button to save the changes

<a name="run-the-tests"></a>
#### Run the tests
Select the test in the top right corner and press the play button to let the 
global suite run.

<a name="explanation-ilias-installation-bound-tests"></a>
#### Explanation ILIAS installation bound tests
ILIAS with the version 5.3 contains installation bound tests which require 
a fully installed ILIAS. All tests which are bound belong to a special test group
called *needInstalledILIAS* which can be excluded to run the remaining test 
for example on a continuous integration server.

The tests in the *needInstalledILIAS* group also need an additional configuration
which is shown bellow. 

<a name="configure-ilias-installation-bound-tests"></a>
#### Configure ILIAS installation bound tests
The ILIAS bound test uses a configuration located in 
{ILIAS root}/Services/PHPUnit/config/cfg.phpunit.php. If the file doesn't exist 
the template file must be copied and configured as shown bellow. 
The location of the template file is 
{ILIAS root}/Services/PHPUnit/config/cfg.phpunit.template.php.

Rename the template *cfg.phpunit.template.php* to *cfg.phpunit.php*.

```
$_SESSION["AccountId"] = '6';
$_POST["username"] = 'root';
$_GET["client_id"] = 'default';
```

The values above shows an example configuration which loads the root user which 
has the id 6 and the ILIAS client with the id "default".

<a name="find-installation-specific-values"></a>
##### Find installation specific values
* The account id is equal to the user id which can be found
at *Administration -> User Management -> Username*.
* The value for the username is equal to the field called "Login"
right after the user id. 
* A full list of installed clients is provided in the setup screen which is located
at {ILIAS URL}/setup/setup.php. In order to see all clients a master password
login is required.

<a name="exclude-ilias-installation-bound-tests"></a>
#### Exclude ILIAS installation bound tests
- Navigate to "Run -> Edit Configurations..."
- Select your PHPUnit run configuration
- Add "--exclude-group needInstalledILIAS" to the Test runner options.
- Hit OK to save the changes

<a name="run-only-ilias-installation-bound-tests"></a>
#### Run only ILIAS installation bound tests
- Navigate to "Run -> Edit Configurations..."
- Select your PHPUnit run configuration
- Add "--group needsInstalledILIAS" to the Test runner options.
- Hit OK to save the changes

<a name="run-tests-with-cli"></a>
### Run tests with CLI
At the current state of the ILIAS test, these require the backup of the global
scope which indicates a dependency between some of the tests. Therefore, if the
test should run as configured on the CI server omit the *--no-globals-backup*,
*--report-useless-tests* and *--disallow-todo-tests* options.

The commands bellow must be run from the ILIAS web root directory.

<a name="execute-ilias-installation-bound-tests"></a>
#### Execute ILIAS installation bound tests
```
./libs/composer/vendor/bin/phpunit -c ./Services/PHPUnit/config/PhpUnitConfig.xml \
	--colors=always \
	--no-globals-backup \
	--report-useless-tests \
	--disallow-todo-tests \
	--group needsInstalledILIAS
```

<a name="execute-all-tests"></a>
#### Execute all tests
```
./libs/composer/vendor/bin/phpunit -c ./Services/PHPUnit/config/PhpUnitConfig.xml \
	--colors=always \
	--report-useless-tests \
	--disallow-todo-tests \
	--no-globals-backup
```

<a name="execute-only-installation-unbound-tests"></a>
#### Execute only installation unbound tests
```
./libs/composer/vendor/bin/phpunit -c ./Services/PHPUnit/config/PhpUnitConfig.xml \
	--colors=always \
	--no-globals-backup \
	--report-useless-tests \
	--disallow-todo-tests \
	--exclude-group needsInstalledILIAS
```

<a name="guidelines"></a>
## Guidelines

<a name="foreword"></a>
### Foreword
Maybe the reader is asking him self why this guidelines refers to a book
which writes about JUnit testing in Java 8.

The reason for this is that unit testing in his very nature is the same in
every language. The second reason is that no recently written books are
on the market which describe modern unit testing with PHPUnit.

There is no major reason why this particular book was taken. For the initial author
this book seemed like a good starting point to create the unit test investigation.

<a name="naming"></a>
### Naming
"Rework test names and code to tell stories." [1, Chap. 4]
This means to treat the tests as a specification which tells everything
about the behaviours of the unit under test.

<a name="namespace"></a>
#### Namespace
The test class should always life in the same namespace as the test subject.
For example the `ILIAS\HTTP\Cookies\CookieJarWrapperTest` 
and the implementation `ILIAS\HTTP\Cookies\CookieJarWrapper` are in the same 
namespace.

<a name="class"></a>
#### Class
The filename of the test class should always be named like 
*\<class name of the implementation\>Test.php*. Furthermore, the test class 
should always be named as the class which is tested by the unit test class.
For example the real class is called *Car* the corresponding test class would be 
named *CarTest* and the filename *CarTest.php*.

<a name="unit-test-method"></a>
#### Unit-Test (Method)
The method name must describe what your test is doing. For example the name 
"testSomeBasics" is not really saying much about the test. It is also possible 
that the test is actually testing multiple behaviours because of the 
generic name.

Some good, more descriptive names have the following forms:
- "doingSomeOperationGeneratesSomeResult"       [1, Chap. 4]
- "someResultOccursUnderSomeCondition"          [ebenda]
- "whenDoingSomeBehaviourThenSomeResultOccurs"  [ebenda]

<a name="current-state"></a>
##### Current state
The style how methods are named in the tests are different the most common two
are lower camel case and snake case.

<a name="proposal"></a>
###### Proposal
A more or less recent study has shown that *snake_case* and *CamelCase* provide 
the same readability. However the new class names in ILIAS are *CamelCase* and 
the old ones *lowerCamelCase*. Therefore, the new test methods should be written 
in lower camel case to match the camel case class names in a more consistent way.

<a name="further-improvements"></a>
#### Further improvements
If the test code is still hard to understand following improvements could be made:
- Improve any local variable names.
- Use meaningful constants.
- Split large test into more specific ones to make them more meaningful.
- Move the test clutter into setUp and helper methods.  

<a name="directory-structure"></a>
### Directory structure
<a name="old-parts"></a>
#### Old parts
Each module / service has its own test folder which should have the same 
structure as the classes directory.
For example:

```
WAC/
|
-----> classes/
|        |
|        ------> subdir/
|                  |
|                  -----> SecurePath
|        
-----> test/
         |
         ------> subdir/
                   |
                   -----> SecurePath
```

<a name="collector-ilglobalsuite"></a>
##### Collector ilGlobalSuite
The old part of the global test suite searches the code in the Service and Module
directory. Afterwards it loops over each directory and searches after a nested 
folder named test. The test suite must be named accordingly to be found by the 
global suite.

For module test suites the following pattern is applied:

```
ilModule{Module name}Suite.php
```

For service test suites the following pattern is applied:

```
ilService{Service name}Suite.php
```

<a name="new-parts-src"></a>
#### New parts (/src)
The test source for the new parts of ILIAS are located in the *tests* directory,
which is located in the web root directory of ILIAS. The structure should be the 
same than the *src* directory where the actual implementation lives.

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

<a name="collector-ilglobalsuite-1"></a>
##### Collector ilGlobalSuite
The new tests are loaded from the *tests* directory which is located at the ILIAS 
web root. Each class which extends the *PHPUnit_Framework_TestCase* is loaded as 
test class.

The new collector part must be updated with the PHPUnit 6 migration because 
the *TestCase* class no longer extends the legacy class 
*PHPUnit_Framework_TestCase*. The legacy class *PHPUnit_Framework_TestCase* will 
be entirely removed with PHPUnit 6.

<a name="unit-test-structure"></a>
### Unit-Test structure
Each unit test is usually structured into three parts: arrange, act and assert. 
These are also known as the triple A mnemonic. [1, Chap. 4]

- **Arrange** A proper system state is created by creating objects and 
interacting with them.
- **Act** Invoke the part of the code which should be tested. This is usually 
one method call.
- **Assert** Verify that the executed code behaves as expected. For example the 
verification of a return value or state of any objects involved. It can also 
involve verifications of interactions between objects with the help of mocks.

If there is the need to clean up resources, a fourth step should be added.
This step should hardly ever be required. 
- **After** Ensures the cleanup of the used resources.

All parts should be visually separated by a blank line to highlight the different parts.

<a name="good-tests-are-first"></a>
### Good tests are FIRST
The following chapters about FIRST are based on the content of [1, Chap. 5].

Many problems while unit testing can be avoided by following the FIRST principles.
- **F** ast
- **I** solated
- **R** epeatable
- **S** elf-validating
- **T** imely

Following the FIRST principles, tests are independent and do not rely on other tests to 
be processed. This makes it independent in which order tests are performed. Furthermore, 
the performance of the entire test suite is kept at a level that makes it possible to 
make sensible use of the suite and to run it as often as possible without long waiting times.
The FIRST principles makes tests reliable for developers and always delivers the same result
(no flickering tests). Because the tests themselves verify the result, there are no manual 
steps to verify the tests.

<a name="fast"></a>
#### Fast
Keep the unit tests as fast as possible. They will be run multiple times a day 
to verify the behaviour of all the classes.

The small grade between fast and slow test can be sometimes a bit blurred, 
however if the tested part of the code opens database connection or operates with 
files on a real filesystem the test are always slow.

A lot of slow unit tests are usually an indicator of not so well designed component. 
Because all the code is tightly coupled to the slow parts or operation of a system.   

<a name="isolated"></a>
#### Isolated
Unit-Test focus on small junks of code. So called single *unit*. The more code 
a test involves the more likely it is that the test fails out of unreasonably 
circumstances.

For example the code which is under test might interact with other code which 
connects and interacts with a database. The database itself needs an entire host. 
So, in fact the test depends on that database and the structure as well as the 
data. If the datasource is shared between developers the result of the tests are
no longer reliable because of external changes which are out of control for each 
individual developer.

Good unit tests also don't depend on other unit tests. For example, all unit tests
depend on each other to safe some time creating expensive objects. 
After some time something goes wrong, there will be a massive amount of time 
spend to found the actual cause because everything is failing due to the 
high coupling between each test.

Therefore, unit test must be executable any time in any possible order.

The Single Responsibility Principle (SRP) of the SOLID class design principle 
describes that class should only have one reason to change. This principe is also 
really good for unit tests because if a test can break for more than one
reason. It's the best to split the test in multiple cases. 
"When a focused unit test breaks, it's usually obvious why." [1, Chap. 5]

<a name="repeatable"></a>
#### Repeatable
A repeatable test is one which creates the same results all the time. In order to 
accomplish that, the test must be *isolated*. Each system will interact with elements 
which are not under the control of the developers. For example, if a system has to 
deal with dates or time. That means this test have to deal with additional problem 
which makes writing them more difficult.

In such situations mock objects are used to isolate the class from the outer world. 
If the dependencies are not mockable there is usually something wrong with the 
design of the component.

<a name="self-validating"></a>
#### Self-Validating
Test always assert that something went as expected. Unit test are used to save 
time and not the other way around. If a test result sometimes must be verified 
manual it is not useful at all.

On a larger scale there are continues integration server like team city, 
bamboo or jenkins which are running the unit test if changes on the monitored 
branches are detected. For example ILIAS is automatically tested by 
a team city server.
The server is located at [ci.ilias.de](http://ci.ilias.de/).

<a name="timely"></a>
#### Timely
Unit test can be written at any time for each part of the system. However unit 
tests are better written in a timely fashion. It will immediately pay off if the 
unit test are written along with the production code because odd behaviours can be 
spotted as early as possible which minimized the possibility of expensive bug hunts 
in the future.

There are developers which even develop the unit test before they write 
the actual code, this technique is called test driven development or short (TDD).

<a name="write-correct-tests"></a>
### Write CORRECT tests
The following chapters about CORRECT are based on the content of [1, Chap. 7].

Found bugs are often involve so called boundary conditions. 
These are the edges of the sane-path where many problems appear.
The CORRECT acronym can be used to think of possible problems while 
writing unit tests.
- **C** onformance (Is the value conform with an expected format ?)
- **O** rdering (Is the collection of values ordered or unordered as expected ?)
- **R** ange (Is the value between the expected min and max value ?)
- **R** eference (Does the code reference external things which is not under 
direct control of the code itself ?)
- **E** xistence (Does a value exist or is it null or empty present into 
a collection or not and so on ?)
- **C** ardinality (Are there exactly enough values ?)
- **T** ime (Is everything happening in order ? At the right time and in time ?)

<a name="conformance"></a>
#### Conformance
Many data structures must conform to a certain format. A well known format 
is the email or the ip address.

For example a system has a data import format which consists of head multiple body 
entries and a trailing entry.
Some of the boundary conditions would be:
- Just data
- Just header
- Just trailing entry
- Just a header and data
- Just a header and a trailing entry
- Just data and the trailing entry

Brainstorming about these boundary conditions is helpful to find different 
kind of problems within a system. However, unit tests should not be written 
for cases which will never happen at all. This introduces the question at which 
point are unit tests no longer useful?

For example someone passed an email address into a system because that person 
changed the provider. That email will be passed through countless methods of the 
system. However, if the email address is validated at the entry point of the 
system, the address can be threaded as safe in each underlying method and subsequent 
validations are not needed at all. Therefore, it would be useless to test the 
underlying methods in terms of the format validity because the will never receive 
an invalid email address.

To summarize, it is very important to understand the data flow in the system to 
reduce unnecessary unit tests.

<a name="ordering"></a>
#### Ordering
The order of data or the position of specific data in larger collections are 
often a point were something goes wrong within a system.

<a name="range"></a>
#### Range
The the 64bit integer of PHP has far more capacity than needed. For example the 
age of a dog will never exceed a certain point, however if something went wrong 
the dog gets 2 pow 64 years old.

The excessive usage of primitives is known as a code smell with the name 
*primitive obsession*. One of the primal benefit of PHP is that data can be 
abstracted with its own logic. For example a dog has at most four legs and its 
age is between 1 second and 30 years.  

To abstract these values and test the constraints of the abstraction makes the 
rest of the application more resistant against such errors.

<a name="reference"></a>
#### Reference
When a method is tested the following criteria should be considered:
- What is the method referencing outside of the scope ?
- Which dependencies are there ? 
- If the method depends on objects being in a specific state. 
- Other conditions which must exist for the method.

If assumptions are made about a state, the code should be tested that it is not 
behaving in a wrong way when the assumption is not true. For example a plane has 
to expand the wheels before landing or the plane will most likely be destroyed
after the landing. Therefore, a plane must transition into the right state before 
doing a certain action. This situations must be tested or it is almost certain that 
something goes wrong in the future.

```php
<?php
/**
 * @Test
 */
public function testPlaneLandingWithExpandedWheelsWhichShouldSucceed() {

	//arrange (preconditions)
	$plane = new Plane();
	$plane->start();
	$plane->expandWheels();

	//act
	$plane->land();

	//assert (postconditions)
	$this->assertSame(0, $plane->getSpeed());
}
```

A fun fact of this unit test, if the *expandWheels* method is not behaving well 
in case of failure and just ignores the fact that the wheels can't be used. 
The plane would crash and the speed would also equals zero. Therefore, a green 
unit test is displayed, the plane landed but not as expected! However if the 
class is fully tested some or at least one of the *expandWheels* tests will fail 
and the error is easily spotted and fixed.

<a name="existence"></a>
#### Existence
A potentially large sum of defects could be discovered by asking the question 
"Does something given exist?" For a method which has parameters or accessing fields 
should be thought about if they can be null and how the code should behave 
in such a case.

Sadly, at the time a null value gets into the wrong place it is often not easy 
to tell where the actual problems are. Using exceptions to tell the consumer of 
code what went wrong greatly simplifies the search after a problem.

<a name="cardinality"></a>
#### Cardinality
Often errors arise due to incorrect counting. For example the fencepost which 
can be illustrated with the following question:

"If you build a straight fence 30 meter long with posts spaced 3 meters apart, 
how many posts do you need?"

Of course the answer 10 is wrong because it needs 11 posts for 10 sections. 
Basically the count of sets of values are interesting in
the following cases.
- Zero
- One
- Many (more than one element)

Some developers refer to this as the 0-1-n rule. Zero is important as already 
mentioned in the *Existence* part. To have only one element of its kind is also 
important in some situations. In collections the exact amount of items is normally 
not really important because the code is the same if there are ten or 1 billion 
elements, with some exceptions of course.

As an example the best 10 students should be displayed within a test as a ordered 
list top best to bottom which is still very good. Every time a student takes the 
test the list gets updated. Here are a list of things which should be considered 
corresponding to the cardinality:

- Producing a list without students
- Producing a list with exactly one student
- Producing a list with ten students
- Adding a student to the empty list
- Adding a student to the list which contains only one other student
- Adding a student to the list which contains not ten other students
- Adding a student to the list which contains already ten students

In general test should focus on boundary conditions with 0, 1 and n.

<a name="time"></a>
#### Time
There are several things which should be considered regarding the wall clock time. 
If some portion of code rely on time for example a timestamp. The unit test may 
work 1 or 2 times but will break in the future because time itself is not under 
the control of the test and developer. Therefore, the standard time sources must 
be faked with more controllable ones to make the tests repeatable (FI**R**ST).

An other aspect are timezones which are normally not a problem in PHP, 
but there are some edge cases with switching hours which should
be considered. A short example from 
[stackoverflow](https://stackoverflow.com/a/19004000) illustrates the error in 
a simple way.

| Europe/Warsaw time  |   offset |  UTC                |    php2utc conversion |  php offset  |
| ------------------- | -------- | ------------------- | --------------------- | ------------ |
| 2013-10-27 01:00:00 |    +2    | 2013-10-26 23:00:00 |   2013-10-26 23:00:00 | +2           |
| 2013-10-27 01:30:00 |    +2    | 2013-10-26 23:30:00 |   2013-10-26 23:30:00 | +2           |
| 2013-10-27 02:00:00 |    +2    | 2013-10-27 00:00:00 |   2013-10-27 01:00:00 | **+1**       |
| 2013-10-27 02:30:00 |    +2    | 2013-10-27 00:30:00 |   2013-10-27 01:30:00 | **+1**       |
| 2013-10-27 02:59:00 |    +2    | 2013-10-27 00:59:00 |   2013-10-27 01:59:00 | **+1**       |

3am -> 2am .....................................summer time changes to standard(winter) time @3am 
we subtract 1h so 3am becomes 2am

| Europe/Warsaw time  |   offset |  UTC                  |    php2utc conversion  |  php offset  |
| ------------------- | -------- | --------------------- | ---------------------- | ------------ |
| 2013-10-27 02:00:00 |    +1    | 2013-10-27 01:00:00   | 2013-10-27 01:00:00    | +1           |
| 2013-10-27 02:30:00 |    +1    | 2013-10-27 01:30:00   | 2013-10-27 01:30:00    | +1           |
| 2013-10-27 03:00:00 |    +1    | 2013-10-27 02:00:00   | 2013-10-27 02:00:00    | +1           |
| 2013-10-27 03:30:00 |    +1    | 2013-10-27 02:30:00   | 2013-10-27 02:30:00    | +1           |


<a name="test-examples"></a>
## Test Examples
This test section will show good and bad unit tests in ILIAS.

<a name="negative-examples"></a>
### Negative Examples
All the examples are shown in this section have some different problems which 
will be explained in detail. However it is really important to understand that 
only the code is wrong. The statements made are never about a developers skills 
and how they are now in the present. "It's just code!"

<a name="wrong-location-and-bloated"></a>
#### Wrong Location and Bloated
The test shown below is the only test of the course module. However the code 
actually test the *ilMemberAgreement* class which is part of the Membership 
service.

In addition this test tests more than one thing:
- Agreement of a user to some policy
- Removal of a user from the acceptance list of a policy
- Test of a second static method which makes the same
- Fetch the agreement with the object id 8888.
- Removal of a user with the user id 9999, however the result is not evaluated.

The test requires also an initialised ILIAS instance because the values are 
written to the db which could lead to an unpredictable result if an other 
test maybe an other *ilMemberAgreement* test class manipulates the database.

Finally in this case it should be considered to remove the test entirely because 
it is not of real use. In addition this test is skipped on the CI server which 
leads to the final question if test is ever executed.


```php
<?php
	/**
	 * Test member agreement
	 * @group IL_Init
	 */
	public function testMemberAgreement()
	{
		include_once 'Services/Membership/classes/class.ilMemberAgreement.php';

		global $ilDB;


		$agree = new ilMemberAgreement(9999,8888);
		$agree->read();
		$agree->setAccepted(true);
		$agree->save();

		$agree = new ilMemberAgreement(9999,8888);
		$agree->read();
		$sta = $agree->isAccepted();
		$this->assertEquals($sta,true);
		$agree->delete();

		$agree = new ilMemberAgreement(9999,8888);
		$agree->read();
		$sta = $agree->isAccepted();
		$this->assertEquals($sta,false);

		$sta = ilMemberAgreement::_hasAccepted(9999,8888);
		$this->assertEquals($sta,false);

		$agree = new ilMemberAgreement(9999,8888);
		$agree->read();
		$agree->setAccepted(true);
		$agree->save();

		$sta = ilMemberAgreement::_hasAgreementsByObjId(8888);
		$this->assertEquals($sta,true);

		$sta = ilMemberAgreement::_hasAgreements();
		$this->assertEquals($sta,true);

		ilMemberAgreement::_deleteByUser(9999);
	}
```

<a name="testing-php-behaviour--wrong-class"></a>
#### Testing PHP behaviour / Wrong class
The Button test contains tests which belongs into another class and testing PHP 
behaviour. In the case of the *test_implements_factory_interface* test the only 
thing which is tested is the *Factory* class which is living in the 
"\ILIAS\UI\Implementation\Component\Button\" namespace. In addition the only
tested logic is the instantiation of the concrete button implementations.

Furthermore this test checks 4 different things:
- Standard button implements Standard interface ?
- Primary button implements Primary interface ?
- Close button implements Close interface ?
- Shy button implements Shy interface ?

```php
<?php
class ButtonTest extends ILIAS_UI_TestBase {

	public function getButtonFactory() {
		return new \ILIAS\UI\Implementation\Component\Button\Factory();
	}

	public function test_implements_factory_interface() {
		$f = $this->getButtonFactory();

		$this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Factory", $f);
		$this->assertInstanceOf
			( "ILIAS\\UI\\Component\\Button\\Standard"
			, $f->standard("label", "http://www.ilias.de")
			);
		$this->assertInstanceOf
			( "ILIAS\\UI\\Component\\Button\\Primary"
			, $f->primary("label", "http://www.ilias.de")
			);
		$this->assertInstanceOf
			( "ILIAS\\UI\\Component\\Button\\Close"
			, $f->close()
			);
		$this->assertInstanceOf
			( "ILIAS\\UI\\Component\\Button\\Shy"
			, $f->shy("label", "http://www.ilias.de")
			);
	}
}
```

<a name="example-solution"></a>
##### Example solution
The proposed solution would be to remove the test entirely because they test 
if the new keyword works. However, if the author wishes to keep the tests. 
They could be moved into the ButtonFactoryTest class and split up in smaller 
more precise tests. As a result the developer which runs the test is now able 
to see which part of the factory failed.

```php
<?php
use \ILIAS\UI\Implementation\Component\Button\Factory;
use \ILIAS\UI\Component\Button\Standard;
use \ILIAS\UI\Component\Button\Primary;
use \ILIAS\UI\Component\Button\Shy;
use \ILIAS\UI\Component\Button\Close;

class ButtonFactoryTest extends AbstractFactoryTest {

	private $subject;

	public function setUp() {
		parent::setUp();

		$this->subject = new Factory();
	}

	// ... other parts of the test class ...

	/**
	 * @test
	 * @small
	 */
	public function testCreationOfStandardButton() {
		$label = "standard";
		$url = "http://www.ilias.de";
		
		$result = $this->subject->standard($label, $url);
		
		$this->assertInstanceOf(Standard::class, $result);
	}

	/**
	 * @test
	 * @small
	 */
	public function testCreationOfPrimaryButton() {
		$label = "primary";
		$url = "http://www.ilias.de";
		
		$result = $this->subject->primary($label, $url);
		
		$this->assertInstanceOf(Primary::class, $result);
	}

	// ... other button type tests ...
}
```


<a name="useless-test--generic-naming"></a>
#### Useless test / Generic naming
Unit tests should always have an assertion of the result, because of that 
PHPUnit 6 started to mark such tests as useless. Useless test are always 
threaded as failed. Furthermore, the test name *test_button_label_or_glyph_only*
is not really telling whats exactly tested.

Another aspect of the whole *ButtonTest* class is that a factory is used to 
create concrete instances of the Buttons. But neither the factory nor the 
concrete subclasses of the Button class is a test subject here only the Button 
class itself. Of course to test all button instances is not really effective. 
However, the button can also be created with the help of mockery which subclasses 
the button dynamically within the tests. This allows to test the Button class in 
a dedicated way. If logic is added to one of the specific implementations only
this part has to be tested within the test class of the specific button subclass 
for example the primary button.


```php
<?php
class ButtonTest extends ILIAS_UI_TestBase {

	public function getButtonFactory() {
			return new \ILIAS\UI\Implementation\Component\Button\Factory();
	}

	public function button_type_provider() {
			return array
				( array("standard")
				, array("primary")
				, array("shy")
				, array("tag")
				);
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_button_label_or_glyph_only($factory_method) {
		$f = $this->getButtonFactory();
		try {
			$f->$factory_method($this, "http://www.ilias.de");
			$this->assertFalse("This should not happen");
		}
		catch (\InvalidArgumentException $e) {}
	}
}
```

<a name="example-solution-1"></a>
##### Example solution
The proposed solution of this example would be to write a dedicated test class
for the Button class it self, as shown below.

First the *Factory* was removed because this class is meant for the *Button* 
class. Second the data provider has been removed due to the fact that the 
button itself will be tested and not the children of the button. Third the try 
catch was replaced with the phpunit construct which is designed to test 
exception occurrence. Finally the *Button* class has been partial mocked with 
a full method delegation which means that the test code is directly talking to 
the real button implementation. The second parameter is a list of construct 
argument for the button which remained unchanged to get same result as before.

```php
<?php

use ILIAS\UI\Implementation\Component\Button\Button;  

class ButtonTest extends ILIAS_UI_TestBase {

	use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

	/**
	 * @test
	 * @small
	 */
	public function testButtonCreationWithInvalidArgumentWhichShouldFail() {

		$constructorArgs = [$this, 'http://www.ilias.de'];

		$this->expectException(InvalidArgumentException::class);

		//create a partial mock because the button is abstract
		Mockery::mock(Button::class . '[]', $constructorArgs);
	}
}
```

<a name="test-regression"></a>
#### Test regression
Many tests in ILIAS were not updated with the production source code. For example 
some of the RBAC classes are gone but still tested.

```php
<?php
class ilRBACTest extends PHPUnit_Framework_TestCase {
		/**
		 * @group IL_Init
		 */
		public function testCache()
		{
			//the ilAccessHandler does not exist anymore
			include_once './Services/AccessControl/classes/class.ilAccessHandler.php';

			//ilAccessHandler is an interface located in './Services/AccessControl/interfaces/interface.ilAccessHandler.php'
			$handler = new ilAccessHandler();
			$handler->setResults(array(1,2,3));
			$handler->storeCache();
			$handler->readCache();
			$res = $handler->getResults();

			$this->assertEquals(array(1,2,3),$res);
		}

		//more tests ...
}
```

Test like this should be removed because they have a negative impact on the 
global test suite due to the fact that this tests require a full bootstrapped 
ILIAS. Furthermore, all RBAC are in the wrong test class which should be moved as 
described in chapter (guidelines -> naming -> class).

<a name="test-examples-1"></a>
### Test Examples

<a name="template"></a>
#### Template
This is just a normal template how a basic unit test class could look like 
without any additions.  

```php
<?php
use\PHPUnit\Framework\TestCase;
use\Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;


class TemplateUnitTest extends TestCase {
	use MockeryPHPUnitIntegration;

	/**
	 * @inheritDoc
	 */
	protected function setUp()
	{
		parent::setUp();

		//prepare your stuff which is needed all the time here
	}

	//create your unit test here

}
```
<a name="normal-test"></a>
#### Normal test
This example is from the Filesystem service which tests the
file access of the implementation which is based upon fly system.
The example should only illustrate a possible usage of template above.
Comments were added in comparison to the original to show the triple A structure 
explained in an earlier chapter.

```php
<?php
use ILIAS\Data\DataSize;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\IOException;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
// test dependencies like mockery ...

class FlySystemFileAccessTest extends TestCase {

	use MockeryPHPUnitIntegration;

	/**
	 * @var FlySystemFileAccess $subject
	 */
	private $subject;
	/**
	 * @var Filesystem | MockInterface
	 */
	private $filesystemMock;


	/**
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();
		date_default_timezone_set('Africa/Lagos');
		$this->filesystemMock = Mockery::mock(FilesystemInterface::class);
		$this->subject = new FlySystemFileAccess($this->filesystemMock);
	}


	/**
	 * @Test
	 * @small
	 */
	public function testReadWhichShouldSucceed() {
		//Arrange
		$fileContent = 'Test file content.';
		$this->filesystemMock->shouldReceive('read')
			->once()
			->andReturn($fileContent);

		//Act
		$actualContent = $this->subject->read('/path/to/your/file');

		//Assert
		$this->assertSame($fileContent, $actualContent);
	}
}
```

<a name="fat-legacy-class-with-parent"></a>
#### Fat legacy class with parent
Fat legacy classes especially in ILIAS extend each other to do some work.
For example the ilObject2 has a create method which depends on the create method 
of the ilObject. The ilObject create method writes the data to the database which 
is not desirable in unit tests of ilObject2. Therefore ilObject should be replaced 
with a stub and ilObject2 should operate on that stub which has proper expectation 
in place to test the behaviour.

This requires an autoload "hack" which loads the base class mock (ilObject) 
before the partial mock ilObject2 is created. Important is that all expectation 
are set on the ilObject mock before the ilObject2 instance is created otherwise 
the expectations have no effect.

```php
<?php
require_once './libs/composer/vendor/autoload.php';

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Class ilObject2Test
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ilObject2Test extends TestCase {

	use MockeryPHPUnitIntegration;
	/**
	 * @var ilObject2 $subject
	 */
	private $subject;
	/**
	 * @var ilObject | \Mockery\MockInterface $subjectParent
	 */
	private $subjectParent;

	/**
	 * @test
	 * @small
	 */
	public function testCreateWithoutCloneModeWhichShouldSucceed() {

		//expectations must be set before the child class is loaded
		$expectedId = 5;
		$this->subjectParent = Mockery::mock('overload:' . ilObject::class);
		$this->subjectParent->shouldReceive('create')
			->once()
			->andReturn($expectedId);
		$this->subject = Mockery::mock(ilObject2::class . '[]', []);

		$result = $this->subject->create();

		$this->assertSame($expectedId, $result);
	}

	//test other cases ...
}
```   
In cases as shown above, each test has to be run in a separate PHP process because 
of the specially loaded classes. This can be done with the 
*@runTestsInSeparateProcesses* annotation.

<a name="partial-mocks"></a>
##### Partial mocks
On the line with the suspicious looking string concatenation of the class name 
with empty brackets:

```
$this->subject = Mockery::mock(ilObject2::class . '[]', []);
```

This is directly creating a partial mock without any mocked methods. That is
useful to test abstract classes without an actual implementation. The second 
parameter is used to pass additional constructor arguments.

<a name="class-overload"></a>
##### Class overload
The string concatenation with overload string and the classname tells mockery 
to replace the class. This is possible with an autoloader trick which loads the 
mock class instead of the real implementation.

```
$this->subjectParent = Mockery::mock('overload:' . ilObject::class);
```

<a name="mock-static-calls"></a>
#### Mock static calls
In some situation it is necessary to mock an entire class due to static method 
access or a new call. Mockery provides a convenient way to load a class alias 
which replaces the class entirely. The following example illustrates mocking of 
static method calls to test the Stream class within the filesystem service.

The concrete problem within the filesystem service was that this service has to 
interact with php built in functions to manipulate the underlying resource of 
the stream. PHP has no functionality to autoload functions, which makes them 
difficult to mock. The solution of the author was to wrap the PHP functions 
with a helper class which can be replaced.

```php
<?php
	/**
	 * @Test
	 * @small
	 */
	public function testReadWithFailingFreadCallWhichShouldFail() {

		//Arrange
		$content = 'awesome content stream';
		$mode = 'r';
		$length = 3;
		$resource = $this->createResource($content, $mode);

		$subject = new Stream($resource);

		//load mock class
		$functionMock = Mockery::mock('alias:' . PHPStreamFunctions::class);

		$functionMock->shouldReceive('fread')
			->once()
			->withArgs([$resource, $length])
			->andReturn(false);

		$functionMock->shouldReceive('fclose')
			->once()
			->with($resource);

		//set the exception assertion
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Unable to read from stream');

		//act
		$subject->read($length);
	}
```  
The prefix *alias* before the class name tells mockery to load an empty class 
with the same name. Afterwards exceptions are placed on the empty mock alias.
It is important that these tests must run in separate PHP processes because PHP 
has no functionality to unload classes. Therefore, a redefinition of a class would 
lead to a fatal error. PHPUnit has a build in function which does that. Every 
class which is annotated with *@runTestsInSeparateProcesses* will spawn a new PHP 
process for each unit test.

<a name="fluent-interfaces"></a>
#### Fluent interfaces
Fluent interfaces or long call chains might be easy to read and leverage but 
kind of hard to test because a lot of expectation have to be set on many mocks 
which are not really interesting for the test at all. PHPUnit has a feature which 
simplifies this process a lot.

For example file upload test have to mock the http service. The http service has 
methods like request and response and subsequent methods to interact with them. 
To avoid to create unneeded expectations on the http mock the request mock is 
generated on the fly with the expectation set on it. 
The pattern is *{method name}->{method name}* the last method gets the actual 
expectations defined after the *shouldReceive* call.

```php
<?php
//class FileUploadImplTest
	/**
	 * @Test
	 * @small
	 */
	public function testRegisterWithProcessedFilesWhichShouldFail() {
		$processorMock = \Mockery::mock(PreProcessor::class);
		//create a request mock on the fly and set an expectation on it with the arrows (->).
		$this->globalHttpStateMock->shouldReceive('request->getUploadedFiles')
			->once()
			->andReturn([]);

		$this->expectException(IllegalStateException::class);
		$this->expectExceptionMessage('Can not register processor after the upload was processed.');

		$this->subject->process();
		$this->subject->register($processorMock);
	}
```

<a name="rise-testability"></a>
### Rise testability
<a name="solid"></a>
#### SOLID
"Robert C. Martin gathered five principles for object-oriented class 
design" [1, Chap. 9], for building maintainable object oriented system.

<a name="single-responsibility-principle-srp"></a>
##### Single Responsibility Principle (SRP)
Classes should have one reason to change. Keep the classes small 
and single-purposed. [4]

<a name="open-closed-principle-ocp"></a>
##### Open-Closed Principle (OCP)
Classes should be design to be open for extension but closed for modification. 
The need to make changes to existing classes should be minimized. [4]

<a name="liskov-substitution-principle-lsp"></a>
##### Liskov Substitution Principle (LSP)
Subtypes should be substitutable for their base types. From a clients perspective 
overriding methods should not break functionality. [4]

<a name="interface-segregation-principle-isp"></a>
##### Interface Segregation Principle (ISP)
Clients should not be forced to depend on methods they don't use. Split a larger 
interface into a number of smaller interfaces. [4]

<a name="dependency-inversion-principal-dip"></a>
##### Dependency Inversion Principal (DIP)
High-level modules should not depend on low-level modules; both should depend on 
abstractions. Abstractions should not depend on details; details should depend on 
abstractions. [4]

<a name="a-solid-way-to-use-the-dic"></a>
#### A SOLID way to use the DIC
A good way to improve the testability of new and old classes are the inversion of 
the dependencies. For example the classic way to use another class is:  

```php
<?php
class Car {

	private $breaks;

	public function __construct() {
		$this->breaks = new StandardBreaks();
	}

	public function stop() { /* use the breaks ... */}
}
```
To verify that a car is able to stop, the breaks have to be replaced to verify 
the behaviour because the stop function has no return value. However there is 
no way to replace the hardwired dependency to the breaks except to load a class 
with the same name before actual class is loaded which could be considered a hack.

In order to increase the testability the hardwired dependency has to be 
inverted (DIP).
- First a Breaks interface is created for the StandardBreaks.
- The Breaks will be passed to the Car at construction time. 
(The factories from Audi etc. do the same.)

After these changes the car don't care about the actual implementation because 
it only depends on the breaks abstraction. This changes allows the car developers 
to finally test the class because the breaks can be exchanged at test time 
without hassle.

```php
<?php
class Car {

	private $breaks;

	public function __construct(Breaks $breaks) {
		$this->breaks = $breaks;
	}

	public function stop() { /* use the breaks ... */}
}
```

The corresponding test class would look like this:

```php
<?php
use \PHPUnit\Framework\TestCase;
use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class CarTest extends TestCase {

	use MockeryPHPUnitIntegration;

	private $subject, $breaks;
	/**
	 * @inheritDoc
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->breaks = Mockery::mock(Breaks::class);
		$this->subject = new Car($this->breaks);
	}

	/**
	 * @test
	 */
	public function testStopCarWhileDriving() { /* use the breaks mock to verify behaviour ... */}
}
```

This principle could improve the current situation in ILIAS a lot because many 
classes are hardwired and therefore not testable at all. In almost any part in 
the old code structures in ILIAS serves the DIC as nothing more as a service locator 
which is technically the same than the globals used before. The usage of service 
locators are even discouraged by the 
[PHP-FIG](http://www.php-fig.org/psr/psr-11/meta/#4-recommended-usage-container-psr-and-the-service-locator).   

Services in the /src directory use the DIP to break dependencies and improve the 
testability and maintainability.

<a name="distinction-between-unit-and-integration-tests"></a>
#### Distinction between unit and integration tests
In ILIAS there are a lot of test which test a bunch of classes together which is 
useful in its own but these are no unit tests. Tests which combine multiple unit 
tested classes to test their behaviour when they actually work together are called 
integration tests.


<a name="continues-integration"></a>
## Continues integration
The ILIAS continues integration server provides a clean supported environment to 
run different tests against the latest ILIAS version.

<a name="test-suites"></a>
### Test suites
Currently there are three different test suites:
- ILIAS test suite which runs all ILIAS unbound unit tests
- ILIAS performance tests
- ILIAS static code analysis (done with Dicto.php)

The ILIAS unit test suite runs agains the following PHP versions:
- 5.6
- 7.0
- 7.1

<a name="benefits"></a>
### Benefits
There are several benefits, first the tests are running in clean defined 
environment carefully monitored and maintained by the community, second all 
developer are informed about the actual condition of ILIAS which allows to react 
fast to emerging problems.

<a name="current-problems"></a>
### Current problems
The CI server uses different configuration which could lead to confusing results 
for the developer which checked-in the code.

<a name="external-phpunit-version"></a>
#### External PHPUnit version
The CI server uses a external php unit version which is not updated by composer
which could lead to different results.

The current command of the php 7 worker looks like this:

```
/usr/bin/php7.0 /usr/local/bin/phpunit-5.7.20.phar /
	--log-junit %system.teamcity.build.tempDir%/phpunit-log-7.0.x.xml /
	-c  %system.teamcity.build.workingDir%/Services/PHPUnit/config/PhpUnitConfig.xml
```

But should look like this to leverage the composer version of php unit:

```
/usr/bin/php7.0 ./libs/composer/vendor/bin/phpunit /
	--log-junit %system.teamcity.build.tempDir%/phpunit-log-7.0.x.xml /
	-c  %system.teamcity.build.workingDir%/Services/PHPUnit/config/PhpUnitConfig.xml
```

<a name="risky-prebuild-step"></a>
#### Risky prebuild step
The CI server runs commands before the test suite to ensure a known state of the
environment.

The prebuild step looks like this:

```
cd %system.teamcity.build.workingDir%/libs/composer && rm -rf vendor/geshi/ && /usr/bin/composer update --prefer-dist
```

Composer has a special file called composer.lock which ensures that the 
dependencies are the same on each host. This lock file is red at install time to 
download all dependencies. However the composer update command ignores and overwrites 
the lock file to update the dependencies to the newest version. That could lead 
to unpredictable results because the dependencies may be newer than the ones the 
developer used at check-in time.

The command should look like this:

```
cd %system.teamcity.build.workingDir%/libs/composer && rm -rf vendor/geshi/ && /usr/bin/composer install
```

<a name="external-documentation"></a>
## External Documentation
PHP Unit Documentation: <https://phpunit.de/manual/5.7/en/index.html>

Mockery Documentation: <http://docs.mockery.io/en/latest/>

ILIAS CI Server: <https://ci.ilias.de/>

xDebug Documentation: <https://xdebug.org/docs/>

Dicto.php: <https://github.com/lechimp-p/dicto.php>

<a name="faq"></a>
## FAQ
<a name="what-needs-to-be-tested-"></a>
### What needs to be tested ?
Basically behaviour has to be tested which is the smallest testable unit 
of a class. A class should be tested within an isolated environment without 
external dependencies like a database, filesystem or network. New or refactored 
code should be unit tested. If something is updated the unit test must be updated 
as well.

<a name="how-is-a-local-test-environment-set-up-"></a>
### How is a local test environment set up ?
Please refer to the chapter "Setup test environment".

<a name="how-do-i-start-the-ilias-unit-tests-in-phpstorm-"></a>
### How do I start the ILIAS unit tests in PHPStorm ?
Please refer to the chapter "Run tests with PHPStorm".

<a name="how-do-i-start-the-ilias-unit-tests-in-cli-"></a>
### How do I start the ILIAS unit tests in CLI ?
Please refer to the chapter "Run tests with CLI".

<a name="where-do-i-put-my-unit-tests-"></a>
### Where do I put my unit tests ?
Please refer to the chapter "Directory structure" within the guidelines.

<a name="what-is-the-ilias-ci-server-and-how-can-i-benefit-from-it-in-terms-of-unit-tests-"></a>
### What is the ILIAS CI-Server and how can I benefit from it in terms of unit tests ?
Please refer to the chapter "Continues integration".

<a name="what-do-i-need-to-consider-concerning-unit-tests-before-pushing-code-to-the-ilias-repo-"></a>
### What do I need to consider concerning unit tests before pushing code to the ILIAS repo ?
First of all the test should comply with the described guidelines.
Afterwards it should be verified that the test actually test behaviour and not 
getter, setter or similar code. Furthermore the checked-in unit tests should not 
depend on external resources like database, real filesystem access, network access.
If the tests still need ILIAS, consider to remove them because these are likely 
no unit tests.

The unit tests must be green before pushing the code to the ILIAS repo. Another 
important part is to add and update the unit tests as the production code evolves.

<a name="glossary"></a>
## Glossary
| Term          | Description   |
| :-----------: | ------------- |
| Stub          | A stub is the same as a mock, however the stub only returns preset values. In contrast, the mock object requires expectations to verify the actual behaviour. [5] |
| Test Coverage | Test coverage indicates which part of the code has been run for a specific test suite. A high test coverage indicates that most of the code runs most likely as expected. [6] |
    
<a name="sources"></a>
## Sources
[1] Langr, Jeff (2015): Pragmatic Unit Testing in Java 8 with JUnit.
[2] Pádraic Brady, Dave Marshall and contributors, (11.05.2017): Mockery, <http://docs.mockery.io/en/latest/>
[3] Pádraic Brady, Dave Marshall and contributors, (11.05.2017): Mock Objects, <http://docs.mockery.io/en/latest/>
[4] Martin Robin C., (17.07.2014): The Principles of OOD, <http://butunclebob.com/ArticleS.UncleBob.PrinciplesOfOod>
[5] Pádraic Brady, Dave Marshall and contributors, (11.05.2017): Creating Test Doubles, <http://docs.mockery.io/en/latest/reference/creating_test_doubles.html>
[6] (30.11.2017): Code coverage, <https://en.wikipedia.org/wiki/Code_coverage>
[7] Roy Osherove, (15.01.2018): Unit Test - Definition, <http://artofunittesting.com/definition-of-a-unit-test/>
[8] Bruno Skvorc, (31.07.2017): Re-Introducing PHPUnit – Getting Started with TDD in PHP, <https://www.sitepoint.com/re-introducing-phpunit-getting-started-tdd-php/>
