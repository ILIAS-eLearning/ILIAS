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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMMailNotification extends ilMailNotification
{
    public const TYPE_USER_BLOCKED = 10;
    protected int $question_id = 0;

    protected ilObjUser $user;

    public function __construct(
        bool $a_is_personal_workspace = false
    ) {
        global $DIC;
        parent::__construct($a_is_personal_workspace);
        $this->user = $DIC->user();
    }

    public function setQuestionId(int $a_val): void
    {
        $this->question_id = $a_val;
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }

    public function send(): bool
    {
        $ilUser = $this->user;

        switch ($this->getType()) {
            case self::TYPE_USER_BLOCKED:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('cont_user_blocked'),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('cont_user_blocked2')
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('cont_user_blocked3') . " '" . $this->getLanguageText('objs_qst') . "' > '" . $this->getLanguageText('cont_blocked_users') . "'"
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('obj_lm') . ": " . $this->getObjectTitle(true)
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('user') . ": " . ilUserUtil::getNamePresentation($ilUser->getId(), false, false, "")
                    );
                    $this->appendBody("\n");

                    $this->appendBody(
                        $this->getLanguageText('question') . ": " . assQuestion::_getTitle($this->getQuestionId())
                    );
                    $this->appendBody("\n");
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('cont_lm_mail_permanent_link'));
                    $this->appendBody("\n");
                    $this->appendBody($this->createPermanentLink(array(), ""));
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp));
                }
                break;

        }
        return true;
    }

    protected function initLanguage(int $a_usr_id): void
    {
        parent::initLanguage($a_usr_id);
        $this->getLanguage()->loadLanguageModule('content');
    }
}
