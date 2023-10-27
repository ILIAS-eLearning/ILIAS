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

use ILIAS\LegalDocuments\PageFragment\ShowOnScreenMessage;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\PageFragment\PageContent;
use ilGlobalTemplateInterface;

require_once __DIR__ . '/../ContainerMock.php';

class PageContentTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(PageContent::class, new PageContent('foo', []));
    }

    public function testRender(): void
    {
        $template = $this->mock(ilGlobalTemplateInterface::class);
        $template->expects(self::once())->method('setTitle')->with('foo');
        $components = [$this->mock(Component::class), $this->mock(Component::class)];
        $renderer = $this->mockMethod(Renderer::class, 'render', [$components], 'rendered');

        $instance = new PageContent('foo', $components);
        $this->assertSame('rendered', $instance->render($template, $renderer));
    }

    public function testWithOnScreenMessage(): void
    {
        $instance = new PageContent('foo', []);
        $this->assertInstanceOf(ShowOnScreenMessage::class, $instance->withOnScreenMessage('foo', 'bar', true));
    }
}
