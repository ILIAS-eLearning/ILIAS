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

/**
 * Class ilMediaItem
 * Media Item, component of a media object (file or reference)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaItem
{
    protected string $tried_thumb = "";
    protected string $text_representation = "";
    protected ilDBInterface $db;
    protected ilLanguage $lng;

    public int $id = 0;
    public string $purpose = "";
    public string $location = "";
    public string $location_type = "";
    public string $format = "";
    public string $width = "";
    public string $height = "";
    public string $caption = "";
    public string $halign = "";
    public array $parameters = [];
    public int $mob_id = 0;
    public int $nr = 0;
    public array $mapareas = [];
    public int $map_cnt = 0;
    /**
     * @var ?GdImage|resource
     */
    public $map_image = null;            // image map work copy image
    public int $color1;            // map area line color 1
    public int $color2;            // map area line color 2
    protected int $duration = 0;
    protected string $upload_hash = '';

    public function __construct(
        int $a_id = 0
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->parameters = array();
        $this->mapareas = array();
        $this->map_cnt = 0;

        if ($a_id != 0) {
            $this->setId($a_id);
            $this->read();
        }
    }

    /**
     * set media item id
     */
    public function setId(int $a_id) : void
    {
        $this->id = $a_id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    /**
     * set id of parent media object
     */
    public function setMobId(int $a_mob_id) : void
    {
        $this->mob_id = $a_mob_id;
    }

    public function getMobId() : int
    {
        return $this->mob_id;
    }

    /**
     * set number of media item within media object
     */
    public function setNr(int $a_nr) : void
    {
        $this->nr = $a_nr;
    }

    public function getNr() : int
    {
        return $this->nr;
    }
    
    /**
     * returns the best supported image type by this PHP build
     *
     * @param string $a_desired_type
     * @return string supported image type ("jpg" | "gif" | "png" | "")
     * @static
     */
    private static function getGDSupportedImageType(string $a_desired_type) : string
    {
        $a_desired_type = strtolower($a_desired_type);
        // get supported Image Types
        $im_types = ImageTypes();
        
        switch ($a_desired_type) {
            case "jpg":
            case "jpeg":
                if ($im_types & IMG_JPG) {
                    return "jpg";
                }
                if ($im_types & IMG_GIF) {
                    return "gif";
                }
                if ($im_types & IMG_PNG) {
                    return "png";
                }
                break;
            
            case "gif":
                if ($im_types & IMG_GIF) {
                    return "gif";
                }
                if ($im_types & IMG_JPG) {
                    return "jpg";
                }
                if ($im_types & IMG_PNG) {
                    return "png";
                }
                break;

            case "svg":
            case "png":
                if ($im_types & IMG_PNG) {
                    return "png";
                }
                if ($im_types & IMG_JPG) {
                    return "jpg";
                }
                if ($im_types & IMG_GIF) {
                    return "gif";
                }
                break;
        }
        
        return "";
    }
    
    public function setDuration(int $a_val) : void
    {
        $this->duration = $a_val;
    }

    public function getDuration() : int
    {
        return $this->duration;
    }

    public function setTextRepresentation(string $a_val) : void
    {
        $this->text_representation = $a_val;
    }

    public function getTextRepresentation() : string
    {
        return $this->text_representation;
    }

    public function setUploadHash(string $a_val) : void
    {
        $this->upload_hash = $a_val;
    }

    public function getUploadHash() : string
    {
        return $this->upload_hash;
    }

    public function create() : void
    {
        $ilDB = $this->db;

        $item_id = $ilDB->nextId("media_item");
        $query = "INSERT INTO media_item (id,mob_id, purpose, location, " .
            "location_type, format, width, " .
            "height, halign, caption, nr, text_representation, upload_hash, duration) VALUES " .
            "(" .
            $ilDB->quote($item_id, "integer") . "," .
            $ilDB->quote($this->getMobId(), "integer") . "," .
            $ilDB->quote($this->getPurpose(), "text") . "," .
            $ilDB->quote($this->getLocation(), "text") . "," .
            $ilDB->quote($this->getLocationType(), "text") . "," .
            $ilDB->quote($this->getFormat(), "text") . "," .
            $ilDB->quote($this->getWidth(), "text") . "," .
            $ilDB->quote($this->getHeight(), "text") . "," .
            $ilDB->quote($this->getHAlign(), "text") . "," .
            $ilDB->quote($this->getCaption(), "text") . "," .
            $ilDB->quote($this->getNr(), "integer") . "," .
            $ilDB->quote($this->getTextRepresentation(), "text") . "," .
            $ilDB->quote($this->getUploadHash(), "text") . "," .
            $ilDB->quote($this->getDuration(), "integer") .
            ")";
        $ilDB->manipulate($query);

        $this->setId($item_id);

        // create mob parameters
        $params = $this->getParameters();
        foreach ($params as $name => $value) {
            $query = "INSERT INTO mob_parameter (med_item_id, name, value) VALUES " .
                "(" . $ilDB->quote($item_id, "integer") . "," .
                $ilDB->quote($name, "text") . "," .
                $ilDB->quote($value, "text") . ")";
            $ilDB->manipulate($query);
        }

        // create map areas
        for ($i = 0; $i < count($this->mapareas); $i++) {
            if (is_object($this->mapareas[$i])) {
                $this->mapareas[$i]->setItemId($this->getId());
                $this->mapareas[$i]->setNr($i + 1);
                $this->mapareas[$i]->create();
            }
        }
    }

    public function update() : void
    {
        $ilDB = $this->db;

        $query = "UPDATE media_item SET " .
            " mob_id = " . $ilDB->quote($this->getMobId(), "integer") . "," .
            " purpose = " . $ilDB->quote($this->getPurpose(), "text") . "," .
            " location = " . $ilDB->quote($this->getLocation(), "text") . "," .
            " location_type = " . $ilDB->quote($this->getLocationType(), "text") . "," .
            " format = " . $ilDB->quote($this->getFormat(), "text") . "," .
            " width = " . $ilDB->quote($this->getWidth(), "text") . "," .
            " height = " . $ilDB->quote($this->getHeight(), "text") . "," .
            " halign = " . $ilDB->quote($this->getHAlign(), "text") . "," .
            " caption = " . $ilDB->quote($this->getCaption(), "text") . "," .
            " nr = " . $ilDB->quote($this->getNr(), "integer") . "," .
            " text_representation = " . $ilDB->quote($this->getTextRepresentation(), "text") . "," .
            " upload_hash = " . $ilDB->quote($this->getUploadHash(), "text") . "," .
            " duration = " . $ilDB->quote($this->getDuration(), "integer") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);

        // delete mob parameters
        $query = "DELETE FROM mob_parameter WHERE med_item_id = " .
            $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);

        // create mob parameters
        $params = $this->getParameters();
        foreach ($params as $name => $value) {
            $query = "INSERT INTO mob_parameter (med_item_id, name, value) VALUES " .
                "(" . $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($name, "text") . "," .
                $ilDB->quote($value, "text") . ")";
            $ilDB->manipulate($query);
        }
    }

    public function writeParameter(
        string $a_name,
        string $a_value
    ) : void {
        $ilDB = $this->db;

        $query = "INSERT INTO mob_parameter (med_item_id, name, value) VALUES " .
            "(" . $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($a_name, "text") . "," .
            $ilDB->quote($a_value, "text") . ")";
        $ilDB->manipulate($query);
    }

    /**
     * read media item data (item id or (mob_id and nr) must be set)
     */
    public function read() : void
    {
        $ilDB = $this->db;

        $item_id = $this->getId();
        $mob_id = $this->getMobId();
        $nr = $this->getNr();
        $query = "";
        if ($item_id > 0) {
            $query = "SELECT * FROM media_item WHERE id = " .
                $ilDB->quote($this->getId(), "integer");
        } elseif ($mob_id > 0 && $nr > 0) {
            $query = "SELECT * FROM media_item WHERE mob_id = " .
                $ilDB->quote($this->getMobId(), "integer") . " " .
                "AND nr=" . $ilDB->quote($this->getNr(), "integer");
        }
        if ($query != "") {
            $item_set = $ilDB->query($query);
            $item_rec = $ilDB->fetchAssoc($item_set);

            $this->setLocation((string) $item_rec["location"]);
            $this->setLocationType((string) $item_rec["location_type"]);
            $this->setFormat((string) $item_rec["format"]);
            $this->setWidth((string) $item_rec["width"]);
            $this->setHeight((string) $item_rec["height"]);
            $this->setHAlign((string) $item_rec["halign"]);
            $this->setCaption((string) $item_rec["caption"]);
            $this->setPurpose((string) $item_rec["purpose"]);
            $this->setNr((int) $item_rec["nr"]);
            $this->setMobId((int) $item_rec["mob_id"]);
            $this->setId((int) $item_rec["id"]);
            $this->setThumbTried((string) $item_rec["tried_thumb"]);
            $this->setTextRepresentation((string) $item_rec["text_representation"]);
            $this->setUploadHash((string) $item_rec["upload_hash"]);
            $this->setDuration((int) $item_rec["duration"]);

            // get item parameter
            $query = "SELECT * FROM mob_parameter WHERE med_item_id = " .
                $ilDB->quote($this->getId(), "integer");
            $par_set = $ilDB->query($query);
            while ($par_rec = $ilDB->fetchAssoc($par_set)) {
                $this->setParameter($par_rec["name"], $par_rec["value"]);
            }

            // get item map areas
            $max = ilMapArea::_getMaxNr($this->getId());
            for ($i = 1; $i <= $max; $i++) {
                $area = new ilMapArea($this->getId(), $i);
                $this->addMapArea($area);
            }
        }
    }

    /**
     * write thumbnail creation try data ("y"/"n")
     */
    public function writeThumbTried(string $a_tried) : void
    {
        $ilDB = $this->db;

        $q = "UPDATE media_item SET tried_thumb = " .
            $ilDB->quote($a_tried, "text") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");

        $ilDB->manipulate($q);
    }

    public static function _lookupLocationForMobId(
        int $a_mob_id,
        string $a_purpose
    ) : string {
        global $DIC;

        $ilDB = $DIC->database();

        // read media_object record
        $query = "SELECT * FROM media_item WHERE mob_id = " .
            $ilDB->quote($a_mob_id, "integer") . " " .
            "AND purpose = " . $ilDB->quote($a_purpose, "text");
        $set = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["location"];
        }

        return "";
    }

    public static function _lookupMobId(
        int $a_med_id
    ) : int {
        global $DIC;

        $ilDB = $DIC->database();

        // read media_object record
        $query = "SELECT * FROM media_item WHERE id = " .
            $ilDB->quote($a_med_id, "integer");
        $set = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (int) $rec["mob_id"];
        }

        return 0;
    }

    /**
     * read media item with specific purpose and mobId
     * @param int    $a_mobId
     * @param string $a_purpose
     * @return ?ilMediaItem[]
     */
    public static function _getMediaItemsOfMObId(
        int $a_mobId,
        string $a_purpose
    ) : ?array {
        global $DIC;

        $ilDB = $DIC->database();

        // read media_object record
        $query = "SELECT * FROM media_item WHERE mob_id = " .
            $ilDB->quote($a_mobId, "integer") . " " .
            "AND purpose=" . $ilDB->quote($a_purpose, "text") . " ORDER BY nr";
        $item_set = $ilDB->query($query);

        while ($item_rec = $ilDB->fetchAssoc($item_set)) {
            return $item_rec;
        }
        return null;
    }

    /**
     * Read media items into(!) media object (static)
     */
    public static function _getMediaItemsOfMOb(
        ilObjMediaObject $a_mob
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();

        // read media_object record
        $query = "SELECT * FROM media_item WHERE mob_id = " .
            $ilDB->quote($a_mob->getId(), "integer") . " " .
            "ORDER BY nr";
        $item_set = $ilDB->query($query);
        while ($item_rec = $ilDB->fetchAssoc($item_set)) {
            $media_item = new ilMediaItem();
            $media_item->setNr((int) $item_rec["nr"]);
            $media_item->setId((int) $item_rec["id"]);
            $media_item->setLocation((string) $item_rec["location"]);
            $media_item->setLocationType((string) $item_rec["location_type"]);
            $media_item->setFormat((string) $item_rec["format"]);
            $media_item->setWidth((string) $item_rec["width"]);
            $media_item->setHeight((string) $item_rec["height"]);
            $media_item->setHAlign((string) $item_rec["halign"]);
            $media_item->setCaption((string) $item_rec["caption"]);
            $media_item->setPurpose((string) $item_rec["purpose"]);
            $media_item->setMobId((int) $item_rec["mob_id"]);
            $media_item->setThumbTried((string) $item_rec["tried_thumb"]);
            $media_item->setTextRepresentation((string) $item_rec["text_representation"]);
            $media_item->setUploadHash((string) $item_rec["upload_hash"]);
            $media_item->setDuration((int) $item_rec["duration"]);

            // get item parameter
            $query = "SELECT * FROM mob_parameter WHERE med_item_id = " .
                $ilDB->quote($item_rec["id"], "integer");
            $par_set = $ilDB->query($query);
            while ($par_rec = $ilDB->fetchAssoc($par_set)) {
                $media_item->setParameter($par_rec["name"], $par_rec["value"]);
            }

            // get item map areas
            $max = ilMapArea::_getMaxNr($media_item->getId());
            for ($i = 1; $i <= $max; $i++) {
                $area = new ilMapArea($media_item->getId(), $i);
                $media_item->addMapArea($area);
            }

            // add media item to media object
            $a_mob->addMediaItem($media_item);
        }
    }

    public static function deleteAllItemsOfMob(int $a_mob_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        // iterate all media items ob mob
        $query = "SELECT * FROM media_item WHERE mob_id = " .
            $ilDB->quote($a_mob_id, "integer");
        $item_set = $ilDB->query($query);
        while ($item_rec = $ilDB->fetchAssoc($item_set)) {
            // delete all parameters of media item
            $query = "DELETE FROM mob_parameter WHERE med_item_id = " .
                $ilDB->quote($item_rec["id"], "integer");
            $ilDB->manipulate($query);

            // delete all map areas of media item
            $query = "DELETE FROM map_area WHERE item_id = " .
                $ilDB->quote($item_rec["id"], "integer");
            $ilDB->manipulate($query);
        }

        // delete media items
        $query = "DELETE FROM media_item WHERE mob_id = " .
            $ilDB->quote($a_mob_id, "integer");
        $ilDB->manipulate($query);
    }

    public function setPurpose(string $a_purpose) : void
    {
        $this->purpose = $a_purpose;
    }

    public function getPurpose() : string
    {
        return $this->purpose;
    }

    public function setLocation(string $a_location) : void
    {
        $this->location = $a_location;
    }

    public function getLocation() : string
    {
        return $this->location;
    }

    public function setLocationType(string $a_type) : void
    {
        $this->location_type = $a_type;
    }

    public function getLocationType() : string
    {
        return $this->location_type;
    }

    public function setFormat(string $a_format) : void
    {
        $this->format = $a_format;
    }

    public function getFormat() : string
    {
        return $this->format;
    }

    public function setThumbTried(string $a_tried) : void
    {
        $this->tried_thumb = $a_tried;
    }

    public function getThumbTried() : string
    {
        return $this->tried_thumb;
    }

    public function addMapArea(ilMapArea $a_map_area) : void
    {
        $this->mapareas[$this->map_cnt] = $a_map_area;
        $this->map_cnt++;
    }

    public function deleteMapArea(int $nr) : void
    {
        for ($i = 1; $i <= $this->map_cnt; $i++) {
            if ($i > $nr) {
                $this->mapareas[$i - 2] = $this->mapareas[$i - 1];
                $this->mapareas[$i - 2]->setNr($i - 1);
            }
        }
        if ($nr <= $this->map_cnt) {
            unset($this->mapareas[$this->map_cnt - 1]);
            $this->map_cnt--;
        }
    }

    public function getMapArea(int $nr) : ?ilMapArea
    {
        return $this->mapareas[$nr - 1] ?? null;
    }
    
    public function getMapAreas() : array
    {
        return $this->mapareas;
    }

    public function getWidth() : string
    {
        return $this->width;
    }

    public function setWidth(string $a_width) : void
    {
        $this->width = $a_width;
    }

    public function getHeight() : string
    {
        return $this->height;
    }

    public function setHeight(string $a_height) : void
    {
        $this->height = $a_height;
    }
    
    public function getOriginalSize() : ?array
    {
        $mob_dir = ilObjMediaObject::_getDirectory($this->getMobId());

        if (ilUtil::deducibleSize($this->getFormat())) {
            if ($this->getLocationType() == "LocalFile") {
                $loc = $mob_dir . "/" . $this->getLocation();
            } else {
                $loc = $this->getLocation();
            }

            $size = ilMediaImageUtil::getImageSize($loc);
            if ($size[0] > 0 && $size[1] > 0) {
                return array("width" => $size[0], "height" => $size[1]);
            }
        }

        return null;
    }

    public function setCaption(string $a_caption) : void
    {
        $this->caption = $a_caption;
    }

    public function getCaption() : string
    {
        return $this->caption;
    }

    /**
     * set horizontal align
     */
    public function setHAlign(string $a_halign) : void
    {
        $this->halign = $a_halign;
    }

    public function getHAlign() : string
    {
        return $this->halign;
    }

    public function setParameter(
        string $a_name,
        string $a_value
    ) : void {
        if (self::checkParameter($a_name, $a_value)) {
            $this->parameters[$a_name] = $a_value;
        }
    }

    public function resetParameters() : void
    {
        $this->parameters = [];
    }

    /**
     * set all parameters via parameter string (format: par1="value1", par2="value2", ...)
     */
    public function setParameters(string $a_par) : void
    {
        $this->resetParameters();
        $par_arr = ilUtil::extractParameterString($a_par);
        if (is_array($par_arr)) {
            foreach ($par_arr as $par => $val) {
                $this->setParameter($par, $val);
            }
        }
    }

    /**
     * Check parameter (filter javascript related and other unsafe parameters/values)
     */
    public static function checkParameter(
        string $a_par,
        string $a_val
    ) : bool {
        // do not allow event attributes
        if (substr(strtolower(trim($a_par)), 0, 2) == "on") {
            return false;
        }
        // no javascript in value
        if (is_int(strpos(strtolower($a_val), "javascript"))) {
            return false;
        }
        // do not allow to change the src attribute
        if (strtolower(trim($a_par)) == "src") {
            return false;
        }

        return true;
    }
    
    public function getParameters() : array
    {
        return $this->parameters;
    }
    
    public function getParameterString() : string
    {
        if (is_array($this->parameters)) {
            $target_arr = [];
            foreach ($this->parameters as $par => $val) {
                $target_arr[] = "$par=\"$val\"";
            }
            return implode(", ", $target_arr);
        }
        return "";
    }
    
    public function getParameter(string $a_name) : string
    {
        return (string) ($this->parameters[$a_name] ?? "");
    }

    /**
     * get work directory for image map editing
     */
    public function getWorkDirectory() : string
    {
        return ilFileUtils::getDataDir() . "/map_workfiles/item_" . $this->getId();
    }

    /**
     * create work directory for image map editing
     */
    public function createWorkDirectory() : void
    {
        if (!is_dir(ilFileUtils::getDataDir() . "/map_workfiles")) {
            ilFileUtils::createDirectory(ilFileUtils::getDataDir() . "/map_workfiles");
        }
        $work_dir = $this->getWorkDirectory();
        if (!is_dir($work_dir)) {
            ilFileUtils::createDirectory($work_dir);
        }
    }

    /**
     * get location suffix
     */
    public function getSuffix() : string
    {
        $loc_arr = explode(".", $this->getLocation());

        return $loc_arr[count($loc_arr) - 1];
    }

    /**
     * get image type of image map work copy
     */
    public function getMapWorkCopyType() : string
    {
        return self::getGDSupportedImageType($this->getSuffix());
    }

    /**
     * Get name of image map work copy file
     * @param bool $a_reference_copy get name for copy of external referenced image
     */
    public function getMapWorkCopyName(
        bool $a_reference_copy = false
    ) : string {
        $file_arr = explode("/", $this->getLocation());
        $o_file = $file_arr[count($file_arr) - 1];
        $file_arr = explode(".", $o_file);
        unset($file_arr[count($file_arr) - 1]);
        $file = implode(".", $file_arr);

        if (!$a_reference_copy) {
            return $this->getWorkDirectory() . "/" . $file . "." . $this->getMapWorkCopyType();
        } else {
            return $this->getWorkDirectory() . "/l_copy_" . $o_file;
        }
    }

    /**
     * get media file directory
     */
    public function getDirectory() : string
    {
        return ilObjMediaObject::_getDirectory($this->getMobId());
    }

    /**
     * get media file directory
     */
    public function getThumbnailDirectory(
        string $a_mode = "filesystem"
    ) : string {
        return ilObjMediaObject::_getThumbnailDirectory($this->getMobId(), $a_mode);
    }

    /**
     * get thumbnail target
     */
    public function getThumbnailTarget(
        string $a_size = ""
    ) : string {
        $jpeg_file = $this->getThumbnailDirectory() . "/" .
            $this->getPurpose() . ".jpeg";
        $format = "png";
        if (is_file($jpeg_file)) {
            $format = "jpeg";
        }

        if (is_int(strpos($this->getFormat(), "image"))) {
            $thumb_file = $this->getThumbnailDirectory() . "/" .
                $this->getPurpose() . "." . $format;

            $thumb_file_small = $this->getThumbnailDirectory() . "/" .
                $this->getPurpose() . "_small." . $format;
            // generate thumbnail (if not tried before)
            if ($this->getThumbTried() == "n" && $this->getLocationType() == "LocalFile") {
                if (is_file($thumb_file)) {
                    unlink($thumb_file);
                }
                if (is_file($thumb_file_small)) {
                    unlink($thumb_file_small);
                }
                $this->writeThumbTried("y");
                ilObjMediaObject::_createThumbnailDirectory($this->getMobId());
                $med_file = $this->getDirectory() . "/" . $this->getLocation();

                if (is_file($med_file)) {
                    ilShellUtil::convertImage($med_file, $thumb_file, $format, "80");
                    ilShellUtil::convertImage($med_file, $thumb_file_small, $format, "40");
                }
            }
            if ($a_size == "small") {
                if (is_file($thumb_file_small)) {
                    $random = new \ilRandom();
                    return $this->getThumbnailDirectory("output") . "/" .
                        $this->getPurpose() . "_small." . $format . "?dummy=" . $random->int(1, 999999);
                }
            } else {
                if (is_file($thumb_file)) {
                    $random = new \ilRandom();
                    return $this->getThumbnailDirectory("output") . "/" .
                        $this->getPurpose() . "." . $format . "?dummy=" . $random->int(1, 999999);
                }
            }
        }

        return "";
    }

    /**
     * Copy the original file for map editing
     * to the working directory
     * @throws ilMapEditingException
     */
    public function copyOriginal() : void
    {
        $lng = $this->lng;
        $this->createWorkDirectory();

        $geom = ($this->getWidth() != "" && $this->getHeight() != "")
            ? $this->getWidth() . "x" . $this->getHeight()
            : "";

        if ($this->getLocationType() !== "Reference") {
            ilShellUtil::convertImage(
                $this->getDirectory() . "/" . $this->getLocation(),
                $this->getMapWorkCopyName(),
                $this->getMapWorkCopyType(),
                $geom
            );
        } else {
            // first copy the external file, if necessary
            if (!is_file($this->getMapWorkCopyName(true)) || (filesize($this->getMapWorkCopyName(true)) == 0)) {
                $handle = fopen($this->getLocation(), "r");
                $lcopy = fopen($this->getMapWorkCopyName(true), "w");
                if ($handle && $lcopy) {
                    while (!feof($handle)) {
                        $content = fread($handle, 4096);
                        fwrite($lcopy, $content);
                    }
                }
                fclose($lcopy);
                fclose($handle);
            }

            // now, create working copy
            ilShellUtil::convertImage(
                $this->getMapWorkCopyName(true),
                $this->getMapWorkCopyName(),
                $this->getMapWorkCopyType(),
                $geom
            );
        }
        if (!is_file($this->getMapWorkCopyName())) {
            throw new ilMapEditingException($lng->txt("cont_map_file_not_generated"));
        }
    }

    /**
     * make map work copy of image
     * @param int  $a_area_nr draw area $a_area_nr only
     * @param bool $a_exclude true: draw all areas but area $a_area_nr
     */
    public function makeMapWorkCopy(
        int $a_area_nr = 0,
        bool $a_exclude = false
    ) : void {
        $lng = $this->lng;

        $this->copyOriginal();
        $this->buildMapWorkImage();

        // determine ratios
        $size = getimagesize($this->getMapWorkCopyName());
        $x_ratio = 1;
        if ($size[0] > 0 && $this->getWidth() > 0) {
            $x_ratio = $this->getWidth() / $size[0];
        }
        $y_ratio = 1;
        if ($size[1] > 0 && $this->getHeight() > 0) {
            $y_ratio = $this->getHeight() / $size[1];
        }

        // draw map areas
        for ($i = 0; $i < count($this->mapareas); $i++) {
            if (((($i + 1) == $a_area_nr) && !$a_exclude) ||
                ((($i + 1) != $a_area_nr) && $a_exclude) ||
                ($a_area_nr == 0)
            ) {
                $area = $this->mapareas[$i];
                $area->draw(
                    $this->getMapWorkImage(),
                    $this->color1,
                    $this->color2,
                    true,
                    $x_ratio,
                    $y_ratio
                );
            }
        }

        $this->saveMapWorkImage();
    }

    /**
     * draw a new area in work image
     * @param string $a_shape  shape
     * @param string $a_coords coordinates string
     */
    public function addAreaToMapWorkCopy(
        string $a_shape,
        string $a_coords
    ) : void {
        $this->buildMapWorkImage();

        // determine ratios
        $size = getimagesize($this->getMapWorkCopyName());
        $x_ratio = 1;
        if ($size[0] > 0 && $this->getWidth() > 0) {
            $x_ratio = $this->getWidth() / $size[0];
        }
        $y_ratio = 1;
        if ($size[1] > 0 && $this->getHeight() > 0) {
            $y_ratio = $this->getHeight() / $size[1];
        }

        // add new area to work image
        $area = new ilMapArea();
        $area->setShape($a_shape);
        $area->setCoords($a_coords);
        $area->draw(
            $this->getMapWorkImage(),
            $this->color1,
            $this->color2,
            false,
            $x_ratio,
            $y_ratio
        );

        $this->saveMapWorkImage();
    }

    /**
     * output raw map work copy file
     */
    public function outputMapWorkCopy() : void
    {
        if ($this->getMapWorkCopyType() != "") {
            header("Pragma: no-cache");
            header("Expires: 0");
            header("Content-type: image/" . strtolower($this->getMapWorkCopyType()));
            readfile($this->getMapWorkCopyName());
        }
        exit;
    }

    /**
     * build image map work image
     */
    public function buildMapWorkImage() : void
    {
        $im_type = strtolower($this->getMapWorkCopyType());

        switch ($im_type) {
            case "gif":
                $this->map_image = imagecreatefromgif($this->getMapWorkCopyName());
                break;

            case "jpg":
            case "jpeg":
                $this->map_image = imagecreatefromjpeg($this->getMapWorkCopyName());
                break;

            case "png":
                $this->map_image = imagecreatefrompng($this->getMapWorkCopyName());
                break;
        }

        // try to allocate black and white as color. if this is not possible, get the closest colors
        if (imagecolorstotal($this->map_image) > 250) {
            $this->color1 = imagecolorclosest($this->map_image, 0, 0, 0);
            $this->color2 = imagecolorclosest($this->map_image, 255, 255, 255);
        } else {
            $this->color1 = imagecolorallocate($this->map_image, 0, 0, 0);
            $this->color2 = imagecolorallocate($this->map_image, 255, 255, 255);
        }
    }

    /**
     * save image map work image as file
     */
    public function saveMapWorkImage() : void
    {
        $im_type = strtolower($this->getMapWorkCopyType());

        // save image work-copy and free memory
        switch ($im_type) {
            case "gif":
                imagegif($this->map_image, $this->getMapWorkCopyName());
                break;

            case "jpg":
            case "jpeg":
                imagejpeg($this->map_image, $this->getMapWorkCopyName());
                break;

            case "png":
                imagepng($this->map_image, $this->getMapWorkCopyName());
                break;
        }

        imagedestroy($this->map_image);
    }

    /**
     * @return GdImage|resource|null
     */
    public function getMapWorkImage()
    {
        return $this->map_image;
    }

    /**
     * get xml code of media items' areas
     */
    public function getMapAreasXML(
        bool $a_insert_inst = false,
        int $a_inst = 0
    ) : string {
        $xml = "";

        // build xml of map areas
        for ($i = 0; $i < count($this->mapareas); $i++) {
            $area = $this->mapareas[$i];

            // highlight mode
            $hm = "";
            if ($area->getHighlightMode() != "") {
                $hm = ' HighlightMode="' . $area->getHighlightMode() . '" ';
                $hcl = ($area->getHighlightClass() != "")
                    ? $area->getHighlightClass()
                    : "Accented";
                $hm .= 'HighlightClass="' . $hcl . '" ';
            }

            $xml .= "<MapArea Shape=\"" . $area->getShape() . "\" Coords=\"" . $area->getCoords() . "\" " . $hm . ">";
            if ($area->getLinkType() == IL_INT_LINK) {
                $target_frame = $area->getTargetFrame();

                if ($area->getType() == "GlossaryItem" && $target_frame == "") {
                    $target_frame = "Glossary";
                }

                $tf_str = ($target_frame == "")
                    ? ""
                    : "TargetFrame=\"" . $target_frame . "\"";

                $xml .= "<IntLink Target=\"" . $area->getTarget($a_insert_inst, $a_inst) . "\" Type=\"" .
                    $area->getType() . "\" $tf_str>";
                // see bug 17893 and http://stackoverflow.com/questions/4026502/xml-error-at-ampersand
                $xml .= htmlspecialchars($area->getTitle(), ENT_QUOTES);
                $xml .= "</IntLink>";
            } else {
                $xml .= "<ExtLink Href=\"" . str_replace("&", "&amp;", $area->getHref()) . "\" Title=\"" .
                    str_replace("&", "&amp;", $area->getExtTitle()) . "\">";
                $xml .= str_replace("&", "&amp;", $area->getTitle());
                $xml .= "</ExtLink>";
            }
            $xml .= "</MapArea>";
        }
        return $xml;
    }

    /**
     * resolve internal links of all media items of a media object
     * @param int $a_mob_id media object id
     */
    public static function _resolveMapAreaLinks(
        int $a_mob_id
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();

        //echo "mediaItems::resolve<br>";
        // read media_object record
        $query = "SELECT * FROM media_item WHERE mob_id = " .
            $ilDB->quote($a_mob_id, "integer") . " " .
            "ORDER BY nr";
        $item_set = $ilDB->query($query);
        while ($item_rec = $ilDB->fetchAssoc($item_set)) {
            ilMapArea::_resolveIntLinks($item_rec["id"]);
        }
    }

    /**
     * get all internal links of map areas of a mob
     * @param int $a_mob_id media object id
     */
    public static function _getMapAreasIntLinks(
        int $a_mob_id
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        // read media_items records
        $query = "SELECT * FROM media_item WHERE mob_id = " .
            $ilDB->quote($a_mob_id, "integer") . " ORDER BY nr";

        $item_set = $ilDB->query($query);
        $links = array();
        while ($item_rec = $ilDB->fetchAssoc($item_set)) {
            $map_links = ilMapArea::_getIntLinks($item_rec["id"]);
            foreach ($map_links as $key => $map_link) {
                $links[$key] = $map_link;
            }
        }
        return $links;
    }

    /**
     * Extract parameters of special external references to parameter array
     */
    public function extractUrlParameters() : void
    {
        $par = ilExternalMediaAnalyzer::extractUrlParameters(
            $this->getLocation(),
            $this->getParameters()
        );
        foreach ($par as $k => $v) {
            $this->setParameter($k, $v);
        }
    }

    public function determineDuration() : void
    {
        $ana = new ilMediaAnalyzer();

        if (ilExternalMediaAnalyzer::isVimeo($this->getLocation())) {
            $par = ilExternalMediaAnalyzer::extractVimeoParameters($this->getLocation());
            $meta = ilExternalMediaAnalyzer::getVimeoMetadata($par["id"]);
            if ($meta["duration"] > 0) {
                $this->setDuration((int) $meta["duration"]);
            }
        } else {
            $file = ($this->getLocationType() == "Reference")
                ? $this->getLocation()
                : ilObjMediaObject::_getDirectory($this->getMobId()) . "/" . $this->getLocation();

            $remote = false;

            if (substr($file, 0, 4) == "http") {
                if ($fp_remote = fopen($file, 'rb')) {
                    $tmpdir = ilFileUtils::ilTempnam();
                    ilFileUtils::makeDir($tmpdir);
                    $localtempfilename = tempnam($tmpdir, 'getID3');
                    if ($fp_local = fopen($localtempfilename, 'wb')) {
                        while ($buffer = fread($fp_remote, 8192)) {
                            fwrite($fp_local, $buffer);
                        }
                        fclose($fp_local);
                        $file = $localtempfilename;
                    }
                    fclose($fp_remote);
                }
            }

            $ana->setFile($file);
            $ana->analyzeFile();
            $this->setDuration((int) $ana->getPlaytimeSeconds());

            if ($remote) {
                unlink($localtempfilename);
            }
        }
    }

    /**
     * Get media items for upload hash
     * @param string $a_hash upload hash
     * @return array[]
     */
    public static function getMediaItemsForUploadHash(
        string $a_hash
    ) : array {
        global $DIC;

        $db = $DIC->database();

        $set = $db->queryF(
            "SELECT * FROM media_item " .
            " WHERE upload_hash = %s ",
            array("text"),
            array($a_hash)
        );
        $media_items = array();
        while ($rec = $db->fetchAssoc($set)) {
            $media_items[] = $rec;
        }
        return $media_items;
    }
}
