YUI Library - YUITest - Release Notes

2.9.0

  * Works with YUI Test Selenium Driver
  * Added JUnit XML test format.
  * Added TAP test format.
  * Added getResults() method on YAHOO.tool.TestRunner.
  * Added isRunning() method on YAHOO.tool.TestRunner.
  * Added coverage support, including getCoverage() on YAHOO.tool.TestRunner and YAHOO.tool.CoverageFormat.
  * Added getName()/setName() method to allow setting overall results name.
  * Changed master suite default name to be "yuitests" plus a timestamp.
  * Changed functionality of TestRunner when there's only one suite to run. Internally, the TestRunner uses
    a TestSuite to manage everything added via add(). Previously, this test suite was always represented
    in the results. Now, if you've only added one TestSuite to the TestRunner via add(), the specified
    TestSuite becomes the root. This may affect the reporting of test results if you're using TestReporter.
    To run tests in the old way, call TestRunner.run(true).

2.8.1

  * No changes

2.8.0

  * Fixed issues with ArrayAssert methods (trac #2528121)
  * UserAction moved into a new module: event-simulate.
  * Custom Error objects inherit from Object instead of Error.

2.7.0

  * No changes

2.6.0

  * Added failsafe mechanism for wait() - will automatically fail a test after 10 seconds if resume() isn't called.
  * Augmented wait() to allow a single argument, a timeout after which point the test should fail.
  * Fixed small bug in TestManager.
  
2.5.1

  * Fixed ObjectAssert.hasProperty() so that is correctly identifies declared properties with a value of undefined.
  * Fixed DateAssert documentation errors.
  * Added ability to include framework assertion message in addition to custom assertion message.
  * Fixed Assert.isUndefined() documentation error.
  
2.5.0
 
  * Updated test results format to include ignored tests, result types, and names.
  * Introduced test result formats in JSON and XML.
  * Introduced TestReporter object.
  * Removed beta tag.
    
2.4.0

  * Changed test running from synchronous to asynchronous.
  * Added wait() and resume() methods to TestRunner to allow testing of asynchronous features.
  
2.3.1

  * No changes
  
2.3.0

  * Beta release




  




