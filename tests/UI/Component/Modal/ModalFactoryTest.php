<?php declare(strict_types=1);

require_once(__DIR__ . '/ModalBase.php');

/**
 * Tests on factory implementation for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ModalFactoryTest extends ModalBase
{
    public function test_implements_factory_interface() : void
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
