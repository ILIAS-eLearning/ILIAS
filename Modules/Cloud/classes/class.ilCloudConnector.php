<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Modules/Cloud/exceptions/class.ilCloudException.php');
include_once('class.ilCloudPluginItemCreationListGUI.php');
include_once('class.ilCloudPluginActionListGUI.php');
include_once('class.ilCloudPlugin.php');
include_once('class.ilCloudPluginInitGUI.php');
include_once('class.ilCloudPluginSettingsGUI.php');
include_once('class.ilCloudPluginService.php');
include_once('class.ilCloudPluginFileTreeGUI.php');
include_once('class.ilCloudPluginUploadGUI.php');
include_once('class.ilCloudPluginDeleteGUI.php');
include_once('class.ilCloudPluginCreateFolderGUI.php');
include_once('class.ilCloudPluginHeaderActionGUI.php');
include_once('class.ilCloudPluginCreationGUI.php');
include_once('class.ilCloudPluginInfoScreenGUI.php');

/**
 * ilCloudConnector class
 * Needed to check if a a plugin making a conncection to a service like GoogleDrive (simply named "service" is active or not.
 * Further the getXXXClass functions of this class are used to check if a given class is extended and if so returning the extended
 * version and if not returning the core version.
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 *
 * @ingroup ModulesCloud
 */
class ilCloudConnector
{

    /**
     * @return array
     * @throws ilCloudException
     */
    public static function getActiveServices()
    {
        global $ilPluginAdmin;

        $cloud_services = $ilPluginAdmin->getActivePluginsForSlot("Modules", "Cloud", "cldh");
        if (!$cloud_services)
        {
            throw new ilCloudException(ilCloudException::NO_SERVICE_ACTIVE);
        }
        $services_names = array();
        foreach ($cloud_services as $service)
        {
            $services_names[$service] = $service;
        }
        return $services_names;
    }

    /**
     * @param string $name
     * @return bool
     * @throws ilCloudException
     */
    public static function checkServiceActive($name)
    {
        if (!$name)
        {
            throw new ilCloudException(ilCloudException::NO_SERVICE_SELECTED);
        }

        if (array_key_exists($name, ilCloudConnector::getActiveServices()))
        {
            return true;
        } else
        {
            throw new ilCloudException(ilCloudException::SERVICE_NOT_ACTIVE, $name);
        }
    }

    /**
     * @param string $service_name
     * @param string $class_name
     * @return string
     */
    protected static function getFullClassName($service_name, $class_name)
    {
        if (file_exists("./Customizing/global/plugins/Modules/Cloud/CloudHook/" . $service_name . "/classes/class.il" .$service_name . $class_name.".php"))
        {
            include_once("./Customizing/global/plugins/Modules/Cloud/CloudHook/" . $service_name . "/classes/class.il" .$service_name . $class_name.".php");
            $class_full_name = "il" . $service_name . $class_name;
            return $class_full_name;
        }
        return "ilCloudPlugin" .$class_name;
    }
    /**
     * @param string $name
     * @param int $obj_id
     * @param bool $connect
     * @return ilCloudPluginService
     * @throws ilCloudException
     */
    public static function getServiceClass($service_name, $obj_id, $connect = true)
    {
        if (!$service_name)
        {
            throw new ilCloudException(ilCloudException::NO_SERVICE_SELECTED);
        }

        if (array_key_exists($service_name, ilCloudConnector::getActiveServices()))
        {
            $class_name    = ilCloudConnector::getFullClassName($service_name, "Service");
            return new $class_name($service_name, $obj_id);
        } else
        {
            throw new ilCloudException(ilCloudException::SERVICE_NOT_ACTIVE, $service_name);
        }
    }

    /**
     * @param string $name
     * @param int $obj_id
     * @return ilCloudPlugin
     */
    public static function getPluginClass($service_name, $obj_id)
    {
        $class_name = ilCloudConnector::getFullClassName($service_name, "");
        return new $class_name($service_name, $obj_id);
    }

    /**
     * @param string $name
     * @param int $obj_id
     * @return ilCloudHookPlugin
     */
    public static function getPluginHookClass($service_name)
    {
        $class_name = ilCloudConnector::getFullClassName($service_name, "Plugin");
        return new $class_name($service_name);
    }

    /**
     * @param string $name
     * @param int $obj_id
     * @return ilCloudPluginSettingsGUI
     */
    public static function getSettingsGUIClass(ilCloudPluginService $plugin_service_class)
    {
        $class_name = ilCloudConnector::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(), "SettingsGUI");
        return new $class_name($plugin_service_class);
    }

    /**
     * @param $service_name
     * @param $obj_id
     * @return ilCloudPluginActionListGUI
     */
    public static function getActionListGUIClass(ilCloudPluginService $plugin_service_class)
    {
        $class_name = ilCloudConnector::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(), "ActionListGUI");
        return new $class_name($plugin_service_class);
    }

    /**
     * @param string $name
     * @param int $obj_id
     * @return ilCloudPluginItemCreationListGUI
     */
    public static function getItemCreationListGUIClass(ilCloudPluginService $plugin_service_class)
    {
        $class_name = ilCloudConnector::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(), "ItemCreationListGUI");
        return new $class_name($plugin_service_class);
    }

    /**
     * @param string $name
     * @param int $obj_id
     * @return ilCloudPluginInitGUI
     */
    public static function getInitGUIClass(ilCloudPluginService $plugin_service_class)
    {
        $class_name = ilCloudConnector::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(), "InitGUI");
        return new $class_name($plugin_service_class);
    }

    /**
     * @param $service_name
     * @param $obj_id
     * @param ilCloudPluginFileTree
     * @return ilCloudPluginFileTreeGUI
     */
    public static function getFileTreeGUIClass(ilCloudPluginService $plugin_service_class, ilCloudFileTree $file_tree)
    {
        $class_name = ilCloudConnector::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(), "FileTreeGUI");
        return new $class_name($plugin_service_class,  $file_tree);
    }

    /**
     * @param string $name
     * @param int $obj_id
     * @return ilCloudPluginCreateFolderGUI
     */
    public static function getCreateFolderGUIClass(ilCloudPluginService $plugin_service_class)
    {
        $class_name = ilCloudConnector::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(), "CreateFolderGUI");
        return new $class_name($plugin_service_class);
    }

    /**
     * @param string $name
     * @param int $obj_id
     * @return ilCloudPluginUploadGUI
     */
    public static function getUploadGUIClass(ilCloudPluginService $plugin_service_class)
    {
        $class_name = ilCloudConnector::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(), "UploadGUI");
        return new $class_name($plugin_service_class);
    }

    /**
     * @param ilCloudPluginService $plugin_service_class
     * @return ilCloudPluginDeleteGUI
     */
    public static function getDeleteGUIClass(ilCloudPluginService $plugin_service_class)
    {
        $class_name = ilCloudConnector::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(), "DeleteGUI");
        return new $class_name($plugin_service_class);
    }

    /**
     * @param ilCloudPluginService $plugin_service_class
     * @return ilCloudPluginHeaderActionGUI
     */
    public static function getHeaderActionGUIClass(ilCloudPluginService $plugin_service_class)
    {
        $class_name = ilCloudConnector::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(), "HeaderActionGUI");
        return new $class_name($plugin_service_class);
    }

    /**
     * @param ilCloudPluginService $plugin_service_class
     * @return ilCloudPluginCreationGUI
     */
    public static function getCreationGUIClass(ilCloudPluginService $plugin_service_class)
    {
        $class_name = ilCloudConnector::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(), "CreationGUI");
        return new $class_name($plugin_service_class);
    }

    /**
     * @param ilCloudPluginService $plugin_service_class
     * @return ilCloudPluginInfoScreenGUI
     */
    public static function getInfoScreenGUIClass(ilCloudPluginService $plugin_service_class)
    {
        $class_name = ilCloudConnector::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(), "InfoScreenGUI");
        return new $class_name($plugin_service_class);
    }
}
?>