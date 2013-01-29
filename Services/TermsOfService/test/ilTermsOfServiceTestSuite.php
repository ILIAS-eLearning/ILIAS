<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceTestSuite extends PHPUnit_Framework_TestSuite
{
	/**
	 * @return ilTermsOfServiceTestSuite
	 */
	public static function suite()
	{
		$suite = new self();

		require_once 'Services/TermsOfService/test/requests/ilTermsOfServiceAcceptanceRequestTest.php';
		$suite->addTestSuite('ilTermsOfServiceAcceptanceRequestTest');

		require_once 'Services/TermsOfService/test/requests/ilTermsOfServiceCurrentAcceptanceRequestTest.php';
		$suite->addTestSuite('ilTermsOfServiceCurrentAcceptanceRequestTest');

		require_once 'Services/TermsOfService/test/responses/ilTermsOfServiceCurrentAcceptanceResponseTest.php';
		$suite->addTestSuite('ilTermsOfServiceCurrentAcceptanceResponseTest');

		require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceTableDataProviderFactoryTest.php';
		$suite->addTestSuite('ilTermsOfServiceTableDataProviderFactoryTest');

		require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceEntityFactoryTest.php';
		$suite->addTestSuite('ilTermsOfServiceEntityFactoryTest');

		require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceInteractorFactoryTest.php';
		$suite->addTestSuite('ilTermsOfServiceInteractorFactoryTest');

		require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceRequestFactoryTest.php';
		$suite->addTestSuite('ilTermsOfServiceRequestFactoryTest');

		require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceResponseFactoryTest.php';
		$suite->addTestSuite('ilTermsOfServiceResponseFactoryTest');

		require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceDataGatewayFactoryTest.php';
		$suite->addTestSuite('ilTermsOfServiceDataGatewayFactoryTest');

		require_once 'Services/TermsOfService/test/provider/ilTermsOfServiceAgreementsByLanguageTableDataProviderTest.php';
		$suite->addTestSuite('ilTermsOfServiceAgreementsByLanguageTableDataProviderTest');

		require_once 'Services/TermsOfService/test/entities/ilTermsOfServiceAcceptanceEntityTest.php';
		$suite->addTestSuite('ilTermsOfServiceAcceptanceEntityTest');

		require_once 'Services/TermsOfService/test/interactors/ilTermsOfServiceAcceptanceInteractorTest.php';
		$suite->addTestSuite('ilTermsOfServiceAcceptanceInteractorTest');

		require_once 'Services/TermsOfService/test/interactors/ilTermsOfServiceCurrentAcceptanceInteractorTest.php';
		$suite->addTestSuite('ilTermsOfServiceCurrentAcceptanceInteractorTest');

		require_once 'Services/TermsOfService/test/gateways/ilTermsOfServiceAcceptanceDatabaseGatewayTest.php';
		$suite->addTestSuite('ilTermsOfServiceAcceptanceDatabaseGatewayTest');

		return $suite;
	}
}
