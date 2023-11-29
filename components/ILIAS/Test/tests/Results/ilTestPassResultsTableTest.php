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

namespace Results;

use ilTestBaseTestCase;
use ilTestPassResultsTable;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPService;
use ILIAS\Data\Factory as DataFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class ilTestPassResultsTableTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestPassResultsTable = new ilTestPassResultsTable(
            $this->createMock(UIFactory::class),
            $this->createMock(UIRenderer::class),
            $this->createMock(Refinery::class),
            $this->createConfiguredMock(HTTPService::class, [
                'request' => $this->createConfiguredMock(RequestInterface::class, [
                    'getUri' => $this->createConfiguredMock(UriInterface::class, [
                        '__toString' => ''
                    ]),
                ]),
            ]),
            $this->createMock(DataFactory::class),
            $this->createMock(\ilLanguage::class),
            $this->createMock(\ilTestPassResult::class),
            '',
        );
        $this->assertInstanceOf(ilTestPassResultsTable::class, $ilTestPassResultsTable);
    }
}