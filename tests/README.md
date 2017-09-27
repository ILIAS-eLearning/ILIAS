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

#### Execute all tests
```bash
./libs/composer/vendor/bin/phpunit ./Services/PHPUnit/test/ilGlobalSuite.php \
	--colors=always \
	--report-useless-tests \
	--disallow-todo-tests \
	--no-globals-backup
``` 

#### Execute only installation unbound tests
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

The excessive use of primitives is known as a code smell known as *primitive obsession*. One of the primal benefit of PHP is that data 
can be abstracted with its own logic. For example a dog has at most four legs and its age is between 1 second and 30 years.  

To abstract these values and test the constraints of the abstraction makes the rest of the application more resistant again such errors.

#### Reference
When a method is tested the following criteria should be considered:
- What is the method referencing outside of the scope
- Which dependencies are there
- If the method depends on objects being in a specific state
- Other conditions which must exist for the method

If assumptions are made about a state, the code should be tested that it is not behaving in a wrong way 
when the assumption is not true. For example a plain has to expand the wheels before landing or the plain will most likely be destroyed
after the landing. Therefore, a plain must transition into the right state before doing a certain action. This situations must be 
tested or it is almost certain that something goes wrong in the future.

```php
<?php
/**
 * @Test
 */
public function testPlainLandingWithExpandedWheelsWhichShouldSucceed() {
	
	//arrange (preconditions)
	$plain = new Plain();
	$plain->start();
	$plain->expandWheels();
	
	//act
	$plain->land();
	
	//assert (postconditions)
	$this->assertSame(0, $plain->getSpeed());
}
```

#### Existence
A potentially large sum of defects could be discovered by asking the question "Does something given thing exist?" For a method which has parameters
or accessing fields should be thought about if they can be null and how the code should behave in such a case. 

Sadly, at the time a null value gets into the wrong place it is often not easy to tell where the actual problems are. Using exceptions
to tell the consumer of code what went wrong greatly simplifies the search after a problem.

#### Cardinality
Often errors arise due to incorrect counting. For example the fencepost which can be illustrated with the following question:
"If you build a straight fence 30 meter long with posts spaced 3 meters apart, how many posts do you need?"

Of course the answer 10 is wrong because it needs 11 posts for 10 sections. Basically the count of sets of values are interesting in 
the following cases.
- Zero
- One
- Many (more than one element)

Some developers refer to this as the 0-1-n rule. Zero is important as already mentioned in the *Existence* part. 
To have only one element of its kind is also important in some situations. In collections the exact amount of items is normally not
really important because the code is the same if there are ten or 1 billion elements, with some exceptions of course. 

As an example the best 10 students should be displayed within a test as a ordered list top best to bottom which is still very good.
Every time a student takes the test the list gets updated. Here are a list of things which should be considered corresponding to 
the cardinality:
- Producing a list without students
- Producing a list with exactly one student
- Producing a list without ten students
- Adding a student to the empty list
- Adding a student to the list which contains only one other student
- Adding a student to the list which contains not ten other students
- Adding a student to the list which contains already ten students

In general test should focus on boundary conditions with 0, 1 and n.

#### Time
There are several things which should be considered regarding the walk clock time. If some portion of code
rely on time for example a timestamp. The unit test may work 1 or 2 times but will break in the future because time it self is
not under the control of the test and developer. Therefore, the standard time sources must be faked with more controllable ones
to make the tests repeatable (FI**R**ST). 

An other aspect are timezones which are normally not a problem in PHP, but there are some edge cases with switching hours which should
be considered. A short example from [stackoverflow](https://stackoverflow.com/a/19004000) illustrates the error in a simple way.
 
 | Europe/Warsaw time  |   offset |  UTC                |    php2utc conversion |  php offset  |
 | ------------------- | -------- | ------------------- | --------------------- | ------------ |
 | 2013-10-27 01:00:00 |    +2    | 2013-10-26 23:00:00 |   2013-10-26 23:00:00 | +2           |
 | 2013-10-27 01:30:00 |    +2    | 2013-10-26 23:30:00 |   2013-10-26 23:30:00 | +2           |
 | 2013-10-27 02:00:00 |    +2    | 2013-10-27 00:00:00 |   2013-10-27 01:00:00 | **+1**       |
 | 2013-10-27 02:30:00 |    +2    | 2013-10-27 00:30:00 |   2013-10-27 01:30:00 | **+1**       |
 | 2013-10-27 02:59:00 |    +2    | 2013-10-27 00:59:00 |   2013-10-27 01:59:00 | **+1**       |
 
 3am -> 2am .....................................summer time changes to standard(winter) time @3am we subtract 1h so 3am becomes 2am 
 
 | Europe/Warsaw time  |   offset |  UTC                  |    php2utc conversion  |  php offset  |
 | ------------------- | -------- | --------------------- | ---------------------- | ------------ |
 | 2013-10-27 02:00:00 |    +1    | 2013-10-27 01:00:00   | 2013-10-27 01:00:00    | +1           |
 | 2013-10-27 02:30:00 |    +1    | 2013-10-27 01:30:00   | 2013-10-27 01:30:00    | +1           |
 | 2013-10-27 03:00:00 |    +1    | 2013-10-27 02:00:00   | 2013-10-27 02:00:00    | +1           |
 | 2013-10-27 03:30:00 |    +1    | 2013-10-27 02:30:00   | 2013-10-27 02:30:00    | +1           |
 

## Test Examples
This test section will show good and bad unit tests in ILIAS.

### Negative Examples
All the examples are shown in this section have some different problems which will be explained in detail. However
it is really important to understand that only the code is wrong. The statements made are never about a developers skills and how they are now
in the present. "It's just code!"

#### Wrong Location and Bloated
The test shown below is the only test of the course module. However the code actually test the 
*ilMemberAgreement* class which is part of the Membership service.

In addition this test tests more than one thing:
- Agreement of a user to some policy
- Removal of a user from the acceptance list of a policy
- Test of a second static method which makes the same
- Fetch the agreement with the object id 8888.
- Removal of a user with the user id 9999, however the result is not evaluated.

The test requires also an initialised ILIAS instance because the values are written to the db which could lead
to an unpredictable result if an other test maybe an other *ilMemberAgreement* test class manipulates the database.

Finally in this case it should be considered to remove the test entirely because it is not of real use. In addition this test is
skipped on the CI server which leads to the final question if test is ever executed.


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

#### Testing PHP behaviour / Wrong class
The Button test contains tests which belongs into another class and testing PHP behaviour.
In the case of the *test_implements_factory_interface* test the only thing which is tested is the *Factory* class
which is living in the "\ILIAS\UI\Implementation\Component\Button\" namespace. In addition the only 
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

##### Example solution
The proposed solution would be to remove the test entirely. However, if the author wishes to keep the 
tests. They could be moved into the ButtonFactoryTest class and split up in smaller more precise tests.
As a result the developer which runs the test is now able to see which part of the factory failed.

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
	public function test_creation_of_standard_button() {
		$this->assertInstanceOf
        			( Standard::class
        			, $this->subject->standard("label", "http://www.ilias.de")
        			);
	}
	
	/**
     * @test 
     * @small
     */
    public function test_creation_of_primary_button() {
        $this->assertInstanceOf
                    ( Primary::class
                    , $this->subject->primary("label", "http://www.ilias.de")
                    );
    }
    
    /**
     * @test 
     * @small
     */
    public function test_creation_of_Shy_button() {
        $this->assertInstanceOf
                    ( Shy::class
                    , $this->subject->shy("label", "http://www.ilias.de")
                    );
    }
    
    /**
     * @test 
     * @small
     */
    public function test_creation_of_close_button() {
        $this->assertInstanceOf
                    ( Close::class
                    , $this->subject->close("label", "http://www.ilias.de")
                    );
    }
}
```


#### Useless test / Naming
Unit tests should always have an assertion of the result, because of that PHPUnit 6 started to mark such 
tests as useless. Useless test are always threaded as failed. Furthermore, the test name *test_button_label_or_glyph_only*
is not really telling whats exactly tested.

Another aspect of the whole *ButtonTest* class is that a factory is used to create concrete instances of
the Buttons. But the factory nor the concrete subclasses of the Button class is a test subject here only the 
Button class it self. Of course to test all button instances is not really effective. However, the button can
also be created with the help of mockery which subclasses the button dynamically within the tests. This allows
to test the Button class in a dedicated way. If logic is added to one of the specific implementations only 
this part has to be tested within the test class of the specific button subclass for example the primary button.
 

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

##### Example solution
The proposed solution of this example would be to remove the test because the trait should be tested in a separate
class dedicated to the trait. However, if the author decides to test the class which use the trait in stead of the trait it self
the solution would look like this. 

First the *Factory* was removed because this class is meant for the *Button* class.
Second the data provider has been removed due to the fact that the button it self will be tested and not 
the children of the button.
Third the try catch was replaced with the phpunit construct which is designed to test exception occurrence.
Finally the *Button* class has been partial mocked with a full method delegation which means that the test code
is directly talking to the real button implementation. The second parameter is a list of construct argument for the button
which remained unchanged to get same result as before.

```php
<?php

use ILIAS\UI\Implementation\Component\Button\Button;  

class ButtonTest extends ILIAS_UI_TestBase {
	
	use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
	
	/**
	 * @test
     * @small
	 */
	public function test_button_creation_with_invalid_argument_which_should_fail() {
		
		$this->expectException(InvalidArgumentException::class);
		
		//create a partial mock because the button is abstract
		Mockery::mock(Button::class . '[]', [$this, 'http://www.ilias.de']);
	}
}
```

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
