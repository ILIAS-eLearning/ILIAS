<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/Mail/classes/class.ilMailNotification.php';

/**
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup ServicesWorkflowEngine
 */
class ilWorkflowEngineMailNotification extends ilMailNotification
{
    /** @var string $subject_text */
    protected string $subject_text;

    /** @var string $body_text */
    protected string $body_text;

    /**
     * @return string
     */
    public function getSubjectText() : string
    {
        return $this->subject_text;
    }

    /**
     * @param string $subject_text
     */
    public function setSubjectText(string $subject_text) : void
    {
        $this->subject_text = $subject_text;
    }

    /**
     * @return string
     */
    public function getBodyText() : string
    {
        return $this->body_text;
    }

    /**
     * @param string $body_text
     */
    public function setBodyText(string $body_text) : void
    {
        $this->body_text = $body_text;
    }

    public function send($rcp) : void
    {
        $this->initLanguage($rcp);
        $this->initMail();

        $this->setSubject($this->subject_text);
        $this->setBody($this->body_text);
        $this->getMail()->appendInstallationSignature(true);

        $this->sendMail(array($rcp), false);
    }
}
