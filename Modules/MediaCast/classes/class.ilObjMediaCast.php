<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilObjMediaCast
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjMediaCast extends ilObject
{
    public const ORDER_TITLE = 1;
    public const ORDER_CREATION_DATE_ASC = 2;
    public const ORDER_CREATION_DATE_DESC = 3;
    public const ORDER_MANUAL = 4;
    public const VIEW_LIST = "";
    public const VIEW_GALLERY = "gallery";          // legacy gallery, @todo remove
    public const VIEW_IMG_GALLERY = "img_gallery";
    public const VIEW_PODCAST = "podcast";
    public const VIEW_VCAST = "video";
    public const AUTOPLAY_NO = 0;
    public const AUTOPLAY_ACT = 1;
    public const AUTOPLAY_INACT = 2;


    protected array $itemsarray;
    protected ilObjUser $user;
    public static array $purposes = array("Standard");
    protected bool $online = false;
    protected bool $publicfiles = false;
    protected bool $downloadable = true;
    protected int $order = 0;
    protected string $view_mode = self::VIEW_VCAST;
    protected int $defaultAccess = 0;   // 0 = logged in users, 1 = public access
    protected array $mob_mapping = [];
    protected int $nr_initial_videos = 5;
    protected bool $new_items_in_lp = true;
    protected int $autoplay_mode = self::AUTOPLAY_ACT;

    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->type = "mcst";
        $mcst_set = new ilSetting("mcst");
        $this->setDefaultAccess($mcst_set->get("defaultaccess") == "users" ? 0 : 1);
        $this->setOrder(self::ORDER_CREATION_DATE_DESC);
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function setOnline(bool $a_online) : void
    {
        $this->online = $a_online;
    }

    public function getOnline() : bool
    {
        return $this->online;
    }

    public function setPublicFiles(bool $a_publicfiles) : void
    {
        $this->publicfiles = $a_publicfiles;
    }

    public function getPublicFiles() : bool
    {
        return $this->publicfiles;
    }

    public function setViewMode(string $a_val) : void
    {
        $this->view_mode = $a_val;
    }

    public function getViewMode() : string
    {
        return $this->view_mode;
    }

    public function setItemsArray(array $a_itemsarray) : void
    {
        $this->itemsarray = $a_itemsarray;
    }

    public function getItemsArray() : array
    {
        return $this->itemsarray;
    }

    public function setAutoplayMode(int $a_val) : void
    {
        $this->autoplay_mode = $a_val;
    }

    public function getAutoplayMode() : int
    {
        return $this->autoplay_mode;
    }

    public function setNumberInitialVideos(int $a_val) : void
    {
        $this->nr_initial_videos = $a_val;
    }

    public function getNumberInitialVideos() : int
    {
        return $this->nr_initial_videos;
    }

    /**
     * Set new items automatically in lp
     */
    public function setNewItemsInLearningProgress(bool $a_val) : void
    {
        $this->new_items_in_lp = $a_val;
    }

    public function getNewItemsInLearningProgress() : bool
    {
        return $this->new_items_in_lp;
    }

    public function getSortedItemsArray() : array
    {
        $med_items = $this->getItemsArray();

        // sort by order setting
        switch ($this->getOrder()) {
            case ilObjMediaCast::ORDER_TITLE:
                $med_items = ilArrayUtil::sortArray($med_items, "title", "asc", false, true);
                break;
            
            case ilObjMediaCast::ORDER_CREATION_DATE_ASC:
                $med_items = ilArrayUtil::sortArray($med_items, "creation_date", "asc", false, true);
                break;
            
            case ilObjMediaCast::ORDER_CREATION_DATE_DESC:
                $med_items = ilArrayUtil::sortArray($med_items, "creation_date", "desc", false, true);
                break;
            
            case ilObjMediaCast::ORDER_MANUAL:
                $order = array_flip($this->readOrder());
                $pos = sizeof($order);
                foreach (array_keys($med_items) as $idx) {
                    if (array_key_exists($idx, $order)) {
                        $med_items[$idx]["order"] = ($order[$idx] + 1) * 10;
                    }
                    // item has no order yet
                    else {
                        $med_items[$idx]["order"] = (++$pos) * 10;
                    }
                }
                
                $med_items = ilArrayUtil::sortArray($med_items, "order", "asc", true, true);
                break;
        }
        return $med_items;
    }
    
    public function setDownloadable(bool $a_downloadable) : void
    {
        $this->downloadable = $a_downloadable;
    }

    public function getDownloadable() : bool
    {
        return $this->downloadable;
    }
    
    public function getDefaultAccess() : int
    {
        return $this->defaultAccess;
    }
    
    public function setDefaultAccess(int $value) : void
    {
        $this->defaultAccess = ($value === 0) ? 0 : 1;
    }
    
    public function setOrder(int $a_value) : void
    {
        $this->order = $a_value;
    }

    public function getOrder() : int
    {
        return $this->order;
    }
    
    /**
     * Gets the disk usage of the object in bytes.
     */
    public function getDiskUsage() : int
    {
        return ilObjMediaCastAccess::_lookupDiskUsage($this->id);
    }
    
    public function create() : int
    {
        $ilDB = $this->db;

        $id = parent::create();
        
        $query = "INSERT INTO il_media_cast_data (" .
            " id" .
            ", is_online" .
            ", public_files" .
            ", downloadable" .
            ", def_access" .
            ", sortmode" .
            ", viewmode" .
            ", autoplaymode" .
            ", nr_initial_videos" .
            ", new_items_in_lp" .
            " ) VALUES (" .
            $ilDB->quote($this->getId(), "integer")
            . "," . $ilDB->quote((int) $this->getOnline(), "integer")
            . "," . $ilDB->quote((int) $this->getPublicFiles(), "integer")
            . "," . $ilDB->quote((int) $this->getDownloadable(), "integer")
            . "," . $ilDB->quote((int) $this->getDefaultAccess(), "integer")
            . "," . $ilDB->quote((int) $this->getOrder(), "integer")
            . "," . $ilDB->quote($this->getViewMode(), "text")
            . "," . $ilDB->quote((int) $this->getAutoplayMode(), "integer")
            . "," . $ilDB->quote((int) $this->getNumberInitialVideos(), "integer")
            . "," . $ilDB->quote((int) $this->getNewItemsInLearningProgress(), "integer")
            . ")";
        $ilDB->manipulate($query);
        return $id;
    }

    public function update() : bool
    {
        $ilDB = $this->db;
        
        if (!parent::update()) {
            return false;
        }

        // update media cast data
        $query = "UPDATE il_media_cast_data SET " .
            " is_online = " . $ilDB->quote((int) $this->getOnline(), "integer") .
            ", public_files = " . $ilDB->quote((int) $this->getPublicFiles(), "integer") .
            ", downloadable = " . $ilDB->quote((int) $this->getDownloadable(), "integer") .
            ", def_access = " . $ilDB->quote($this->getDefaultAccess(), "integer") .
            ", sortmode = " . $ilDB->quote($this->getOrder(), "integer") .
            ", viewmode = " . $ilDB->quote($this->getViewMode(), "text") .
            ", autoplaymode = " . $ilDB->quote($this->getAutoplayMode(), "integer") .
            ", nr_initial_videos = " . $ilDB->quote($this->getNumberInitialVideos(), "integer") .
            ", new_items_in_lp = " . $ilDB->quote($this->getNewItemsInLearningProgress(), "integer") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");

        $ilDB->manipulate($query);

        return true;
    }
    
    public function read() : void
    {
        $ilDB = $this->db;
        
        parent::read();
        $this->readItems();
        
        $query = "SELECT * FROM il_media_cast_data WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        $this->setOnline($rec["is_online"]);
        $this->setPublicFiles($rec["public_files"]);
        $this->setDownloadable($rec["downloadable"]);
        $this->setDefaultAccess($rec["def_access"]);
        $this->setOrder((int) $rec["sortmode"]);
        $this->setViewMode($rec["viewmode"]);
        $this->setAutoplayMode((int) $rec["autoplaymode"]);
        $this->setNumberInitialVideos((int) $rec["nr_initial_videos"]);
        $this->setNewItemsInLearningProgress((bool) $rec["new_items_in_lp"]);
    }


    public function delete() : bool
    {
        $ilDB = $this->db;

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // delete all items
        $med_items = $this->getItemsArray();
        foreach ($med_items as $item) {
            $news_item = new ilNewsItem($item["id"]);
            $news_item->delete();
        }
        
        $this->deleteOrder();

        // delete record of table il_media_cast_data
        $query = "DELETE FROM il_media_cast_data" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);
        
        return true;
    }

    public function readItems(bool $a_oldest_first = false) : array
    {
        //
        $it = new ilNewsItem();
        $it->setContextObjId($this->getId());
        $it->setContextObjType($this->getType());
        $this->itemsarray = $it->queryNewsForContext(false, 0, "", false, $a_oldest_first);
        
        return $this->itemsarray;
    }

    public function deleteOrder() : void
    {
        $ilDB = $this->db;
        
        if (!$this->getId()) {
            return;
        }
        
        $sql = "DELETE FROM il_media_cast_data_ord" .
            " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($sql);
    }
    
    public function readOrder() : ?array
    {
        $ilDB = $this->db;
        
        if (!$this->getId()) {
            return null;
        }
        
        $all = array();
        $sql = "SELECT item_id FROM il_media_cast_data_ord" .
            " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer") .
            " ORDER BY pos";
        $res = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[] = $row["item_id"];
        }
        return $all;
    }
    
    public function saveOrder(array $a_items) : void
    {
        $ilDB = $this->db;
        
        if (!$this->getId()) {
            return;
        }
        
        $this->deleteOrder();
        
        $pos = 0;
        foreach ($a_items as $item_id) {
            $pos++;
            
            $sql = "INSERT INTO il_media_cast_data_ord (obj_id,item_id,pos)" .
                " VALUES (" . $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($item_id, "integer") . "," .
                $ilDB->quote($pos, "integer") . ")";
            $ilDB->manipulate($sql);
        }
    }

    protected function copyOrder(
        ilObjMediaCast $newObj,
        array $mapping
    ) : void {
        $items = [];
        foreach ($this->readOrder() as $i) {
            $items[] = $mapping[$i];
        }
        $newObj->saveOrder($items);
    }

    public function cloneObject(int $a_target_id, int $a_copy_id = 0, bool $a_omit_tree = false) : ?ilObject
    {
        /** @var ilObjMediaCast $new_obj */
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOnline($this->getOnline());
        }
        
        $new_obj->setPublicFiles($this->getPublicFiles());
        $new_obj->setDownloadable($this->getDownloadable());
        $new_obj->setDefaultAccess($this->getDefaultAccess());
        $new_obj->setOrder($this->getOrder());
        $new_obj->setViewMode($this->getViewMode());
        $new_obj->setAutoplayMode($this->getAutoplayMode());
        $new_obj->setNumberInitialVideos($this->getNumberInitialVideos());
        $new_obj->setNewItemsInLearningProgress($this->getNewItemsInLearningProgress());

        $new_obj->update();

        $pf = ilBlockSetting::_lookup("news", "public_feed", 0, $this->getId());
        $keeprss = (int) ilBlockSetting::_lookup("news", "keep_rss_min", 0, $this->getId());
        ilBlockSetting::_write("news", "public_feed", $pf, 0, $new_obj->getId());
        ilBlockSetting::_write("news", "keep_rss_min", $keeprss, 0, $new_obj->getId());

        // copy items
        $mapping = $this->copyItems($new_obj);
        $this->copyOrder($new_obj, $mapping);

        // clone LP settings
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_obj->getId());
        unset($obj_settings);

        /** @var ilScormLP $olp */

        $olp = ilObjectLP::getInstance($this->getId());
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $collection->cloneCollection($new_obj->getRefId(), $cp_options->getCopyId());
        }

        return $new_obj;
    }

    public function copyItems(ilObjMediaCast $a_new_obj) : array
    {
        $ilUser = $this->user;

        $item_mapping = [];
        foreach ($this->readItems(true) as $item) {
            // copy media object
            $mob_id = $item["mob_id"];
            $mob = new ilObjMediaObject($mob_id);
            $new_mob = $mob->duplicate();

            // copy news item
            // create new media cast item
            $mc_item = new ilNewsItem();
            $mc_item->setMobId($new_mob->getId());
            $mc_item->setContentType(NEWS_AUDIO);
            $mc_item->setContextObjId($a_new_obj->getId());
            $mc_item->setContextObjType($a_new_obj->getType());
            $mc_item->setUserId($ilUser->getId());
            $mc_item->setPlaytime($item["playtime"]);
            $mc_item->setTitle($item["title"]);
            $mc_item->setContent($item["content"]);
            $mc_item->setVisibility($item["visibility"]);
            $mc_item->create();
            $this->mob_mapping[$mob_id] = $new_mob->getId();
            $item_mapping[$item["id"]] = $mc_item->getId();
        }
        return $item_mapping;
    }
    
    public function handleLPUpdate(
        int $a_user_id,
        int $a_mob_id
    ) : void {
        // using read events to persist mob status
        ilChangeEvent::_recordReadEvent(
            "mob",
            $this->getRefId(),
            $a_mob_id,
            $a_user_id
        );
        
        // trigger LP update
        ilLPStatusWrapper::_updateStatus($this->getId(), $a_user_id);
    }

    /**
     * Add media object to media cast
     */
    public function addMobToCast(
        int $mob_id,
        int $user_id,
        string $long_desc = ""
    ) : int {
        $mob = new ilObjMediaObject($mob_id);
        $news_set = new ilSetting("news");
        $enable_internal_rss = $news_set->get("enable_rss_for_internal");

        // create new media cast item
        $mc_item = new ilNewsItem();
        $mc_item->setMobId($mob->getId());
        $mc_item->setContentType(NEWS_AUDIO);
        $mc_item->setContextObjId($this->getId());
        $mc_item->setContextObjType($this->getType());
        $mc_item->setUserId($user_id);
        $med_item = $mob->getMediaItem("Standard");
        $mc_item->setPlaytime($this->getPlaytimeForSeconds($med_item->getDuration()));
        $mc_item->setTitle($mob->getTitle());
        $mc_item->setContent($mob->getLongDescription());
        if ($long_desc != "") {
            $mc_item->setContent($long_desc);
        }
        $mc_item->setLimitation(false);
        // @todo handle visibility
        $mc_item->create();

        $lp = ilObjectLP::getInstance($this->getId());

        // see ilLPListOfSettingsGUI assign
        $collection = $lp->getCollectionInstance();
        if ($collection && $collection->hasSelectableItems()) {
            $collection->activateEntries([$mob_id]);
            $lp->resetCaches();
            ilLPStatusWrapper::_refreshStatus($this->getId());
        }
        return $mc_item->getId();
    }

    /**
     * Get playtime for seconds
     */
    public function getPlaytimeForSeconds(int $seconds) : string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        $duration = str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" .
            str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":" .
            str_pad($seconds, 2, "0", STR_PAD_LEFT);
        return $duration;
    }
}
