<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/class.ilMailNotification.php';


class ilTestManScoringParticipantNotification extends ilMailNotification
{
	private $userId = null;
	private $questionGuiList = null;
	private $notificationData = null;
	
	public function __construct($userId, $testRefId)
	{
		parent::__construct();

		$this->setRecipient($userId);
		$this->setRefId($testRefId);
		
		$this->initLanguage( $this->getRecipient() );
		$this->getLanguage()->loadLanguageModule('assessment');

		$this->initMail()->enableSoap(false);
	}
	
	public function send()
	{
		$this->buildSubject();
		
		$this->buildBody();

		$this->sendMail(
				$this->getRecipients(), array('system')
		);
	}
	
	private function buildSubject()
	{
		$info = $this->getAdditionalInformation();
		
		$this->setSubject( sprintf($this->getLanguageText('tst_notify_manscoring_done_body_msg_subject'), $info['test_title']) );
	}
	
	private function buildBody()
	{
		//	Salutation
		
		$this->setBody(
				ilMail::getSalutation( $this->getRecipient(), $this->getLanguage() )
		);
		$this->appendBody("\n\n");

		//	Message (What has happened?)

		$this->appendBody( $this->getLanguageText('tst_notify_manscoring_done_body_msg_topic') );
		$this->appendBody("\n\n");
		
		$info = $this->getAdditionalInformation();
		
		$this->appendBody( $this->getLanguageText('obj_tst').': '.$info['test_title'] );
		$this->appendBody("\n");
		$this->appendBody( $this->getLanguageText('pass').': '.$info['test_pass'] );
		$this->appendBody("\n\n");
		
		foreach($info['questions_gui_list'] as $questionId => $questionGui)
		{
			$points = $info['questions_scoring_data'][$questionId]['points'];
			$feedback = $info['questions_scoring_data'][$questionId]['feedback'];
			
			$feedback = $this->convertFeedbackForMail($feedback);
			
			$this->appendBody( $this->getLanguageText('tst_question').': '.$questionGui->object->getTitle() );
			$this->appendBody("\n");
			$this->appendBody( $this->getLanguageText('tst_reached_points').': '.$points );
			$this->appendBody("\n");
			$this->appendBody( $this->getLanguageText('set_manual_feedback').":\n".$feedback );
			$this->appendBody("\n\n");
		}

		//	Task (What do I have to do?
		
		/* NOTHING TODO FOR PARTICIPANT */
		
		//	Explanation (Why do I receive the following message?)
		
		$this->appendBody("\n");
		$this->appendBody($this->getLanguageText('tst_notify_manscoring_done_body_msg_reason'));
				
		//	Signature
		
		$this->getMail()->appendInstallationSignature(true);
	}
	
	private function setRecipient($userId)
	{
		$this->setRecipients( array($userId) );
	}
	
	private function getRecipient()
	{
		return current( $this->getRecipients() );
	}
	
	private function convertFeedbackForMail($feedback)
	{
		if( strip_tags($feedback) != $feedback )
		{
			$feedback = preg_replace('/<br(.*\/)>/m', "\n", $feedback);
			$feedback = strip_tags($feedback);
		}
		
		return $feedback;
	}
}

