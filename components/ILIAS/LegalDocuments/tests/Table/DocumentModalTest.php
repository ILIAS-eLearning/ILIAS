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

namespace ILIAS\LegalDocuments\test\Table;

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Button\Factory as ButtonFactory;
use ILIAS\UI\Component\Modal\LightboxTextPage;
use ILIAS\UI\Component\Modal\Lightbox;
use ILIAS\UI\Component\Modal\Factory as ModalFactory;
use ILIAS\UI\Component\Component;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\UI\Renderer;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\DI\UIServices;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Table\DocumentModal;

require_once __DIR__ . '/../ContainerMock.php';

class DocumentModalTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(DocumentModal::class, new DocumentModal($this->mock(UIServices::class), $this->fail(...)));
    }

    public function testCreate(): void
    {
        $content = $this->mockTree(DocumentContent::class, ['title' => 'foo']);
        $component = $this->mock(Component::class);
        $signal = $this->mock(Signal::class);
        $modal_component = $this->mockTree(Lightbox::class, ['getShowSignal' => $signal]);
        $button_component = $this->mock(Shy::class);
        $button_component->expects(self::once())->method('withOnClick')->with($signal)->willReturn($button_component);

        $text_page = $this->mock(LightboxTextPage::class);

        $modal = $this->mock(ModalFactory::class);
        $modal->expects(self::once())->method('lightboxTextPage')->with('rendered', 'foo')->willReturn($text_page);
        $modal->expects(self::once())->method('lightbox')->with([$text_page])->willReturn($modal_component);

        $instance = new DocumentModal($this->mockTree(UIServices::class, [
            'renderer' => $this->mockMethod(Renderer::class, 'render', [$component], 'rendered'),
            'factory' => [
                'modal' => $modal,
                'button' => $this->mockMethod(ButtonFactory::class, 'shy', ['foo', ''], $button_component),
            ],
        ]), function (DocumentContent $c) use ($content, $component): Component {
            $this->assertSame($content, $c);
            return $component;
        });

        $this->assertSame([$button_component, $modal_component], $instance->create($content));
    }
}
