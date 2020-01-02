<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");
include_once("class.ilCloudUtil.php");
include_once("class.ilCloudConnector.php");

/**
 * Class ilObjCloud
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * $Id$
 *
 * @extends ilObject2
 */
class ilObjCloud extends ilObject2
{
    /**
     * @var bool
     */
    protected $online = false;

    /**
     * @var string
     */
    protected $service = "";

    /**
     * @var string
     */
    protected $root_folder = "";

    /**
     * @var int
     */
    protected $owner_id = 0;

    /**
     * @var bool
     */
    protected $auth_complete = false;


    /**
     * Constructor
     *
     * @access    public
     */
    public function __construct($a_ref_id = 0, $a_reference = true)
    {
        parent::__construct($a_ref_id, $a_reference);
    }

    /*
 * initType
 */
    public function initType()
    {
        $this->type = "cld";
    }

    /**
     * Create object
     */
    public function doCreate()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        /**
         * The owner of the cloud Object is only set once, when the object is created. This prevents, that users
         * access data of a cloud folder which they are not authorized to through changing the root_path of the cloud_folder
         */
        $this->setOwnerId($ilUser->getId());
        $ilDB->manipulate("INSERT INTO il_cld_data " .
            "(id, is_online, service, root_folder, root_id, owner_id, auth_complete) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote("", "integer") . "," .
            $ilDB->quote("", "text") . "," .
            $ilDB->quote("", "text") . "," .
            $ilDB->quote("", "text") . "," .
            $ilDB->quote($this->getOwnerId(), "integer") . "," .
            $ilDB->quote("", "integer") .
            ")");
    }

    /**
     * Read data from db
     */
    public function doRead()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query("SELECT * FROM il_cld_data " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));

        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->setOnline($rec["is_online"]);
            $this->setRootFolder($rec["root_folder"]);
            $this->setRootId($rec["root_id"]);
            $this->setServiceName($rec["service"]);
            $this->setOwnerId($rec["owner_id"]);
            $this->setAuthComplete($rec["auth_complete"]);
        }
    }

    /**
     * Update data
     */
    public function doUpdate()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate(
            $up = "UPDATE il_cld_data SET " .
                " is_online = " . $ilDB->quote($this->getOnline(), "integer") . "," .
                " service = " . $ilDB->quote($this->getServiceName(), "text") . "," .
                " root_folder = " . $ilDB->quote($this->getRootFolder(), "text") . "," .
                " root_id = " . $ilDB->quote($this->getRootId(), "text") . "," .
                " owner_id = " . $ilDB->quote($this->getOwnerId(), "integer") . "," .
                " auth_complete = " . $ilDB->quote($this->getAuthComplete(), "integer") .
                " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Delete data from db
     */
    public function doDelete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($this->getServiceName() != null) {
            $plugin_class = ilCloudConnector::getPluginClass($this->getServiceName(), $this->getId());
            if ($plugin_class) {
                $plugin_class->doDelete($this->getId());
            }
        }

        $ilDB->manipulate(
            "DELETE FROM il_cld_data WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Do Cloning
     */
    public function doClone($a_target_id, $a_copy_id, $new_obj)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOnline($this->getOnline());
        }

        $new_obj->setRootFolder($this->getRootFolder());
        $new_obj->getRootId($this->getRootId());
        $new_obj->setServiceName($this->getServiceName());
        $new_obj->serOwnerId($this->getOwnerId());
        $new_obj->setAuthComplete($this->getAuthComplete());
        $new_obj->update();
    }

//
    // Set/Get Methods for our example properties
//

    /**
     * Set online
     *
     * @param    boolean        online
     */
    public function setOnline($a_val)
    {
        $this->online = $a_val;
    }

    /**
     * Get online
     *
     * @return    boolean        online
     */
    public function getOnline()
    {
        return $this->online;
    }

    /**
     * Set root_folder, this may only be changed by the owner of the object. This is due too security constraints.
     * The parameter $no_check could be used by plugins for which these constraint is not an issue.
     *
     * @param    string        root_folder
     */
    public function setRootFolder($a_val, $no_check = false)
    {
        if ($this->currentUserIsOwner() || $no_check || $this->getRootFolder() == null) {
            $a_val             = ilCloudUtil::normalizePath($a_val);
            $this->root_folder = $a_val;
        } else {
            throw new ilCloudException(ilCloudException::PERMISSION_TO_CHANGE_ROOT_FOLDER_DENIED);
        }
    }

    /**
     * Get root_folder
     *
     * @return    string        root_folder
     */
    public function getRootFolder()
    {
        return $this->root_folder;
    }

    /**
     * Set root_id
     *
     * @param    string        root_id
     */
    public function setRootId($a_val, $no_check = false)
    {
        if ($this->currentUserIsOwner() || $no_check || $this->getRootId() == null) {
            $this->root_id = $a_val;
        } else {
            throw new ilCloudException(ilCloudException::PERMISSION_TO_CHANGE_ROOT_FOLDER_DENIED);
        }
    }

    /**
     * Get root_id
     *
     * @return    string        root_id
     */
    public function getRootId()
    {
        return $this->root_id;
    }

    /**
     * Set service
     *
     * @param    string        service
     */
    public function setServiceName($a_val)
    {
        $this->service_name = $a_val;
    }

    /**
     * Get service
     *
     * @return    string        service
     */
    public function getServiceName()
    {
        return $this->service_name;
    }

    /**
     * @param int $owner_id
     */
    public function setOwnerId($owner_id)
    {
        $this->owner_id = $owner_id;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * @return bool
     */
    public function currentUserIsOwner()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        return $ilUser->getId() == $this->getOwnerId();
    }

    /**
     * @param boolean $auth_complete
     */
    public function setAuthComplete($auth_complete)
    {
        $this->auth_complete = $auth_complete;
    }

    /**
     * @return boolean
     */
    public function getAuthComplete()
    {
        return $this->auth_complete;
    }
}
