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

namespace ILIAS\Mail\Object;

final class Mb3SafeMailEncoder implements MailPayloadEncoder
{
    public function __construct(
        private readonly MailPayloadEncoder $inner,
        private readonly \ILIAS\Mail\Transformation\Utf8Mb4Sanitizer $trafo
    ) {
    }

    public function encode(array $mails): string
    {
        $sanitized = [];
        foreach ($mails as $m) {
            $sanitized[] = new \ilMailValueObject(
                $m->getFrom(),
                $m->getRecipients(),
                $m->getRecipientsCC(),
                $m->getRecipientsBCC(),
                $this->trafo->transform($m->getSubject()),
                $this->trafo->transform($m->getBody()),
                $m->getAttachments(),
                $m->isUsingPlaceholders(),
                $m->shouldSaveInSentBox()
            );
        }

        return $this->inner->encode($sanitized);
    }
}
