<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class ilServicesAssessmentQuestionSuite
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class ilServicesAssessmentQuestionSuite extends TestSuite
{
	/**
	 * @var array
	 */
	protected static $testSuites = array(
		
		'Services/AssessmentQuestion/test/ilHelloWorldTest.php' => 'ilHelloWorldTest'
		
	);
	
	/**
	 * @return ilServicesAssessmentQuestionSuite
	 * @throws ReflectionException
	 */
	public static function suite()
	{
		chdir( dirname( __FILE__ ) );
		chdir('../../../');

		$suite = new ilServicesAssessmentQuestionSuite();
	
		foreach(self::$testSuites as $classFile => $className)
		{
			require_once $classFile;
			$suite->addTestSuite($className);
		}

		return $suite;
	}
}