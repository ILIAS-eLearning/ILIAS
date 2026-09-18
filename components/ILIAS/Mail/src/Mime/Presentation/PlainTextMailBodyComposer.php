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

use ILIAS\Mail\Mime\Presentation\Asset\InlineImages;

/**
 * Turns a mail body into plain text, for installations with HTML mails disabled
 * and for the alternative part of a multipart mail.
 */
final class PlainTextMailBodyComposer implements MailBodyComposer
{
    /** @var list<string> */
    private const array LINE_BREAKS = ['<br />', '<br>', '<br/>'];

    public function compose(MailBodySource $source): ComposedMailBody
    {
        $without_breaks = str_ireplace(self::LINE_BREAKS, "\n", $source->raw());
        $plain_text = html_entity_decode(strip_tags($without_breaks), ENT_QUOTES);

        return new ComposedMailBody($plain_text, null, InlineImages::none());
    }
}
