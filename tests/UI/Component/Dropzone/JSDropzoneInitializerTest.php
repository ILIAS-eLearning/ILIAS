<?php

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");

use ILIAS\UI\Implementation\Component\Dropzone\SimpleDropzone;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\TriggeredSignal;
use ILIAS\UI\Implementation\Component\Dropzone\JSDropzoneInitializer;

/**
 * Class JSDropzoneInitializerTest
 *
 * @author  nmaerchy
 * @date    22.05.17
 * @version 0.0.1
 *
 */
class JSDropzoneInitializerTest extends PHPUnit_Framework_TestCase {

	private $signalGenerator;


	/**
	 * JSDropzoneInitializerTest constructor.
	 */
	public function __construct() {
		$this->signalGenerator = new SignalGenerator();
	}


	/**
	 * A JSDropzoneInitializer
	 */

	/**
	 * should create the javascript code to initialize a dropzone when all values are given.
	 */
	public function testInitDropzone() {

		// setup example objects
		$signal = $this->signalGenerator->create();
		$triggeredSignal = new TriggeredSignal($signal, "drop");

		$dropzone = SimpleDropzone::of()
			->setId("id_1")
			->setType(\ILIAS\UI\Component\Dropzone\Standard::class)
			->setDarkenedBackground(true)
			->setRegisteredSignals(array($triggeredSignal));

		// setup expected objects
		$expectedHtml = "
		
			il.UI.dropzone.initializeDropzone(\"{$dropzone->getType()}\", {
			
				\"id\": \"{$dropzone->getId()}\",
				\"darkenedBackground\": true,
				\"registeredSignals\": [{$signal->__toString()}]
			});
		";

		// start test
		$jsDropzoneInitializer = new JSDropzoneInitializer($dropzone);

		$this->assertEquals($expectedHtml, $jsDropzoneInitializer->initDropzone());
	}


	/**
	 * should create the javascript code with an empty string as id.
	 */
	public function testInitDropzoneWhenDropzoneIdIsNotSet() {

		// setup example objects
		$signal = $this->signalGenerator->create();
		$triggeredSignal = new TriggeredSignal($signal, "drop");

		// id is not set
		$dropzone = SimpleDropzone::of()
			->setType(\ILIAS\UI\Component\Dropzone\Standard::class)
			->setDarkenedBackground(true)
			->setRegisteredSignals(array($triggeredSignal));

		// setup expected objects
		$expectedHtml = "
		
			il.UI.dropzone.initializeDropzone(\"{$dropzone->getType()}\", {
			
				\"id\": \"\",
				\"darkenedBackground\": true,
				\"registeredSignals\": [{$signal->__toString()}]
			});
		";

		// start test
		$jsDropzoneInitializer = new JSDropzoneInitializer($dropzone);

		$this->assertEquals($expectedHtml, $jsDropzoneInitializer->initDropzone());
	}


	/**
	 * should create the javascript code with an empty array for parameter registeredSignals of the json object.
	 */
	public function testInitDropzoneWhenNoSignalsAreRegistered() {

		// setup example objects
		$dropzone = SimpleDropzone::of()
			->setId("id_1")
			->setType(\ILIAS\UI\Component\Dropzone\Standard::class)
			->setDarkenedBackground(true);

		// setup expected objects
		$expectedHtml = "
		
			il.UI.dropzone.initializeDropzone(\"{$dropzone->getType()}\", {
			
				\"id\": \"{$dropzone->getId()}\",
				\"darkenedBackground\": true,
				\"registeredSignals\": []
			});
		";

		// start test
		$jsDropzoneInitializer = new JSDropzoneInitializer($dropzone);

		$this->assertEquals($expectedHtml, $jsDropzoneInitializer->initDropzone());
	}
}
