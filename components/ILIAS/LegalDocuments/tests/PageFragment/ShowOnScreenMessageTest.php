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

namespace ILIAS\LegalDocuments\test\PageFragment;

use ILIAS\UI\Renderer;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\PageFragment;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\PageFragment\ShowOnScreenMessage;
use ilGlobalTemplateInterface;

require_once __DIR__ . '/../ContainerMock.php';

class ShowOnScreenMessageTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ShowOnScreenMessage::class, new ShowOnScreenMessage($this->mock(PageFragment::class), 'foo', 'bar', false));
    }

    public function testRender(): void
    {
        $template = $this->mock(ilGlobalTemplateInterface::class);
        $template->expects(self::once())->method('setOnScreenMessage')->with('foo', 'bar', true);
        $renderer = $this->mock(Renderer::class);
        $page = $this->mockMethod(PageFragment::class, 'render', [$template, $renderer], 'rendered');

        $instance = new ShowOnScreenMessage($page, 'foo', 'bar', true);
        $this->assertSame('rendered', $instance->render($template, $renderer));
    }
}
