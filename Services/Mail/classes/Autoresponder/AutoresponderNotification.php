<?php

declare(strict_types=1);

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

namespace ILIAS\Mail\Autoresponder;

use ilMailNotification;
use ilMailOptions;
use ilDatePresentation;
use DateTimeImmutable;
use ilDateTime;

class AutoresponderNotification extends ilMailNotification
{
    private ilMailOptions $sender_mail_options;
    private DateTimeImmutable $next_auto_responder_datetime;

    public function __construct(
        ilMailOptions $sender_mail_options,
        int $receiver_id,
        DateTimeImmutable $next_auto_responder_datetime
    ) {
        $this->sender_mail_options = $sender_mail_options;
        $this->next_auto_responder_datetime = $next_auto_responder_datetime;

        parent::__construct();

        $this->setSender($sender_mail_options->getUsrId());
        $this->setRecipients([$receiver_id]);
    }

    public function send(): bool
    {
        $use_relative_dates = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        foreach ($this->getRecipients() as $recipient) {
            $this->initLanguage($recipient);
            $this->initMail();

            $this->getMail()->setSaveInSentbox(false);
            $former_language = ilDatePresentation::getLanguage();
            ilDatePresentation::setLanguage($this->getLanguage());

            $this->setSubject($this->sender_mail_options->getAbsenceAutoresponderSubject());

            $this->setBody($this->sender_mail_options->getAbsenceAutoresponderBody());
            $this->appendBody("\n");
            $this->appendBody($this->sender_mail_options->getSignature());
            $this->appendBody("\n\n");
            $this->appendBody(
                str_ireplace(
                    [
                        '[NEXT_AUTO_RESPONDER_DATETIME]'
                    ],
                    [
                        ilDatePresentation::formatDate(
                            new ilDateTime($this->next_auto_responder_datetime->getTimestamp(), IL_CAL_UNIX)
                        )
                    ],
                    $this->getLanguageText('mail_absence_auto_responder_body_hint')
                )
            );

            $this->sendMail([$recipient]);

            ilDatePresentation::setLanguage($former_language);
        }

        ilDatePresentation::setUseRelativeDates($use_relative_dates);

        return true;
    }
}
