<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Page object factory
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageObjectFactory
{
    /**
     * Get page object instance
     */
    public static function getInstance(
        string $a_parent_type,
        int $a_id = 0,
        int $a_old_nr = 0,
        string $a_lang = "-"
    ) : ilPageObject {
        $def = ilCOPageObjDef::getDefinitionByParentType($a_parent_type);
        $class = $def["class_name"];
        $obj = new $class($a_id, $a_old_nr, $a_lang);

        return $obj;
    }

    /**
     * Get page config instance
     */
    public static function getConfigInstance(
        string $a_parent_type
    ) : ilPageConfig {
        $def = ilCOPageObjDef::getDefinitionByParentType($a_parent_type);
        $class = $def["class_name"] . "Config";
        $path = "./" . $def["component"] . "/" . $def["directory"] . "/class." . $class . ".php";
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
        $def = ilCOPageObjDef::getDefinitionByParentType($a_parent_type);
        $class = $def["class_name"]."GUI";
        $path = "./".$def["component"]."/".$def["directory"]."/class.".$class.".php";
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
