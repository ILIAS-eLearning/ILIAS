<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * COPage page object definition handler
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
class ilCOPageObjDef
{
    public static $page_obj_def = null;
    
    /**
     * Init
     *
     * @param
     * @return
     */
    public static function init()
    {
        global $DIC;

        $db = $DIC->database();
        
        if (self::$page_obj_def == null) {
            $set = $db->query("SELECT * FROM copg_pobj_def ");
            while ($rec = $db->fetchAssoc($set)) {
                self::$page_obj_def[$rec["parent_type"]] = $rec;
            }
        }
    }
    
    /**
     * Get definitions
     *
     * @param
     * @return
     */
    public function getDefinitions()
    {
        self::init();
        return self::$page_obj_def;
    }
    
    /**
     * Get definition by parent type
     *
     * @param string $a_parent_type parent type
     * @return array definition
     */
    public static function getDefinitionByParentType($a_parent_type)
    {
        self::init();
        return self::$page_obj_def[$a_parent_type];
    }
}
