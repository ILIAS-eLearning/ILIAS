<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCloudPluginGUI
 * Abstract base class for all GUI classes that can be extended by the plugin.
 * @author  Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @author  Martin Studer martin@fluxlabs.ch
 * @version $Id$
 * @ingroup ModulesCloud
 */
abstract class ilCloudPluginGUI
{
    protected ?ilCloudPluginService $service = null;

    public function __construct(string $plugin_service_class)
    {
        $this->service = $plugin_service_class;
    }

    public function getPluginObject() : ilCloudPlugin
    {
        return $this->service->getPluginObject();
    }

    public function getPluginHookObject() : ilCloudHookPlugin
    {
        return $this->getPluginObject()->getPluginHookObject();
    }

    public function getAdminConfigObject() : ilCloudPluginConfig
    {
        return $this->getPluginObject()->getAdminConfigObject();
    }

    public function getService() : ilCloudPluginService
    {
        return $this->service;
    }

    public function txt(string $var = "") : string
    {
        return $this->getPluginHookObject()->txt($var);
    }

    public function executeCommand() : void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $cmd = $ilCtrl->getCmd();

        switch ($cmd) {
            default:
                $this->$cmd();
                break;
        }
    }
}
