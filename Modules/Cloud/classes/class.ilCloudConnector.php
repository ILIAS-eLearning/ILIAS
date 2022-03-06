<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Modules/Cloud/exceptions/class.ilCloudException.php');
require_once('class.ilCloudPluginItemCreationListGUI.php');
require_once('class.ilCloudPluginActionListGUI.php');
require_once('class.ilCloudPlugin.php');
require_once('class.ilCloudPluginInitGUI.php');
require_once('class.ilCloudPluginSettingsGUI.php');
require_once('class.ilCloudPluginService.php');
require_once('class.ilCloudPluginFileTreeGUI.php');
require_once('class.ilCloudPluginUploadGUI.php');
require_once('class.ilCloudPluginDeleteGUI.php');
require_once('class.ilCloudPluginCreateFolderGUI.php');
require_once('class.ilCloudPluginHeaderActionGUI.php');
require_once('class.ilCloudPluginCreationGUI.php');
require_once('class.ilCloudPluginInfoScreenGUI.php');

/**
 * ilCloudConnector class
 * Needed to check if a a plugin making a conncection to a service like GoogleDrive (simply named "service" is active or not.
 * Further the getXXXClass functions of this class are used to check if a given class is extended and if so returning the extended
 * version and if not returning the core version.
 * @author  Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @author  Martin Studer martin@fluxlabs.ch
 * @version $Id$
 * @ingroup ModulesCloud
 */
class ilCloudConnector
{

    /**
     * @throws ilCloudException
     */
    public static function getActiveServices(): array
    {
        global $DIC;
        $component_repository = $DIC['component.repository'];

        $cloud_services = $component_repository->getPluginSlotById("cldh")->getActivePlugins();
        if (!count($cloud_services)) {
            throw new ilCloudException(ilCloudException::NO_SERVICE_ACTIVE);
        }
        $services_names = array();
        foreach ($cloud_services as $service) {
            $services_names[$service] = $service->getName();
        }

        return $services_names;
    }

    /**
     * @throws ilCloudException
     */
    public static function checkServiceActive(string $name): bool
    {
        if (!$name) {
            throw new ilCloudException(ilCloudException::NO_SERVICE_SELECTED);
        }

        if (array_key_exists($name, ilCloudConnector::getActiveServices())) {
            return true;
        } else {
            throw new ilCloudException(ilCloudException::SERVICE_NOT_ACTIVE, $name);
        }
    }

    protected static function getFullClassName(string $service_name, string $class_name): string
    {
        if (file_exists("./Customizing/global/plugins/Modules/Cloud/CloudHook/" . $service_name . "/classes/class.il" . $service_name . $class_name . ".php")) {
            require_once("./Customizing/global/plugins/Modules/Cloud/CloudHook/" . $service_name . "/classes/class.il" . $service_name . $class_name . ".php");
            $class_full_name = "il" . $service_name . $class_name;

            return $class_full_name;
        }

        return "ilCloudPlugin" . $class_name;
    }

    /**
     * @throws ilCloudException
     */
    public static function getServiceClass(string $service_name, int $obj_id, bool $connect = true): ilCloudPluginService
    {
        if (!$service_name) {
            throw new ilCloudException(ilCloudException::NO_SERVICE_SELECTED);
        }

        if (array_key_exists($service_name, self::getActiveServices())) {
            $class_name = self::getFullClassName($service_name, "Service");

            return new $class_name($service_name, $obj_id);
        } else {
            throw new ilCloudException(ilCloudException::SERVICE_NOT_ACTIVE, $service_name);
        }
    }

    public static function getPluginClass(string $service_name, int $obj_id): ilCloudPlugin
    {
        $class_name = self::getFullClassName($service_name, "");

        return new $class_name($service_name, $obj_id);
    }

    public static function getPluginHookClass(string $service_name): ilCloudHookPlugin
    {
        $class_name = self::getFullClassName($service_name, "Plugin");

        return new $class_name($service_name);
    }

    public static function getSettingsGUIClass(ilCloudPluginService $plugin_service_class): ilCloudPluginSettingsGUI
    {
        $class_name = self::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(),
            "SettingsGUI");

        return new $class_name($plugin_service_class);
    }

    public static function getActionListGUIClass(ilCloudPluginService $plugin_service_class): ilCloudPluginActionListGUI
    {
        $class_name = self::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(),
            "ActionListGUI");

        return new $class_name($plugin_service_class);
    }

    public static function getItemCreationListGUIClass(ilCloudPluginService $plugin_service_class): ilCloudPluginItemCreationListGUI
    {
        $class_name = self::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(),
            "ItemCreationListGUI");

        return new $class_name($plugin_service_class);
    }

    public static function getInitGUIClass(ilCloudPluginService $plugin_service_class): ilCloudPluginInitGUI
    {
        $class_name = self::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(),
            "InitGUI");

        return new $class_name($plugin_service_class);
    }

    public static function getFileTreeGUIClass(ilCloudPluginService $plugin_service_class, ilCloudFileTree $file_tree): ilCloudPluginFileTreeGUI
    {
        $class_name = self::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(),
            "FileTreeGUI");

        return new $class_name($plugin_service_class, $file_tree);
    }

    public static function getCreateFolderGUIClass(ilCloudPluginService $plugin_service_class): ilCloudPluginCreateFolderGUI
    {
        $class_name = self::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(),
            "CreateFolderGUI");

        return new $class_name($plugin_service_class);
    }

    public static function getUploadGUIClass(ilCloudPluginService $plugin_service_class): ilCloudPluginUploadGUI
    {
        $class_name = self::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(),
            "UploadGUI");

        return new $class_name($plugin_service_class);
    }

    public static function getDeleteGUIClass(ilCloudPluginService $plugin_service_class): ilCloudPluginDeleteGUI
    {
        $class_name = self::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(),
            "DeleteGUI");

        return new $class_name($plugin_service_class);
    }

    public static function getHeaderActionGUIClass(ilCloudPluginService $plugin_service_class): ilCloudPluginHeaderActionGUI
    {
        $class_name = self::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(),
            "HeaderActionGUI");

        return new $class_name($plugin_service_class);
    }

    public static function getCreationGUIClass(ilCloudPluginService $plugin_service_class): ilCloudPluginCreationGUI
    {
        $class_name = self::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(),
            "CreationGUI");

        return new $class_name($plugin_service_class);
    }

    public static function getInfoScreenGUIClass(ilCloudPluginService $plugin_service_class): ilCloudPluginInfoScreenGUI
    {
        $class_name = self::getFullClassName($plugin_service_class->getPluginHookObject()->getPluginName(),
            "InfoScreenGUI");

        return new $class_name($plugin_service_class);
    }
}
