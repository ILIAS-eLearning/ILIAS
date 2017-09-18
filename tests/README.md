# ILIAS Unit Testing investigation
## Definition
Unit testing is the process of testing the smallest testable part of an application, called units.
Units are usually one method or behaviour of a class or one logical concept of a method.

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

[xDebug Documentation](https://xdebug.org/docs/)

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

//optional
### How do I inrease the readability of my unit tests ?
//talk about testability