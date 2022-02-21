<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilObjSearchSettings
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @extends ilObject
* @package ilias-core
*/
class ilObjSearchSettings extends ilObject
{
    public ?ilSearchSettings $settings_obj = null;


    /**
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "seas";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function initSettingsObject() : void
    {
        $this->settings_obj = new ilSearchSettings();
    }



    public function update() : bool
    {
        if (!parent::update()) {
            return false;
        }
        return true;
    }
} // END class.ilObjSearchSettings
