<?php

use PHPUnit\Framework\TestSuite;

/**
 * Class ilServicesFileDeliverySuite
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class ilServicesFileDeliverySuite extends TestSuite {

	/**
	 * @return \ilServicesFileDeliverySuite
	 */
	public static function suite()
	{
		$suite = new self();

		$suite->addTestFiles([
			'./Services/FileDelivery/test/FileDeliveryTypes/XSendfileTest.php',
			'./Services/FileDelivery/test/FileDeliveryTypes/XAccelTest.php',
			'./Services/FileDelivery/test/FileDeliveryTypes/FileDeliveryTypeFactoryTest.php'
		]);

		return $suite;
	}
}