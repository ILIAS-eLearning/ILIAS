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

namespace ILIAS\LegalDocuments\ConsumerToolbox;

use ilMimeMailNotification;
use ilMailException;
use ilMail;

class Mail extends ilMimeMailNotification
{
    public function __construct()
    {
        parent::__construct(false);
    }

    public function sendGeneric(string $subject, string $body): void
    {
        foreach ($this->getRecipients() as $rcp) {
            try {
                $this->handleCurrentRecipient($rcp);
            } catch (ilMailException) {
                continue;
            }

            if (!$this->getCurrentRecipient()) {
                continue;
            }

            $this->initMimeMail();
            $this->initLanguageByIso2Code();

            $this->setSubject($subject);
            $this->appendBody($body);
            $this->appendBody(ilMail::_getInstallationSignature());

            $this->sendMimeMail($this->getCurrentRecipient());
        }
    }
}
