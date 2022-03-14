<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("class.ilCloudPluginConfig.php");
require_once("class.ilObjCloud.php");

/**
 * Class ilCloudPlugin
 * Base Class for the model of the plugin. Probably will be extended by most plugins.
 * @author  Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @author  Martin Studer martin@fluxlabs.ch
 * @version $Id$
 * @ingroup ModulesCloud
 */
class ilCloudPlugin
{
    protected int $obj_id;
    protected ilCloudHookPlugin $plugin_hook_object;
    protected string $table_name = "";
    protected ilCloudPluginConfig $admin_config_object;
    protected ilObjCloud $cloud_modul_object;
    protected int $max_file_size = 25;

    /**
     * @param $obj_id
     */
    public function __construct(string $service_name, int $obj_id, ?ilObjCloud $cloud_modul_object = null)
    {
        $this->setObjId($obj_id);

        $this->plugin_hook_object = ilCloudConnector::getPluginHookClass($service_name);
        if (!is_object($this->plugin_hook_object)) {
            throw new ilCloudException(ilCloudException::PLUGIN_HOOK_COULD_NOT_BE_INSTANTIATED);
        }
        $this->admin_config_object = new ilCloudPluginConfig($this->plugin_hook_object->getPluginConfigTableName());
        if (!$this->read()) {
            $this->create();
        }

        if (!$cloud_modul_object) {
            // in the context of deleting, it's possible that the ilObjCloud with this obj_id is already pushing up the daisies
            // so instantiating it would lead to an error
            if ($obj_id == 0 || ilObjCloud::_exists($obj_id, false, 'cld')) {
                $cloud_modul_object = new ilObjCloud($obj_id, false);
            }
        }
        $this->setCloudModulObject($cloud_modul_object);
    }

    public function setObjId(int $obj_id): void
    {
        $this->obj_id = $obj_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getPluginHookObject(): ilCloudHookPlugin
    {
        return $this->plugin_hook_object;
    }

    public function getAdminConfigObject(): ilCloudPluginConfig
    {
        return $this->admin_config_object;
    }

    public function setPluginHookObject(ilCloudHookPlugin $plugin_hook_object): void
    {
        $this->plugin_hook_object = $plugin_hook_object;
    }

    public function getTableName(): string
    {
        return $this->getPluginHookObject()->getPluginTableName();
    }

    public function setMaxFileSize(int $max_file_size): void
    {
        $this->max_file_size = $max_file_size;
    }

    public function getMaxFileSize(): int
    {
        return $this->max_file_size;
    }

    public function setCloudModulObject(ilObjCloud $cloud_modul_object): void
    {
        $this->cloud_modul_object = $cloud_modul_object;
    }

    public function getCloudModulObject(): ilObjCloud
    {
        return $this->cloud_modul_object;
    }

    public function getOwnerId(): int
    {
        require_once("./Modules/Cloud/classes/class.ilObjCloud.php");
        return (new ilObjCloud($this->getObjId(), false))->getOwnerId();
    }

    public function read(): void
    {
    }

    public function create(): void
    {
    }

    public function doUpdate(): void
    {
    }

    public function doDelete(): void
    {
    }
}
