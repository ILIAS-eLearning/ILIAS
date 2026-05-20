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
 * Renders Transfer components inside the Footer MainControl differently.
 *
 * The label is omitted and the payload is visually hidden, because the
 * footer region already provides context. Users were confused by the
 * permanent-link in particular, because the copied URL differs from the
 * one shown in the browser.
 *
 * This is a reminder that we want proper routing inside ILIAS:
 * https://docu.ilias.de/go/wiki/wpage_8780_1357
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class FooterContextRenderer extends Renderer
{
    protected function renderLabel(Transfer $component, Template $template, RendererInterface $default_renderer): void
    {
        // omit label in Footer MainControl because region provides context
    }

    protected function renderUrlPayload(Transfer $component, Template $template, RendererInterface $default_renderer): void
    {
        $template->setVariable('URL', $component->getUrl());
        $template->setVariable('PAYLOAD_VISIBILITY', self::VISIBILITY_HIDDEN);
    }
}
