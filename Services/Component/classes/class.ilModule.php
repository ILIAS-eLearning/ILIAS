<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


include_once("./Services/Component/classes/class.ilComponent.php");

/**
* ILIAS Module
*
* Modules handle resource object types (one or more), that can be
* added to the repository, e.g. forums, glossaries, ...
*
* Modules are ILIAS components, like services. Services can also handle
* resource object types, but only administrative ones.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesComponent
*/
abstract class ilModule extends ilComponent
{
    
    /**
    * Constructor: read information on component
    */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    final public function getComponentType() : string
    {
        return IL_COMP_MODULE;
    }
    
    /**
    * Get Name.
    *
    * @return	string	Name
    */
    final public function getName()
    {
        // class is always il<ModuleName>Module
        $class = get_class($this);
        
        return substr($class, 2, strlen($class) - 8);
    }

    /**
    * Get all available core modules. Core modules are located in the
    * main ILIAS/Modules folder and provide a module.xml file that
    * includes information about the module.
    *
    * @return	array		array of module names (strings)
    */
    final public static function getAvailableCoreModules()
    {
        $modules_dir = ILIAS_ABSOLUTE_PATH . "/Modules";

        if (!@is_dir($modules_dir)) {
            return array();
        }

        // read current directory
        $dir = opendir($modules_dir);

        $modules = array();
        while ($file = readdir($dir)) {
            if ($file != "." and
                $file != "..") {
                // directories
                if (@is_dir($modules_dir . "/" . $file)) {
                    if (@is_file($modules_dir . "/" . $file . "/module.xml")) {
                        $modules[] = array("subdir" => $file);
                    }
                }
            }
        }
        return $modules;
    }
}
