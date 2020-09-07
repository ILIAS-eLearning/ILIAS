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
* ILIAS Service (A service provides cross-sectional functionalities, used by
* other components)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesComponent
*/
abstract class ilService extends ilComponent
{
    /**
     * @inheritdoc
     */
    final public function getComponentType() : string
    {
        return IL_COMP_SERVICE;
    }
    
    /**
    * Get Name.
    *
    * @return	string	Name
    */
    final public function getName()
    {
        // class is always il<ModuleName>Service
        $class = get_class($this);
        
        return substr($class, 2, strlen($class) - 9);
    }

    /**
    * Get all available core services. Core services are located in the
    * main ILIAS/Services folder and provide a service.xml file that
    * includes information about the service. (please note that currently
    * only a few services provide a service.xml file)
    *
    * @return	array		array of services (assoc array, "name", "dir")
    */
    final public static function getAvailableCoreServices()
    {
        $services_dir = ILIAS_ABSOLUTE_PATH . "/Services";

        if (!@is_dir($services_dir)) {
            return array();
        }

        // read current directory
        $dir = opendir($services_dir);

        $services = array();
        while ($file = readdir($dir)) {
            if ($file != "." and
                $file != "..") {
                // directories
                if (@is_dir($services_dir . "/" . $file)) {
                    if (@is_file($services_dir . "/" . $file . "/service.xml")) {
                        $services[] = array("subdir" => $file);
                    }
                }
            }
        }
        return $services;
    }
}
