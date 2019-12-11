<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define("IL_MODE_ALIAS", 1);
define("IL_MODE_OUTPUT", 2);
define("IL_MODE_FULL", 3);

require_once("./Services/MediaObjects/classes/class.ilMediaItem.php");
include_once "./Services/Object/classes/class.ilObject.php";

/** @defgroup ServicesMediaObjects Services/MediaObjects
 */

/**
* Class ilObjMediaObject
*
* Todo: this class must be integrated with group/folder handling
*
* ILIAS Media Object
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesMediaObjects
*/
class ilObjMediaObject extends ilObject
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAppEventHandler
     */
    protected $app_event_handler;

    public $is_alias;
    public $origin_id;
    public $id;
    public $media_items;
    public $contains_int_link;

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->app_event_handler = $DIC["ilAppEventHandler"];
        $this->lng = $DIC->language();
        $this->is_alias = false;
        $this->media_items = array();
        $this->contains_int_link = false;
        $this->type = "mob";
        parent::__construct($a_id, false);
    }

    public function setRefId($a_id)
    {
        $this->ilias->raiseError("Operation ilObjMedia::setRefId() not allowed.", $this->ilias->error_obj->FATAL);
    }

    public function getRefId()
    {
        return false;
    }

    public function putInTree($a_parent_ref)
    {
        $this->ilias->raiseError("Operation ilObjMedia::putInTree() not allowed.", $this->ilias->error_obj->FATAL);
    }

    public function createReference()
    {
        $this->ilias->raiseError("Operation ilObjMedia::createReference() not allowed.", $this->ilias->error_obj->FATAL);
    }

    public function setTitle($a_title)
    {
        parent::setTitle($a_title);
    }

    public function getTitle()
    {
        return parent::getTitle();
    }

    /**
    * checks wether a lm content object with specified id exists or not
    *
    * @param	int		$id		id
    *
    * @return	boolean		true, if lm content object exists
    */
    public static function _exists($a_id, $a_reference = false, $a_type = null)
    {
        global $DIC;

        $ilDB = $DIC->database();

        include_once("./Services/Link/classes/class.ilInternalLink.php");
        if (is_int(strpos($a_id, "_"))) {
            $a_id = ilInternalLink::_extractObjIdOfTarget($a_id);
        }
        
        if (parent::_exists($a_id, false) && ilObject::_lookupType($a_id) == "mob") {
            return true;
        }
        return false;
    }

    /**
    * delete media object
    */
    public function delete()
    {
        $mob_logger = ilLoggerFactory::getLogger('mob');
        $mob_logger->debug("ilObjMediaObject: Delete called for media object ID '" . $this->getId() . "'.");

        if (!($this->getId() > 0)) {
            return;
        }

        $usages = $this->getUsages();

        $mob_logger->debug("ilObjMediaObject: ... Found " . count($usages) . " usages.");

        if (count($usages) == 0) {
            // remove directory
            ilUtil::delDir(ilObjMediaObject::_getDirectory($this->getId()));

            // remove thumbnail directory
            ilUtil::delDir(ilObjMediaObject::_getThumbnailDirectory($this->getId()));

            // delete meta data of mob
            $this->deleteMetaData();

            // delete media items
            ilMediaItem::deleteAllItemsOfMob($this->getId());
            
            // this is just to make sure, there should be no entries left at
            // this point as they depend on the usage
            self::handleQuotaUpdate($this);
                    
            // delete object
            parent::delete();

            $mob_logger->debug("ilObjMediaObject: ... deleted.");
        } else {
            foreach ($usages as $u) {
                $mob_logger->debug("ilObjMediaObject: ... usage type:" . $u["type"] .
                    ", id:" . $u["id"] .
                    ", lang:" . $u["lang"] .
                    ", hist_nr:" . $u["hist_nr"] . ".");
            }
            $mob_logger->debug("ilObjMediaObject: ... not deleted.");
        }
    }

    /**
    * get description of media object
    *
    * @return	string		description
    */
    public function getDescription()
    {
        return parent::getDescription();
    }

    /**
    * set description of media object
    */
    public function setDescription($a_description)
    {
        parent::setDescription($a_description);
    }

    /**
    * Meta data update listener
    *
    * Important note: Do never call create() or update()
    * method of ilObject here. It would result in an
    * endless loop: update object -> update meta -> update
    * object -> ...
    * Use static _writeTitle() ... methods instead.
    *
    * @param	string		$a_element
    */
    public function MDUpdateListener($a_element)
    {
        include_once 'Services/MetaData/classes/class.ilMD.php';

        switch ($a_element) {
            case 'General':

                // Update Title and description
                $md = new ilMD(0, $this->getId(), $this->getType());
                $md_gen = $md->getGeneral();

                if (is_object($md_gen)) {
                    ilObject::_writeTitle($this->getId(), $md_gen->getTitle());
                    $this->setTitle($md_gen->getTitle());
    
                    foreach ($md_gen->getDescriptionIds() as $id) {
                        $md_des = $md_gen->getDescription($id);
                        ilObject::_writeDescription($this->getId(), $md_des->getDescription());
                        $this->setDescription($md_des->getDescription());
                        break;
                    }
                }

                break;

            default:
        }
        return true;
    }

    /**
    * create meta data entry
    */
    public function createMetaData()
    {
        include_once 'Services/MetaData/classes/class.ilMDCreator.php';

        $ilUser = $this->user;

        $md_creator = new ilMDCreator(0, $this->getId(), $this->getType());
        $md_creator->setTitle($this->getTitle());
        $md_creator->setTitleLanguage($ilUser->getPref('language'));
        $md_creator->setDescription($this->getDescription());
        $md_creator->setDescriptionLanguage($ilUser->getPref('language'));
        $md_creator->setKeywordLanguage($ilUser->getPref('language'));
        $md_creator->setLanguage($ilUser->getPref('language'));
        $md_creator->create();

        return true;
    }

    /**
    * update meta data entry
    */
    public function updateMetaData()
    {
        include_once("Services/MetaData/classes/class.ilMD.php");
        include_once("Services/MetaData/classes/class.ilMDGeneral.php");
        include_once("Services/MetaData/classes/class.ilMDDescription.php");

        $md = new ilMD(0, $this->getId(), $this->getType());
        $md_gen = $md->getGeneral();
        $md_gen->setTitle($this->getTitle());

        // sets first description (maybe not appropriate)
        $md_des_ids = $md_gen->getDescriptionIds();
        if (count($md_des_ids) > 0) {
            $md_des = $md_gen->getDescription($md_des_ids[0]);
            $md_des->setDescription($this->getDescription());
            $md_des->update();
        }
        $md_gen->update();
    }

    /**
    * delete meta data entry
    */
    public function deleteMetaData()
    {
        // Delete meta data
        include_once('Services/MetaData/classes/class.ilMD.php');
        $md = new ilMD(0, $this->getId(), $this->getType());
        $md->deleteAll();
    }


    /**
    * add media item to media object
    *
    * @param	object		$a_item		media item object
    */
    public function addMediaItem($a_item)
    {
        $this->media_items[] = $a_item;
    }


    /**
    * get all media items
    *
    * @return	array		array of media item objects
    */
    public function &getMediaItems()
    {
        return $this->media_items;
    }

    /**
     * get item for media purpose
     *
     * @param string $a_purpose
     * @return ilMediaItem
     */
    public function &getMediaItem($a_purpose)
    {
        foreach ($this->media_items as $media_item) {
            if ($media_item->getPurpose() == $a_purpose) {
                return $media_item;
            }
        }
        return false;
    }


    /**
    *
    */
    public function removeMediaItem($a_purpose)
    {
        foreach ($this->media_items as $key => $media_item) {
            if ($media_item->getPurpose() == $a_purpose) {
                unset($this->media_items[$key]);
            }
        }
        // update numbers and keys
        $i = 1;
        $media_items = array();
        foreach ($this->media_items as $media_item) {
            $media_items [$i] = $media_item;
            $media_item->setMobId($this->getId());
            $media_item->setNr($i);
            $i++;
        }
        $this->media_items = $media_items;
    }
    
    /**
    * remove all media items
    */
    public function removeAllMediaItems()
    {
        $this->media_items = array();
    }


    public function getMediaItemNr($a_purpose)
    {
        for ($i=0; $i<count($this->media_items); $i++) {
            if ($this->media_items[$i]->getPurpose() == $a_purpose) {
                return $i + 1;
            }
        }
        return false;
    }

    
    public function hasFullscreenItem()
    {
        return $this->hasPurposeItem("Fullscreen");
    }
    
    /**
     * returns wether object has media item with specific purpose
     *
     * @param string $purpose
     * @return boolean
     */
    public function hasPurposeItem($purpose)
    {
        if (is_object($this->getMediaItem($purpose))) {
            return true;
        } else {
            return false;
        }
    }
    
    

    /**
    * read media object data from db
    */
    public function read()
    {
        //echo "<br>ilObjMediaObject:read";
        parent::read();

        // get media items
        ilMediaItem::_getMediaItemsOfMOb($this);
    }

    /**
    * set id
    */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
    * set wether page object is an alias
    */
    public function setAlias($a_is_alias)
    {
        $this->is_alias = $a_is_alias;
    }

    public function isAlias()
    {
        return $this->is_alias;
    }

    public function setOriginID($a_id)
    {
        return $this->origin_id = $a_id;
    }

    public function getOriginID()
    {
        return $this->origin_id;
    }

    /*
    function getimportId()
    {
        return $this->meta_data->getImportIdentifierEntryID();
    }*/


    /**
    * get import id
    */
    public function getImportId()
    {
        return $this->import_id;
    }

    public function setImportId($a_id)
    {
        $this->import_id = $a_id;
    }

    /**
    * create media object in db
    */
    public function create($a_create_meta_data = false, $a_save_media_items = true)
    {
        parent::create();

        if (!$a_create_meta_data) {
            $this->createMetaData();
        }

        if ($a_save_media_items) {
            $media_items = $this->getMediaItems();
            for ($i=0; $i<count($media_items); $i++) {
                $item = $media_items[$i];
                $item->setMobId($this->getId());
                $item->setNr($i+1);
                $item->create();
            }
        }

        self::handleQuotaUpdate($this);

        $ilAppEventHandler = $this->app_event_handler;
        $ilAppEventHandler->raise(
            'Services/MediaObjects',
            'create',
            array('object' => $this,
            'obj_type' => 'mob',
            'obj_id' => $this->getId())
        );
    }


    /**
    * update media object in db
    */
    public function update($a_upload=false)
    {
        parent::update();
        
        if (!$a_upload) {
            $this->updateMetaData();
        }
        
        ilMediaItem::deleteAllItemsOfMob($this->getId());

        // iterate all items
        $media_items = $this->getMediaItems();
        $j = 1;
        foreach ($media_items as $key => $val) {
            $item = $media_items[$key];
            if (is_object($item)) {
                $item->setMobId($this->getId());
                $item->setNr($j);
                if ($item->getLocationType() == "Reference") {
                    $item->extractUrlParameters();
                }
                $item->create();
                $j++;
            }
        }
        
        self::handleQuotaUpdate($this);
        $ilAppEventHandler = $this->app_event_handler;
        $ilAppEventHandler->raise(
            'Services/MediaObjects',
            'update',
            array('object' => $this,
                    'obj_type' => 'mob',
                    'obj_id' => $this->getId())
        );
    }
    
    protected static function handleQuotaUpdate(ilObjMediaObject $a_mob)
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        // if neither workspace nor portfolios are activated, we skip
        // the quota update here. this due to performance reasons on installations
        // that do not use workspace/portfolios, but heavily copy content.
        // in extreme cases (media object in pool and personal blog, deactivate workspace, change media object,
        // this may lead to incorrect data in the quota calculation)
        if ($ilSetting->get("disable_personal_workspace") && !$ilSetting->get('user_portfolios')) {
            return;
        }

        $parent_obj_ids = array();
        foreach ($a_mob->getUsages() as $item) {
            $parent_obj_id = $a_mob->getParentObjectIdForUsage($item);
            if ($parent_obj_id &&
                !in_array($parent_obj_id, $parent_obj_ids)) {
                $parent_obj_ids[]= $parent_obj_id;
            }
        }
        
        // we could suppress this if object is present in a (repository) media pool
        // but this would lead to "quota-breaches" when the pool item is deleted
        // and "suddenly" all workspace owners get filesize added to their
        // respective quotas, regardless of current status
        
        include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
        ilDiskQuotaHandler::handleUpdatedSourceObject(
            $a_mob->getType(),
            $a_mob->getId(),
            ilUtil::dirSize($a_mob->getDataDirectory()),
            $parent_obj_ids
        );
    }

    /**
     * Get absolute directory
     *
     * @param int $a_mob_id
     * @return string
     */
    public static function _getDirectory($a_mob_id)
    {
        return ilUtil::getWebspaceDir() . "/" . self::_getRelativeDirectory($a_mob_id);
    }

    /**
     * Get relative (to webspace dir) directory
     *
     * @param int $a_mob_id
     * @return string
     */
    public static function _getRelativeDirectory($a_mob_id)
    {
        return "mobs/mm_" . $a_mob_id;
    }


    /**
     * get directory for files of media object (static)
     * @param int $a_mob_id media object id
     * @return string
     */
    public static function _getURL($a_mob_id)
    {
        return ilUtil::getHtmlPath(ilUtil::getWebspaceDir() . "/mobs/mm_" . $a_mob_id);
    }

    /**
    * get directory for files of media object (static)
    *
    * @param	int		$a_mob_id		media object id
    */
    public static function _getThumbnailDirectory($a_mob_id, $a_mode = "filesystem")
    {
        return ilUtil::getWebspaceDir($a_mode) . "/thumbs/mm_" . $a_mob_id;
    }
    
    /**
    * Get path for standard item.
    *
    * @param	int		$a_mob_id		media object id
    */
    public static function _lookupStandardItemPath(
        $a_mob_id,
        $a_url_encode = false,
        $a_web = true
    ) {
        return ilObjMediaObject::_lookupItemPath($a_mob_id, $a_url_encode, $a_web, "Standard");
    }
    
    /**
    * Get path for item with specific purpose.
    *
    * @param	int		$a_mob_id		media object id
    */
    public static function _lookupItemPath(
        $a_mob_id,
        $a_url_encode = false,
        $a_web = true,
        $a_purpose = ""
    ) {
        if ($a_purpose == "") {
            $a_purpose = "Standard";
        }
        $location = ilMediaItem::_lookupLocationForMobId($a_mob_id, $a_purpose);
        if (preg_match("/https?\:/i", $location)) {
            return $location;
        }
            
        if ($a_url_encode) {
            $location = rawurlencode($location);
        }

        $path = ($a_web)
            ? ILIAS_HTTP_PATH
            : ".";
            
        return $path . "/data/" . CLIENT_ID . "/mobs/mm_" . $a_mob_id . "/" . $location;
    }

    /**
     * Create file directory of media object
     */
    public function createDirectory()
    {
        $path = ilObjMediaObject::_getDirectory($this->getId());
        ilUtil::createDirectory($path);
        if (!is_dir($path)) {
            $this->ilias->raiseError("Failed to create directory $path.", $this->ilias->error_obj->FATAL);
        }
    }

    /**
     * Create thumbnail directory
     */
    public static function _createThumbnailDirectory($a_obj_id)
    {
        ilUtil::createDirectory(ilUtil::getWebspaceDir() . "/thumbs");
        ilUtil::createDirectory(ilUtil::getWebspaceDir() . "/thumbs/mm_" . $a_obj_id);
    }
    
    /**
     * Get files of directory
     *
     * @param string $a_subdir subdirectry
     * @return array array of files
     */
    public function getFilesOfDirectory($a_subdir = "")
    {
        $a_subdir = str_replace("..", "", $a_subdir);
        $dir = ilObjMediaObject::_getDirectory($this->getId());
        if ($a_subdir != "") {
            $dir.= "/" . $a_subdir;
        }
        
        $files = array();
        if (is_dir($dir)) {
            $entries = ilUtil::getDir($dir);
            foreach ($entries as $e) {
                if (is_file($dir . "/" . $e["entry"]) && $e["entry"] != "." && $e["entry"] != "..") {
                    $files[] = $e["entry"];
                }
            }
        }
 
        return $files;
    }
    

    /**
    * get MediaObject XLM Tag
    *  @param	int		$a_mode		IL_MODE_ALIAS | IL_MODE_OUTPUT | IL_MODE_FULL
    */
    public function getXML($a_mode = IL_MODE_FULL, $a_inst = 0, $a_sign_locals = false)
    {
        $ilUser = $this->user;
        
        // TODO: full implementation of all parameters
        //echo "-".$a_mode."-";
        switch ($a_mode) {
            case IL_MODE_ALIAS:
                $xml = "<MediaObject>";
                $xml .= "<MediaAlias OriginId=\"il__mob_" . $this->getId() . "\"/>";
                $media_items = $this->getMediaItems();
                for ($i=0; $i<count($media_items); $i++) {
                    $item = $media_items[$i];
                    $xml .= "<MediaAliasItem Purpose=\"" . $item->getPurpose() . "\">";

                    // Layout
                    $width = ($item->getWidth() != "")
                        ? "Width=\"" . $item->getWidth() . "\""
                        : "";
                    $height = ($item->getHeight() != "")
                        ? "Height=\"" . $item->getHeight() . "\""
                        : "";
                    $halign = ($item->getHAlign() != "")
                        ? "HorizontalAlign=\"" . $item->getHAlign() . "\""
                        : "";
                    $xml .= "<Layout $width $height $halign />";

                    // Caption
                    if ($item->getCaption() != "") {
                        $xml .= "<Caption Align=\"bottom\">" .
                            $this->escapeProperty($item->getCaption()) . "</Caption>";
                    }

                    // Text Representation
                    if ($item->getTextRepresentation() != "") {
                        $xml .= "<TextRepresentation>" .
                            $this->escapeProperty($item->getTextRepresentation()) . "</TextRepresentation>";
                    }

                    // Parameter
                    $parameters = $item->getParameters();
                    foreach ($parameters as $name => $value) {
                        $xml .= "<Parameter Name=\"$name\" Value=\"$value\"/>";
                    }
                    $xml .= $item->getMapAreasXML();
                    $xml .= "</MediaAliasItem>";
                }
                break;

            // for output we need technical sections of meta data
            case IL_MODE_OUTPUT:

                // get first technical section
//				$meta = $this->getMetaData();
                $xml = "<MediaObject Id=\"il__mob_" . $this->getId() . "\">";

                $media_items = $this->getMediaItems();
                for ($i=0; $i<count($media_items); $i++) {
                    $item = $media_items[$i];

                    $xml .= "<MediaItem Purpose=\"" . $item->getPurpose() . "\">";

                    if ($a_sign_locals && $item->getLocationType() == "LocalFile") {
                        require_once 'Services/WebAccessChecker/classes/class.ilWACSignedPath.php';
                        $location = ilWACSignedPath::signFile($this->getDataDirectory() . "/" . $item->getLocation());
                        $location = substr($location, strrpos($location, "/") + 1);
                    } else {
                        $location = $item->getLocation();
                        if ($item->getLocationType() != "LocalFile") {  //#25941
                            $location = ilUtil::secureUrl($location); //#23518
                        }
                    }

                    $xml.= "<Location Type=\"" . $item->getLocationType() . "\">" .
                        $this->handleAmps($location) . "</Location>";

                    // Format
                    $xml.= "<Format>" . $item->getFormat() . "</Format>";

                    // Layout
                    $width = ($item->getWidth() != "")
                        ? "Width=\"" . $item->getWidth() . "\""
                        : "";
                    $height = ($item->getHeight() != "")
                        ? "Height=\"" . $item->getHeight() . "\""
                        : "";
                    $halign = ($item->getHAlign() != "")
                        ? "HorizontalAlign=\"" . $item->getHAlign() . "\""
                        : "";
                    $xml .= "<Layout $width $height $halign />";

                    // Caption
                    if ($item->getCaption() != "") {
                        $xml .= "<Caption Align=\"bottom\">" .
                            $this->escapeProperty($item->getCaption()) . "</Caption>";
                    }
                    
                    // Text Representation
                    if ($item->getTextRepresentation() != "") {
                        $xml .= "<TextRepresentation>" .
                            $this->escapeProperty($item->getTextRepresentation()) . "</TextRepresentation>";
                    }

                    // Title
                    if ($this->getTitle() != "") {
                        $xml .= "<Title>" .
                            str_replace("&", "&amp;", $this->getTitle()) . "</Title>";
                    }

                    // Parameter
                    $parameters = $item->getParameters();
                    foreach ($parameters as $name => $value) {
                        $xml .= "<Parameter Name=\"$name\" Value=\"$value\"/>";
                    }
                    $xml .= $item->getMapAreasXML();
                    
                    // Subtitles
                    if ($item->getPurpose() == "Standard") {
                        $srts = $this->getSrtFiles();
                        foreach ($srts as $srt) {
                            $def = "";
                            $meta_lang = "";
                            if ($ilUser->getLanguage() != $meta_lang &&
                                $ilUser->getLanguage() == $srt["language"]) {
                                $def = ' Default="true" ';
                            }
                            $xml .= "<Subtitle File=\"" . $srt["full_path"] .
                                "\" Language=\"" . $srt["language"] . "\" " . $def . "/>";
                        }
                    }
                    $xml .= "</MediaItem>";
                }
                break;

            // full xml for export
            case IL_MODE_FULL:

//				$meta = $this->getMetaData();
                $xml = "<MediaObject>";

                // meta data
                include_once("Services/MetaData/classes/class.ilMD2XML.php");
                $md2xml = new ilMD2XML(0, $this->getId(), $this->getType());
                $md2xml->setExportMode(true);
                $md2xml->startExport();
                $xml.= $md2xml->getXML();

                $media_items = $this->getMediaItems();
                for ($i=0; $i<count($media_items); $i++) {
                    $item = $media_items[$i];
                    
                    // highlight mode
                    $xml .= "<MediaItem Purpose=\"" . $item->getPurpose() . "\">";

                    // Location
                    $xml.= "<Location Type=\"" . $item->getLocationType() . "\">" .
                        $this->handleAmps($item->getLocation()) . "</Location>";

                    // Format
                    $xml.= "<Format>" . $item->getFormat() . "</Format>";

                    // Layout
                    $width = ($item->getWidth() != "")
                        ? "Width=\"" . $item->getWidth() . "\""
                        : "";
                    $height = ($item->getHeight() != "")
                        ? "Height=\"" . $item->getHeight() . "\""
                        : "";
                    $halign = ($item->getHAlign() != "")
                        ? "HorizontalAlign=\"" . $item->getHAlign() . "\""
                        : "";
                    $xml .= "<Layout $width $height $halign />";

                    // Caption
                    if ($item->getCaption() != "") {
                        $xml .= "<Caption Align=\"bottom\">" .
                            str_replace("&", "&amp;", $item->getCaption()) . "</Caption>";
                    }
                    
                    // Text Representation
                    if ($item->getTextRepresentation() != "") {
                        $xml .= "<TextRepresentation>" .
                            str_replace("&", "&amp;", $item->getTextRepresentation()) . "</TextRepresentation>";
                    }

                    // Parameter
                    $parameters = $item->getParameters();
                    foreach ($parameters as $name => $value) {
                        $xml .= "<Parameter Name=\"$name\" Value=\"$value\"/>";
                    }
                    $xml .= $item->getMapAreasXML(true, $a_inst);
                    $xml .= "</MediaItem>";
                }
                break;
        }
        $xml .= "</MediaObject>";
        return $xml;
    }

    /**
     * Escape property (e.g. title, caption) to XSLT -> HTML output
     *
     * @param string $a_value
     * @return string
     */
    protected function escapeProperty($a_value)
    {
        return htmlspecialchars($a_value);
    }


    /**
    * Replace "&" (if not an "&amp;") with "&amp;"
    */
    public function handleAmps($a_str)
    {
        $a_str = str_replace("&amp;", "&", $a_str);
        $a_str = str_replace("&", "&amp;", $a_str);
        return $a_str;
    }
    
    /**
    * export XML
    */
    public function exportXML(&$a_xml_writer, $a_inst = 0)
    {
        $a_xml_writer->appendXML($this->getXML(IL_MODE_FULL, $a_inst));
    }


    /**
    * export all media files of object to target directory
    * note: target directory must be the export target directory,
    * "/objects/il_<inst>_mob_<mob_id>/..." will be appended to this directory
    *
    * @param	string		$a_target_dir		target directory
    */
    public function exportFiles($a_target_dir)
    {
        $subdir = "il_" . IL_INST_ID . "_mob_" . $this->getId();
        ilUtil::makeDir($a_target_dir . "/objects/" . $subdir);

        $mobdir = ilUtil::getWebspaceDir() . "/mobs/mm_" . $this->getId();
        ilUtil::rCopy($mobdir, $a_target_dir . "/objects/" . $subdir);
        //echo "from:$mobdir:to:".$a_target_dir."/objects/".$subdir.":<br>";
    }

    public function exportMediaFullscreen($a_target_dir, $pg_obj)
    {
        $subdir = "il_" . IL_INST_ID . "_mob_" . $this->getId();
        $a_target_dir = $a_target_dir . "/objects/" . $subdir;
        ilUtil::makeDir($a_target_dir);
        $tpl = new ilTemplate("tpl.fullscreen.html", true, true, "Modules/LearningModule");
        $tpl->setCurrentBlock("ilMedia");

        //$int_links = $page_object->getInternalLinks();
        $med_links = ilMediaItem::_getMapAreasIntLinks($this->getId());

        // @todo
        //$link_xml = $this->getLinkXML($med_links, $this->getLayoutLinkTargets());

        require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        //$media_obj = new ilObjMediaObject($_GET["mob_id"]);
        require_once("./Services/COPage/classes/class.ilPageObject.php");

        $xml = "<dummy>";
        // todo: we get always the first alias now (problem if mob is used multiple
        // times in page)
        $xml.= $pg_obj->getMediaAliasElement($this->getId());
        $xml.= $this->getXML(IL_MODE_OUTPUT);
        //$xml.= $link_xml;
        $xml.="</dummy>";
        
        //die(htmlspecialchars($xml));

        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        $args = array( '/_xml' => $xml, '/_xsl' => $xsl );
        $xh = xslt_create();

        //echo "<b>XML:</b>".htmlentities($xml);
        // determine target frames for internal links
        $wb_path = "";
        $enlarge_path = "";
        $params = array('mode' => "fullscreen", 'enlarge_path' => $enlarge_path,
            'link_params' => "ref_id=" . $_GET["ref_id"],'fullscreen_link' => "",
            'ref_id' => $_GET["ref_id"], 'webspace_path' => $wb_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        //echo xslt_error($xh);
        xslt_free($xh);

        // unmask user html
        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
        $tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "../../css/style.css");
        $tpl->setVariable("LOCATION_STYLESHEET", "../../css/system.css");
        $tpl->setVariable("MEDIA_CONTENT", $output);
        $output = $tpl->get();
        //$output = preg_replace("/\/mobs\/mm_(\d+)\/([^\"]+)/i","$2",$output);
        $output = preg_replace("/mobs\/mm_(\d+)\/([^\"]+)/i", "$2", $output);
        $output = preg_replace("/\.\/Services\/MediaObjects\/flash_mp3_player/i", "../../players", $output);
        $output = preg_replace("/\.\/" . str_replace("/", "\/", ilPlayerUtil::getFlashVideoPlayerDirectory()) . "/i", "../../players", $output);
        $output = preg_replace("/file=..\/..\/..\//i", "file=../objects/" . $subdir . "/", $output);
        //die(htmlspecialchars($output));
        fwrite(fopen($a_target_dir . '/fullscreen.html', 'w'), $output);
    }

    public function modifyExportIdentifier($a_tag, $a_param, $a_value)
    {
        if ($a_tag == "Identifier" && $a_param == "Entry") {
            $a_value = ilUtil::insertInstIntoID($a_value);
        }

        return $a_value;
    }


    //////
    // EDIT METHODS: these methods act on the media alias in the dom
    //////

    /**
    * content parser set this flag to true, if the media object contains internal links
    * (this method should only be called by the import parser)
    *
    * @param	boolean		$a_contains_link		true, if page contains intern link tag(s)
    */
    public function setContainsIntLink($a_contains_link)
    {
        $this->contains_int_link = $a_contains_link;
    }

    /**
    * returns true, if mob was marked as containing an intern link (via setContainsIntLink)
    * (this method should only be called by the import parser)
    */
    public function containsIntLink()
    {
        return $this->contains_int_link;
    }

    /**
    * static
    */
    public static function _deleteAllUsages($a_type, $a_id, $a_usage_hist_nr = 0, $a_lang = "-")
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $and_hist = "";
        if ($a_usage_hist_nr !== false) {
            $and_hist = " AND usage_hist_nr = " . $ilDB->quote($a_usage_hist_nr, "integer");
        }
        
        $mob_ids = array();
        $set = $ilDB->query("SELECT id FROM mob_usage" .
            " WHERE usage_type = " . $ilDB->quote($a_type, "text") .
            " AND usage_id = " . $ilDB->quote($a_id, "integer") .
            " AND usage_lang = " . $ilDB->quote($a_lang, "text") .
            $and_hist);
        while ($row = $ilDB->fetchAssoc($set)) {
            $mob_ids[] = $row["id"];
        }
        
        $q = "DELETE FROM mob_usage WHERE usage_type = " .
            $ilDB->quote($a_type, "text") .
            " AND usage_id= " . $ilDB->quote($a_id, "integer") .
            " AND usage_lang = " . $ilDB->quote($a_lang, "text") .
            $and_hist;
        $ilDB->manipulate($q);
        
        foreach ($mob_ids as $mob_id) {
            self::handleQuotaUpdate(new self($mob_id));
        }
    }

    /**
    * get mobs of object
    */
    public static function _getMobsOfObject($a_type, $a_id, $a_usage_hist_nr = 0, $a_lang = "-")
    {
        global $DIC;

        $ilDB = $DIC->database();

        $lstr = "";
        if ($a_lang != "") {
            $lstr = " AND usage_lang = " . $ilDB->quote($a_lang, "text");
        }
        $hist_str = "";
        if ($a_usage_hist_nr !== false) {
            $hist_str = " AND usage_hist_nr = " . $ilDB->quote($a_usage_hist_nr, "integer");
        }

        $q = "SELECT * FROM mob_usage WHERE " .
            "usage_type = " . $ilDB->quote($a_type, "text") . " AND " .
            "usage_id = " . $ilDB->quote($a_id, "integer") .
            $lstr . $hist_str;
        $mobs = array();
        $mob_set = $ilDB->query($q);
        while ($mob_rec = $ilDB->fetchAssoc($mob_set)) {
            if (ilObject::_lookupType($mob_rec["id"]) == "mob") {
                $mobs[$mob_rec["id"]] = $mob_rec["id"];
            }
        }

        return $mobs;
    }

    /**
    * Save usage of mob within another container (e.g. page)
    */
    public static function _saveUsage($a_mob_id, $a_type, $a_id, $a_usage_hist_nr = 0, $a_lang = "-")
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->replace(
            "mob_usage",
            array(
                "id" => array("integer", (int) $a_mob_id),
                "usage_type" => array("text", $a_type),
                "usage_id" => array("integer", $a_id),
                "usage_lang" => array("text", $a_lang),
                "usage_hist_nr" => array("integer", (int) $a_usage_hist_nr)
                ),
            array()
        );

        self::handleQuotaUpdate(new self($a_mob_id));
    }

    /**
    * Remove usage of mob in another container
    */
    public static function _removeUsage($a_mob_id, $a_type, $a_id, $a_usage_hist_nr = 0, $a_lang = "-")
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "DELETE FROM mob_usage WHERE " .
            " id = " . $ilDB->quote((int) $a_mob_id, "integer") . " AND " .
            " usage_type = " . $ilDB->quote($a_type, "text") . " AND " .
            " usage_id = " . $ilDB->quote((int) $a_id, "integer") . " AND " .
            " usage_lang = " . $ilDB->quote($a_lang, "text") . " AND " .
            " usage_hist_nr = " . $ilDB->quote((int) $a_usage_hist_nr, "integer");
        $ilDB->manipulate($q);
        
        self::handleQuotaUpdate(new self($a_mob_id));
    }

    /**
    * get all usages of current media object
    */
    public function getUsages($a_include_history = true)
    {
        return self::lookupUsages($this->getId(), $a_include_history);
    }
    
    /**
    * Lookup usages of media object
    *
    * @todo: This should be all in one context -> mob id table
    */
    public static function lookupUsages($a_id, $a_include_history = true)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $hist_str = "";
        if ($a_include_history) {
            $hist_str = ", usage_hist_nr";
        }
        
        // get usages in pages
        $q = "SELECT DISTINCT usage_type, usage_id, usage_lang" . $hist_str . " FROM mob_usage WHERE id = " .
            $ilDB->quote($a_id, "integer");

        if (!$a_include_history) {
            $q.= " AND usage_hist_nr = " . $ilDB->quote(0, "integer");
        }
        
        $us_set = $ilDB->query($q);
        $ret = array();
        while ($us_rec = $ilDB->fetchAssoc($us_set)) {
            $ut = "";
            if (is_int(strpos($us_rec["usage_type"], ":"))) {
                $us_arr = explode(":", $us_rec["usage_type"]);
                $ut = $us_arr[1];
                $ct = $us_arr[0];
            }

            // check whether page exists
            $skip = false;
            if ($ut == "pg") {
                include_once("./Services/COPage/classes/class.ilPageObject.php");
                if (!ilPageObject::_exists($ct, $us_rec["usage_id"])) {
                    $skip = true;
                }
            }
                
            if (!$skip) {
                $ret[] = array("type" => $us_rec["usage_type"],
                    "id" => $us_rec["usage_id"],
                    "lang" => $us_rec["usage_lang"],
                    "hist_nr" => $us_rec["usage_hist_nr"]);
            }
        }

        // get usages in media pools
        $q = "SELECT DISTINCT mep_id FROM mep_tree JOIN mep_item ON (child = obj_id) WHERE mep_item.foreign_id = " .
            $ilDB->quote($a_id, "integer") . " AND mep_item.type = " . $ilDB->quote("mob", "text");
        $us_set = $ilDB->query($q);
        while ($us_rec = $ilDB->fetchAssoc($us_set)) {
            $ret[] = array("type" => "mep",
                "id" => $us_rec["mep_id"]);
        }
        
        // get usages in news items (media casts)
        include_once("./Services/News/classes/class.ilNewsItem.php");
        $news_usages = ilNewsItem::_lookupMediaObjectUsages($a_id);
        foreach ($news_usages as $nu) {
            $ret[] = $nu;
        }
        

        // get usages in map areas
        $q = "SELECT DISTINCT mob_id FROM media_item it, map_area area " .
            " WHERE area.item_id = it.id " .
            " AND area.link_type = " . $ilDB->quote("int", "text") . " " .
            " AND area.target = " . $ilDB->quote("il__mob_" . $a_id, "text");
        $us_set = $ilDB->query($q);
        while ($us_rec = $ilDB->fetchAssoc($us_set)) {
            $ret[] = array("type" => "map",
                "id" => $us_rec["mob_id"]);
        }

        // get usages in personal clipboards
        $users = ilObjUser::_getUsersForClipboadObject("mob", $a_id);
        foreach ($users as $user) {
            $ret[] = array("type" => "clip",
                "id" => $user);
        }

        return $ret;
    }

    /**
    * Get's the repository object ID of a parent object, if possible
    *
    * see ilWebAccessChecker
    */
    public static function getParentObjectIdForUsage($a_usage, $a_include_all_access_obj_ids = false)
    {
        if (is_int(strpos($a_usage["type"], ":"))) {
            $us_arr = explode(":", $a_usage["type"]);
            $type = $us_arr[1];
            $cont_type = $us_arr[0];
        } else {
            $type = $a_usage["type"];
        }
        
        $id = $a_usage["id"];
        $obj_id = false;
        
        switch ($type) {
            // RTE / tiny mce
            case "html":
                
                switch ($cont_type) {
                    case "qpl":
                        // Question Pool *Question* Text (Test)
                        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
                        $qinfo = assQuestion::_getQuestionInfo($id);
                        if ($qinfo["original_id"] > 0) {
                            include_once("./Modules/Test/classes/class.ilObjTest.php");
                            $obj_id = ilObjTest::_lookupTestObjIdForQuestionId($id);	// usage in test
                        } else {
                            $obj_id = $qinfo["obj_fi"];		// usage in pool
                        }
                        break;
                        
                    case "spl":
                        // Question Pool *Question* Text (Survey)
                        include_once("./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php");
                        $quest = SurveyQuestion::_instanciateQuestion($id);
                        if ($quest) {
                            $parent_id = $quest->getObjId();
                            
                            // pool question copy - find survey, do not use pool itself
                            if ($quest->getOriginalId() &&
                                ilObject::_lookupType($parent_id) == "spl") {
                                $obj_id = SurveyQuestion::_lookupSurveyObjId($id);
                            }
                            // original question (in pool or survey)
                            else {
                                $obj_id = $parent_id;
                            }
                            
                            unset($quest);
                        }
                        break;
                        
                    case "exca":
                        // Exercise assignment
                        $returned_pk = $a_usage['id'];
                        // #15995 - we are just checking against exercise object
                        include_once 'Modules/Exercise/classes/class.ilExSubmission.php';
                        $obj_id = ilExSubmission::lookupExerciseIdForReturnedId($returned_pk);
                        break;
                    
                    case "frm":
                        // Forum
                        $post_pk = $a_usage['id'];
                        include_once 'Modules/Forum/classes/class.ilForumPost.php';
                        include_once 'Modules/Forum/classes/class.ilForum.php';
                        $oPost = new ilForumPost($post_pk);
                        $frm_pk =  $oPost->getForumId();
                        $obj_id = ilForum::_lookupObjIdForForumId($frm_pk);
                        break;
                    
                    
                    case "frm~d":
                        $draft_id = $a_usage['id'];
                        include_once 'Modules/Forum/classes/class.ilForumPostDraft.php';
                        include_once 'Modules/Forum/classes/class.ilForum.php';
                        $oDraft = ilForumPostDraft::newInstanceByDraftId($draft_id);
                        
                        $frm_pk =  $oDraft->getForumId();
                        $obj_id = ilForum::_lookupObjIdForForumId($frm_pk);
                        break;
                    case "frm~h":
                        $history_id = $a_usage['id'];
                        include_once 'Modules/Forum/classes/class.ilForumDraftsHistory.php';
                        include_once 'Modules/Forum/classes/class.ilForumPostDraft.php';
                        include_once 'Modules/Forum/classes/class.ilForum.php';
                        $oHistoryDraft = new ilForumDraftsHistory($history_id);
                        $oDraft = ilForumPostDraft::newInstanceByDraftId($oHistoryDraft->getDraftId());
                        
                        $frm_pk =  $oDraft->getForumId();
                        $obj_id = ilForum::_lookupObjIdForForumId($frm_pk);
                        break;
                    // temporary items (per user)
                    case "frm~":
                    case "exca~":
                        $obj_id = $a_usage['id'];
                        break;
                    
                    // "old" category pages
                    case "cat":
                    // InfoScreen Text
                    case "tst":
                    case "svy":
                    // data collection
                    case "dcl":
                        $obj_id = $id;
                        break;
                }
                break;
                
            // page editor
            case "pg":
                
                switch ($cont_type) {
                    // question feedback // parent obj id is q id
                    case "qfbg":
                        include_once('./Services/COPage/classes/class.ilPageObject.php');
                        $id = ilPageObject::lookupParentId($id, 'qfbg');
                        // note: no break here, we only altered the $id to the question id

                        // no break
                    case "qpl":
                        // Question Pool Question Pages
                        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
                        $qinfo = assQuestion::_getQuestionInfo($id);
                        if ($qinfo["original_id"] > 0) {
                            include_once("./Modules/Test/classes/class.ilObjTest.php");
                            $obj_id = ilObjTest::_lookupTestObjIdForQuestionId($id);	// usage in test
                        } else {
                            $obj_id = $qinfo["obj_fi"];		// usage in pool
                        }
                        if ($obj_id == 0) {	// this is the case, if question is in learning module -> get lm id
                            include_once("./Services/COPage/classes/class.ilPCQuestion.php");
                            $pinfo = ilPCQuestion::_getPageForQuestionId($id, "lm");
                            if ($pinfo && $pinfo["parent_type"] == "lm") {
                                include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
                                $obj_id = ilLMObject::_lookupContObjID($pinfo["page_id"]);
                            }
                            $pinfo = ilPCQuestion::_getPageForQuestionId($id, "sahs");
                            if ($pinfo && $pinfo["parent_type"] == "sahs") {
                                include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
                                $obj_id = ilSCORM2004Node::_lookupSLMID($pinfo["page_id"]);
                            }
                        }
                        break;
                        
                    case "lm":
                        // learning modules
                        include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
                        $obj_id = ilLMObject::_lookupContObjID($id);
                        break;
                
                    case "gdf":
                        // glossary definition
                        include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
                        include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
                        $term_id = ilGlossaryDefinition::_lookupTermId($id);
                        $obj_id = ilGlossaryTerm::_lookGlossaryID($term_id);
                        break;
                    
                    case "wpg":
                        // wiki page
                        include_once 'Modules/Wiki/classes/class.ilWikiPage.php';
                        $obj_id = ilWikiPage::lookupObjIdByPage($id);
                        break;
                    
                    case "sahs":
                        // sahs page
                        // can this implementation be used for other content types, too?
                        include_once('./Services/COPage/classes/class.ilPageObject.php');
                        $obj_id = ilPageObject::lookupParentId($id, 'sahs');
                        break;
                    
                    case "prtf":
                        // portfolio
                        include_once "Modules/Portfolio/classes/class.ilPortfolioPage.php";
                        $obj_id = ilPortfolioPage::findPortfolioForPage($id);
                        break;
                    
                    case "prtt":
                        // portfolio template
                        include_once "Modules/Portfolio/classes/class.ilPortfolioTemplatePage.php";
                        $obj_id = ilPortfolioTemplatePage::findPortfolioForPage($id);
                        break;
                    
                    case "blp":
                        // blog
                        include_once('./Services/COPage/classes/class.ilPageObject.php');
                        $obj_id = ilPageObject::lookupParentId($id, 'blp');
                        break;
                    
                    case "impr":
                        // imprint page - always id 1
                        // fallthrough
                        
                    case "crs":
                    case "grp":
                    case "cat":
                    case "fold":
                    case "root":
                    case "cont":
                    case "copa":
                    case "cstr":
                        // repository pages
                        $obj_id = $id;
                        break;
                }
                break;
                
            // Media Pool
            case "mep":
                $obj_id = $id;
                break;

            // News Context Object (e.g. MediaCast)
            case "news":
                include_once("./Services/News/classes/class.ilNewsItem.php");
                $obj_id = ilNewsItem::_lookupContextObjId($id);
                break;
        }
        
        return $obj_id;
    }
    
    /**
    * resize image and return new image file ("_width_height" string appended)
    *
    * @param	string		$a_file		full file name
    * @param	int			$a_width	width
    * @param	int			$a_height	height
    */
    public static function _resizeImage($a_file, $a_width, $a_height, $a_constrain_prop = false)
    {
        $file_path = pathinfo($a_file);
        $location = substr($file_path["basename"], 0, strlen($file_path["basename"]) -
            strlen($file_path["extension"]) - 1) . "_" .
            $a_width . "_" .
            $a_height . "." . $file_path["extension"];
        $target_file = $file_path["dirname"] . "/" .
            $location;
        ilUtil::resizeImage(
            $a_file,
            $target_file,
            (int) $a_width,
            (int) $a_height,
            $a_constrain_prop
        );

        return $location;
    }

    /**
    * get mime type for file
    *
    * @param	string		$a_file		file name
    * @return	string					mime type
    * static
    */
    public static function getMimeType($a_file, $a_external = null)
    {
        include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
        $mime = ilMimeTypeUtil::lookupMimeType($a_file, ilMimeTypeUtil::APPLICATION__OCTET_STREAM, $a_external);
        return $mime;
    }

    /**
    * Determine width and height
    */
    public static function _determineWidthHeight(
        $a_format,
        $a_type,
        $a_file,
        $a_reference,
        $a_constrain_proportions,
        $a_use_original,
        $a_user_width,
        $a_user_height
    ) {
        global $DIC;

        $lng = $DIC->language();
        
        // determine width and height of known image types
        //$width = 640;
        //$height = 360;
        $info = "";
        
        if ($a_format == "audio/mpeg") {
            $width = 300;
            $height = 20;
        }
        
        if (ilUtil::deducibleSize($a_format)) {
            include_once("./Services/MediaObjects/classes/class.ilMediaImageUtil.php");
            if ($a_type == "File") {
                $size = ilMediaImageUtil::getImageSize($a_file);
            } else {
                $size = ilMediaImageUtil::getImageSize($a_reference);
            }
        }

        if ($a_use_original) {
            if ($size[0] > 0 && $size[1] > 0) {
                //$width = $size[0];
                //$height = $size[1];
                $width = "";
                $height = "";
            } else {
                $info = $lng->txt("cont_could_not_determine_resource_size");
            }
        } else {
            $w = (int) $a_user_width;
            $h = (int) $a_user_height;
            $width = $w;
            $height = $h;
            //echo "<br>C-$width-$height-";
            if (ilUtil::deducibleSize($a_format) && $a_constrain_proportions) {
                if ($size[0] > 0 && $size[1] > 0) {
                    if ($w > 0) {
                        $wr = $size[0] / $w;
                    }
                    if ($h > 0) {
                        $hr = $size[1] / $h;
                    }
                    //echo "<br>+".$wr."+".$size[0]."+".$w."+";
                    //echo "<br>+".$hr."+".$size[1]."+".$h."+";
                    $r = max($wr, $hr);
                    if ($r > 0) {
                        $width = (int) ($size[0]/$r);
                        $height = (int) ($size[1]/$r);
                    }
                }
            }
            //echo "<br>D-$width-$height-";
        }
        //echo "<br>E-$width-$height-";

        if ($width == 0 && $a_user_width === "") {
            $width = "";
        }
        if ($height == 0 && $a_user_height === "") {
            $height = "";
        }

        return array("width" => $width, "height" => $height, "info" => $info);
    }

    /**
    * Get simple mime types that deactivate parameter property
    * files tab in ILIAS
    */
    public static function _getSimpleMimeTypes()
    {
        return array("image/x-ms-bmp", "image/gif", "image/jpeg", "image/x-portable-bitmap",
            "image/png", "image/psd", "image/tiff", "application/pdf");
    }
    
    public function getDataDirectory()
    {
        return ilUtil::getWebspaceDir() . "/mobs/mm_" . $this->getId();
    }

    /**
    * Check whether only autostart parameter should be supported (instead
    * of parameters input field.
    *
    * This should be the same behaviour as mp3/flv in page.xsl
    */
    public static function _useAutoStartParameterOnly($a_loc, $a_format)
    {
        $lpath = pathinfo($a_loc);
        if ($lpath["extension"] == "mp3" && $a_format == "audio/mpeg") {
            return true;
        }
        if ($lpath["extension"] == "flv") {
            return true;
        }
        if (in_array($a_format, array("video/mp4", "video/webm"))) {
            return true;
        }
        return false;
    }

    /**
     * Create new media object and update page in db and return new media object
     */
    public static function _saveTempFileAsMediaObject($name, $tmp_name, $upload = true)
    {
        // create dummy object in db (we need an id)
        $media_object = new ilObjMediaObject();
        $media_object->setTitle($name);
        $media_object->setDescription("");
        $media_object->create();

        // determine and create mob directory, move uploaded file to directory
        $media_object->createDirectory();
        $mob_dir = ilObjMediaObject::_getDirectory($media_object->getId());

        $media_item = new ilMediaItem();
        $media_object->addMediaItem($media_item);
        $media_item->setPurpose("Standard");

        $file = $mob_dir . "/" . $name;
        if ($upload) {
            ilUtil::moveUploadedFile($tmp_name, $name, $file);
        } else {
            copy($tmp_name, $file);
        }
        // get mime type
        $format = ilObjMediaObject::getMimeType($file);
        $location = $name;
        // set real meta and object data
        $media_item->setFormat($format);
        $media_item->setLocation($location);
        $media_item->setLocationType("LocalFile");
        $media_object->setTitle($name);
        $media_object->setDescription($format);

        if (ilUtil::deducibleSize($format)) {
            include_once("./Services/MediaObjects/classes/class.ilMediaImageUtil.php");
            $size = ilMediaImageUtil::getImageSize($file);
            $media_item->setWidth($size[0]);
            $media_item->setHeight($size[1]);
        }
        $media_item->setHAlign("Left");

        self::renameExecutables($mob_dir);
        include_once("./Services/MediaObjects/classes/class.ilMediaSvgSanitizer.php");
        ilMediaSvgSanitizer::sanitizeDir($mob_dir);	// see #20339

        $media_object->update();

        return $media_object;
    }
    
    /**
     * Create new media object and update page in db and return new media object
     */
    public function uploadAdditionalFile($a_name, $tmp_name, $a_subdir = "", $a_mode = "move_uploaded")
    {
        $a_subdir = str_replace("..", "", $a_subdir);
        $dir = $mob_dir = ilObjMediaObject::_getDirectory($this->getId());
        if ($a_subdir != "") {
            $dir.= "/" . $a_subdir;
        }
        ilUtil::makeDirParents($dir);
        if ($a_mode == "rename") {
            rename($tmp_name, $dir . "/" . $a_name);
        } else {
            ilUtil::moveUploadedFile($tmp_name, $a_name, $dir . "/" . $a_name, true, $a_mode);
        }
        self::renameExecutables($mob_dir);
        include_once("./Services/MediaObjects/classes/class.ilMediaSvgSanitizer.php");
        ilMediaSvgSanitizer::sanitizeDir($mob_dir);	// see #20339
    }
    
    /**
     * Upload srt file
     *
     * @param
     * @return
     */
    public function uploadSrtFile($a_tmp_name, $a_language, $a_mode = "move_uploaded")
    {
        if (is_file($a_tmp_name) && $a_language != "") {
            $this->uploadAdditionalFile("subtitle_" . $a_language . ".srt", $a_tmp_name, "srt", $a_mode);
            return true;
        }
        return false;
    }
    
    /**
     * Get srt files
     */
    public function getSrtFiles()
    {
        $srt_dir = ilObjMediaObject::_getDirectory($this->getId()) . "/srt";
        
        if (!is_dir($srt_dir)) {
            return array();
        }
        
        $items = ilUtil::getDir($srt_dir);

        $srt_files = array();
        foreach ($items as $i) {
            if (!in_array($i["entry"], array(".", "..")) && $i["type"] == "file") {
                $name = explode(".", $i["entry"]);
                if ($name[1] == "srt" && substr($name[0], 0, 9) == "subtitle_") {
                    $srt_files[] = array("file" => $i["entry"],
                        "full_path" => "srt/" . $i["entry"], "language" => substr($name[0], 9, 2));
                }
            }
        }
        
        return $srt_files;
    }

    /**
     * Make thumbnail
     */
    public function makeThumbnail(
        $a_file,
        $a_thumbname,
        $a_format = "png",
        $a_size = "80"
    ) {
        $m_dir = ilObjMediaObject::_getDirectory($this->getId());
        $t_dir = ilObjMediaObject::_getThumbnailDirectory($this->getId());
        self::_createThumbnailDirectory($this->getId());
        ilUtil::convertImage(
            $m_dir . "/" . $a_file,
            $t_dir . "/" . $a_thumbname,
            $a_format,
            $a_size
        );
    }
    
    /**
     * Get thumbnail path
     *
     * @param string $a_thumbname thumbnail file name
     * @return string thumbnail path
     */
    public static function getThumbnailPath($a_mob_id, $a_thumbname)
    {
        $t_dir = ilObjMediaObject::_getThumbnailDirectory($a_mob_id);
        return $t_dir . "/" . $a_thumbname;
    }
    
    
    /**
     * Remove additional file
     */
    public function removeAdditionalFile($a_file)
    {
        $file = str_replace("..", "", $a_file);
        $file = ilObjMediaObject::_getDirectory($this->getId()) . "/" . $file;
        if (is_file($file)) {
            unlink($file);
        }
    }
    
    
    /**
    * Get all media objects linked in map areas of this media object
    */
    public function getLinkedMediaObjects($a_ignore = "")
    {
        $linked = array();
        
        if (!is_array($a_ignore)) {
            $a_ignore = array();
        }
        
        // get linked media objects (map areas)
        $med_items = $this->getMediaItems();

        foreach ($med_items as $med_item) {
            $int_links = ilMapArea::_getIntLinks($med_item->getId());
            foreach ($int_links as $k => $int_link) {
                if ($int_link["Type"] == "MediaObject") {
                    include_once("./Services/Link/classes/class.ilInternalLink.php");
                    $l_id = ilInternalLink::_extractObjIdOfTarget($int_link["Target"]);
                    if (ilObject::_exists($l_id)) {
                        if (!in_array($l_id, $linked) &&
                            !in_array($l_id, $a_ignore)) {
                            $linked[] = $l_id;
                        }
                    }
                }
            }
        }
        //var_dump($linked);
        return $linked;
    }
    
    /**
     * Get restricted file types (this is for the input form, this list will be empty, if "allowed list" is empty)
     */
    public static function getRestrictedFileTypes()
    {
        return array_filter(self::getAllowedFileTypes(), function ($v) {
            return !in_array($v, self::getForbiddenFileTypes());
        });
    }
    
    /**
     * Get forbidden file types
     *
     * @return array
     */
    public static function getForbiddenFileTypes()
    {
        $mset = new ilSetting("mobs");
        if (trim($mset->get("black_list_file_types")) == "") {
            return array();
        }
        return array_map(
            function ($v) {
                return strtolower(trim($v));
            },
            explode(",", $mset->get("black_list_file_types"))
        );
    }

    /**
     * Get allowed file types
     *
     * @return array
     */
    public static function getAllowedFileTypes()
    {
        $mset = new ilSetting("mobs");
        if (trim($mset->get("restricted_file_types")) == "") {
            return array();
        }
        return array_map(
            function ($v) {
                return strtolower(trim($v));
            },
            explode(",", $mset->get("restricted_file_types"))
        );
    }
    
    /**
     * Is type allowed
     *
     * @param string $a_type
     * @return bool
     */
    public static function isTypeAllowed($a_type)
    {
        if (in_array($a_type, self::getForbiddenFileTypes())) {
            return false;
        }
        if (count(self::getAllowedFileTypes()) == 0 || in_array($a_type, self::getAllowedFileTypes())) {
            return true;
        }
        return false;
    }


    /**
     * Duplicate media object, return new media object
     */
    public function duplicate()
    {
        $new_obj = new ilObjMediaObject();
        $new_obj->setTitle($this->getTitle());
        $new_obj->setDescription($this->getDescription());
        
        // media items
        foreach ($this->getMediaItems() as $key => $val) {
            $new_obj->addMediaItem($val);
        }

        $new_obj->create(false, true);

        // files
        $new_obj->createDirectory();
        self::_createThumbnailDirectory($new_obj->getId());
        ilUtil::rCopy(
            ilObjMediaObject::_getDirectory($this->getId()),
            ilObjMediaObject::_getDirectory($new_obj->getId())
        );
        ilUtil::rCopy(
            ilObjMediaObject::_getThumbnailDirectory($this->getId()),
            ilObjMediaObject::_getThumbnailDirectory($new_obj->getId())
        );
        
        // meta data
        include_once("Services/MetaData/classes/class.ilMD.php");
        $md = new ilMD(0, $this->getId(), "mob");
        $new_md = $md->cloneMD(0, $new_obj->getId(), "mob");

        return $new_obj;
    }
    
    /**
     * Upload video preview picture
     *
     * @param
     * @return
     */
    public function uploadVideoPreviewPic($a_prevpic)
    {
        // remove old one
        if ($this->getVideoPreviewPic(true) != "") {
            $this->removeAdditionalFile($this->getVideoPreviewPic(true));
        }

        $pi = pathinfo($a_prevpic["name"]);
        $ext = $pi["extension"];
        if (in_array($ext, array("jpg", "jpeg", "png"))) {
            $this->uploadAdditionalFile("mob_vpreview." . $ext, $a_prevpic["tmp_name"]);
        }
    }

    /**
     * Upload video preview picture
     *
     * @param
     * @return
     */
    public function generatePreviewPic($a_width, $a_height)
    {
        $item = $this->getMediaItem("Standard");

        if ($item->getLocationType() == "LocalFile" &&
            is_int(strpos($item->getFormat(), "image/"))) {
            $dir = ilObjMediaObject::_getDirectory($this->getId());
            $file = $dir . "/" .
                $item->getLocation();
            if (is_file($file)) {
                if (ilUtil::isConvertVersionAtLeast("6.3.8-3")) {
                    ilUtil::execConvert(ilUtil::escapeShellArg($file) . "[0] -geometry " . $a_width . "x" . $a_height . "^ -gravity center -extent " . $a_width . "x" . $a_height . " PNG:" . $dir . "/mob_vpreview.png");
                } else {
                    ilUtil::convertImage($file, $dir . "/mob_vpreview.png", "PNG", $a_width . "x" . $a_height);
                }
            }
        }
    }

    /**
     * Get video preview pic
     *
     * @param
     * @return
     */
    public function getVideoPreviewPic($a_filename_only = false)
    {
        $dir = ilObjMediaObject::_getDirectory($this->getId());
        $ppics = array("mob_vpreview.jpg",
            "mob_vpreview.jpeg",
            "mob_vpreview.png");
        foreach ($ppics as $p) {
            if (is_file($dir . "/" . $p)) {
                if ($a_filename_only) {
                    return $p;
                } else {
                    return $dir . "/" . $p;
                }
            }
        }
        return "";
    }

    /**
     * Fix filename of uploaded file
     *
     * @param string $a_name upload file name
     * @return string fixed file name
     */
    public static function fixFilename($a_name)
    {
        $a_name = ilUtil::getASCIIFilename($a_name);

        $rchars = array("`", "=", "$", "{", "}", "'", ";", " ", "(", ")");
        $a_name = str_replace($rchars, "_", $a_name);
        $a_name = str_replace("__", "_", $a_name);
        return $a_name;
    }


    /**
     * Get directory for multi srt upload
     *
     * @param
     * @return
     */
    public function getMultiSrtUploadDir()
    {
        return ilObjMediaObject::_getDirectory($this->getId() . "/srt/tmp");
    }


    /**
     * Upload multi srt file
     *
     * @param array $a_file file info array
     * @throws ilMediaObjectsException
     */
    public function uploadMultipleSubtitleFile($a_file)
    {
        $lng = $this->lng;
        $ilUser = $this->user;

        include_once("./Services/MediaObjects/exceptions/class.ilMediaObjectsException.php");
        if (!is_file($a_file["tmp_name"])) {
            throw new ilMediaObjectsException($lng->txt("mob_file_could_not_be_uploaded"));
        }

        $dir = $this->getMultiSrtUploadDir();
        ilUtil::delDir($dir, true);
        ilUtil::makeDirParents($dir);
        ilUtil::moveUploadedFile($a_file["tmp_name"], "multi_srt.zip", $dir . "/" . "multi_srt.zip");
        ilUtil::unzip($dir . "/multi_srt.zip", true);
    }

    /**
     * Clear multi feedback directory
     */
    public function clearMultiSrtDirectory()
    {
        ilUtil::delDir($this->getMultiSrtUploadDir());
    }

    /**
     * Get all srt files of srt multi upload
     */
    public function getMultiSrtFiles()
    {
        $items = array();

        include_once("./Services/MetaData/classes/class.ilMDLanguageItem.php");
        $lang_codes = ilMDLanguageItem::_getPossibleLanguageCodes();

        $dir = $this->getMultiSrtUploadDir();
        $files = ilUtil::getDir($dir);
        foreach ($files as $k => $i) {
            // check directory
            if ($i["type"] == "file" && !in_array($k, array(".", ".."))) {
                if (pathinfo($k, PATHINFO_EXTENSION) == "srt") {
                    $lang = "";
                    if (substr($k, strlen($k) - 7, 1) == "_") {
                        $lang = substr($k, strlen($k) - 6, 2);
                        if (!in_array($lang, $lang_codes)) {
                            $lang = "";
                        }
                    }
                    $items[] = array("filename" => $k, "lang" => $lang);
                }
            }
        }
        return $items;
    }

    /**
     * Rename executables
     *
     * @param string
     */
    public static function renameExecutables($a_dir)
    {
        ilUtil::renameExecutables($a_dir);
        if (!self::isTypeAllowed("html")) {
            ilUtil::rRenameSuffix($a_dir, "html", "sec");        // see #20187
        }
    }
}
