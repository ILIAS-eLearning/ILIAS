<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjectCustomIconPresenter
 */
class ilObjectReferenceCustomIconPresenter implements \ilObjectCustomIconPresenter
{
	/**
	 * @var \ilObjectCustomIcon
	 */
	private $icon = null;

	/**
	 * ilObjectReferenceCustomIconPresenter constructor.
	 *
	 */
	public function __construct(\ilObjectCustomIcon $icon)
	{
		$this->icon = $icon;
	}

	/**
	 * Lookup target id of container reference
	 *
	 * @param $a_obj_id
	 * @param ilDBInterface $db
	 * @return int
	 * @throws ilDatabaseException
	 */
	public static function lookupTargetId($a_obj_id, \ilDBInterface $db)
	{
		$query = 'select target_obj_id from container_reference '.
			'where obj_id = ' . $db->quote($a_obj_id,'integer');
		$res = $db->query($query);

		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT))
		{
			return $row->target_obj_id;
		}
		return 0;
	}


	/**
	 * @return bool
	 */
	public function exists()
	{
		return $this->icon->exists();
	}

	/**
	 * @return string
	 */
	public function getFullPath()
	{
		return $this->icon->getFullPath();
	}

}
?>