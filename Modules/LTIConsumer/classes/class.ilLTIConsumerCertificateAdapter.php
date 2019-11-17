<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLTIConsumerCertificateAdapter
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerCertificateAdapter extends ilCertificateAdapter
{
	/**
	 * @var ilObjLTIConsumer
	 */
	protected $object;
	
	public function __construct(ilObjLTIConsumer $object)
	{
		$this->object = $object;
		$this->certificatesByUser = null;
		parent::__construct();
	}

	public function getCertificatePath()
	{
		return CLIENT_WEB_DIR . "/lti_data/certificates/" . $this->object->getId() . "/";
	}
	
	public function getCertificateVariablesForPreview()
	{
		$vars = $this->getBaseVariablesForPreview(false);
		
		$tags = [
			"[OBJECT_TITLE]" => $this->object->getTitle(),
			"[OBJECT_DESCRIPTION]" => $this->object->getDescription(),
			"[MASTERY_SCORE]" => $this->getMasteryScore(),
			"[REACHED_SCORE]" => $this->getExampleScore()
		];
		
		foreach($vars as $id => $caption)
		{
			$tags["[$id]"] = $caption;
		}
		
		return $tags;
	}
	
	public function getCertificateVariablesForPresentation($params = array())
	{
		$userData = ilObjUser::_lookupFields($params['user_id']);
		
		$vars = $this->getBaseVariablesForPresentation($userData);
		
		$tags = [
			"[OBJECT_TITLE]" => $this->object->getTitle(),
			"[OBJECT_DESCRIPTION]" => $this->object->getDescription(),
			"[MASTERY_SCORE]" => $this->getMasteryScore(),
			"[REACHED_SCORE]" => $this->getReachedScore($params['user_id'])
		];
		
		foreach($vars as $id => $caption)
		{
			$tags["[$id]"] = $caption;
		}
		
		return $tags;
	}
	
	public function getCertificateVariablesDescription()
	{
		$vars = $this->getBaseVariablesDescription(false);
		
		$vars["OBJECT_TITLE"] = $this->lng->txt("lti_cert_ph_object_title");
		$vars["OBJECT_DESCRIPTION"] = $this->lng->txt("lti_cert_ph_object_description");
		
		$vars["MASTERY_SCORE"] = $this->lng->txt("lti_cert_ph_mastery_score");
		$vars["REACHED_SCORE"] = $this->lng->txt("lti_cert_ph_reached_score");
		
		// JUST KEEP IT THIS WAY (!)
		$template = new ilTemplate("tpl.il_as_tst_certificate_edit.html", TRUE, TRUE, "Modules/Test");
		
		$template->setCurrentBlock("items");
		foreach($vars as $id => $caption)
		{
			$template->setVariable("ID", $id);
			$template->setVariable("TXT", $caption);
			$template->parseCurrentBlock();
		}
		
		$template->setVariable("PH_INTRODUCTION", $this->lng->txt("certificate_ph_introduction"));
		
		return $template->get();
	}
	
	public function getAdapterType()
	{
		return $this->object->getType();
	}
	
	public function getCertificateID()
	{
		return $this->object->getId();
	}
	
	/**
	 * @param $userId
	 * @return string
	 */
	protected function getReachedScore($userId): string
	{
		$userResult = ilLTIConsumerResult::getByKeys(
			$this->object->getId(), $userId
		);
		
		$reachedScore = sprintf('%0.2f %%', 0);
		if ($userResult) {
			$reachedScore = sprintf('%0.2f %%', $userResult->getResult() * 100);
		}
		
		return $reachedScore;
	}
	
	/**
	 * @return string
	 */
	protected function getMasteryScore(): string
	{
		$masteryScore = sprintf('%0.2f %%', $this->object->getMasteryScorePercent());
		return $masteryScore;
	}
	
	/**
	 * @return string
	 */
	protected function getExampleScore(): string
	{
		$masteryScore = sprintf('%0.2f %%', 97.11);
		return $masteryScore;
	}
	
	/**
	 * @param int $objId
	 * @param int $usrId
	 * @return bool
	 */
	public static function hasCertificate($objId, $usrId)
	{
		return ilLPStatus::_lookupStatus($objId, $usrId) == ilLPStatus::LP_STATUS_COMPLETED_NUM;
	}
	
	/**
	 * @param int $objId
	 * @return bool
	 * @throws ilLtiConsumerException
	 */
	public static function hasCurrentUserCertificate($objId)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		return ilLPStatus::_hasUserCompleted($objId, $DIC->user()->getId());
	}
	
	/**
	 * @return bool
	 */
	public static function areCertificatesForCurrentUserPreloaded()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		if( !is_array(self::$certificatesByUser) )
		{
			return false;
		}

		if( !isset(self::$certificatesByUser[$DIC->user()->getId()]) )
		{
			return false;
		}
		
		return is_array(self::$certificatesByUser[$DIC->user()->getId()]);
	}
}
