<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component as C;

/**
 * Tests on factory implementation for layout
 *
 * @author Nils Haagen <nhaagen@concepts-and-training.de>
 */
class LayoutFactoryTest extends AbstractFactoryTest {

	public $kitchensink_info_settings = array
		(
			"page" => array(
					"context" => false,
					"rules" => false
			)
	);
	public $factory_title = 'ILIAS\\UI\\Component\\Layout\\Factory';
}
