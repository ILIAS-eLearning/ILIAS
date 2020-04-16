<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/MediaObjects/classes/class.ilMapArea.php");

/**
* Class ilMediaItem
*
* Media Item, component of a media object (file or reference)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesMediaObjects
*/
class ilMediaItem
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilLanguage
     */
    protected $lng;

    public $id;
    public $purpose;
    public $location;
    public $location_type;
    public $format;
    public $width;
    public $height;
    public $caption;
    public $halign;
    public $parameters;
    public $mob_id;
    public $nr;
    public $mapareas;
    public $map_cnt;
    public $map_image;			// image map work copy image
    public $color1;			// map area line color 1
    public $color2;			// map area line color 2

    /**
     * @var string
     */
    protected $upload_hash;

    public function __construct($a_id = 0)
    {
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
    *
    * @param	int		$a_id		media item id
    */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    /**
    * get media item id
    *
    * @return	int		media item id
    */
    public function getId()
    {
        return $this->id;
    }

    /**
    * set id of parent media object
    *
    * @param	int		$a_mob_id		media object id
    */
    public function setMobId($a_mob_id)
    {
        $this->mob_id = $a_mob_id;
    }

    /**
    * get id of parent media object
    *
    * @return	int		media object id
    */
    public function getMobId()
    {
        return $this->mob_id;
    }

    /**
    * set number of media item within media object
    */
    public function setNr($a_nr)
    {
        $this->nr = $a_nr;
    }

    public function getNr()
    {
        return $this->nr;
    }

    /**
     * Set text representation
     *
     * @param	string	text representation
     */
    public function setTextRepresentation($a_val)
    {
        $this->text_representation = $a_val;
    }
    
    /**
     * Get text representation
     *
     * @return	string	text representation
     */
    public function getTextRepresentation()
    {
        return $this->text_representation;
    }
    
    /**
     * Set upload hash
     *
     * @param string $a_val upload hash
     */
    public function setUploadHash($a_val)
    {
        $this->upload_hash = $a_val;
    }

    /**
     * Get upload hash
     *
     * @return string upload hash
     */
    public function getUploadHash()
    {
        return $this->upload_hash;
    }


    /**
    * create persistent media item
    */
    public function create()
    {
        $ilDB = $this->db;

        $item_id = $ilDB->nextId("media_item");
        $query = "INSERT INTO media_item (id,mob_id, purpose, location, " .
            "location_type, format, width, " .
            "height, halign, caption, nr, text_representation, upload_hash) VALUES " .
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
            $ilDB->quote($this->getUploadHash(), "text") .
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

    /**
    * update media item data (without map areas!)
    */
    public function update()
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
            " upload_hash = " . $ilDB->quote($this->getUploadHash(), "text") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);

        // delete mob parameters
        $query = "DELETE FROM mob_parameter WHERE med_item_id = " .
            $ilDB->quote($this->getId(), "integer");

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

    /**
     * Write parameter
     *
     * @param
     * @return
     */
    public function writeParameter($a_name, $a_value)
    {
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
    public function read()
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

            $this->setLocation($item_rec["location"]);
            $this->setLocationType($item_rec["location_type"]);
            $this->setFormat($item_rec["format"]);
            $this->setWidth($item_rec["width"]);
            $this->setHeight($item_rec["height"]);
            $this->setHAlign($item_rec["halign"]);
            $this->setCaption($item_rec["caption"]);
            $this->setPurpose($item_rec["purpose"]);
            $this->setNr($item_rec["nr"]);
            $this->setMobId($item_rec["mob_id"]);
            $this->setId($item_rec["id"]);
            $this->setThumbTried($item_rec["tried_thumb"]);
            $this->setTextRepresentation($item_rec["text_representation"]);
            $this->setUploadHash($item_rec["upload_hash"]);

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
    public function writeThumbTried($a_tried)
    {
        $ilDB = $this->db;
        
        $q = "UPDATE media_item SET tried_thumb = " .
            $ilDB->quote($a_tried, "text") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
            
        $ilDB->manipulate($q);
    }

    /**
    * Lookup location for mob id
    *
    * @param	int		$a_mob_id	media object id
    * @param	string	$a_purpose	purpose
    */
    public static function _lookupLocationForMobId($a_mob_id, $a_purpose)
    {
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

    /**
    * Lookup Mob ID
    *
    * @param	int		$a_med_id	media item id
    */
    public static function _lookupMobId($a_med_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        // read media_object record
        $query = "SELECT * FROM media_item WHERE id = " .
            $ilDB->quote($a_med_id, "integer");
        $set = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["mob_id"];
        }

        return "";
    }

    /* read media item with specific purpose and mobId
    *
    * @param	integer		$a_mobId	 	media object id
    * @param	string		$a_purpose	 	media object purpose
    * @return 	array		$mob			media object
    */
    public static function _getMediaItemsOfMObId($a_mobId, $a_purpose)
    {
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
        return false;
    }
    
    /**
    * read media items into media objects (static)
    *
    * @param	object		$a_mob	 	media object
    */
    public static function _getMediaItemsOfMOb(&$a_mob)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        // read media_object record
        $query = "SELECT * FROM media_item WHERE mob_id = " .
            $ilDB->quote($a_mob->getId(), "integer") . " " .
            "ORDER BY nr";
        $item_set = $ilDB->query($query);
        while ($item_rec = $ilDB->fetchAssoc($item_set)) {
            $media_item = new ilMediaItem();
            $media_item->setNr($item_rec["nr"]);
            $media_item->setId($item_rec["id"]);
            $media_item->setLocation($item_rec["location"]);
            $media_item->setLocationType($item_rec["location_type"]);
            $media_item->setFormat($item_rec["format"]);
            $media_item->setWidth($item_rec["width"]);
            $media_item->setHeight($item_rec["height"]);
            $media_item->setHAlign($item_rec["halign"]);
            $media_item->setCaption($item_rec["caption"]);
            $media_item->setPurpose($item_rec["purpose"]);
            $media_item->setMobId($item_rec["mob_id"]);
            $media_item->setThumbTried($item_rec["tried_thumb"]);
            $media_item->setTextRepresentation($item_rec["text_representation"]);
            $media_item->setUploadHash($item_rec["upload_hash"]);

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

    /**
     * Delete all items of a mob
     *
     * @param int $a_mob_id media object id
     */
    public static function deleteAllItemsOfMob($a_mob_id)
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

    public function setPurpose($a_purpose)
    {
        $this->purpose = $a_purpose;
    }

    public function getPurpose()
    {
        return $this->purpose;
    }

    public function setLocation($a_location)
    {
        $this->location = $a_location;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocationType($a_type)
    {
        $this->location_type = $a_type;
    }

    public function getLocationType()
    {
        return $this->location_type;
    }

    public function setFormat($a_format)
    {
        $this->format = $a_format;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setThumbTried($a_tried)
    {
        $this->tried_thumb = $a_tried;
    }

    public function getThumbTried()
    {
        return $this->tried_thumb;
    }

    public function addMapArea(&$a_map_area)
    {
        $this->mapareas[$this->map_cnt] = $a_map_area;
        $this->map_cnt++;
    }

    /**
    * delete map area
    */
    public function deleteMapArea($nr)
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

    /**
    * get map area
    */
    public function &getMapArea($nr)
    {
        return $this->mapareas[$nr - 1];
    }

    /**
    * get map areas
    */
    public function getMapAreas()
    {
        return $this->mapareas;
    }

    /**
    * get width
    */
    public function getWidth()
    {
        return $this->width;
    }

    /**
    * set width
    */
    public function setWidth($a_width)
    {
        $this->width = $a_width;
    }

    /**
    * get height
    */
    public function getHeight()
    {
        return $this->height;
    }

    /**
    * set height
    */
    public function setHeight($a_height)
    {
        $this->height = $a_height;
    }

    /**
    * get original size
    */
    public function getOriginalSize()
    {
        $mob_dir = ilObjMediaObject::_getDirectory($this->getMobId());

        if (ilUtil::deducibleSize($this->getFormat())) {
            if ($this->getLocationType() == "LocalFile") {
                $loc = $mob_dir . "/" . $this->getLocation();
            } else {
                $loc = $this->getLocation();
            }

            include_once("./Services/MediaObjects/classes/class.ilMediaImageUtil.php");
            $size = ilMediaImageUtil::getImageSize($loc);
            if ($size[0] > 0 && $size[1] > 0) {
                return array("width" => $size[0], "height" => $size[1]);
            }
        }

        return false;
    }

    /**
    * set caption
    */
    public function setCaption($a_caption)
    {
        $this->caption = $a_caption;
    }

    /**
    * get caption
    */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
    * set horizontal align
    */
    public function setHAlign($a_halign)
    {
        $this->halign = $a_halign;
    }

    /**
    * get horizontal align
    */
    public function getHAlign()
    {
        return $this->halign;
    }


    /**
    * set parameter
    *
    * @param	string	$a_name		parameter name
    * @param	string	$a_value	parameter value
    */
    public function setParameter($a_name, $a_value)
    {
        if (self::checkParameter($a_name, $a_value)) {
            $this->parameters[$a_name] = $a_value;
        }
    }

    /**
    * reset parameters
    */
    public function resetParameters()
    {
        $this->parameters = array();
    }

    /**
    * set alle parameters via parameter string (format: par1="value1", par2="value2", ...)
    *
    * @param	string		$a_par		parameter string
    */
    public function setParameters($a_par)
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
     *
     * @param string $a_par parameter
     * @param string $a_val value
     * @return bool
     */
    public static function checkParameter($a_par, $a_val)
    {
        // do not allow event attributes
        if (substr(strtolower(trim($a_par)), 0, 2) == "on") {
            return false;
        }
        // no javascript in value
        if (is_int(strpos(strtolower($a_val), "javascript"))) {
            return false;
        }
        // do not allow to change the src attribute
        if (in_array(strtolower(trim($a_par)), array("src"))) {
            return false;
        }

        return true;
    }


    /**
    * get all parameters (in array)
    */
    public function getParameters()
    {
        return $this->parameters;
    }


    /**
    * get all parameters (as string)
    */
    public function getParameterString()
    {
        return ilUtil::assembleParameterString($this->parameters);
    }


    /**
    * get a single parameter
    */
    public function getParameter($a_name)
    {
        return $this->parameters[$a_name];
    }

    /**
    * get work directory for image map editing
    */
    public function getWorkDirectory()
    {
        return ilUtil::getDataDir() . "/map_workfiles/item_" . $this->getId();
    }

    /**
    * create work directory for image map editing
    */
    public function createWorkDirectory()
    {
        if (!@is_dir(ilUtil::getDataDir() . "/map_workfiles")) {
            ilUtil::createDirectory(ilUtil::getDataDir() . "/map_workfiles");
        }
        $work_dir = $this->getWorkDirectory();
        if (!@is_dir($work_dir)) {
            ilUtil::createDirectory($work_dir);
        }
    }

    /**
    * get location suffix
    */
    public function getSuffix()
    {
        $loc_arr = explode(".", $this->getLocation());

        return $loc_arr[count($loc_arr) - 1];
    }

    /**
    * get image type of image map work copy
    */
    public function getMapWorkCopyType()
    {
        return ilUtil::getGDSupportedImageType($this->getSuffix());
    }

    /**
    * Get name of image map work copy file
    *
    * @param	string		Get name, for copy of external referenced image
    */
    public function getMapWorkCopyName($a_reference_copy = false)
    {
        $file_arr = explode("/", $this->getLocation());
        $o_file = $file_arr[count($file_arr) - 1];
        $file_arr = explode(".", $o_file);
        unset($file_arr[count($file_arr) - 1]);
        $file = implode($file_arr, ".");

        if (!$a_reference_copy) {
            return $this->getWorkDirectory() . "/" . $file . "." . $this->getMapWorkCopyType();
        } else {
            return $this->getWorkDirectory() . "/l_copy_" . $o_file;
        }
    }

    /**
    * get media file directory
    */
    public function getDirectory()
    {
        return ilObjMediaObject::_getDirectory($this->getMobId());
    }

    /**
    * get media file directory
    */
    public function getThumbnailDirectory($a_mode = "filesystem")
    {
        return ilObjMediaObject::_getThumbnailDirectory($this->getMobId(), $a_mode);
    }

    /**
    * get thumbnail target
    */
    public function getThumbnailTarget($a_size = "")
    {
        if (is_int(strpos($this->getFormat(), "image"))) {
            $thumb_file = $this->getThumbnailDirectory() . "/" .
                $this->getPurpose() . ".jpeg";

            $thumb_file_small = $this->getThumbnailDirectory() . "/" .
                $this->getPurpose() . "_small.jpeg";

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
                    ilUtil::convertImage($med_file, $thumb_file, "jpeg", "80");
                    ilUtil::convertImage($med_file, $thumb_file_small, "jpeg", "40");
                }
            }

            if ($a_size == "small") {
                if (is_file($thumb_file_small)) {
                    return $this->getThumbnailDirectory("output") . "/" .
                        $this->getPurpose() . "_small.jpeg?dummy=" . rand(1, 999999);
                }
            } else {
                if (is_file($thumb_file)) {
                    return $this->getThumbnailDirectory("output") . "/" .
                        $this->getPurpose() . ".jpeg?dummy=" . rand(1, 999999);
                }
            }
        }

        return "";
    }


    /**
    * Copy the orginal file
    */
    public function copyOriginal()
    {
        $lng = $this->lng;
        $this->createWorkDirectory();

        $geom = ($this->getWidth() != "" && $this->getHeight() != "")
            ? $this->getWidth() . "x" . $this->getHeight()
            : "";

        if ($this->getLocationType() != "Reference") {
            ilUtil::convertImage(
                $this->getDirectory() . "/" . $this->getLocation(),
                $this->getMapWorkCopyName(),
                $this->getMapWorkCopyType(),
                $geom
            );
        } else {
            // first copy the external file, if necessary
            if (!is_file($this->getMapWorkCopyName(true)) || (filesize($this->getMapWorkCopyName(true)) == 0)) {
                $handle = @fopen($this->getLocation(), "r");
                $lcopy = fopen($this->getMapWorkCopyName(true), "w");
                if ($handle && $lcopy) {
                    while (!feof($handle)) {
                        $content = fread($handle, 4096);
                        fwrite($lcopy, $content);
                    }
                }
                @fclose($lcopy);
                @fclose($handle);
            }
            
            // now, create working copy
            ilUtil::convertImage(
                $this->getMapWorkCopyName(true),
                $this->getMapWorkCopyName(),
                $this->getMapWorkCopyType(),
                $geom
            );
        }

        if (!is_file($this->getMapWorkCopyName())) {
            ilUtil::sendFailure($lng->txt("cont_map_file_not_generated"));
            return false;
        }
        return true;
    }
    
    /**
    * make map work copy of image
    *
    * @param	int			$a_area_nr		draw area $a_area_nr only
    * @param	boolean		$a_exclude		true: draw all areas but area $a_area_nr
    */
    public function makeMapWorkCopy($a_area_nr = 0, $a_exclude = false)
    {
        $lng = $this->lng;
        
        if (!$this->copyOriginal()) {
            return false;
        }
        $this->buildMapWorkImage();
        
        // determine ratios
        $size = @getimagesize($this->getMapWorkCopyName());
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
        
        return true;
    }


    /**
    * draw a new area in work image
    *
    * @param	string		$a_shape		shape
    * @param	string		$a_coords		coordinates string
    */
    public function addAreaToMapWorkCopy($a_shape, $a_coords)
    {
        $this->buildMapWorkImage();

        // determine ratios
        $size = @getimagesize($this->getMapWorkCopyName());
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
    public function outputMapWorkCopy()
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
    public function buildMapWorkImage()
    {
        $im_type = strtolower($this->getMapWorkCopyType());

        switch ($im_type) {
            case "gif":
                $this->map_image = ImageCreateFromGIF($this->getMapWorkCopyName());
                break;

            case "jpg":
            case "jpeg":
                $this->map_image = ImageCreateFromJPEG($this->getMapWorkCopyName());
                break;

            case "png":
                $this->map_image = ImageCreateFromPNG($this->getMapWorkCopyName());
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
    * save image map work image
    */
    public function saveMapWorkImage()
    {
        $im_type = strtolower($this->getMapWorkCopyType());

        // save image work-copy and free memory
        switch ($im_type) {
            case "gif":
                ImageGIF($this->map_image, $this->getMapWorkCopyName());
                break;

            case "jpg":
            case "jpeg":
                ImageJPEG($this->map_image, $this->getMapWorkCopyName());
                break;

            case "png":
                ImagePNG($this->map_image, $this->getMapWorkCopyName());
                break;
        }

        ImageDestroy($this->map_image);
    }

    /**
    * get image map work image
    */
    public function &getMapWorkImage()
    {
        return $this->map_image;
    }


    /**
    * get xml code of media items' areas
    */
    public function getMapAreasXML($a_insert_inst = false, $a_inst = 0)
    {
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
    *
    * @param	int		$a_mob_id		media object id
    */
    public static function _resolveMapAreaLinks($a_mob_id)
    {
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
    *
    * @param	int		$a_mob_id		media object id
    */
    public static function _getMapAreasIntLinks($a_mob_id)
    {
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
    public function extractUrlParameters()
    {
        include_once("./Services/MediaObjects/classes/class.ilExternalMediaAnalyzer.php");
        $par = ilExternalMediaAnalyzer::extractUrlParameters(
            $this->getLocation(),
            $this->getParameters()
        );
        foreach ($par as $k => $v) {
            $this->setParameter($k, $v);
        }
    }

    /**
     * Get media items for upload hash
     *
     * @param string $a_hash upload hash
     * @return array
     */
    public static function getMediaItemsForUploadHash($a_hash)
    {
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
