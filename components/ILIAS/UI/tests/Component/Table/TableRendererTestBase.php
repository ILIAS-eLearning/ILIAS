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

require_once("vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/TableTestBase.php");

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\Data;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Tests for the Renderer of DataTables.
 */
class TableRendererTestBase extends TableTestBase
{
    protected function getActionFactory()
    {
        return new I\Table\Action\Factory();
    }

    protected function getColumnFactory()
    {
        return new I\Table\Column\Factory(
            $this->getLanguage()
        );
    }

    protected function getDummyRequest()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method("getUri")
            ->willReturn(new \GuzzleHttp\Psr7\Uri('http://localhost:80'));
        $request
            ->method("getQueryParams")
            ->willReturn([]);
        return $request;
    }

    public function getDataFactory(): Data\Factory
    {
        return new Data\Factory();
    }

    public function getUIFactory(): NoUIFactory
    {
        $factory = new class ($this->getTableFactory()) extends NoUIFactory {
            public function __construct(
                protected Component\Table\Factory $table_factory
            ) {
            }
            public function button(): Component\Button\Factory
            {
                return new I\Button\Factory();
            }
            public function dropdown(): Component\Dropdown\Factory
            {
                return new I\Dropdown\Factory();
            }
            public function symbol(): Component\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
            }
            public function table(): Component\Table\Factory
            {
                return $this->table_factory;
            }
            public function divider(): Component\Divider\Factory
            {
                return new I\Divider\Factory();
            }
        };
        return $factory;
    }
}
