<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Page object factory
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
class ilPageObjectFactory
{
    /**
     * Get page object instance
     *
     * @param string $a_parent_type parent type
     * @param int $a_id page id
     * @param int $a_old_nr history number of page
     * @param string $a_lang language
     * @return object
     */
    public static function getInstance($a_parent_type, $a_id = 0, $a_old_nr = 0, $a_lang = "-")
    {
        include_once("./Services/COPage/classes/class.ilCOPageObjDef.php");
        $def = ilCOPageObjDef::getDefinitionByParentType($a_parent_type);
        $class = $def["class_name"];
        $path = "./" . $def["component"] . "/" . $def["directory"] . "/class." . $class . ".php";
        include_once($path);
        $obj = new $class($a_id, $a_old_nr, $a_lang);
        
        return $obj;
    }
    
    /**
     * Get page config instance
     *
     * @param string $a_parent_type parent type
     * @return object
     */
    public static function getConfigInstance($a_parent_type)
    {
        include_once("./Services/COPage/classes/class.ilCOPageObjDef.php");
        $def = ilCOPageObjDef::getDefinitionByParentType($a_parent_type);
        $class = $def["class_name"] . "Config";
        $path = "./" . $def["component"] . "/" . $def["directory"] . "/class." . $class . ".php";
        include_once($path);
        $cfg = new $class();
        
        return $cfg;
    }

    /**
     * Get page object GUI instance (currently unfinished, problems e.g. ilBlogPosting constructor)
     *
     * @param string $a_parent_type parent type
     * @param int $a_id page id
     * @param int $a_old_nr history number of page
     * @param string $a_lang language
     * @return object
     */
    /* static function getGUIInstance($a_parent_type, $a_id = 0, $a_old_nr = 0, $a_lang = "-")
    {
        include_once("./Services/COPage/classes/class.ilCOPageObjDef.php");
        $def = ilCOPageObjDef::getDefinitionByParentType($a_parent_type);
        $class = $def["class_name"]."GUI";
        $path = "./".$def["component"]."/".$def["directory"]."/class.".$class.".php";
        include_once($path);
        if (in_array($a_parent_type, array("cont", "cstr", "lm")))
        {
            $obj = new $class($a_id , $a_old_nr, $a_lang);
        }
        else if (in_array($a_parent_type, array("impr")))
        {
            $obj = new $class();
        }
        else if (in_array($a_parent_type, array("stys")))
        {
            $obj = new $class($a_parent_type, $a_id, $a_old_nr);
        }
        else if (in_array($a_parent_type, array("blog")))
        {
            $obj = new $class($a_parent_type, $a_id, $a_old_nr);
        }
        else
        {
            $obj = new $class($a_id , $a_old_nr);
        }

        return $obj;
    }*/
}
