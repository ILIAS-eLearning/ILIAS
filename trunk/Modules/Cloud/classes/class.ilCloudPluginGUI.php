<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("class.ilCloudConnector.php");

/**
 * Class ilCloudPluginGUI
 *
 * Abstract base class for all GUI classes that can be extended by the plugin.
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 *
 * @ingroup ModulesCloud
 */
abstract class ilCloudPluginGUI
{
    /**
     * @var ilCloudPluginService $service
     */
    protected $service = null;

    /**
     * @param $service_name
     * @param $obj_id
     */
    public function __construct($plugin_service_class)
    {
        $this->service = $plugin_service_class;
    }

    /**
     * @return ilCloudPlugin
     */
    public function getPluginObject()
    {
        return $this->service->getPluginObject();
    }

    /**
     * @return ilCloudHookPlugin
     */
    public function getPluginHookObject()
    {
        return $this->getPluginObject()->getPluginHookObject();
    }

    public function getAdminConfigObject()
    {
        return $this->getPluginObject()->getAdminConfigObject();
    }

    /**
     * @return ilCloudPluginService
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param string $var
     */
    public function txt($var = "")
    {
        return $this->getPluginHookObject()->txt($var);
    }

    public function executeCommand()
    {
        global $ilCtrl;

        $cmd = $ilCtrl->getCmd();

        switch ($cmd)
        {
            default:
                $this->$cmd();
                break;
        }
    }
}

?>