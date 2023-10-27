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

namespace ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots;

use ILIAS\UI\Component\Component;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\Provide;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\ShowOnLoginPage;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../ContainerMock.php';

class ShowOnLoginPageTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ShowOnLoginPage::class, new ShowOnLoginPage($this->mock(Provide::class), $this->mock(UI::class)));
    }

    public function testInvokeWithoutDocuments(): void
    {
        $instance = new ShowOnLoginPage($this->mockTree(Provide::class, [
            'document' => ['repository' => ['countAll' => 0]],
        ]), $this->mock(UI::class));

        $this->assertSame([], $instance());
    }

    public function testInvoke(): void
    {
        $instance = new ShowOnLoginPage($this->mockTree(Provide::class, [
            'document' => ['repository' => ['countAll' => 8]],
        ]), $this->mock(UI::class));

        $array = $instance();

        $this->assertSame(1, count($array));
        $this->assertInstanceOf(Component::class, $array[0]);
    }
}
