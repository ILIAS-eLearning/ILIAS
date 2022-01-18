<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilCmiXapiUserAutocomplete
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiUserAutocomplete extends ilUserAutoComplete
{
    /**
     * @var int
     */
    protected $objId;
    
    /**
     * @param int $objId
     */
    public function __construct($objId)
    {
        parent::__construct();
        $this->objId = $objId;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getFromPart(): string
    {
        global $DIC;
        
        $fromPart = parent::getFromPart();
        
        $fromPart .= "
			INNER JOIN (SELECT DISTINCT usr_id, obj_id FROM cmix_users) c
			ON c.obj_id = {$DIC->database()->quote($this->objId, 'integer')}
			AND c.usr_id = ud.usr_id
		";
        return $fromPart;
    }
}
