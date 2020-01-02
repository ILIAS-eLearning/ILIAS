<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("class.ilCloudPluginConfig.php");
include_once("class.ilObjCloud.php");

/**
 * Class ilCloudPlugin
 *
 * Base Class for the model of the plugin. Probably will be extended by most plugins.
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 *
 * @ingroup ModulesCloud
 */
class ilCloudPlugin
{
    /**
     * @var
     */
    protected $obj_id;

    /**
     * @var ilCloudHookPlugin
     */
    protected $plugin_hook_object;

    /**
     * @var string
     */
    protected $table_name = "";

    /**
     * @var ilCloudPluginConfig
     */
    protected $admin_config_object;

    /**
     * @var ilObjCloud
     */
    protected $cloud_modul_object;

    /**
     * @var int
     */
    protected $max_file_size = 25;

    /**
     * @var bool
     */
    //protected $async_drawing = false;

    /**
     * @param $obj_id
     */
    public function __construct($service_name, $obj_id, $cloud_modul_object = null)
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

    /**
     * @param  $obj_id
     */
    public function setObjId($obj_id)
    {
        $this->obj_id = $obj_id;
    }

    /**
     * @return
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * @return \ilCloudHookPlugin
     */
    public function getPluginHookObject()
    {
        return $this->plugin_hook_object;
    }

    /**
     * @return \ilCloudPluginConfig
     */
    public function getAdminConfigObject()
    {
        return $this->admin_config_object;
    }

    /**
     * @param \ilCloudHookPlugin $plugin_hook_object
     */
    public function setPluginHookObject($plugin_hook_object)
    {
        $this->plugin_hook_object = $plugin_hook_object;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->getPluginHookObject()->getPluginTableName();
    }


    /**
     * @param boolean $async_drawing

    public function setAsyncDrawing($async_drawing)
    {
        $this->async_drawing = $async_drawing;
    }

    /**
     * @return boolean
     *
    public function getAsyncDrawing()
    {
        return $this->async_drawing;
    }*/

    /**
     * @param int $max_file_size
     */
    public function setMaxFileSize($max_file_size)
    {
        $this->max_file_size = $max_file_size;
    }

    /**
     * @return int
     */
    public function getMaxFileSize()
    {
        return $this->max_file_size;
    }

    /**
     * @param $cloud_modul_object
     */
    public function setCloudModulObject($cloud_modul_object)
    {
        $this->cloud_modul_object = $cloud_modul_object;
    }

    /**
     * @return ilObjCloud
     */
    public function getCloudModulObject()
    {
        return $this->cloud_modul_object;
    }

    /**
     * $return integer
     */
    public function getOwnerId()
    {
        include_once("./Modules/Cloud/classes/class.ilObjCloud.php");
        $cloud_object = new ilObjCloud($this->getObjId(), false);
        return $cloud_object->getOwnerId();
    }

    public function read()
    {
    }

    public function create()
    {
    }

    public function doUpdate()
    {
    }

    public function doDelete()
    {
    }
}
