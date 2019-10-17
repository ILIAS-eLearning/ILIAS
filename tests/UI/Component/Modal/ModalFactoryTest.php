<?php

require_once(__DIR__ . '/ModalBase.php');

use \ILIAS\UI\Component as C;

/**
 * Tests on factory implementation for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ModalFactoryTest extends ModalBase
{
    public function test_implements_factory_interface()
    {
        $factory = $this->getModalFactory();
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Modal\\Factory", $factory);

        $interruptive = $factory->interruptive('myTitle', 'myMessage', 'myFormAction');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Modal\\Interruptive", $interruptive);

        $round_trip = $factory->roundtrip('myTitle', $this->getDummyComponent());
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Modal\\RoundTrip", $round_trip);

        $lightbox = $factory->lightbox(new LightboxMockPage());
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Modal\\LightBox", $lightbox);
    }
}
