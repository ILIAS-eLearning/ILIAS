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

namespace ILIAS\Test\Tests\Results\Presentation;

use ILIAS\Test\Results\Presentation\Factory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPService;
use ILIAS\Data\Factory as DataFactory;

class FactoryTest extends \ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestResultsPresentationFactory = new Factory(
            $this->createMock(UIFactory::class),
            $this->createMock(UIRenderer::class),
            $this->createMock(Refinery::class),
            $this->createMock(DataFactory::class),
            $this->createMock(HTTPService::class),
            $this->createMock(\ilLanguage::class)
        );
        $this->assertInstanceOf(Factory::class, $ilTestResultsPresentationFactory);
    }
}
