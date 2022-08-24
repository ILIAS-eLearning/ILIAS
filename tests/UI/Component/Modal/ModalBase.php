<?php

declare(strict_types=1);

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

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Base class for modal tests
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
abstract class ModalBase extends ILIAS_UI_TestBase
{
    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function counter(): C\Counter\Factory
            {
                return new I\Component\Counter\Factory();
            }
            public function legacy(string $content): C\Legacy\Legacy
            {
                $f = new I\Component\Legacy\Factory(new I\Component\SignalGenerator());
                return $f->legacy($content);
            }
        };
    }

    protected function getModalFactory(): I\Component\Modal\Factory
    {
        return new I\Component\Modal\Factory(new SignalGeneratorMock());
    }

    protected function getButtonFactory(): I\Component\Button\Factory
    {
        return new I\Component\Button\Factory();
    }

    protected function getDummyComponent(): DummyComponent
    {
        return new DummyComponent();
    }

    public function normalizeHTML(string $html): string
    {
        $html = parent::normalizeHTML($html);
        // The times entity is used for closing the modal and not supported in DomDocument::loadXML()
        return str_replace(['&times;', "\t"], ['', ''], $html);
    }
}
