<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("class.ilCloudUtil.php");
require_once("class.ilCloudConnector.php");

/**
 * Class ilObjCloud
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author  Martin Studer martin@fluxlabs.ch
 * $Id$
 * @extends ilObject2
 */
class ilObjCloud extends ilObject2
{
    protected string $root_id = '';
    protected bool $online = false;
    protected string $service = '';
    protected string $root_folder = '';
    protected int $owner_id = 0;
    protected bool $auth_complete = false;

    public function __construct(int $a_ref_id = 0, bool $a_reference = true)
    {
        parent::__construct($a_ref_id, $a_reference);
    }

    public function initType() : void
    {
        $this->type = "cld";
    }

    public function doCreate() : void
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

    public function doRead() : void
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

    public function doUpdate() : void
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

    public function doDelete() : void
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

    public function doClone(int $a_target_id, int $a_copy_id, ilObjCloud $new_obj) : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOnline($this->getOnline());
        }

        $new_obj->setRootFolder($this->getRootFolder());
        $new_obj->setRootId($this->getRootId());
        $new_obj->setServiceName($this->getServiceName());
        $new_obj->setOwnerId($this->getOwnerId());
        $new_obj->setAuthComplete($this->getAuthComplete());
        $new_obj->update();
    }

    //
    // Set/Get Methods for our example properties
    //
    public function setOnline(bool $a_val) : void
    {
        $this->online = $a_val;
    }

    /**
     * Get online
     */
    public function getOnline() : bool
    {
        return $this->online;
    }

    /**
     * Set root_folder, this may only be changed by the owner of the object. This is due too security constraints.
     * The parameter $no_check could be used by plugins for which these constraint is not an issue.
     */
    public function setRootFolder(string $a_val, bool $no_check = false) : void
    {
        if ($this->currentUserIsOwner() || $no_check || $this->getRootFolder() == null) {
            $a_val = ilCloudUtil::normalizePath($a_val);
            $this->root_folder = $a_val;
        } else {
            throw new ilCloudException(ilCloudException::PERMISSION_TO_CHANGE_ROOT_FOLDER_DENIED);
        }
    }

    /**
     * Get root_folder
     */
    public function getRootFolder() : string
    {
        return $this->root_folder;
    }

    public function setRootId(string $a_val, bool $no_check = false) : void
    {
        if ($this->currentUserIsOwner() || $no_check || $this->getRootId() == null) {
            $this->root_id = $a_val;
        } else {
            throw new ilCloudException(ilCloudException::PERMISSION_TO_CHANGE_ROOT_FOLDER_DENIED);
        }
    }

    public function getRootId() : string
    {
        return $this->root_id;
    }

    public function setServiceName(string $service_name) : void
    {
        $this->service_name = $service_name;
    }

    public function getServiceName() : string
    {
        return $this->service_name;
    }

    public function setOwnerId(int $owner_id) : void
    {
        $this->owner_id = $owner_id;
    }

    public function getOwnerId() : int
    {
        return $this->owner_id;
    }

    public function currentUserIsOwner() : bool
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        return ($ilUser->getId() === $this->getOwnerId());
    }

    public function setAuthComplete(bool $auth_complete) : void
    {
        $this->auth_complete = $auth_complete;
    }

    public function getAuthComplete() : bool
    {
        return $this->auth_complete;
    }
}
