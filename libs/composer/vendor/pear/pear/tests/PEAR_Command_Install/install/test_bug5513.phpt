--TEST--
install command, bug #5513 test - PEAR 1.4 does not install non-pear.php.net packages [EXTREMELY slow test]
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$reg = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/versioncontrol_svn/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>VersionControl_SVN</p>
 <c>pear.php.net</c>
 <r><v>0.3.0alpha1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/versioncontrol_svn/0.3.0alpha1.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/versioncontrol_svn">VersionControl_SVN</p>
 <c>pear.php.net</c>
 <v>0.3.0alpha1</v>
 <st>alpha</st>
 <l>BSD License</l>
 <m>clay</m>
 <s>Simple OO wrapper interface for the Subversion command-line client.</s>
 <d>What is VersionControl_SVN?

VersionControl_SVN is a simple OO-style interface for Subversion,
the free/open-source version control system.

VersionControl_SVN can be used to manage trees of source code,
text files, image files -- just about any
collection of files.

Some of VersionControl_SVN\'s features:

* Full support of svn command-line client\'s
  subcommands.
* Use of flexible error reporting provided by
  PEAR_ErrorStack.
* Multi-object factory.
* Source fully documented with PHPDoc.
* Stable, extensible interface.
* Collection of helpful quickstart examples and
  tutorials.

What can be done with VersionControl_SVN?

* Make your source code available to your
  remote dev team or project manager.

* Build your own WYSIWYG web interface to a
  Subversion repository.

* Add true version control to a content management
  system!

Note: Requires a Subversion installation.
Subverison is available from
http://subversion.tigris.org/

VersionControl_SVN is tested against Subversion 1.0.4
</d>
 <da>2004-06-09 13:05:00</da>
 <n>- Completed all svn subcommand packages.
</n>
 <f>33829</f>
 <g>http://pear.php.net/get/VersionControl_SVN-0.3.0alpha1</g>
 <x xlink:href="package.0.3.0alpha1.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/versioncontrol_svn/deps.0.3.0alpha1.txt", 'a:2:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.1";s:8:"optional";s:2:"no";s:4:"name";s:10:"XML_Parser";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpunit2/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PHPUnit2</p>
 <c>pear.php.net</c>
 <r><v>2.3.0beta5</v><s>beta</s></r>
 <r><v>2.3.0beta4</v><s>beta</s></r>
 <r><v>2.3.0beta3</v><s>beta</s></r>
 <r><v>2.3.0beta2</v><s>beta</s></r>
 <r><v>2.3.0beta1</v><s>beta</s></r>
 <r><v>2.2.1</v><s>stable</s></r>
 <r><v>2.2.0</v><s>stable</s></r>
 <r><v>2.2.0beta7</v><s>beta</s></r>
 <r><v>2.2.0beta6</v><s>beta</s></r>
 <r><v>2.1.6</v><s>stable</s></r>
 <r><v>2.2.0beta5</v><s>beta</s></r>
 <r><v>2.1.5</v><s>stable</s></r>
 <r><v>2.2.0beta4</v><s>beta</s></r>
 <r><v>2.2.0beta3</v><s>beta</s></r>
 <r><v>2.2.0beta2</v><s>beta</s></r>
 <r><v>2.2.0beta1</v><s>beta</s></r>
 <r><v>2.1.4</v><s>stable</s></r>
 <r><v>2.1.3</v><s>stable</s></r>
 <r><v>2.1.2</v><s>stable</s></r>
 <r><v>2.1.1</v><s>stable</s></r>
 <r><v>2.1.0</v><s>stable</s></r>
 <r><v>2.0.3</v><s>stable</s></r>
 <r><v>2.0.2</v><s>stable</s></r>
 <r><v>2.0.1</v><s>stable</s></r>
 <r><v>2.0.0</v><s>stable</s></r>
 <r><v>2.0.0beta2</v><s>beta</s></r>
 <r><v>2.0.0beta1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpunit2/2.3.0beta5.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/phpunit2">PHPUnit2</p>
 <c>pear.php.net</c>
 <v>2.3.0beta5</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>sebastian</m>
 <s>Regression testing framework for unit tests.</s>
 <d>PHPUnit is a regression testing framework used by the developer who implements unit tests in PHP. This is the version to be used with PHP 5.
</d>
 <da>2005-09-09 04:35:31</da>
 <n>+ Added PHPUnit2_Framework_Assert::assertNotEquals().

+ Added PHPUnit2_Framework_TestSuite::addTestFile() and PHPUnit2_Framework_TestSuite::addTestFiles () as convenience methods that wrap PHPUnit2_Framework_TestSuite::addTest() and PHPUnit2_Framework_TestSuite::addTestSuite(). Contribution by Stefano F. Rausch &lt;stefano@rausch-e.net&gt;.

+ Added BankAccount and Money samples.

* Made the mechanism provided by PHPUnit2_Extensions_TestSetup actually work.

* The PHPUnit2_Runner_StandardTestSuiteLoader now checks the test suite sourcefile for syntax errors before loading it. Before, a syntax error in the test suite sourcefile caused a termination of the TextUI test runner, for instance, without any error information being printed. Please note that sourcefiles included by the test suite sourcefile are not checked. This will be done at a later time utilizing the sandboxed interpreter feature of the Runkit extension.

* PHPUnit2_Framework_TestResult::run() now saves the $GLOBALS array before and restores it after each test execution for better isolation.

* Code Coverage collection has been moved from PHPUnit2_Framework_TestCase to PHPUnit2_Framework_TestResult. This allows for Code Coverage analysis of tests that are written in a class that does not inherit from PHPUnit2_Framework_TestCase.

* Code Coverage information is no longer collected by default (when the Xdebug extension is available) but only when it is requested (by using the --coverage-* parameters with the TextUI TestRunner, for instance). PHPUnit2_Framework_TestResult::collectCodeCoverageInformation(TRUE) has to be called to enable the collection of Code Coverage information.

* PHPUnit2_Framework_Assert::assertSame() and PHPUnit2_Framework_AssertNotSame() now work on non-objects and assert that two variables (do not) have the same type and value.

* PHPUnit2_Framework_Assert::assertType() and PHPUnit2_Framework_AssertNotType() now consider subclasses when used on objects.

* PHPUnit2_Util_Skeleton now generates stubs for the setUp() and tearDown() methods.

* PHPUnit2_Extensions_TestDox_NamePrettifier now removes digits from the end of test method names and PHPUnit2_Extensions_TestDox_ResultPrinter treats test methods like testBalanceCannotBecomeNegative() and testBalanceCannotBecomeNegative2() as one and prints &quot;Balance cannot become negative&quot; only once.

* PHPUnit2 now uses the Standard PHP Library (SPL)\'s specialized exceptions InvalidArgumentException and RuntimeException instead of the generic Exception exception class.

* Renamed Extensions_CodeCoverage_*, Extensions_Log_*, and Extensions_TestDox_* to Util_CodeCoverage_*, Util_Log_*, and Util_TestDox_* as the Extensions_* namespace is intended for extensions of the framework.

* Stacktraces for failed tests now show the failing assertion.

* PHPUnit2_Util_Printer no longer uses fopen/fputs/fclose to write to STDOUT.

* Implemented RFE #4456.

* Moved tests outside of installation directory.

! PHP 5.1.0 (or greater) is now required.
</n>
 <f>44001</f>
 <g>http://pear.php.net/get/PHPUnit2-2.3.0beta5</g>
 <x xlink:href="package.2.3.0beta5.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpunit2/deps.2.3.0beta5.txt", 'a:7:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:7:"5.1.0b1";}i:2;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"dom";}i:3;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"pcre";}i:4;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"spl";}i:5;a:5:{s:4:"type";s:3:"ext";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta2";s:8:"optional";s:3:"yes";s:4:"name";s:6:"xdebug";}i:6;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:9:"Benchmark";}i:7;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:3:"Log";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpdocumentor/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PhpDocumentor</p>
 <c>pear.php.net</c>
 <r><v>1.3.0RC3</v><s>beta</s></r>
 <r><v>1.3.0RC2</v><s>beta</s></r>
 <r><v>1.3.0RC1</v><s>beta</s></r>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2.1</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0a</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.2.0beta3</v><s>beta</s></r>
 <r><v>1.2.0beta2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpdocumentor/1.3.0RC3.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/phpdocumentor">PhpDocumentor</p>
 <c>pear.php.net</c>
 <v>1.3.0RC3</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>The phpDocumentor package provides automatic documenting of php api directly from the source.</s>
 <d>The phpDocumentor tool is a standalone auto-documentor similar to JavaDoc
written in PHP.  It differs from PHPDoc in that it is MUCH faster, parses a much
wider range of php files, and comes with many customizations including 11 HTML
templates, windows help file CHM output, PDF output, and XML DocBook peardoc2
output for use with documenting PEAR.  In addition, it can do PHPXref source
code highlighting and linking.

Features (short list):
-output in HTML, PDF (directly), CHM (with windows help compiler), XML DocBook
-very fast
-web and command-line interface
-fully customizable output with Smarty-based templates
-recognizes JavaDoc-style documentation with special tags customized for PHP 4
-automatic linking, class inheritance diagrams and intelligent override
-customizable source code highlighting, with phpxref-style cross-referencing
-parses standard README/CHANGELOG/INSTALL/FAQ files and includes them
 directly in documentation
-generates a todo list from @todo tags in source
-generates multiple documentation sets based on @access private, @internal and
 {@internal} tags
-example php files can be placed directly in documentation with highlighting
 and phpxref linking using the @example tag
-linking between external manual and API documentation is possible at the
 sub-section level in all output formats
-easily extended for specific documentation needs with Converter
-full documentation of every feature, manual can be generated directly from
 the source code with &quot;phpdoc -c makedocs&quot; in any format desired.
-current manual always available at http://www.phpdoc.org/manual.php
-user .ini files can be used to control output, multiple outputs can be
 generated at once

**WARNING**:
To use the web interface, you must set PEAR\'s data_dir to a subdirectory of
document root.

If browsing to http://localhost/index.php displays /path/to/htdocs/index.php,
set data_dir to a subdirectory of /path/to/htdocs:

$ pear config-set data_dir /path/to/htdocs/pear
$ pear install PhpDocumentor

http://localhost/pear/PhpDocumentor is the web interface
</d>
 <da>2004-04-10 15:16:24</da>
 <n>PHP 5 support and more, fix bugs

This will be the last release in the 1.x series.  2.0 is next

Features added to this release include:

 * Full PHP 5 support, phpDocumentor both runs in and parses Zend Engine 2
   language constructs.  Note that you must be running phpDocumentor in
   PHP 5 in order to parse PHP 5 code
 * XML:DocBook/peardoc2:default converter now beautifies the source using
   PEAR\'s XML_Beautifier if available
 * inline {@example} tag - this works just like {@source} except that
   it displays the contents of another file.  In tutorials, it works
   like &lt;programlisting&gt;
 * customizable README/INSTALL/CHANGELOG files
 * phpDocumentor tries to run .ini files out of the current directory
   first, to allow you to put them anywhere you want to
 * multi-national characters are now allowed in package/subpackage names
 * images in tutorials with the &lt;graphic&gt; tag
 * un-modified output with &lt;programlisting role=&quot;html&quot;&gt;
 * html/xml source highlighting with &lt;programlisting role=&quot;tutorial&quot;&gt;

From both Windows and Unix, both the command-line version
of phpDocumentor and the web interface will work
out of the box by using command phpdoc - guaranteed :)

WARNING: in order to use the web interface through PEAR, you must set your
data_dir to a subdirectory of your document root.

$ pear config-set data_dir /path/to/public_html/pear

on Windows with default apache setup, it might be

C:\\&gt; pear config-set data_dir &quot;C:\\Program Files\\Apache\\htdocs\\pear&quot;

After this, install/upgrade phpDocumentor

$ pear upgrade phpDocumentor

and you can browse to:

http://localhost/pear/PhpDocumentor/

for the web interface

------
WARNING: The PDF Converter will not work in PHP5.  The PDF library that it relies upon
segfaults with the simplest of files.  Generation still works great in PHP4
------

- WARNING: phpDocumentor installs phpdoc in the
  scripts directory, and this will conflict with PHPDoc,
  you can\'t have both installed at the same time
- Switched to Smarty 2.6.0, now it will work in PHP 5.  Other
  changes made to the code to make it work in PHP 5, including parsing
  of private/public/static/etc. access modifiers
- fixed these bugs:
 [ 834941 ] inline @link doesn\'t work within &lt;b&gt;
 [ 839092 ] CHM:default:default produces bad links
 [ 839466 ] {$array[\'Key\']} in heredoc
 [ 840792 ] File Missing XML:DocBook/peardoc2:default &quot;errors.tpl&quot;
 [ 850731 ] No DocBlock template after page-level DocBlock?
 [ 850767 ] MHW Reference wrong
 [ 854321 ] web interface errors with template directory
 [ 856310 ] HTML:frames:DOM/earthli missing Class_logo.png image
 [ 865126 ] CHM files use hard paths
 [ 875525 ] &lt;li&gt; escapes &lt;pre&gt; and ignores paragraphs
 [ 876674 ] first line of pre and code gets left trimmed
 [ 877229 ] PHP 5 incompatibilities bork tutorial parsing
 [ 877233 ] PHP 5 incompatibilities bork docblock source highlighting
 [ 878911 ] [PHP 5 incompatibility] argv
 [ 879068 ] var arrays tripped up by comments
 [ 879151 ] HTML:frames:earthli Top row too small for IE
 [ 880070 ] PHP5 visability for member variables not working
 [ 880488 ] \'0\' file stops processing
 [ 884863 ] Multiple authors get added in wrong order.
 [ 884869 ] Wrong highligthing of object type variables
 [ 892305 ] peardoc2: summary require_once Path/File.php is PathFile.php
 [ 892306 ] peardoc2: @see of method not working
 [ 892479 ] {@link} in // comment is escaped
 [ 893470 ] __clone called directly in PackagePageElements.inc
 [ 895656 ] initialized private variables not recognized as private
 [ 904823 ] IntermediateParser fatal error
 [ 910676 ] Fatal error: Smarty error: unable to write to $compile_dir
 [ 915770 ] Classes in file not showing
 [ 924313 ] Objec access on array
</n>
 <f>2711672</f>
 <g>http://pear.php.net/get/PhpDocumentor-1.3.0RC3</g>
 <x xlink:href="package.1.3.0RC3.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpdocumentor/deps.1.3.0RC3.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.1.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:8:"optional";s:2:"no";s:4:"name";s:11:"Archive_Tar";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:8:"optional";s:3:"yes";s:4:"name";s:14:"XML_Beautifier";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xdebug/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pecl.php.net/rest/r/xdebug/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>xdebug</p>
 <c>pecl.php.net</c>
 <r><v>2.0.0beta4</v><s>beta</s></r>
 <r><v>2.0.0beta3</v><s>beta</s></r>
 <r><v>2.0.0beta2</v><s>beta</s></r>
 <r><v>2.0.0beta1</v><s>beta</s></r>
 <r><v>1.3.2</v><s>stable</s></r>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.3.0rc2</v><s>beta</s></r>
 <r><v>1.3.0rc1</v><s>beta</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pecl.php.net/rest/r/xdebug/2.0.0beta4.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/xdebug">xdebug</p>
 <c>pecl.php.net</c>
 <v>2.0.0beta4</v>
 <st>beta</st>
 <l>BSD style</l>
 <m>derick</m>
 <s>Provides functions for function traces and profiling</s>
 <d>The Xdebug extension helps you debugging your script by providing a lot of
valuable debug information. The debug information that Xdebug can provide
includes the following:

    * stack and function traces in error messages with:
          o full parameter display for user defined functions
          o function name, file name and line indications
          o support for member functions
    * memory allocation
    * protection for infinite recursions

Xdebug also provides:

    * profiling information for PHP scripts
    * script execution analysis
    * capabilities to debug your scripts interactively with a debug client</d>
 <da>2005-09-24 18:03:07</da>
 <n>+ Added new features:
    - Added xdebug_debug_zval_stdout().
	- Added xdebug_get_profile_filename() function which returns the current
	  profiler dump file.
	- Updated for latest 5.1 and 6.0 CVS versions of PHP.
	- Added FR #148: Option to append to cachegrind files, instead of
	  overwriting.
	- Implemented FR #114: Rename tests/*.php to tests/*.inc

- Changed features:
	- Allow &quot;xdebug.default_enable&quot; to be set everywhere.

= Fixed bugs:
	- DBGP: Xdebug should return &quot;array&quot; with property get, which is defined
	  in the typemap to the common type &quot;hash&quot;.
	- Debugclient: Will now build with an older libedit as found in FreeBSD
	  4.9.
	- Fixed bug #142: xdebug crashes with implicit destructor calls.
	- Fixed bug #136: The &quot;type&quot; attribute is missing from stack_get returns.
	- Fixed bug #133: PHP scripts exits with 0 on PHP error.
	- Fixed bug #132: use of eval causes a segmentation fault.</n>
 <f>228343</f>
 <g>http://pecl.php.net/get/xdebug-2.0.0beta4</g>
 <x xlink:href="package.2.0.0beta4.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pecl.php.net/rest/r/xdebug/2.0.0beta4.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/xdebug">xdebug</p>
 <c>pecl.php.net</c>
 <v>2.0.0beta4</v>
 <st>beta</st>
 <l>BSD style</l>
 <m>derick</m>
 <s>Provides functions for function traces and profiling</s>
 <d>The Xdebug extension helps you debugging your script by providing a lot of
valuable debug information. The debug information that Xdebug can provide
includes the following:

    * stack and function traces in error messages with:
          o full parameter display for user defined functions
          o function name, file name and line indications
          o support for member functions
    * memory allocation
    * protection for infinite recursions

Xdebug also provides:

    * profiling information for PHP scripts
    * script execution analysis
    * capabilities to debug your scripts interactively with a debug client</d>
 <da>2005-09-24 18:03:07</da>
 <n>+ Added new features:
    - Added xdebug_debug_zval_stdout().
	- Added xdebug_get_profile_filename() function which returns the current
	  profiler dump file.
	- Updated for latest 5.1 and 6.0 CVS versions of PHP.
	- Added FR #148: Option to append to cachegrind files, instead of
	  overwriting.
	- Implemented FR #114: Rename tests/*.php to tests/*.inc

- Changed features:
	- Allow &quot;xdebug.default_enable&quot; to be set everywhere.

= Fixed bugs:
	- DBGP: Xdebug should return &quot;array&quot; with property get, which is defined
	  in the typemap to the common type &quot;hash&quot;.
	- Debugclient: Will now build with an older libedit as found in FreeBSD
	  4.9.
	- Fixed bug #142: xdebug crashes with implicit destructor calls.
	- Fixed bug #136: The &quot;type&quot; attribute is missing from stack_get returns.
	- Fixed bug #133: PHP scripts exits with 0 on PHP error.
	- Fixed bug #132: use of eval causes a segmentation fault.</n>
 <f>228343</f>
 <g>http://pecl.php.net/get/xdebug-2.0.0beta4</g>
 <x xlink:href="package.2.0.0beta4.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pecl.php.net/rest/r/xdebug/deps.2.0.0beta4.txt", 'a:1:{s:8:"required";a:2:{s:3:"php";a:1:{s:3:"min";s:5:"4.3.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Archive_Tar</p>
 <c>pear.php.net</c>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.10-b1</v><s>beta</s></r>
 <r><v>0.9</v><s>stable</s></r>
 <r><v>0.4</v><s>stable</s></r>
 <r><v>0.3</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/1.3.1.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/archive_tar">Archive_Tar</p>
 <c>pear.php.net</c>
 <v>1.3.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>vblavet</m>
 <s>Tar file management class</s>
 <d>This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.
</d>
 <da>2005-03-17 16:09:16</da>
 <n>Correct Bug #3855
</n>
 <f>15102</f>
 <g>http://pear.php.net/get/Archive_Tar-1.3.1</g>
 <x xlink:href="package.1.3.1.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/deps.1.3.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR_PackageFileManager</p>
 <c>pear.php.net</c>
 <r><v>1.6.0a3</v><s>alpha</s></r>
 <r><v>1.6.0a2</v><s>alpha</s></r>
 <r><v>1.6.0a1</v><s>alpha</s></r>
 <r><v>1.5.2</v><s>stable</s></r>
 <r><v>1.5.1</v><s>stable</s></r>
 <r><v>1.5.0</v><s>stable</s></r>
 <r><v>1.4.0</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.15</v><s>beta</s></r>
 <r><v>0.14</v><s>beta</s></r>
 <r><v>0.13</v><s>beta</s></r>
 <r><v>0.12</v><s>beta</s></r>
 <r><v>0.11</v><s>beta</s></r>
 <r><v>0.10</v><s>beta</s></r>
 <r><v>0.9</v><s>alpha</s></r>
 <r><v>0.8</v><s>alpha</s></r>
 <r><v>0.7</v><s>alpha</s></r>
 <r><v>0.6</v><s>alpha</s></r>
 <r><v>0.5</v><s>alpha</s></r>
 <r><v>0.4</v><s>alpha</s></r>
 <r><v>0.3</v><s>alpha</s></r>
 <r><v>0.2</v><s>alpha</s></r>
 <r><v>0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager/1.6.0a3.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear_packagefilemanager">PEAR_PackageFileManager</p>
 <c>pear.php.net</c>
 <v>1.6.0a3</v>
 <st>alpha</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>PEAR_PackageFileManager takes an existing package.xml file and updates it with a new filelist and changelog</s>
 <d>This package revolutionizes the maintenance of PEAR packages.  With a few parameters,
the entire package.xml is automatically updated with a listing of all files in a package.
Features include
 - manages the new package.xml 2.0 format in PEAR 1.4.0
 - can detect PHP and extension dependencies using PHP_CompatInfo
 - reads in an existing package.xml file, and only changes the release/changelog
 - a plugin system for retrieving files in a directory.  Currently two plugins
   exist, one for standard recursive directory content listing, and one that
   reads the CVS/Entries files and generates a file listing based on the contents
   of a checked out CVS repository
 - incredibly flexible options for assigning install roles to files/directories
 - ability to ignore any file based on a * ? wildcard-enabled string(s)
 - ability to include only files that match a * ? wildcard-enabled string(s)
 - ability to manage dependencies
 - can output the package.xml in any directory, and read in the package.xml
   file from any directory.
 - can specify a different name for the package.xml file

PEAR_PackageFileManager is fully unit tested.
The new PEAR_PackageFileManager2 class is not.</d>
 <da>2005-09-06 19:26:57</da>
 <n>Bugfix release
* add addIgnoreToRelease() to replace PEAR_PackageFile_v2_rw::addIgnore()</n>
 <f>60127</f>
 <g>http://pear.php.net/get/PEAR_PackageFileManager-1.6.0a3</g>
 <x xlink:href="package.1.6.0a3.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/versioncontrol_svn/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>VersionControl_SVN</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Version+Control">Version Control</ca>
 <l>BSD License</l>
 <s>Simple OO wrapper interface for the Subversion command-line client.</s>
 <d>What is VersionControl_SVN?

VersionControl_SVN is a simple OO-style interface for Subversion,
the free/open-source version control system.

VersionControl_SVN can be used to manage trees of source code,
text files, image files -- just about any
collection of files.

Some of VersionControl_SVN\'s features:

* Full support of svn command-line client\'s
  subcommands.
* Use of flexible error reporting provided by
  PEAR_ErrorStack.
* Multi-object factory.
* Source fully documented with PHPDoc.
* Stable, extensible interface.
* Collection of helpful quickstart examples and
  tutorials.

What can be done with VersionControl_SVN?

* Make your source code available to your
  remote dev team or project manager.

* Build your own WYSIWYG web interface to a
  Subversion repository.

* Add true version control to a content management
  system!

Note: Requires a Subversion installation.
Subverison is available from
http://subversion.tigris.org/

VersionControl_SVN is tested against Subversion 1.0.4</d>
 <r xlink:href="/rest/r/versioncontrol_svn"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/phpunit2/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>PHPUnit2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Testing">Testing</ca>
 <l>BSD License</l>
 <s>Regression testing framework for unit tests.</s>
 <d>PHPUnit is a regression testing framework used by the developer who implements unit tests in PHP. This is the version to be used with PHP 5.</d>
 <r xlink:href="/rest/r/phpunit2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/phpdocumentor/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>PhpDocumentor</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Tools+and+Utilities">Tools and Utilities</ca>
 <l>PHP License</l>
 <s>The phpDocumentor package provides automatic documenting of php api directly from the source.</s>
 <d>The phpDocumentor tool is a standalone auto-documentor similar to JavaDoc
written in PHP.  It differs from PHPDoc in that it is MUCH faster, parses a much
wider range of php files, and comes with many customizations including 11 HTML
templates, windows help file CHM output, PDF output, and XML DocBook peardoc2
output for use with documenting PEAR.  In addition, it can do PHPXref source
code highlighting and linking.

Features (short list):
-output in HTML, PDF (directly), CHM (with windows help compiler), XML DocBook
-very fast
-web and command-line interface
-fully customizable output with Smarty-based templates
-recognizes JavaDoc-style documentation with special tags customized for PHP 4
-automatic linking, class inheritance diagrams and intelligent override
-customizable source code highlighting, with phpxref-style cross-referencing
-parses standard README/CHANGELOG/INSTALL/FAQ files and includes them
 directly in documentation
-generates a todo list from @todo tags in source
-generates multiple documentation sets based on @access private, @internal and
 {@internal} tags
-example php files can be placed directly in documentation with highlighting
 and phpxref linking using the @example tag
-linking between external manual and API documentation is possible at the
 sub-section level in all output formats
-easily extended for specific documentation needs with Converter
-full documentation of every feature, manual can be generated directly from
 the source code with &quot;phpdoc -c makedocs&quot; in any format desired.
-current manual always available at http://www.phpdoc.org/manual.php
-user .ini files can be used to control output, multiple outputs can be
 generated at once

**WARNING**:
To use the web interface, you must set PEAR\'s data_dir to a subdirectory of
document root.

If browsing to http://localhost/index.php displays /path/to/htdocs/index.php,
set data_dir to a subdirectory of /path/to/htdocs:

$ pear config-set data_dir /path/to/htdocs/pear
$ pear install PhpDocumentor

http://localhost/pear/PhpDocumentor is the web interface</d>
 <r xlink:href="/rest/r/phpdocumentor"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pecl.php.net/rest/p/xdebug/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>Xdebug</n>
 <c>pecl.php.net</c>
 <ca xlink:href="/rest/c/PHP">PHP</ca>
 <l>BSD style</l>
 <s>Provides functions for function traces and profiling</s>
 <d>The Xdebug extension helps you debugging your script by providing a lot of
valuable debug information. The debug information that Xdebug can provide
includes the following:

    * stack and function traces in error messages with:
          o full parameter display for user defined functions
          o function name, file name and line indications
          o support for member functions
    * memory allocation
    * protection for infinite recursions

Xdebug also provides:

    * profiling information for PHP scripts
    * script execution analysis
    * capabilities to debug your scripts interactively with a debug client</d>
 <r xlink:href="/rest/r/xdebug"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/archive_tar/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>Archive_Tar</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Tar file management class</s>
 <d>This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.</d>
 <r xlink:href="/rest/r/archive_tar"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear_packagefilemanager/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR_PackageFileManager</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License 3.0</l>
 <s>PEAR_PackageFileManager takes an existing package.xml file and updates it with a new filelist and changelog</s>
 <d>This package revolutionizes the maintenance of PEAR packages.  With a few parameters,
the entire package.xml is automatically updated with a listing of all files in a package.
Features include
 - manages the new package.xml 2.0 format in PEAR 1.4.0
 - can detect PHP and extension dependencies using PHP_CompatInfo
 - reads in an existing package.xml file, and only changes the release/changelog
 - a plugin system for retrieving files in a directory.  Currently two plugins
   exist, one for standard recursive directory content listing, and one that
   reads the CVS/Entries files and generates a file listing based on the contents
   of a checked out CVS repository
 - incredibly flexible options for assigning install roles to files/directories
 - ability to ignore any file based on a * ? wildcard-enabled string(s)
 - ability to include only files that match a * ? wildcard-enabled string(s)
 - ability to manage dependencies
 - can output the package.xml in any directory, and read in the package.xml
   file from any directory.
 - can specify a different name for the package.xml file

PEAR_PackageFileManager is fully unit tested.
The new PEAR_PackageFileManager2 class is not.</d>
 <r xlink:href="/rest/r/pear_packagefilemanager"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager/deps.1.6.0a3.txt", 'a:1:{s:8:"required";a:2:{s:3:"php";a:1:{s:3:"min";s:5:"4.2.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phing/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pecl.php.net/rest/r/phing/allreleases.xml", false, false);
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'phing-current.tgz';
$pathtobarxml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'agavi-current.tgz';
$_test_dep->setPHPVersion('5.0.4');
$_test_dep->setPEARVersion('1.4.1');
$config->set('preferred_state', 'alpha');
$res = $command->run('install', array(), array($pathtopackagexml));
$phpunit->assertNoErrors('after install');
$phpunit->assertTrue($res, 'result');
$fakelog->getLog();
$res = $command->run('install', array(), array($pathtobarxml));
$phpunit->assertNoErrors('after install');
$phpunit->assertTrue($res, 'result');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
