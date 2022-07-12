<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
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
