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
use ilTestResultsFactory;
use ilTestShuffler;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

class ilTestResultsFactoryTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestResultsFactoryTest = new ilTestResultsFactory(
            $this->createMock(ilTestShuffler::class),
            $this->createMock(UIFactory::class),
            $this->createMock(UIRenderer::class)
        );
        $this->assertInstanceOf(ilTestResultsFactory::class, $ilTestResultsFactoryTest);
    }
}
