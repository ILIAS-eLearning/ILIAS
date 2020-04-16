<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailNotification.php';

/**
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesExercise
 */
class ilExerciseMailNotification extends ilMailNotification
{
    /**
     * @var ilObjUser
     */
    protected $user;

    const TYPE_FEEDBACK_FILE_ADDED = 20;
    const TYPE_SUBMISSION_UPLOAD = 30;
    const TYPE_FEEDBACK_TEXT_ADDED = 40;

    /**
     *
     */
    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        parent::__construct();
    }
    
    /**
     * Set assignment id
     *
     * @param	int		assignment id
     */
    public function setAssignmentId($a_val)
    {
        $this->ass_id = $a_val;
    }
    
    /**
     * Get assignment id
     *
     * @return	int		assignment id
     */
    public function getAssignmentId()
    {
        return $this->ass_id;
    }
    
    /**
     * Send notifications
     * @return
     */
    public function send()
    {
        $ilUser = $this->user;
        // parent::send();
        
        include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
        
        switch ($this->getType()) {
            case self::TYPE_FEEDBACK_FILE_ADDED:
                
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('exc_msg_new_feedback_file_uploaded'),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_msg_new_feedback_file_uploaded2')
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('obj_exc') . ": " . $this->getObjectTitle(true)
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_assignment') . ": " .
                        ilExAssignment::lookupTitle($this->getAssignmentId())
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('exc_mail_permanent_link'));
                    $this->appendBody("\n");
                    $this->appendBody($this->createPermanentLink(array(), '_' . $this->getAssignmentId()) .
                        '#fb' . $this->getAssignmentId());
                    $this->getMail()->appendInstallationSignature(true);
                                        
                    $this->sendMail(array($rcp), array('system'));
                }
                break;

            case self::TYPE_SUBMISSION_UPLOAD:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('exc_submission_notification_subject'),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('exc_submission_notification_body'), $this->getObjectTitle(true))
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_assignment') . ": " .
                        ilExAssignment::lookupTitle($this->getAssignmentId())
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('user') . ": " .
                        $ilUser->getFullName()
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody(sprintf(
                        $this->getLanguageText('exc_submission_notification_link'),
                        $this->createPermanentLink()
                    ));

                    if (ilExAssignment::lookupType($this->getAssignmentId()) == ilExAssignment::TYPE_UPLOAD) {
                        $this->appendBody("\n\n");

                        //new files uploaded
                        $assignment = new ilExAssignment($this->getAssignmentId());
                        $submission = new ilExSubmission($assignment, $ilUser->getId());

                        // since mails are sent immediately after upload the files should always be new
                        //if($submission->lookupNewFiles($submission->getTutor()))
                        //{
                        $this->appendBody(sprintf(
                            $this->getLanguageText('exc_submission_downloads_notification_link'),
                            $this->createPermanentLink(array(), "_" . $this->getAssignmentId() . "_" . $ilUser->getId() . "_setdownload")
                        ));
                        //}
                        //else
                        //{
                        //	$this->appendBody(sprintf($this->getLanguageText('exc_submission_downloads_notification_link'),
                        //		$this->getLanguageText("exc_submission_no_new_files")));
                        //}
                    }

                    $this->appendBody("\n\n");
                    $this->appendBody(sprintf(
                        $this->getLanguageText('exc_submission_and_grades_notification_link'),
                        $this->createPermanentLink(array(), "_" . $this->getAssignmentId() . "_grades")
                    ));

                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp), array('system'));
                }
                break;
                
            case self::TYPE_FEEDBACK_TEXT_ADDED:
                
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('exc_msg_new_feedback_text_uploaded'),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_msg_new_feedback_text_uploaded2')
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('obj_exc') . ": " . $this->getObjectTitle(true)
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_assignment') . ": " .
                        ilExAssignment::lookupTitle($this->getAssignmentId())
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('exc_mail_permanent_link'));
                    $this->appendBody("\n");
                    $this->appendBody($this->createPermanentLink(array(), '_' . $this->getAssignmentId()) .
                        '#fb' . $this->getAssignmentId());
                    $this->getMail()->appendInstallationSignature(true);
                                        
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
        }
        return true;
    }
    
    /**
     * Add language module exc
     * @param object $a_usr_id
     * @return
     */
    protected function initLanguage($a_usr_id)
    {
        parent::initLanguage($a_usr_id);
        $this->getLanguage()->loadLanguageModule('exc');
    }
}
