<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjWorkflowEngine
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilObjWorkflowEngine extends ilObject
{
	/**
	 * ilObjWorkflowEngine constructor.
	 *
	 * @param int  $id
	 * @param bool $call_by_reference
	 */
	public function __construct($id = 0, $call_by_reference = true)
	{
		$this->type = "wfe";
		parent::__construct($id, $call_by_reference);
	}

	/**
	 * @return string
	 */
	public static function getTempDir()
	{
		return ILIAS_DATA_DIR . '/' . CLIENT_ID . '/wfe/upload_temp/';
	}

	/**
	 * @param bool $relative
	 * @return string
	 */
	public static function getRepositoryDir($relative = false)
	{
		$relativeRepositoryPath = 'wfe/repository/';

		if ($relative) {
			return $relativeRepositoryPath; 
		}

		return ILIAS_DATA_DIR . '/' . CLIENT_ID . '/' . $relativeRepositoryPath;
	}
}