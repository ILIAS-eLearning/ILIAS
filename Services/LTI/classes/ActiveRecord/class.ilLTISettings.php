<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class ilLTISettings
 *
 * @author Jesús López <lopez@leifos.com>
 */
class ilLTISettings extends ActiveRecord
{
	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     4
	 */
	protected $type_obj_id = 0;

	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     4
	 */
	protected $role_id = 0;

	/**
	 * @var bool
	 *
	 * @con_has_field true
	 * @con_fieldtype bool
	 */
	protected $active = FALSE;

}