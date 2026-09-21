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

namespace ILIAS\Mail\Mime\Presentation;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Mail\Mime\Presentation\Asset\MailAssets;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;

/**
 * Composes the HTML representation of a mail body, and the plain text alternative
 * that goes with it.
 *
 * Knows nothing about recipients, transports or where assets are stored.
 */
final class HtmlMailBodyComposer implements MailBodyComposer
{
    private const string BLOCK_ELEMENT_PATTERN = '~</?(p|br|div|ul|ol|li|code|pre|h[1-6])\b~i';
    private const string INLINE_TAGS = '<b><u><i><a>';

    public function __construct(
        private readonly MailAssets $assets,
        private readonly PlainTextMailBodyComposer $plain_text,
        private readonly UIFactory $ui,
        private readonly Renderer $renderer,
        private readonly Refinery $refinery,
        private readonly DataFactory $data,
        private readonly string $installation_title,
        private readonly string $installation_url
    ) {
    }

    public function compose(MailBodySource $source): ComposedMailBody
    {
        $html = $source->transformedToHtml();
        $html = $this->containsHtml($html) ? $html : nl2br($html);
        $html = $this->refinery->string()->makeClickable()->transform($html);

        $images = $this->assets->inlineImages();
        $logo = $images->logo();
        $page = $this->ui->layout()->page()->mail(
            $this->assets->styleSheet()->path(),
            'cid:' . ($logo?->cid() ?? ''),
            $this->installation_title,
            $this->ui->legacy()->content($html),
            $this->data->link($this->installation_url, $this->data->uri($this->installation_url))
        );
        $plain_text = $this->plain_text->compose(new MailBodySource($html));

        return new ComposedMailBody(
            $this->renderer->render($page),
            $plain_text->body(),
            $images
        );
    }

    private function containsHtml(string $email_body): bool
    {
        if (!str_contains($email_body, '<') || !str_contains($email_body, '>')) {
            return false;
        }

        if (preg_match(self::BLOCK_ELEMENT_PATTERN, $email_body) === 1) {
            return true;
        }

        return strip_tags($email_body, self::INLINE_TAGS) !== $email_body;
    }
}
