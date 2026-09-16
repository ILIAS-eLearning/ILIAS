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
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Transfer;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Component\Transfer\HasAdditionalTransferMechanism;
use ILIAS\UI\Component\Transfer\TransferMechanism;
use ILIAS\UI\Component\Transfer\Transfer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\Data\SVG;
use ILIAS\Data\URI;

class Renderer extends AbstractComponentRenderer
{
    public function registerResources(ResourceRegistry $registry): void
    {
        $registry->register('assets/js/transfer.min.js');
    }

    public function render(Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Link) {
            return $this->renderLink($component, $default_renderer);
        }
        $this->cannotHandleComponent($component);
    }

    protected function renderLink(Link $component, RendererInterface $default_renderer): string
    {
        $template = $this->getTemplate('tpl.link.html', true, true);

        $template->setVariable('URL', $component->getUrl());

        if ('' !== $component->getLabel()) {
            $template->setVariable('LABEL', $component->getLabel());
        }

        $is_primary_transfer_mechanism = true;
        foreach ($component->getTransferMechanisms() as $transfer_mechanism) {
            $transfer_mechanism_html = match ($transfer_mechanism) {
                TransferMechanism::CLIPBOARD => $this->renderClipboardTransferMechanism($default_renderer, $is_primary_transfer_mechanism),
                TransferMechanism::WEB_SHARE => $this->renderWebShareTransferMechanism($default_renderer, $is_primary_transfer_mechanism),
                TransferMechanism::QR_CODE => $this->renderQrCodeTransferMechanism(
                    $default_renderer,
                    $is_primary_transfer_mechanism,
                    $this->getUriTransformations()->toSvgQrCode()->transform($component->getUrl()),
                    $component->getUrl(),
                ),
            };
            $template->setCurrentBlock('with_transfer_mechanism');
            $template->setVariable('TRANSFER_MECHANISM', $transfer_mechanism_html);
            $template->parseCurrentBlock();
            $is_primary_transfer_mechanism = false;
        }

        $enriched_component = $component->withAdditionalOnLoadCode(static fn($id) => "
            il.UI.Transfer.createLinkTransfer('$id');
        ");

        $id = $this->bindJavaScript($enriched_component);
        $template->setVariable('ID', $id);

        return $template->get();
    }

    protected function renderTransferButton(
        RendererInterface $default_renderer,
        Glyph $default_glyph,
        string $transfer_type,
        string $default_message,
        string $success_message,
        string $failure_message,
        bool $is_message_visible,
    ): string {
        $template = $this->getTemplate('tpl.transfer_button.html', true, true);

        $template->setVariable('TRANSFER_TYPE', $transfer_type);

        $template->setVariable('DEFAULT_MESSAGE', $default_message);
        $template->setVariable('SUCCESS_MESSAGE', $success_message);
        $template->setVariable('FAILURE_MESSAGE', $failure_message);

        if (!$is_message_visible) {
            $template->setVariable('MESSAGE_VISIBILITY', 'sr-only');
        }

        $template->setVariable('DEFAULT_GLYPH', $default_renderer->render($default_glyph->withLabel('')));
        $template->setVariable('SUCCESS_GLYPH', $default_renderer->render(
            $this->getUIFactory()->symbol()->glyph()->apply()->withLabel(''),
        ));
        $template->setVariable('FAILURE_GLYPH', $default_renderer->render(
            $this->getUIFactory()->symbol()->glyph()->close()->withLabel(''),
        ));

        return $template->get();
    }

    protected function renderClipboardTransferMechanism(RendererInterface $default_renderer, bool $is_primary_transfer_mechanism): string
    {
        return $this->renderTransferButton(
            $default_renderer,
            $this->getUIFactory()->symbol()->glyph()->copy(),
            TransferMechanism::CLIPBOARD->value,
            $this->txt('copy_to_clipboard'),
            $this->txt('copy_to_clipboard_success'),
            $this->txt('copy_to_clipboard_failure'),
            $is_primary_transfer_mechanism,
        );
    }

    protected function renderWebShareTransferMechanism(RendererInterface $default_renderer, bool $is_primary_transfer_mechanism): string
    {
        return $this->renderTransferButton(
            $default_renderer,
            $this->getUIFactory()->symbol()->glyph()->share(),
            TransferMechanism::WEB_SHARE->value,
            $this->txt('open_web_share_api'),
            $this->txt('open_web_share_api_success'),
            $this->txt('open_web_share_api_failure'),
            $is_primary_transfer_mechanism,
        );
    }

    /**
     * Note that we only support URI data to be transfered using a QR-code.
     * We do not support arbitrary binary data or other unknown formats for
     * the foreseeable future.
     */
    protected function renderQrCodeTransferMechanism(
        RendererInterface $default_renderer,
        bool $is_primary_transfer_mechanism,
        SVG $qr_code_svg,
        URI $qr_code_url,
    ): string {
        $factory = $this->getUIFactory();
        $svg_data_uri = $this->getUriTransformations()->fromSvg()->transform($qr_code_svg);
        $image = $factory->image()->responsive($svg_data_uri, '')->withAction((string) $qr_code_url);

        $modal = $factory
            ->modal()
            ->roundtrip($this->txt('use_qr_code'), [$image])
            ->withCancelButtonLabel($this->txt('close'))
            ->withCloseWithKeyboard(true);

        $button_label = '';
        if ($is_primary_transfer_mechanism) {
            $button_label = $this->txt('show_qr_code');
        }

        $modal_button = $factory
            ->button()
            ->standard($button_label, '')
            ->withSymbol($factory->symbol()->glyph()->qrCode())
            ->withOnClick($modal->getShowSignal());

        return $default_renderer->render([$modal_button, $modal]);
    }
}
