<?php

declare(strict_types=1);

class LSItemOnlineStatus
{
	const S_LEARNMODULE_IL= "lm";
	const S_LEARNMODULE_HTML = "htlm";
	const S_SAHS = "sahs";
	const S_TEST = "tst";
	const S_SURVEY = "svy";
	const S_CONTENTPAGE = "copa";
	const S_EXERCISE= "exc";
	const S_IND_ASSESSMENT= "iass";
	const S_FILE= "file";

	private static $obj_with_online_status = array(
		self::S_LEARNMODULE_IL,
		self::S_LEARNMODULE_HTML,
		self::S_SAHS,
		self::S_TEST,
		self::S_SURVEY
	);

	public function setOnlineStatus(int $ref_id, bool $status)
	{
		$obj = $this->getObjectFor($ref_id);

		switch ($obj->getType()) {
			case self::S_TEST:
				$obj->setOnline($status);
				$obj->saveToDb(true);
				break;
			case self::S_LEARNMODULE_IL:
			case self::S_LEARNMODULE_HTML:
			case self::S_SAHS:
				$obj->setOnline($status);
				$obj->update();
				break;
			case self::S_SURVEY:
				$obj->setStatus($status);
				$obj->saveToDb();
				break;
			default:
				break;
		}
	}

	public function getOnlineStatus(int $ref_id): bool
	{
		$obj = $this->getObjectFor($ref_id);

		switch ($obj->getType()) {
			case self::S_TEST:
				return !$obj->getOfflineStatus();
				break;
			case self::S_LEARNMODULE_IL:
			case self::S_LEARNMODULE_HTML:
			case self::S_SAHS:
				return !$obj->getOfflineStatus();
				break;
			case self::S_SURVEY:
				return !$obj->getOfflineStatus();
				break;
			default:
				return true;
		}
	}

	public function hasOnlineStatus(int $ref_id): bool
	{
		$type = $this->getObjectTypeFor($ref_id);
		if (in_array ($type, self::$obj_with_online_status)) {
			return true;
		}

		return false;
	}

	protected function getObjectFor(int $ref_id): ilObject
	{
		return ilObjectFactory::getInstanceByRefId($ref_id);
	}

	protected function getObjectTypeFor(int $ref_id): string
	{
		return ilObject::_lookupType($ref_id, true);
	}

}