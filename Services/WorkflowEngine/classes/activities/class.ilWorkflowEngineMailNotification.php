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
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup ServicesWorkflowEngine
 */
class ilWorkflowEngineMailNotification extends ilMailNotification
{
    protected string $subject_text = '';
    protected string $body_text = '';

    public function getSubjectText(): string
    {
        return $this->subject_text;
    }

    public function setSubjectText(string $subject_text): void
    {
        $this->subject_text = $subject_text;
    }

    public function getBodyText(): string
    {
        return $this->body_text;
    }

    public function setBodyText(string $body_text): void
    {
        $this->body_text = $body_text;
    }

    public function send(int $rcp): void
    {
        $this->initLanguage($rcp);
        $this->initMail();

        $this->setSubject($this->subject_text);
        $this->setBody($this->body_text);
        $this->getMail()->appendInstallationSignature(true);

        $this->sendMail([$rcp], false);
    }
}
