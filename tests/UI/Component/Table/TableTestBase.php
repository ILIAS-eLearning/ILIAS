<?php

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

declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation\Component as C;
use ILIAS\UI\Implementation\Component\Input\ViewControl;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Implementation\Component\Input\Container\ViewControl as ViewControlContainer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;

/**
 * Basic Tests for all Tables.
 */
abstract class TableTestBase extends ILIAS_UI_TestBase
{
    protected function buildFieldFactory(): FieldFactory
    {
        return new FieldFactory(
            $this->createMock(UploadLimitResolver::class),
            new C\SignalGenerator(),
            new \ILIAS\Data\Factory(),
            $this->buildRefinery(),
            $this->getLanguage()
        );
    }

    protected function buildRefinery(): Refinery
    {
        return new Refinery(
            new \ILIAS\Data\Factory(),
            $this->createMock(ilLanguage::class)
        );
    }

    protected function getViewControlFactory(): ViewControl\Factory
    {
        return new ViewControl\Factory(
            $this->buildFieldFactory(),
            new \ILIAS\Data\Factory(),
            $this->buildRefinery(),
            new C\SignalGenerator(),
            $this->getLanguage(),
        );
    }

    protected function getViewControlContainerFactory(): ViewControlContainer\Factory
    {
        return new ViewControlContainer\Factory(
            new C\SignalGenerator(),
            $this->getViewControlFactory(),
        );
    }

    protected function getTableFactory(): C\Table\Factory
    {
        return new C\Table\Factory(
            new C\SignalGenerator(),
            $this->getViewControlFactory(),
            $this->getViewControlContainerFactory(),
            new \ILIAS\Data\Factory(),
            new C\Table\Column\Factory($this->getLanguage()),
            new C\Table\Action\Factory(),
            new C\Table\DataRowBuilder(),
            $this->getMockStorage()
        );
    }

    protected function getMockStorage(): ArrayAccess
    {
        return new class () implements ArrayAccess {
            protected array $data = [];
            public function offsetExists($offset)
            {
                return isset($this->data[$offset]);
            }
            public function offsetGet($offset)
            {
                if(!$this->offsetExists($offset)) {
                    return null;
                }
                return $this->data[$offset];
            }
            public function offsetSet($offset, $value)
            {
                $this->data[$offset] = $value;
            }
            public function offsetUnset($offset)
            {
                unset($this->data[$offset]);
            }
        };
    }
}
