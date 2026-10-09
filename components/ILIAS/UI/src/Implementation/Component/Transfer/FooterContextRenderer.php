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

use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component\Transfer\TransferMechanism;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class FooterContextRenderer extends Renderer
{
    protected function renderClipboardTransferMechanism(RendererInterface $default_renderer, bool $is_primary_transfer_mechanism): string
    {
        return $this->renderTransferButton(
            $default_renderer,
            $this->getUIFactory()->symbol()->glyph()->copy(),
            TransferMechanism::CLIPBOARD->value,
            $this->txt('copy_to_clipboard_footer'),
            $this->txt('copy_to_clipboard_aria'),
            $this->txt('copy_to_clipboard_success'),
            $this->txt('copy_to_clipboard_failure'),
        );
    }

    protected function renderWebShareTransferMechanism(RendererInterface $default_renderer, bool $is_primary_transfer_mechanism): string
    {
        return $this->renderTransferButton(
            $default_renderer,
            $this->getUIFactory()->symbol()->glyph()->share(),
            TransferMechanism::WEB_SHARE->value,
            $this->txt('open_web_share_api_footer'),
            $this->txt('open_web_share_api_aria'),
            $this->txt('open_web_share_api_success'),
            $this->txt('open_web_share_api_failure'),
        );
    }

    protected function applyPayloadVisibility(Transfer $component, Template $template): void
    {
        $template->setVariable('VISIBILITY', self::PAYLOAD_SCREEN_READER);
    }
}
