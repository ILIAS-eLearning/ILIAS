<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Database/classes/class.ilDB.php';
require_once 'Services/Language/classes/class.ilLanguage.php';

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

		require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceEntityFactoryTest.php';
		$suite->addTestSuite('ilTermsOfServiceEntityFactoryTest');

		require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceDataGatewayFactoryTest.php';
		$suite->addTestSuite('ilTermsOfServiceDataGatewayFactoryTest');

		require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceTableDataProviderFactoryTest.php';
		$suite->addTestSuite('ilTermsOfServiceTableDataProviderFactoryTest');

		require_once 'Services/TermsOfService/test/provider/ilTermsOfServiceAgreementsByLanguageTableDataProviderTest.php';
		$suite->addTestSuite('ilTermsOfServiceAgreementsByLanguageTableDataProviderTest');

		require_once 'Services/TermsOfService/test/provider/ilTermsOfServiceAcceptanceHistoryProviderTest.php';
		$suite->addTestSuite('ilTermsOfServiceAcceptanceHistoryProviderTest');

		require_once 'Services/TermsOfService/test/entities/ilTermsOfServiceAcceptanceEntityTest.php';
		$suite->addTestSuite('ilTermsOfServiceAcceptanceEntityTest');

		require_once 'Services/TermsOfService/test/gateways/ilTermsOfServiceAcceptanceDatabaseGatewayTest.php';
		$suite->addTestSuite('ilTermsOfServiceAcceptanceDatabaseGatewayTest');

		require_once 'Services/TermsOfService/test/documents/ilTermsOfServiceFileSystemDocumentTest.php';
		$suite->addTestSuite('ilTermsOfServiceFileSystemDocumentTest');

		return $suite;
	}
}
