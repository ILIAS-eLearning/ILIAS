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
    protected $subject_text;

    /** @var string $body_text */
    protected $body_text;

    /**
     * @return string
     */
    public function getSubjectText()
    {
        return $this->subject_text;
    }

    /**
     * @param string $subject_text
     */
    public function setSubjectText($subject_text)
    {
        $this->subject_text = $subject_text;
    }

    /**
     * @return string
     */
    public function getBodyText()
    {
        return $this->body_text;
    }

    /**
     * @param string $body_text
     */
    public function setBodyText($body_text)
    {
        $this->body_text = $body_text;
    }

    public function send($rcp)
    {
        $this->initLanguage($rcp);
        $this->initMail();

        $this->setSubject($this->subject_text);
        $this->setBody($this->body_text);
        $this->getMail()->appendInstallationSignature(true);

        $this->sendMail(array($rcp), array('system'), false);
    }
}
