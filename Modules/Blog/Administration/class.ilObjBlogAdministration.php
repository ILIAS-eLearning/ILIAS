<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjBlogAdministration
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjBlogAdministration extends ilObject
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	bool	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = "blga";
        parent::__construct($a_id, $a_call_by_reference);

        $this->lng->loadLanguageModule("blog");
    }

    /**
    * update object data
    *
    * @access	public
    * @return	bool
    */
    public function update() : bool
    {
        if (!parent::update()) {
            return false;
        }

        // put here object specific stuff
        return true;
    }
}
