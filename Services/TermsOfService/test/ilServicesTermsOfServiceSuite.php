<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';

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

		require_once 'Services/TermsOfService/test/ilTermsOfServiceBaseTest.php';

		require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceEntityFactoryTest.php';
		$suite->addTestSuite('ilTermsOfServiceEntityFactoryTest');

		require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceDataGatewayFactoryTest.php';
		$suite->addTestSuite('ilTermsOfServiceDataGatewayFactoryTest');

		require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceTableDataProviderFactoryTest.php';
		$suite->addTestSuite('ilTermsOfServiceTableDataProviderFactoryTest');

		require_once 'Services/TermsOfService/test/provider/ilTermsOfServiceAcceptanceHistoryProviderTest.php';
		$suite->addTestSuite('ilTermsOfServiceAcceptanceHistoryProviderTest');

		require_once 'Services/TermsOfService/test/entities/ilTermsOfServiceAcceptanceEntityTest.php';
		$suite->addTestSuite('ilTermsOfServiceAcceptanceEntityTest');

		require_once 'Services/TermsOfService/test/gateways/ilTermsOfServiceAcceptanceDatabaseGatewayTest.php';
		$suite->addTestSuite('ilTermsOfServiceAcceptanceDatabaseGatewayTest');

		return $suite;
	}
}
