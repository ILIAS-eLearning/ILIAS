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

require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class LinkTransferTest extends \ILIAS_UI_TestBase
{
    protected string $uri_stub_value;
    protected \ILIAS\Data\URI&Stub $uri_stub;
    protected string $glyph_stub_html;
    protected I\Symbol\Glyph\Glyph&Stub $glyph_stub;

    protected string $button_mock_html;
    protected I\Button\Standard&MockObject $button_mock;
    protected string $modal_mock_html;
    protected I\Modal\RoundTrip&MockObject $modal_mock;
    protected string $image_mock_html;
    protected I\Image\Image&MockObject $image_mock;

    protected I\Button\Factory&MockObject $button_factory_mock;
    protected I\Modal\Factory&MockObject $modal_factory_mock;
    protected I\Image\Factory&MockObject $image_factory_mock;

    protected \ILIAS\Refinery\Transformation&MockObject $to_svg_qr_code_mock;
    protected \ILIAS\Refinery\Transformation&MockObject $from_svg_mock;

    protected function setUp(): void
    {
        $this->uri_stub_value = sha1(\ILIAS\Data\URI::class);
        $this->uri_stub = $this->createStub(\ILIAS\Data\URI::class);
        $this->uri_stub->method('__toString')->willReturn($this->uri_stub_value);

        $this->glyph_stub_html = sha1(I\Symbol\Glyph\Glyph::class);
        $this->glyph_stub = $this->createStub(I\Symbol\Glyph\Glyph::class);
        $this->glyph_stub->method('getCanonicalName')->willReturn($this->glyph_stub_html);
        $this->glyph_stub->method('withLabel')->willReturnSelf();

        $this->button_mock_html = sha1(I\Button\Standard::class);
        $this->button_mock = $this->createMock(I\Button\Standard::class);
        $this->button_mock->method('getCanonicalName')->willReturn($this->button_mock_html);

        $this->modal_mock_html = sha1(I\Modal\RoundTrip::class);
        $this->modal_mock = $this->createMock(I\Modal\RoundTrip::class);
        $this->modal_mock->method('getCanonicalName')->willReturn($this->modal_mock_html);

        $this->image_mock_html = sha1(I\Image\Image::class);
        $this->image_mock = $this->createMock(I\Image\Image::class);
        $this->image_mock->method('getCanonicalName')->willReturn($this->image_mock_html);

        $this->button_factory_mock = $this->createMock(I\Button\Factory::class);
        $this->button_factory_mock->method('standard')->willReturn($this->button_mock);

        $this->modal_factory_mock = $this->createMock(I\Modal\Factory::class);
        $this->modal_factory_mock->method('roundtrip')->willReturn($this->modal_mock);

        $this->image_factory_mock = $this->createMock(I\Image\Factory::class);
        $this->image_factory_mock->method('responsive')->willReturn($this->image_mock);

        $this->to_svg_qr_code_mock = $this->createMock(\ILIAS\Refinery\Transformation::class);
        $this->from_svg_mock = $this->createMock(\ILIAS\Refinery\Transformation::class);

        parent::setUp();
    }

    public function getUIFactory(): \NoUIFactory
    {
        $glyph_factory_stub = $this->createStub(I\Symbol\Glyph\Factory::class);
        $glyph_factory_stub->method($this->anything())->willReturn($this->glyph_stub);
        $symbol_factory_stub = $this->createStub(I\Symbol\Factory::class);
        $symbol_factory_stub->method('glyph')->willReturn($glyph_factory_stub);

        $real_transfer_factory = new I\Transfer\Factory();

        return new class (
            $symbol_factory_stub,
            $this->button_factory_mock,
            $this->modal_factory_mock,
            $this->image_factory_mock,
            $real_transfer_factory,
        ) extends \NoUIFactory {
            public function __construct(
                protected I\Symbol\Factory $symbol_factory,
                protected I\Button\Factory $button_factory,
                protected I\Modal\Factory $modal_factory,
                protected I\Image\Factory $image_factory,
                protected I\Transfer\Factory $transfer_factory,
            ) {
            }
            public function symbol(): I\Symbol\Factory
            {
                return $this->symbol_factory;
            }
            public function button(): I\Button\Factory
            {
                return $this->button_factory;
            }
            public function modal(): I\Modal\Factory
            {
                return $this->modal_factory;
            }
            public function image(): I\Image\Factory
            {
                return $this->image_factory;
            }
            public function transfer(): I\Transfer\Factory
            {
                return $this->transfer_factory;
            }
        };
    }

    public function getRefinery(): \ILIAS\Refinery\Factory & Stub
    {
        $uri_group_stub = $this->createStub(\ILIAS\Refinery\URI\Group::class);
        $uri_group_stub->method('toSvgQrCode')->willReturn($this->to_svg_qr_code_mock);
        $uri_group_stub->method('fromSvg')->willReturn($this->from_svg_mock);

        $refinery_stub = $this->createStub(\ILIAS\Refinery\Factory::class);
        $refinery_stub->method('uri')->willReturn($uri_group_stub);

        return $refinery_stub;
    }

    public function testRenderWithClipboardTransferMechanism(): void
    {
        $link_label = sha1('testRenderWithClipboardTransferMechanism');
        $link_transfer = $this->getUIFactory()->transfer()->link(
            C\Transfer\TransferMechanism::CLIPBOARD,
            $this->uri_stub,
            $link_label,
        );

        $renderer = $this->getDefaultRenderer(null, [$this->glyph_stub]);

        $expected_transfer_type = C\Transfer\TransferMechanism::CLIPBOARD->value;
        $expected = <<<HTML
<div id="id_2" class="c-transfer c-transfer-link">
    <label for="id_2_value">$link_label</label>
    <div class="c-transfer__contents">
        <input id="id_2_value" class="c-transfer__payload visible" type="url" value="$this->uri_stub_value" readonly="readonly">
        <div class="c-tooltip__container">
            <button data-transfer-type="$expected_transfer_type" class="btn btn-default" aria-label="copy_to_clipboard_aria">
                <span data-transfer-status="default" class="visible">$this->glyph_stub_html</span>
                <span data-transfer-status="success" data-transfer-message="copy_to_clipboard_success" class="hidden">$this->glyph_stub_html</span>
                <span data-transfer-status="failure" data-transfer-message="copy_to_clipboard_failure" class="hidden">$this->glyph_stub_html</span>
                <span>copy_to_clipboard</span>
            </button>
            <div id="id_1" role="status" class="c-tooltip c-tooltip--hidden">
                <p></p>
            </div>
        </div>
    </div>
</div>
HTML;

        $actual = $renderer->render($link_transfer);

        $this->assertSame($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }

    public function testRenderWithWebShreTransferMechanism(): void
    {
        $link_label = sha1('testRenderWithWebShreTransferMechanism');
        $link_transfer = $this->getUIFactory()->transfer()->link(
            C\Transfer\TransferMechanism::WEB_SHARE,
            $this->uri_stub,
            $link_label,
        );

        $renderer = $this->getDefaultRenderer(null, [$this->glyph_stub]);

        $expected_transfer_type = C\Transfer\TransferMechanism::WEB_SHARE->value;
        $expected = <<<HTML
<div id="id_2" class="c-transfer c-transfer-link">
    <label for="id_2_value">$link_label</label>
    <div class="c-transfer__contents">
        <input id="id_2_value" class="c-transfer__payload visible" type="url" value="$this->uri_stub_value" readonly="readonly">
        <div class="c-tooltip__container">
            <button data-transfer-type="$expected_transfer_type" class="btn btn-default" aria-label="open_web_share_api_aria">
                <span data-transfer-status="default" class="visible">$this->glyph_stub_html</span>
                <span data-transfer-status="success" data-transfer-message="open_web_share_api_success" class="hidden">$this->glyph_stub_html</span>
                <span data-transfer-status="failure" data-transfer-message="open_web_share_api_failure" class="hidden">$this->glyph_stub_html</span>
                <span>open_web_share_api</span>
            </button>
            <div id="id_1" role="status" class="c-tooltip c-tooltip--hidden">
                <p></p>
            </div>
        </div>
    </div>
</div>
HTML;

        $actual = $renderer->render($link_transfer);

        $this->assertSame($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }

    public function testRenderWithQrCodeTransferMechanism(): void
    {
        $link_label = sha1('testRenderWithQrCodeTransferMechanism');

        // values passed along the transformation chain
        $svg_stub = $this->createStub(\ILIAS\Data\SVG::class);
        $svg_data_uri = 'data:image/svg+xml;base64,' . base64_encode($link_label);
        $show_signal = $this->createStub(C\Signal::class);

        // test url transformations
        $this->to_svg_qr_code_mock
            ->expects($this->once())
            ->method('transform')
            ->with($this->identicalTo($this->uri_stub))
            ->willReturn($svg_stub);
        $this->from_svg_mock
            ->expects($this->once())
            ->method('transform')
            ->with($this->identicalTo($svg_stub))
            ->willReturn($svg_data_uri);

        // test image compisition
        $this->image_factory_mock
            ->expects($this->once())
            ->method('responsive')
            ->with($svg_data_uri, $link_label);
        $this->image_mock
            ->expects($this->once())
            ->method('withAction')
            ->with($this->uri_stub_value)
            ->willReturnSelf();

        // test modal compisition
        $this->modal_factory_mock
            ->expects($this->once())
            ->method('roundtrip')
            ->with('use_qr_code', [$this->image_mock]);
        $this->modal_mock
            ->expects($this->once())
            ->method('withCancelButtonLabel')
            ->with('close')
            ->willReturnSelf();
        $this->modal_mock
            ->expects($this->once())
            ->method('withCloseWithKeyboard')
            ->with(true)
            ->willReturnSelf();
        $this->modal_mock
            ->expects($this->once())
            ->method('getShowSignal')
            ->willReturn($show_signal);

        // test button compisition
        $this->button_factory_mock
            ->expects($this->once())
            ->method('standard')
            ->with('show_qr_code', '');
        $this->button_mock
            ->expects($this->once())
            ->method('withAriaLabel')
            ->with('show_qr_code_aria')
            ->willReturnSelf();
        $this->button_mock
            ->expects($this->once())
            ->method('withSymbol')
            ->with($this->identicalTo($this->glyph_stub))
            ->willReturnSelf();
        $this->button_mock
            ->expects($this->once())
            ->method('withOnClick')
            ->with($this->identicalTo($show_signal))
            ->willReturnSelf();

        $link_transfer = $this->getUIFactory()->transfer()->link(
            C\Transfer\TransferMechanism::QR_CODE,
            $this->uri_stub,
            $link_label,
        );

        // only the button and modal are rendered directly; the image and glyph
        // are consumed by mocked components and never reach the renderer.
        $renderer = $this->getDefaultRenderer(null, [$this->button_mock, $this->modal_mock]);

        $expected = <<<HTML
<div id="id_1" class="c-transfer c-transfer-link">
    <label for="id_1_value">$link_label</label>
    <div class="c-transfer__contents">
        <input id="id_1_value" class="c-transfer__payload visible" type="url" value="$this->uri_stub_value" readonly="readonly">
        $this->button_mock_html{$this->modal_mock_html}
    </div>
</div>
HTML;

        $actual = $renderer->render($link_transfer);

        $this->assertSame($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }

    public function testWithAdditionalTransferMechanismOrderPreservation(): void
    {
        $initial = C\Transfer\TransferMechanism::CLIPBOARD;
        $first = C\Transfer\TransferMechanism::WEB_SHARE;
        $second = C\Transfer\TransferMechanism::QR_CODE;

        $link_transfer = $this->getUIFactory()->transfer()->link($initial, $this->uri_stub, '');
        $this->assertSame([$initial], array_values($link_transfer->getTransferMechanisms()));

        $link_transfer = $link_transfer->withAdditionalTransferMechanism($first);
        $this->assertSame([$initial, $first], array_values($link_transfer->getTransferMechanisms()));

        $link_transfer = $link_transfer->withAdditionalTransferMechanism($second);
        $this->assertSame([$initial, $first, $second], array_values($link_transfer->getTransferMechanisms()));

        // should not alter the previous ordering
        $link_transfer = $link_transfer->withAdditionalTransferMechanism($second);
        $link_transfer = $link_transfer->withAdditionalTransferMechanism($first);

        $this->assertSame([$initial, $first, $second], array_values($link_transfer->getTransferMechanisms()));
    }
}
