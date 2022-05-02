<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\FileUpload\MimeType;

define("IL_MODE_ALIAS", 1);
define("IL_MODE_OUTPUT", 2);
define("IL_MODE_FULL", 3);

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjMediaObject extends ilObject
{
    protected ilObjUser $user;
    public bool $is_alias;
    public string $origin_id;
    public array $media_items;
    public bool $contains_int_link;

    public function __construct(
        int $a_id = 0
    ) {
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

    public static function _exists(
        int $id,
        bool $reference = false,
        ?string $type = null
    ) : bool {
        if (is_int(strpos($id, "_"))) {
            $a_id = ilInternalLink::_extractObjIdOfTarget($id);
        }
        
        if (parent::_exists($id) && ilObject::_lookupType($id) === "mob") {
            return true;
        }
        return false;
    }

    public function delete() : bool
    {
        $mob_logger = ilLoggerFactory::getLogger('mob');
        $mob_logger->debug("ilObjMediaObject: Delete called for media object ID '" . $this->getId() . "'.");

        if (!($this->getId() > 0)) {
            return false;
        }

        $usages = $this->getUsages();

        $mob_logger->debug("ilObjMediaObject: ... Found " . count($usages) . " usages.");

        if (count($usages) == 0) {
            // remove directory
            ilFileUtils::delDir(ilObjMediaObject::_getDirectory($this->getId()));

            // remove thumbnail directory
            ilFileUtils::delDir(ilObjMediaObject::_getThumbnailDirectory($this->getId()));

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
        return true;
    }

    protected function beforeMDUpdateListener(string $a_element) : bool
    {
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
        }
        return false;       // prevent parent from creating ilMD
    }

    protected function beforeCreateMetaData() : bool
    {
        $ilUser = $this->user;

        $md_creator = new ilMDCreator(0, $this->getId(), $this->getType());
        $md_creator->setTitle($this->getTitle());
        $md_creator->setTitleLanguage($ilUser->getPref('language'));
        $md_creator->setDescription($this->getDescription());
        $md_creator->setDescriptionLanguage($ilUser->getPref('language'));
        $md_creator->setKeywordLanguage($ilUser->getPref('language'));
        $md_creator->setLanguage($ilUser->getPref('language'));
        $md_creator->create();

        return false;   // avoid parent to create md
    }

    protected function beforeUpdateMetaData() : bool
    {
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
        return false;
    }

    protected function beforeDeleteMetaData() : bool
    {
        // Delete meta data
        $md = new ilMD(0, $this->getId(), $this->getType());
        $md->deleteAll();

        return false;
    }


    public function addMediaItem(
        ilMediaItem $a_item
    ) : void {
        $this->media_items[] = $a_item;
    }

    public function &getMediaItems() : array
    {
        return $this->media_items;
    }

    /**
     * get item for media purpose
     */
    public function getMediaItem(
        string $a_purpose
    ) : ?ilMediaItem {
        foreach ($this->media_items as $media_item) {
            if ($media_item->getPurpose() == $a_purpose) {
                return $media_item;
            }
        }
        return null;
    }

    public function removeMediaItem(
        string $a_purpose
    ) : void {
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
    
    public function removeAllMediaItems() : void
    {
        $this->media_items = array();
    }

    public function hasFullscreenItem() : bool
    {
        return $this->hasPurposeItem("Fullscreen");
    }
    
    /**
     * returns whether object has media item with specific purpose
     */
    public function hasPurposeItem(string $purpose) : bool
    {
        if (is_object($this->getMediaItem($purpose))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws ilObjectNotFoundException
     * @throws ilObjectTypeMismatchException
     */
    public function read() : void
    {
        parent::read();
        ilMediaItem::_getMediaItemsOfMOb($this);
    }

    public function setAlias(bool $a_is_alias) : void
    {
        $this->is_alias = $a_is_alias;
    }

    public function isAlias() : bool
    {
        return $this->is_alias;
    }

    /**
     * @deprecated (seems to be obsolete)
     */
    public function setOriginID(string $a_id) : void
    {
        $this->origin_id = $a_id;
    }

    public function getOriginID() : string
    {
        return $this->origin_id;
    }
    
    public function create(bool $a_create_meta_data = false, bool $a_save_media_items = true) : int
    {
        $id = parent::create();

        if (!$a_create_meta_data) {
            $this->createMetaData();
        }

        if ($a_save_media_items) {
            $media_items = $this->getMediaItems();
            for ($i = 0; $i < count($media_items); $i++) {
                $item = $media_items[$i];
                $item->setMobId($this->getId());
                $item->setNr($i + 1);
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

        return $id;
    }
    
    public function update(bool $a_upload = false) : bool
    {
        parent::update();
        
        if (!$a_upload) {
            $this->updateMetaData();
        }
        
        // iterate all items
        $media_items = $this->getMediaItems();
        ilMediaItem::deleteAllItemsOfMob($this->getId());

        $j = 1;
        foreach ($media_items as $key => $val) {
            $item = $val;
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

        return true;
    }

    /**
     * @deprecated
     */
    protected static function handleQuotaUpdate(
        ilObjMediaObject $a_mob
    ) : void {
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
                $parent_obj_ids[] = $parent_obj_id;
            }
        }

        // we could suppress this if object is present in a (repository) media pool
        // but this would lead to "quota-breaches" when the pool item is deleted
        // and "suddenly" all workspace owners get filesize added to their
        // respective quotas, regardless of current status
    }

    /**
     * Get absolute directory
     */
    public static function _getDirectory(
        int $a_mob_id
    ) : string {
        return ilFileUtils::getWebspaceDir() . "/" . self::_getRelativeDirectory($a_mob_id);
    }

    /**
     * Get relative (to webspace dir) directory
     */
    public static function _getRelativeDirectory(int $a_mob_id) : string
    {
        return "mobs/mm_" . $a_mob_id;
    }

    /**
     * get directory for files of media object
     */
    public static function _getURL(
        int $a_mob_id
    ) : string {
        return ilUtil::getHtmlPath(ilFileUtils::getWebspaceDir() . "/mobs/mm_" . $a_mob_id);
    }

    /**
     * get directory for files of media object
     */
    public static function _getThumbnailDirectory(
        int $a_mob_id,
        string $a_mode = "filesystem"
    ) : string {
        return ilFileUtils::getWebspaceDir($a_mode) . "/thumbs/mm_" . $a_mob_id;
    }
    
    /**
     * Get path for standard item.
     */
    public static function _lookupStandardItemPath(
        int $a_mob_id,
        bool $a_url_encode = false,
        bool $a_web = true
    ) : string {
        return ilObjMediaObject::_lookupItemPath($a_mob_id, $a_url_encode, $a_web, "Standard");
    }
    
    /**
     * Get path for item with specific purpose.
     */
    public static function _lookupItemPath(
        int $a_mob_id,
        bool $a_url_encode = false,
        bool $a_web = true,
        string $a_purpose = ""
    ) : string {
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
     * @throws ilMediaObjectsException
     */
    public function createDirectory() : void
    {
        $path = ilObjMediaObject::_getDirectory($this->getId());
        ilFileUtils::makeDirParents($path);
        if (!is_dir($path)) {
            throw new ilMediaObjectsException("Failed to create directory $path.");
        }
    }

    /**
     * Create thumbnail directory
     */
    public static function _createThumbnailDirectory(
        int $a_obj_id
    ) : void {
        ilFileUtils::createDirectory(ilFileUtils::getWebspaceDir() . "/thumbs");
        ilFileUtils::createDirectory(ilFileUtils::getWebspaceDir() . "/thumbs/mm_" . $a_obj_id);
    }
    
    /**
     * Get files of directory
     */
    public function getFilesOfDirectory(
        string $a_subdir = ""
    ) : array {
        $a_subdir = str_replace("..", "", $a_subdir);
        $dir = ilObjMediaObject::_getDirectory($this->getId());
        if ($a_subdir != "") {
            $dir .= "/" . $a_subdir;
        }
        
        $files = array();
        if (is_dir($dir)) {
            $entries = ilFileUtils::getDir($dir);
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
     * @param int  $a_mode IL_MODE_ALIAS | IL_MODE_OUTPUT | IL_MODE_FULL
     * @param int  $a_inst
     * @param bool $a_sign_locals
     * @return string
     * @throws ilWACException
     */
    public function getXML(
        int $a_mode = IL_MODE_FULL,
        int $a_inst = 0,
        bool $a_sign_locals = false
    ) : string {
        $ilUser = $this->user;
        $xml = "";
        // TODO: full implementation of all parameters
        //echo "-".$a_mode."-";
        switch ($a_mode) {
            case IL_MODE_ALIAS:
                $xml = "<MediaObject>";
                $xml .= "<MediaAlias OriginId=\"il__mob_" . $this->getId() . "\"/>";
                $media_items = $this->getMediaItems();
                for ($i = 0; $i < count($media_items); $i++) {
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
                for ($i = 0; $i < count($media_items); $i++) {
                    $item = $media_items[$i];

                    $xml .= "<MediaItem Purpose=\"" . $item->getPurpose() . "\">";

                    if ($a_sign_locals && $item->getLocationType() == "LocalFile") {
                        $location = ilWACSignedPath::signFile($this->getDataDirectory() . "/" . $item->getLocation());
                        $location = substr($location, strrpos($location, "/") + 1);
                    } else {
                        $location = $item->getLocation();
                        if ($item->getLocationType() != "LocalFile") {  //#25941
                            $location = ilUtil::secureUrl($location); //#23518
                        }
                    }

                    $xml .= "<Location Type=\"" . $item->getLocationType() . "\">" .
                        $this->handleAmps($location) . "</Location>";

                    // Format
                    $xml .= "<Format>" . $item->getFormat() . "</Format>";

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
                            $this->escapeProperty($this->getTitle()) . "</Title>";
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
                $md2xml = new ilMD2XML(0, $this->getId(), $this->getType());
                $md2xml->setExportMode(true);
                $md2xml->startExport();
                $xml .= $md2xml->getXML();

                $media_items = $this->getMediaItems();
                for ($i = 0; $i < count($media_items); $i++) {
                    $item = $media_items[$i];
                    
                    // highlight mode
                    $xml .= "<MediaItem Purpose=\"" . $item->getPurpose() . "\">";

                    // Location
                    $xml .= "<Location Type=\"" . $item->getLocationType() . "\">" .
                        $this->handleAmps($item->getLocation()) . "</Location>";

                    // Format
                    $xml .= "<Format>" . $item->getFormat() . "</Format>";

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
     */
    protected function escapeProperty(
        string $a_value
    ) : string {
        return htmlspecialchars($a_value);
    }


    /**
     * Replace "&" (if not an "&amp;") with "&amp;"
     */
    public function handleAmps(
        string $a_str
    ) : string {
        $a_str = str_replace("&amp;", "&", $a_str);
        $a_str = str_replace("&", "&amp;", $a_str);
        return $a_str;
    }
    
    public function exportXML(
        ilXmlWriter $a_xml_writer,
        int $a_inst = 0
    ) : void {
        $a_xml_writer->appendXML($this->getXML(IL_MODE_FULL, $a_inst));
    }


    /**
     * export all media files of object to target directory
     * note: target directory must be the export target directory,
     * "/objects/il_<inst>_mob_<mob_id>/..." will be appended to this directory
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function exportFiles(
        string $a_target_dir
    ) : void {
        $subdir = "il_" . IL_INST_ID . "_mob_" . $this->getId();
        ilFileUtils::makeDir($a_target_dir . "/objects/" . $subdir);

        $mobdir = ilFileUtils::getWebspaceDir() . "/mobs/mm_" . $this->getId();
        ilFileUtils::rCopy($mobdir, $a_target_dir . "/objects/" . $subdir);
    }

    public function modifyExportIdentifier(
        string $a_tag,
        string $a_param,
        string $a_value
    ) : string {
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
     */
    public function setContainsIntLink(
        bool $a_contains_link
    ) : void {
        $this->contains_int_link = $a_contains_link;
    }

    /**
     * returns true, if mob was marked as containing an intern link (via setContainsIntLink)
     * (this method should only be called by the import parser)
     */
    public function containsIntLink() : bool
    {
        return $this->contains_int_link;
    }

    public static function _deleteAllUsages(
        string $a_type,
        int $a_id,
        int $a_usage_hist_nr = 0,
        string $a_lang = "-"
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        
        $and_hist = "";
        if ($a_usage_hist_nr > 0) {
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
     * @return int[]
     */
    public static function _getMobsOfObject(
        string $a_type,
        int $a_id,
        int $a_usage_hist_nr = 0,
        string $a_lang = "-"
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        $lstr = "";
        if ($a_lang != "") {
            $lstr = " AND usage_lang = " . $ilDB->quote($a_lang, "text");
        }
        $hist_str = "";
        if ($a_usage_hist_nr > 0) {
            $hist_str = " AND usage_hist_nr = " . $ilDB->quote($a_usage_hist_nr, "integer");
        }

        $q = "SELECT * FROM mob_usage WHERE " .
            "usage_type = " . $ilDB->quote($a_type, "text") . " AND " .
            "usage_id = " . $ilDB->quote($a_id, "integer") .
            $lstr . $hist_str;
        $mobs = array();
        $mob_set = $ilDB->query($q);
        while ($mob_rec = $ilDB->fetchAssoc($mob_set)) {
            $mob_id = (int) $mob_rec['id'];
            if (ilObject::_lookupType($mob_id) === "mob") {
                $mobs[$mob_id] = $mob_id;
            }
        }

        return $mobs;
    }

    /**
     * Save usage of mob within another container (e.g. page)
     */
    public static function _saveUsage(
        int $a_mob_id,
        string $a_type,
        int $a_id,
        int $a_usage_hist_nr = 0,
        string $a_lang = "-"
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->replace(
            "mob_usage",
            array(
                "id" => array("integer", $a_mob_id),
                "usage_type" => array("text", $a_type),
                "usage_id" => array("integer", $a_id),
                "usage_lang" => array("text", $a_lang),
                "usage_hist_nr" => array("integer", $a_usage_hist_nr)
                ),
            array()
        );

        self::handleQuotaUpdate(new self($a_mob_id));
    }

    /**
     * Remove usage of mob in another container
     */
    public static function _removeUsage(
        int $a_mob_id,
        string $a_type,
        int $a_id,
        int $a_usage_hist_nr = 0,
        string $a_lang = "-"
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "DELETE FROM mob_usage WHERE " .
            " id = " . $ilDB->quote($a_mob_id, "integer") . " AND " .
            " usage_type = " . $ilDB->quote($a_type, "text") . " AND " .
            " usage_id = " . $ilDB->quote($a_id, "integer") . " AND " .
            " usage_lang = " . $ilDB->quote($a_lang, "text") . " AND " .
            " usage_hist_nr = " . $ilDB->quote($a_usage_hist_nr, "integer");
        $ilDB->manipulate($q);
        
        self::handleQuotaUpdate(new self($a_mob_id));
    }

    /**
     * get all usages of current media object
     */
    public function getUsages(
        bool $a_include_history = true
    ) : array {
        return self::lookupUsages($this->getId(), $a_include_history);
    }
    
    /**
     * Lookup usages of media object
     *
     * @todo: This should be all in one context -> mob id table
     */
    public static function lookupUsages(
        int $a_id,
        bool $a_include_history = true
    ) : array {
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
            $q .= " AND usage_hist_nr = " . $ilDB->quote(0, "integer");
        }
        
        $us_set = $ilDB->query($q);
        $ret = array();
        while ($us_rec = $ilDB->fetchAssoc($us_set)) {
            $ut = "";
            $ct = 0;
            if (is_int(strpos($us_rec["usage_type"], ":"))) {
                $us_arr = explode(":", $us_rec["usage_type"]);
                $ut = $us_arr[1];
                $ct = $us_arr[0];
            }

            // check whether page exists
            $skip = false;
            if ($ut == "pg") {
                if (!ilPageObject::_exists($ct, $us_rec["usage_id"])) {
                    $skip = true;
                }
            }
                
            if (!$skip) {
                $ret[] = array(
                    "type" => $us_rec["usage_type"],
                    "id" => $us_rec["usage_id"],
                    "lang" => $us_rec["usage_lang"],
                    "hist_nr" => ($us_rec["usage_hist_nr"] ?? 0)
                );
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
     * see ilWebAccessChecker
     */
    public static function getParentObjectIdForUsage(
        array $a_usage,
        bool $a_include_all_access_obj_ids = false
    ) : ?int {
        $cont_type = "";
        if (is_int(strpos($a_usage["type"], ":"))) {
            $us_arr = explode(":", $a_usage["type"]);
            $type = $us_arr[1];
            $cont_type = $us_arr[0];
        } else {
            $type = $a_usage["type"];
        }
        
        $id = $a_usage["id"];
        $obj_id = null;
        
        switch ($type) {
            // RTE / tiny mce
            case "html":
                
                switch ($cont_type) {
                    case "qpl":
                        // Question Pool *Question* Text (Test)
                        $qinfo = assQuestion::_getQuestionInfo($id);
                        if ($qinfo["original_id"] > 0) {
                            $obj_id = ilObjTest::_lookupTestObjIdForQuestionId($id);	// usage in test
                        } else {
                            $obj_id = (int) $qinfo["obj_fi"];		// usage in pool
                        }
                        break;
                        
                    case "spl":
                        // Question Pool *Question* Text (Survey)
                        $quest = SurveyQuestion::_instanciateQuestion($id);
                        if ($quest) {
                            $parent_id = $quest->getObjId();
                            
                            // pool question copy - find survey, do not use pool itself
                            if ($quest->getOriginalId() &&
                                ilObject::_lookupType($parent_id) == "spl") {
                                $obj_id = (int) SurveyQuestion::_lookupSurveyObjId($id);
                            }
                            // original question (in pool or survey)
                            else {
                                $obj_id = (int) $parent_id;
                            }
                            
                            unset($quest);
                        }
                        break;
                        
                    case "exca":
                        // Exercise assignment
                        $returned_pk = $a_usage['id'];
                        // #15995 - we are just checking against exercise object
                        $obj_id = ilExSubmission::lookupExerciseIdForReturnedId($returned_pk);
                        break;
                    
                    case "frm":
                        // Forum
                        $post_pk = $a_usage['id'];
                        $oPost = new ilForumPost($post_pk);
                        $frm_pk = $oPost->getForumId();
                        $obj_id = ilForum::_lookupObjIdForForumId($frm_pk);
                        break;
                    
                    
                    case "frm~d":
                        $draft_id = $a_usage['id'];
                        $oDraft = ilForumPostDraft::newInstanceByDraftId($draft_id);
                        
                        $frm_pk = $oDraft->getForumId();
                        $obj_id = ilForum::_lookupObjIdForForumId($frm_pk);
                        break;
                    case "frm~h":
                        $history_id = $a_usage['id'];
                        $oHistoryDraft = new ilForumDraftsHistory($history_id);
                        $oDraft = ilForumPostDraft::newInstanceByDraftId($oHistoryDraft->getDraftId());
                        
                        $frm_pk = $oDraft->getForumId();
                        $obj_id = ilForum::_lookupObjIdForForumId($frm_pk);
                        break;
                    // temporary items (per user)
                    case "frm~":
                    case "exca~":
                        $obj_id = (int) $a_usage['id'];
                        break;
                    
                    // "old" category pages
                    case "cat":
                    // InfoScreen Text
                    case "tst":
                    case "svy":
                    // data collection
                    case "dcl":
                        $obj_id = (int) $id;
                        break;
                }
                break;
                
            // page editor
            case "pg":
                
                switch ($cont_type) {
                    // question feedback // parent obj id is q id
                    case "qfbg":
                    case "qpl":

                        if ($cont_type == "qfbg") {
                            $id = ilPageObject::lookupParentId($id, 'qfbg');
                        }

                        // Question Pool Question Pages
                        $qinfo = assQuestion::_getQuestionInfo($id);
                        if ($qinfo["original_id"] > 0) {
                            $obj_id = ilObjTest::_lookupTestObjIdForQuestionId($id);	// usage in test
                        } else {
                            $obj_id = $qinfo["obj_fi"];		// usage in pool
                        }
                        if ($obj_id == 0) {	// this is the case, if question is in learning module -> get lm id
                            $pinfo = ilPCQuestion::_getPageForQuestionId($id, "lm");
                            if ($pinfo && $pinfo["parent_type"] == "lm") {
                                $obj_id = ilLMObject::_lookupContObjID($pinfo["page_id"]);
                            }
                            $pinfo = ilPCQuestion::_getPageForQuestionId($id, "sahs");
                            if ($pinfo && $pinfo["parent_type"] == "sahs") {
                                $obj_id = (int) ilSCORM2004Node::_lookupSLMID($pinfo["page_id"]);
                            }
                        }
                        break;
                        
                    case "lm":
                        // learning modules
                        $obj_id = ilLMObject::_lookupContObjID($id);
                        break;
                
                    case "gdf":
                        // glossary definition
                        $term_id = ilGlossaryDefinition::_lookupTermId($id);
                        $obj_id = (int) ilGlossaryTerm::_lookGlossaryID($term_id);
                        break;
                    
                    case "wpg":
                        // wiki page
                        $obj_id = (int) ilWikiPage::lookupObjIdByPage($id);
                        break;
                    
                    case "sahs":
                        // sahs page
                        // can this implementation be used for other content types, too?
                        $obj_id = ilPageObject::lookupParentId($id, 'sahs');
                        break;
                    
                    case "prtf":
                        // portfolio
                        $obj_id = ilPortfolioPage::findPortfolioForPage($id);
                        break;
                    
                    case "prtt":
                        // portfolio template
                        $obj_id = ilPortfolioTemplatePage::findPortfolioForPage($id);
                        break;
                    
                    case "blp":
                        // blog
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
                $obj_id = ilNewsItem::_lookupContextObjId($id);
                break;
        }
        
        return $obj_id;
    }
    
    /**
     * Resize image and return new image file ("_width_height" string appended)
     */
    public static function _resizeImage(
        string $a_file,
        int $a_width,
        int $a_height,
        bool $a_constrain_prop = false
    ) : string {
        $file_path = pathinfo($a_file);
        $location = substr($file_path["basename"], 0, strlen($file_path["basename"]) -
            strlen($file_path["extension"]) - 1) . "_" .
            $a_width . "_" .
            $a_height . "." . $file_path["extension"];
        $target_file = $file_path["dirname"] . "/" .
            $location;
        ilShellUtil::resizeImage(
            $a_file,
            $target_file,
            $a_width,
            $a_height,
            $a_constrain_prop
        );

        return $location;
    }

    /**
     * get mime type for file
     */
    public static function getMimeType(
        string $a_file,
        bool $a_external = false
    ) : string {
        $mime = MimeType::lookupMimeType($a_file, MimeType::APPLICATION__OCTET_STREAM, $a_external);
        return $mime;
    }

    public static function _determineWidthHeight(
        string $a_format,
        string $a_type,
        string $a_file,
        string $a_reference,
        bool $a_constrain_proportions,
        bool $a_use_original,
        ?int $a_user_width = null,
        ?int $a_user_height = null
    ) : array {
        global $DIC;

        $lng = $DIC->language();
        $size = [];
        $wr = 0;
        $hr = 0;
        $width = 0;
        $height = 0;
        
        // determine width and height of known image types
        //$width = 640;
        //$height = 360;
        $info = "";
        
        if ($a_format == "audio/mpeg") {
            $width = 300;
            $height = 20;
        }
        
        if (ilUtil::deducibleSize($a_format)) {
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
            $w = $a_user_width;
            $h = $a_user_height;
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
                        $width = (int) round($size[0] / $r);
                        $height = (int) round($size[1] / $r);
                    }
                }
            }
            //echo "<br>D-$width-$height-";
        }
        //echo "<br>E-$width-$height-";

        if ($width == 0 && is_null($a_user_width)) {
            $width = "";
        }
        if ($height == 0 && is_null($a_user_height)) {
            $height = "";
        }

        return array("width" => $width, "height" => $height, "info" => $info);
    }

    public function getDataDirectory() : string
    {
        return ilFileUtils::getWebspaceDir() . "/mobs/mm_" . $this->getId();
    }

    /**
     * Create new media object and update page in db and return new media object
     */
    public static function _saveTempFileAsMediaObject(
        string $name,
        string $tmp_name,
        bool $upload = true
    ) : ilObjMediaObject {
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
            ilFileUtils::moveUploadedFile($tmp_name, $name, $file);
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
            $size = ilMediaImageUtil::getImageSize($file);
            $media_item->setWidth($size[0]);
            $media_item->setHeight($size[1]);
        }
        $media_item->setHAlign("Left");

        self::renameExecutables($mob_dir);
        ilMediaSvgSanitizer::sanitizeDir($mob_dir);	// see #20339

        $media_object->update();

        return $media_object;
    }
    
    /**
     * Create new media object and update page in db and return new media object
     */
    public function uploadAdditionalFile(
        string $a_name,
        string $tmp_name,
        string $a_subdir = "",
        string $a_mode = "move_uploaded"
    ) : void {
        $a_subdir = str_replace("..", "", $a_subdir);
        $dir = $mob_dir = ilObjMediaObject::_getDirectory($this->getId());
        if ($a_subdir != "") {
            $dir .= "/" . $a_subdir;
        }
        ilFileUtils::makeDirParents($dir);
        if ($a_mode == "rename") {
            rename($tmp_name, $dir . "/" . $a_name);
        } else {
            ilFileUtils::moveUploadedFile($tmp_name, $a_name, $dir . "/" . $a_name, true, $a_mode);
        }
        self::renameExecutables($mob_dir);
        ilMediaSvgSanitizer::sanitizeDir($mob_dir);	// see #20339
    }
    
    public function uploadSrtFile(
        string $a_tmp_name,
        string $a_language,
        string $a_mode = "move_uploaded"
    ) : bool {
        if (is_file($a_tmp_name) && $a_language != "") {
            $this->uploadAdditionalFile("subtitle_" . $a_language . ".srt", $a_tmp_name, "srt", $a_mode);
            return true;
        }
        return false;
    }
    
    public function getSrtFiles() : array
    {
        $srt_dir = ilObjMediaObject::_getDirectory($this->getId()) . "/srt";
        
        if (!is_dir($srt_dir)) {
            return array();
        }
        
        $items = ilFileUtils::getDir($srt_dir);

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
        string $a_file,
        string $a_thumbname,
        string $a_format = "png",
        int $a_size = 80
    ) : void {
        $m_dir = ilObjMediaObject::_getDirectory($this->getId());
        $t_dir = ilObjMediaObject::_getThumbnailDirectory($this->getId());
        self::_createThumbnailDirectory($this->getId());
        ilShellUtil::convertImage(
            $m_dir . "/" . $a_file,
            $t_dir . "/" . $a_thumbname,
            $a_format,
            $a_size
        );
    }
    
    public static function getThumbnailPath(
        int $a_mob_id,
        string $a_thumbname
    ) : string {
        $t_dir = ilObjMediaObject::_getThumbnailDirectory($a_mob_id);
        return $t_dir . "/" . $a_thumbname;
    }
    
    public function removeAdditionalFile(
        string $a_file
    ) : void {
        $file = str_replace("..", "", $a_file);
        $file = ilObjMediaObject::_getDirectory($this->getId()) . "/" . $file;
        if (is_file($file)) {
            unlink($file);
        }
    }
    
    
    /**
     * Get all media objects linked in map areas of this media object
     * @param int[] $a_ignore array of IDs that should be ignored
     */
    public function getLinkedMediaObjects(
        array $a_ignore = []
    ) : array {
        $linked = array();
        
        // get linked media objects (map areas)
        $med_items = $this->getMediaItems();

        foreach ($med_items as $med_item) {
            $int_links = ilMapArea::_getIntLinks($med_item->getId());
            foreach ($int_links as $k => $int_link) {
                if ($int_link["Type"] == "MediaObject") {
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
     * Get restricted file types (this is for the input form, this list
     * will be empty, if "allowed list" is empty)
     */
    public static function getRestrictedFileTypes() : array
    {
        return array_filter(self::getAllowedFileTypes(), function ($v) {
            return !in_array($v, self::getForbiddenFileTypes());
        });
    }
    
    /**
     * Get forbidden file types
     */
    public static function getForbiddenFileTypes() : array
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
     */
    public static function getAllowedFileTypes() : array
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
    
    public static function isTypeAllowed(
        string $a_type
    ) : bool {
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
    public function duplicate() : ilObjMediaObject
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
        ilFileUtils::rCopy(
            ilObjMediaObject::_getDirectory($this->getId()),
            ilObjMediaObject::_getDirectory($new_obj->getId())
        );
        ilFileUtils::rCopy(
            ilObjMediaObject::_getThumbnailDirectory($this->getId()),
            ilObjMediaObject::_getThumbnailDirectory($new_obj->getId())
        );
        
        // meta data
        $md = new ilMD(0, $this->getId(), "mob");
        $new_md = $md->cloneMD(0, $new_obj->getId(), "mob");

        return $new_obj;
    }
    
    public function uploadVideoPreviewPic(
        array $a_prevpic
    ) : void {
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

    public function generatePreviewPic(
        int $a_width,
        int $a_height,
        int $sec = 1
    ) : void {
        /** @var ilLogger $logger */
        $logger = $GLOBALS['DIC']->logger()->mob();

        $item = $this->getMediaItem("Standard");

        if ($item->getLocationType() == "LocalFile") {
            if (is_int(strpos($item->getFormat(), "image/"))) {
                $a_width = $a_height = 400;


                $dir = ilObjMediaObject::_getDirectory($this->getId());
                $file = $dir . "/" .
                    $item->getLocation();
                if (is_file($file)) {
                    if (ilShellUtil::isConvertVersionAtLeast("6.3.8-3")) {
                        ilShellUtil::execConvert(
                            ilShellUtil::escapeShellArg(
                                $file
                            ) . "[0] -geometry " . $a_width . "x" . $a_height . "^ -gravity center -extent " . $a_width . "x" . $a_height . " PNG:" . $dir . "/mob_vpreview.png"
                        );
                    } else {
                        ilShellUtil::convertImage($file, $dir . "/mob_vpreview.png", "PNG", $a_width . "x" . $a_height);
                    }
                }
            }
        }

        $logger->debug("Generate preview pic...");
        $logger->debug("..." . $item->getFormat());
        if (is_int(strpos($item->getFormat(), "video/mp4"))) {
            try {
                if ($sec < 0) {
                    $sec = 0;
                }
                if ($this->getVideoPreviewPic() != "") {
                    $this->removeAdditionalFile($this->getVideoPreviewPic(true));
                }
                $med = $this->getMediaItem("Standard");
                if ($med->getLocationType() == "LocalFile") {
                    $mob_file = ilObjMediaObject::_getDirectory($this->getId()) . "/" . $med->getLocation();
                } else {
                    $mob_file = $med->getLocation();
                }
                $logger->debug(
                    "...extract " . $mob_file . " in " .
                    ilObjMediaObject::_getDirectory($this->getId())
                );
                ilFFmpeg::extractImage(
                    $mob_file,
                    "mob_vpreview.png",
                    ilObjMediaObject::_getDirectory($this->getId()),
                    $sec
                );
            } catch (ilException $e) {
                $ret = ilFFmpeg::getLastReturnValues();

                $message = '';
                if (is_array($ret) && count($ret) > 0) {
                    $message = "\n" . implode("\n", $ret);
                }

                $logger->warning($e->getMessage() . $message);
                $logger->logStack(ilLogLevel::WARNING);
            }
        }
    }

    public function getVideoPreviewPic(
        bool $a_filename_only = false
    ) : string {
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
     */
    public static function fixFilename(
        string $a_name
    ) : string {
        $a_name = ilFileUtils::getASCIIFilename($a_name);

        $rchars = array("`", "=", "$", "{", "}", "'", ";", " ", "(", ")");
        $a_name = str_replace($rchars, "_", $a_name);
        $a_name = str_replace("__", "_", $a_name);
        return $a_name;
    }


    /**
     * Get directory for multi srt upload
     */
    public function getMultiSrtUploadDir() : string
    {
        return ilObjMediaObject::_getDirectory($this->getId() . "/srt/tmp");
    }


    /**
     * Upload multi srt file
     */
    public function uploadMultipleSubtitleFile(
        array $a_file
    ) : void {
        $lng = $this->lng;

        if (!is_file($a_file["tmp_name"])) {
            throw new ilMediaObjectsException($lng->txt("mob_file_could_not_be_uploaded"));
        }

        $dir = $this->getMultiSrtUploadDir();
        ilFileUtils::delDir($dir, true);
        ilFileUtils::makeDirParents($dir);
        ilFileUtils::moveUploadedFile($a_file["tmp_name"], "multi_srt.zip", $dir . "/" . "multi_srt.zip");
        ilFileUtils::unzip($dir . "/multi_srt.zip", true);
    }

    /**
     * Clear multi srt directory
     */
    public function clearMultiSrtDirectory() : void
    {
        ilFileUtils::delDir($this->getMultiSrtUploadDir());
    }

    /**
     * Get all srt files of srt multi upload
     */
    public function getMultiSrtFiles() : array
    {
        $items = array();

        $lang_codes = ilMDLanguageItem::_getPossibleLanguageCodes();

        $dir = $this->getMultiSrtUploadDir();
        $files = ilFileUtils::getDir($dir);
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

    public static function renameExecutables(
        string $a_dir
    ) : void {
        ilFileUtils::renameExecutables($a_dir);
        if (!self::isTypeAllowed("html")) {
            ilFileUtils::rRenameSuffix($a_dir, "html", "sec");        // see #20187
        }
    }

    public function getExternalMetadata() : void
    {
        // see https://oembed.com/
        $st_item = $this->getMediaItem("Standard");
        if ($st_item->getLocationType() == "Reference") {
            if (ilExternalMediaAnalyzer::isVimeo($st_item->getLocation())) {
                $st_item->setFormat("video/vimeo");
                $par = ilExternalMediaAnalyzer::extractVimeoParameters($st_item->getLocation());
                $meta = ilExternalMediaAnalyzer::getVimeoMetadata($par["id"]);
                $this->setTitle($meta["title"]);
                $description = str_replace("\n", "", $meta["description"]);
                $description = str_replace(["<br>", "<br />"], ["\n", "\n"], $description);
                $description = strip_tags($description);
                $this->setDescription($description);
                $st_item->setDuration((int) $meta["duration"]);
                $url = parse_url($meta["thumbnail_url"]);
                $file = basename($url["path"]);
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if ($ext == "") {
                    $ext = "jpg";
                }
                copy(
                    $meta["thumbnail_url"],
                    ilObjMediaObject::_getDirectory($this->getId()) . "/mob_vpreview." .
                    $ext
                );
            }
            if (ilExternalMediaAnalyzer::isYoutube($st_item->getLocation())) {
                $st_item->setFormat("video/youtube");
                $par = ilExternalMediaAnalyzer::extractYoutubeParameters($st_item->getLocation());
                $meta = ilExternalMediaAnalyzer::getYoutubeMetadata($par["v"]);
                $this->setTitle($meta["title"]);
                $description = str_replace("\n", "", $meta["description"]);
                $description = str_replace(["<br>", "<br />"], ["\n", "\n"], $description);
                $description = strip_tags($description);
                $this->setDescription($description);
                $st_item->setDuration((int) $meta["duration"]);
                $url = parse_url($meta["thumbnail_url"]);
                $file = basename($url["path"]);
                copy(
                    $meta["thumbnail_url"],
                    ilObjMediaObject::_getDirectory($this->getId()) . "/mob_vpreview." .
                    pathinfo($file, PATHINFO_EXTENSION)
                );
            }
        }
    }
}
