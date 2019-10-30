<?php
require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

/**
 * Base class for modal tests
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
abstract class ModalBase extends ILIAS_UI_TestBase
{
    public function getUIFactory()
    {
        return new \ILIAS\UI\Implementation\Factory(
            new I\Component\Counter\Factory(),
            $this->createMock(C\Glyph\Factory::class),
            $this->createMock(C\Button\Factory::class),
            $this->createMock(C\Listing\Factory::class),
            $this->createMock(C\Image\Factory::class),
            $this->createMock(C\Panel\Factory::class),
            $this->createMock(C\Modal\Factory::class),
            $this->createMock(C\Dropzone\Factory::class),
            $this->createMock(C\Popover\Factory::class),
            $this->createMock(C\Divider\Factory::class),
            $this->createMock(C\Link\Factory::class),
            $this->createMock(C\Dropdown\Factory::class),
            $this->createMock(C\Item\Factory::class),
            $this->createMock(C\Icon\Factory::class),
            $this->createMock(C\ViewControl\Factory::class),
            $this->createMock(C\Chart\Factory::class),
            $this->createMock(C\Input\Factory::class),
            $this->createMock(C\Table\Factory::class),
            $this->createMock(C\MessageBox\Factory::class),
            $this->createMock(C\Card\Factory::class)
        );
    }

    protected function getModalFactory()
    {
        return new I\Component\Modal\Factory(new SignalGeneratorMock());
    }

    protected function getButtonFactory()
    {
        return new \ILIAS\UI\Implementation\Component\Button\Factory();
    }

    protected function getDummyComponent()
    {
        return new DummyComponent();
    }

    public function normalizeHTML($html)
    {
        $html = parent::normalizeHTML($html);
        // The times entity is used for closing the modal and not supported in DomDocument::loadXML()
        return str_replace(['&times;', "\t"], ['', ''], $html);
    }
}
