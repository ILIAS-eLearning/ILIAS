<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/bootstrap.php';

/**
 * Class ilServicesTermsOfServiceSuite
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilServicesTermsOfServiceSuite extends \PHPUnit_Framework_TestSuite
{
	/**
	 * @return self
	 */
	public static function suite()
	{
		$suite = new self();

		$suite->addTestSuite('ilTermsOfServiceEntityFactoryTest');
		$suite->addTestSuite('ilTermsOfServiceDataGatewayFactoryTest');
		$suite->addTestSuite('ilTermsOfServiceTableDataProviderFactoryTest');
		$suite->addTestSuite('ilTermsOfServiceAcceptanceHistoryProviderTest');
		$suite->addTestSuite('ilTermsOfServiceAcceptanceEntityTest');
		$suite->addTestSuite('ilTermsOfServiceAcceptanceDatabaseGatewayTest');
		$suite->addTestSuite('ilTermsOfServiceDocumentEvaluationTest');
		$suite->addTestSuite('ilTermsOfServiceDocumentCriteriaEvaluationTest');
		$suite->addTestSuite('ilTermsOfServiceUserHasLanguageCriterionTest');
		$suite->addTestSuite('ilTermsOfServiceUserHasGlobalRoleCriterionTest');
		$suite->addTestSuite('ilTermsOfServiceNullCriterionTest');

		return $suite;
	}
}
