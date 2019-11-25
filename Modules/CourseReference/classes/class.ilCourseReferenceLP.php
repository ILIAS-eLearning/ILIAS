<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCourseReferenceLP
 */
class ilCourseReferenceLP extends ilObjectLP
{
	/**
	 * @var \ilLogger | null
	 */
	private $logger = null;

	protected function __construct($a_obj_id)
	{
		global $DIC;

		parent::__construct($a_obj_id);

		$this->logger = $DIC->logger()->crsr();
	}


	/**
	 * @inheritdoc
	 */
	public function getDefaultMode()
	{
		$this->logger->debug('Called for obj_id: ' . $this->obj_id);
		return [
			\ilLPObjSettings::LP_MODE_DEACTIVATED
		];
	}

	/**
	 * @param bool $a_lp_active
	 * @return array
	 */
	public static function getDefaultModes($a_lp_active)
	{
		if(!$a_lp_active) {
			return [
				\ilLPObjSettings::LP_MODE_DEACTIVATED
			];
		}
		return [
			\ilLPObjSettings::LP_MODE_DEACTIVATED,
			\ilLPObjSettings::LP_MODE_COURSE_REFERENCE
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getValidModes()
	{
		return self::getDefaultModes();
	}

}