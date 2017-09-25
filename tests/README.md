# ILIAS Unit Testing investigation
## Definition
Unit testing is the process of testing the smallest testable part of an application, called units.
Units are usually one behaviour of a class or one logical concept of a method.

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
### Setup composer
Composer is a dependency manager for PHP packages / libraries.
#### Linux
- Download the latest composer version [here](https://getcomposer.org/download/).
- Run the command to install the composer command globally
```bash
mv composer.phar /usr/local/bin/composer
```
- Check that the path */usr/local/bin* is in your *PATH*.
- Verify your installation with the following command
```bash
composer -V
```
Command output should look like this, if the installation was successful. 
```text
Composer version 1.5.1 2017-08-09 16:07:22
```

#### macOS
- Download the latest composer version [here](https://getcomposer.org/download/).
- Verify that the path */usr/local/bin* exists otherwise created it with the command
```bash
mkdir -p /usr/local/bin
```
- Run the command to install the composer command globally
```bash
mv composer.phar /usr/local/bin/composer
```
- Check that the path */usr/local/bin* is in your *PATH*.
- Verify your installation with the following command
```bash
composer -V
```
Command output should look like this, if the installation was successful.  
```text
Composer version 1.5.1 2017-08-09 16:07:22
```

#### Windows
##### Manual
- Change into a directory which is in your *PATH*
- Download the composer phar [here](https://getcomposer.org/download/).
- Create a composer .bat alongside the composer.phar with the following command
```bash
echo @php "%~dp0composer.phar" %*>composer.bat
```
- Open a **new** terminal to verify the composer installation with the following command
```bash
composer -V
```
Command output should look like this, if the installation was successful. 
```text
Composer version 1.5.1 2017-08-09 16:07:22
```


##### Automatic (recommended)
- Download the composer setup [here](https://getcomposer.org/Composer-Setup.exe).
- Run the setup and follow the displayed steps
- Open a **new** terminal to verify the composer installation with the following command
```bash
composer -V
```
Command output should look like this, if the installation was successful. 
```text
Composer version 1.5.1 2017-08-09 16:07:22
```

### Setup ILIAS
Install ILIAS on your favourite operation system with the provided [installation guide](docs/configuration/install.md).
Make sure that all dev and prod dependencies are installed with composer.
```bash
composer show
```
Verify that the phpunit packages are showing up in the displayed list.
If the packages are not listed run the following command to install them.
```bash
composer install
```

### Setup xDebug (test coverage)
XDebug is only used for the generation of the test coverage.
If no test coverage is required please skip this installation step because xDebug will slow down your
unit test quite a bit.

#### Setup for Ubuntu
- Run the following command or use your own package manager to install the following package.
```bash
# trusty
sudo apt-get install php5-xdebug

# xenial
sudo apt-get install php-xdebug
```
#### Setup for macOS
- Install the following package with brew or your own favourite package manager.
```bash
# brew install homebrew/php/<php-version>-xdebug
brew install homebrew/php/php71-xdebug
```

#### Windows
- Open the browser and navigate to the [xdebug installation wizard](https://xdebug.org/wizard.php).
- Copy the output of the following command into the wizard.
```bash
# Output is redirected to the clipboard. 
php -i | clip
```
The xdebug wizard will provide a link to the correct xdebug binary and further installation steps.

#### Configure
Append the following to your php.ini or xdebug.ini and complete / enhance the config as needed.
```ini
[Xdebug]
xdebug.remote_connect_back = 1
xdebug.idekey = PHPSTORM
xdebug.profiler_output_dir = {Writeable output dir for profiler}
xdebug.remote_log = /var/log/xdebug.log
xdebug.profiler_enable_trigger = 1
xdebug.remote_enable = 1
xdebug.remote_port = 9000
```

Please make sure to add the xdebug extension to one of your php.ini files. The entry should look like this. 
However, the path may be different depending on the setup. 
```bash
zend_extension="/usr/local/php/modules/xdebug.so"
```
Please ignore all prompts to add "extension=xdebug.so" to php.ini because this will cause problems.

//Show how to setup the tools described in the previous chapter

### Run tests with PHPStorm

### Run tests with CLI
- Change into the ILIAS web root

#### Execute ILIAS installation bound tests
```bash
./libs/composer/vendor/bin/phpunit ./Services/PHPUnit/test/ilGlobalSuite.php \
	--colors=always \
	--no-globals-backup \
	--report-useless-tests \
	--disallow-todo-tests \
	--group needsInstalledILIAS
``` 

####Execute all tests
```bash
./libs/composer/vendor/bin/phpunit ./Services/PHPUnit/test/ilGlobalSuite.php \
	--colors=always \
	--report-useless-tests \
	--disallow-todo-tests \
	--no-globals-backup
``` 

####Execute only installation unbound tests
```bash
./libs/composer/vendor/bin/phpunit ./Services/PHPUnit/test/ilGlobalSuite.php \
	--colors=always \
	--no-globals-backup \
	--report-useless-tests \
	--disallow-todo-tests \
	--exclude-group needsInstalledILIAS
``` 

## Guidelines
// Right-BICEP and CORRECT
// what should be tested and how

### Naming
"Rework test names and code to tell stories."

#### Class
The filename of the test class should always be named like *\<class name of the implementation\>Test.php*. 
Furthermore, the test class should always be named as the class which is tested by the unit test class.
For example the real class is called *Car* the corresponding test class would be named *CarTest* and the filename
 *CarTest.php*.
 
#### Unit-Test (Method)
The method name must describe what your test is doing. For example the name "testSomeBasics" is not really saying
much about the test. It is also possible that the test is actually testing multiple behaviours because of the
generic name.

Some good, more descriptive names have the following forms:
- doingSomeOperationGeneratesSomeResult
- someResultOccursUnderSomeCondition
- whenDoingSomeBehaviourThenSomeResultOccurs

#### Further improvements
If the test code is still hard to understand following improvements could be made after
the test have more meaningful names.
- Improve any local variable names
- Use meaningful constants
- split large test into more specific ones to make them more meaningful.
- Move the test clutter into setUp and helper methods.  

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

### Unit-Test structure
Each unit test is usually structured into three parts: arrange, act and assert. This are also known as
the triple-A.

- **Arrange** A proper system state is created by creating objects and interacting with them.
- **Act** Invoke the part of the code which should be tested. This is usually one method call.
- **Assert** Verify that the executed code behaves as expected. For example the verification of a return value
or state of any objects involved. It can also involve verifications of interactions between objects with the 
help of mocks.

If there is the need to clean up resources, a fourth step should be added.
- **After** Ensures the cleanup of the used resources.

All parts should be visually separated by a blank line to highlight the different parts.

Example ILIAS Filesystem service (LegacyPathHelperTest):
```php
<?php
	/**
	 * @Test
	 * @small
	 */
	public function testCreateRelativePathWithWebTargetWhichShouldSucceed() {
		$expectedPath = 'testtarget/subdir';
		$target = $this->webPath . '/' . $expectedPath;

		$result = LegacyPathHelper::createRelativePath($target);
		
		$this->assertEquals($expectedPath, $result);
	}
```

### Good tests are FIRST
Many problems while unit testing can be avoided by following the FIRST principles.
- **F**ast
- **I**solated
- **R**epeatable
- **S**elf-validating
- **T**imely

#### Fast
Keep the unit tests as fast as possible. They will be run multiple times a day to verify the
behaviour of all the classes. 

The small grade between fast and slow test can be sometimes a bit blurred, however if the tested part 
of the code opens database connection or operates with files on a real filesystem the test are always slow.

A lot of slow unit tests are usually an indicator of not so well designed component. Because all the code 
is tightly coupled to the slow parts or operation of a system.   

#### Isolated
Unit-Test focus on small junks of code. So called single *unit*. The more code a test involves the
more likely it is that the test fails out of unreasonably circumstances.

For example the code which is under test might interact with other code which connects and interacts with
a database. The database it self needs an entire host. So, in fact the test depends on that database and the
structure as well as the data. If the datasource is shared between developers the result of the tests are 
no longer reliable because of external changes which are out of control for each individual developer.

Good unit tests also don't depend on other unit tests. For example, all unit tests depend on each other to 
safe some time creating expensive objects. After some time something goes wrong, there will be a 
massive amount of time spend to found the actual cause because everything is failing 
due to the high coupling between each test.

Therefore, unit test must be executable any time in any possible order.

The single Responsibility Principle (SRP) of the SOLID class design principle describes that class should be 
small and only serve one purpose. This principe is also really good for unit tests because if a test can for more than one
reason. It's the best to split the test in multiple cases. If a focused test breaks it is normally obvious why.

#### Repeatable
A repeatable test is one which creates the same results all the time. In order to accomplish that, the test must be *isolated*.
Each system will interact with elements which are not under the control of the developers. For example, if a system has to deal with
dates or time. That means this test have to deal with additional problem which makes writing them more difficult.

In such situations mock objects are used to isolate the class from the outer world. If the dependencies are not mockable there is
usually something wrong with the design of the component. 

#### Self-Validating
Test always assert that something went as expected. Unit test are used to save time and not the other way around.
If a test result sometimes must be verified manual it is not useful at all.

On a larger scale there are continues integration server like team city, bamboo or jenkins which are running the unit test if changes on the monitored
branches are detected. For example ILIAS is automatically tested by a team city server. 
The server is located at [ci.ilias.de](http://ci.ilias.de/).

#### Timely
Unit test can be written at any time for each part of the system. However unit tests are better written in a timely fashion.
It will immediately pay off if the unit test are written along with the production code because odd behaviours can be spotted as
early as possible which minimized the possibility of expensive bug hunts in the future.

There are developers which even develop the unit test before they write the actual code this technique is called test driven development or short
(TDD).

### Right-BICEP
### Write CORRECT tests
Found bugs are often involve so called boundary conditions. These are the edges of the sane-path where many problems appear.
The CORRECT acronym can be used to think of possible problems while writing unit tests.
- **C**onformance (Is the value conform with an expected format ?)
- **O**rdering (Is the collection of values ordered or unordered as expected ?)
- **R**ange (Is the value between the expected min and max value ?)
- **R**eference (Does the code reference external things which is not under direct control of the code it self ?)
- **E**xistence (Does a value exist or is it null or empty present into a collection or not and so on ?)
- **C**ardinality (Are there exactly enough values ?)
- **T**ime (Is everything happening in order ? At the right time and in time ?)

#### Conformance
Many data structures must conform to a certain format. A well known format is the email or the ip address.

For example a system has a data import format which consists of head multiple body entries and a trailing entry.
Some of the boundary conditions would be:
- Just data
- Just header
- Just trailing entry
- Just a header and data
- Just a header and a trailing entry
- Just data and the trailing entry

Brainstorming about these boundary conditions is helpful to find different kind of problems within a system. However, unit tests
should not be written for cases which will never happen at all. This introduces the question at which point are unit tests no longer
useful?

For example someone passed an email address into a system because that person changed the provider. That email will be passed 
through countless methods of the system. However, if the email address is validated at the entry point of the system, the address 
can be threaded as safe in each underlying method and subsequent validations are not needed at all. Therefore, it would be useless
to test the underlying methods in terms of the format validity because the will never receive an invalid email address.

To summarize, it is very important to understand the data flow in the system to reduce unnecessary unit tests.

#### Ordering
The order of data or the position of specific data in larger collections are often a point were something 
goes wrong within a system.

#### Range
The the 64bit integer of PHP has far more capacity than needed. For example the age of a dog will never exceed a certain point but
if something went wrong the dog is in a sudden 2 pow 64 years old.

The excessive use of primitive  

   

## Test Examples
//show different test scenarios and how they are looking in ILIAS
//filesystem test
//mocking tests (normal, fat legacy classes, disable constructor, fluent interfaces)
//DI erwähnen
## External documentation
[PHP Unit Documentation](https://phpunit.de/manual/5.7/en/index.html)

[Mockery Documentation](http://docs.mockery.io/en/latest/)

[ILIAS CI Server](https://ci.ilias.de/)

[xDebug Documentation](https://xdebug.org/docs/)

## Old Documentation
[Old ILIAS Unit Test Guide](https://www.ilias.de/docu/goto_docu_pg_29759_42.html)


//Refer to obsolete documentation

// talk about the correct usage of the DIC (Dependency inversion etc)

## FAQ
### What needs to be tested ?

### How is a local test environment set up ?
### How do I start the ILIAS unit tests in PHPStorm ? 
### How do I start the ILIAS unit tests in CLI ?
### Where do I put my unit tests ?
### What is the ILIAS CI-Server and how can I benefit from it in terms of unit tests ?
### What do I need to consider concerning unit tests before pushing code to the ILIAS repo ?

//optional
### How do I inrease the readability of my unit tests ?
//talk about testability