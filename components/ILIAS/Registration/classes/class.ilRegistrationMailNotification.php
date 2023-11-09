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


/**
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesRegistration
 */
class ilRegistrationMailNotification extends ilMailNotification
{
    public const TYPE_NOTIFICATION_APPROVERS = 30;
    public const TYPE_NOTIFICATION_CONFIRMATION = 31;

    /**
     * Parse and send mail
     * @return
     */
    public function send(): void
    {
        switch ($this->getType()) {
            case self::TYPE_NOTIFICATION_APPROVERS:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        $this->getLanguageText('reg_mail_new_user')
                    );

                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");

                    $this->appendBody($this->getLanguageText('reg_mail_new_user_body'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('reg_mail_body_profile'));

                    $info = $this->getAdditionalInformation();

                    $this->appendBody("\n\n");
                    $this->appendBody($info['usr']->getProfileAsString($this->getLanguage()));

                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('reg_mail_body_reason'));

                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail([$rcp]);
                }
                break;

            case self::TYPE_NOTIFICATION_CONFIRMATION:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        $this->getLanguageText('reg_mail_new_user_confirmation')
                    );

                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");

                    $this->appendBody($this->getLanguageText('reg_mail_new_user_body'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('reg_mail_body_profile'));

                    $info = $this->getAdditionalInformation();

                    $this->appendBody("\n\n");
                    $this->appendBody($info['usr']->getProfileAsString($this->getLanguage()));

                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('reg_mail_body_confirmation'));
                    $this->appendBody("\n"); // #4527
                    $this->appendBody(ilLink::_getStaticLink($info['usr']->getId(), "usrf"));

                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('reg_mail_body_reason'));

                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail([$rcp]);
                }
                break;
        }
    }

    protected function initLanguage(int $a_usr_id): void
    {
        parent::initLanguage($a_usr_id);
        $this->getLanguage()->loadLanguageModule('registration');
    }
}
