Please install vfsStream (http://vfs.bovigo.org/) and add the main directory to your php include path.
Otherwise certain tests will be skipped.
To run the unit tests, please "cd" to the ILIAS main directory and execute:

phpunit --configuration Services/Password/phpunit.xml Services/Password/test/ilPasswordTestSuite.php