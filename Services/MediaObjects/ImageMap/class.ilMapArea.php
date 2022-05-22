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

define("IL_AREA_RECT", "Rect");
define("IL_AREA_CIRCLE", "Circle");
define("IL_AREA_POLY", "Poly");
define("IL_AREA_WHOLE_PICTURE", "WholePicture");

define("IL_INT_LINK", "int");
define("IL_EXT_LINK", "ext");
define("IL_NO_LINK", "no");

define("IL_LT_STRUCTURE", "StructureObject");
define("IL_LT_PAGE", "PageObject");
define("IL_LT_MEDIA", "MediaObject");
define("IL_LT_GLITEM", "GlossaryItem");

define("IL_TF_MEDIA", "Media");
define("IL_TF_FAQ", "FAQ");
define("IL_TF_GLOSSARY", "Glossary");
define("IL_TF_NEW", "New");


/**
 * Class ilMapArea
 *
 * Map Area of an Image Map, subobject of Media Item
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMapArea
{
    public const HL_NONE = "";
    public const HL_HOVER = "Hover";
    public const HL_ALWAYS = "Always";
    public const HLCL_ACCENTED = "";
    public const HLCL_LIGHT = "Light";
    public const HLCL_DARK = "Dark";
    protected string $highlight_mode = "";
    protected string $highlight_class = "";

    protected ilDBInterface $db;
    public int $item_id = 0;
    public int $nr = 0;
    public string $shape = "";
    public string $coords = "";
    public string $title = "";
    public string $linktype = "";
    public string $xl_title = "";
    public string $xl_href = "";
    public string $il_target = "";
    public string $il_type = "";
    public string $il_target_frame = "";

    /**
     * @param	int		$a_item_id		parent media item id
     * @param	int		$a_nr			map area number within media item
     */
    public function __construct(
        int $a_item_id = 0,
        int $a_nr = 0
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->title = "";
        if ($a_item_id != 0 && $a_nr != 0) {
            $this->setItemId($a_item_id);
            $this->setNr($a_nr);
            $this->read();
        }
    }

    /**
     * create persistent map area object in db
     */
    public function create() : void
    {
        $ilDB = $this->db;

        $q = "INSERT INTO map_area (item_id, nr, shape, " .
            "coords, link_type, title, href, target, type, highlight_mode, highlight_class, target_frame) " .
            " VALUES (" .
            $ilDB->quote($this->getItemId(), "integer") . "," .
            $ilDB->quote($this->getNr(), "integer") . "," .
            $ilDB->quote($this->getShape(), "text") . "," .
            $ilDB->quote($this->getCoords(), "text") . "," .
            $ilDB->quote($this->getLinkType(), "text") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getHref(), "text") . "," .
            $ilDB->quote($this->getTarget(), "text") . "," .
            $ilDB->quote($this->getType(), "text") . "," .
            $ilDB->quote($this->getHighlightMode(), "text") . "," .
            $ilDB->quote($this->getHighlightClass(), "text") . "," .
            $ilDB->quote($this->getTargetFrame(), "text") . ")";
        $ilDB->manipulate($q);
    }

    /**
     * get maximum nr of media item (static)
     */
    public static function _getMaxNr(
        int $a_item_id
    ) : int {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "SELECT max(nr) AS max_nr FROM map_area WHERE item_id = " .
            $ilDB->quote($a_item_id, "integer");
        $max_set = $ilDB->query($q);
        $max_rec = $ilDB->fetchAssoc($max_set);

        return (int) $max_rec["max_nr"];
    }

    public function read() : void
    {
        $ilDB = $this->db;
        
        $q = "SELECT * FROM map_area WHERE item_id = " .
            $ilDB->quote($this->getItemId(), "integer") .
            " AND nr = " . $ilDB->quote($this->getNr(), "integer");
        $area_set = $ilDB->query($q);
        $area_rec = $ilDB->fetchAssoc($area_set);
        $this->setShape((string) $area_rec["shape"]);
        //echo $area_rec["Shape"];
        $this->setNr((int) $area_rec["nr"]);
        $this->setCoords((string) $area_rec["coords"]);
        $this->setLinkType((string) $area_rec["link_type"]);
        $this->setTitle((string) $area_rec["title"]);
        $this->setHref((string) $area_rec["href"]);
        $this->setTarget((string) $area_rec["target"]);
        $this->setType((string) $area_rec["type"]);
        $this->setTargetFrame((string) $area_rec["target_frame"]);
        $this->setHighlightMode((string) $area_rec["highlight_mode"]);
        $this->setHighlightClass((string) $area_rec["highlight_class"]);
    }

    public function update() : void
    {
        $ilDB = $this->db;

        $q = "UPDATE map_area SET shape = " . $ilDB->quote($this->getShape(), "text") .
            ", coords = " . $ilDB->quote($this->getCoords(), "text") .
            ", link_type = " . $ilDB->quote($this->getLinkType(), "text") .
            ", title = " . $ilDB->quote($this->getTitle(), "text") .
            ", href = " . $ilDB->quote($this->getHref(), "text") .
            ", target = " . $ilDB->quote($this->getTarget(), "text") .
            ", type = " . $ilDB->quote($this->getType(), "text") .
            ", highlight_mode = " . $ilDB->quote($this->getHighlightMode(), "text") .
            ", highlight_class = " . $ilDB->quote($this->getHighlightClass(), "text") .
            ", target_frame = " . $ilDB->quote($this->getTargetFrame(), "text") .
            " WHERE item_id = " . $ilDB->quote($this->getItemId(), "integer") .
            " AND nr = " . $ilDB->quote($this->getNr(), "integer");
        $ilDB->manipulate($q);
    }

    /**
     * resolve internal links of an item id
     */
    public static function _resolveIntLinks(
        int $a_item_id
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();

        //echo "maparea::resolve<br>";
        $q = "SELECT * FROM map_area WHERE item_id = " .
            $ilDB->quote($a_item_id, "integer");
        $area_set = $ilDB->query($q);
        while ($area_rec = $ilDB->fetchAssoc($area_set)) {
            $target = $area_rec["target"];
            $type = $area_rec["type"];
            $item_id = $area_rec["item_id"];
            $nr = $area_rec["nr"];

            if (($area_rec["link_type"] == IL_INT_LINK) && (!is_int(strpos($target, "__")))) {
                $new_target = ilInternalLink::_getIdForImportId($type, $target);
                if ($new_target !== false) {
                    $query = "UPDATE map_area SET " .
                        "target = " . $ilDB->quote($new_target, "text") . " " .
                        "WHERE item_id = " . $ilDB->quote($item_id, "integer") .
                        " AND nr = " . $ilDB->quote($nr, "integer");
                    $ilDB->manipulate($query);
                }
            }
        }
    }

    /**
     * get all internal links of a media items map areas
     *
     * @param	int		$a_item_id		media item id
     */
    public static function _getIntLinks(
        int $a_item_id
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "SELECT * FROM map_area WHERE item_id = " .
            $ilDB->quote($a_item_id, "integer");
        $area_set = $ilDB->query($q);

        $links = array();

        while ($area_rec = $ilDB->fetchAssoc($area_set)) {
            $target = $area_rec["target"];
            $type = $area_rec["type"];
            $targetframe = $area_rec["target_frame"];

            if (($area_rec["link_type"] == IL_INT_LINK) && (is_int(strpos($target, "__")))) {
                $links[$target . ":" . $type . ":" . $targetframe] =
                    array("Target" => $target, "Type" => $type,
                        "TargetFrame" => $targetframe);
            }
        }
        return $links;
    }

    /**
     * Get areas for a certain target
     */
    public static function _getMobsForTarget(
        string $a_type,
        string $a_target
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM map_area WHERE " .
            " link_type = " . $ilDB->quote($a_type, "text") .
            " AND target = " . $ilDB->quote($a_target, "text");
        $set = $ilDB->query($q);
        
        $mobs = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $mob_id = ilMediaItem::_lookupMobId($rec["item_id"]);
            $mobs[$mob_id] = $mob_id;
        }
        
        return $mobs;
    }
    
    public static function getAllHighlightModes() : array
    {
        global $DIC;

        $lng = $DIC->language();
        
        return array(
            self::HL_NONE => $lng->txt("cont_none"),
            self::HL_HOVER => $lng->txt("cont_hover"),
            self::HL_ALWAYS => $lng->txt("cont_always")
            );
    }
    
    public function setHighlightMode(
        string $a_val
    ) : void {
        $this->highlight_mode = $a_val;
    }
    
    public function getHighlightMode() : string
    {
        return $this->highlight_mode;
    }
    
    public static function getAllHighlightClasses() : array
    {
        global $DIC;

        $lng = $DIC->language();
        
        return array(
            self::HLCL_ACCENTED => $lng->txt("cont_accented"),
            self::HLCL_LIGHT => $lng->txt("cont_light"),
            self::HLCL_DARK => $lng->txt("cont_dark"),
        );
    }
    
    public function setHighlightClass(string $a_val) : void
    {
        $this->highlight_class = $a_val;
    }
    
    public function getHighlightClass() : string
    {
        return $this->highlight_class;
    }

    public function setItemId(int $a_item_id) : void
    {
        $this->item_id = $a_item_id;
    }

    public function getItemId() : int
    {
        return $this->item_id;
    }

    public function setNr(int $a_nr) : void
    {
        $this->nr = $a_nr;
    }

    public function getNr() : int
    {
        return $this->nr;
    }

    /**
     * set shape (IL_AREA_RECT, IL_AREA_CIRCLE, IL_AREA_POLY, IL_AREA_WHOLE_PICTURE)
     */
    public function setShape(string $a_shape) : void
    {
        $this->shape = $a_shape;
    }

    public function getShape() : string
    {
        return $this->shape;
    }

    /**
     * set coords of area
     * @param	string		$a_coords		coords (comma separated integers)
     */
    public function setCoords(string $a_coords) : void
    {
        $this->coords = $a_coords;
    }

    public function getCoords() : string
    {
        return $this->coords;
    }

    /**
     * set (tooltip)title of area
     */
    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function appendTitle(string $a_title_str) : void
    {
        $this->title .= $a_title_str;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * set link type
     * @param	string		$a_link_type		link type (IL_INT_LINK, IL_EXT_LINK)
     */
    public function setLinkType(string $a_link_type) : void
    {
        $this->linktype = $a_link_type;
    }

    public function getLinkType() : string
    {
        return $this->linktype;
    }

    /**
     * set hyper reference (external link only)
     */
    public function setHref(string $a_href) : void
    {
        $this->xl_href = $a_href;
    }

    public function getHref() : string
    {
        return $this->xl_href;
    }

    /**
     * set link text (external link only)
     * @param	string		$a_title		link text
     */
    public function setExtTitle(string $a_title) : void
    {
        $this->xl_title = $a_title;
    }

    public function getExtTitle() : string
    {
        return $this->xl_title;
    }

    /**
     * set link target (internal link only)
     * @param	string		$a_target	link target (e.g. "il__pg_23")
     */
    public function setTarget(string $a_target) : void
    {
        $this->il_target = $a_target;
    }

    /**
     * get link target (internal link only)
     */
    public function getTarget(bool $a_insert_inst = false) : string
    {
        $target = $this->il_target;

        if ((substr($target, 0, 4) == "il__") && $a_insert_inst) {
            $target = "il_" . IL_INST_ID . "_" . substr($target, 4, strlen($target) - 4);
        }

        return $target;
    }

    /**
     * set link type (internal link only)
     * @param	string		$a_type			link type
     *				(IL_LT_STRUCTURE | IL_LT_PAGE | IL_LT_MEDIA | IL_LT_GLITEM)
     */
    public function setType(string $a_type) : void
    {
        $this->il_type = $a_type;
    }

    public function getType() : string
    {
        return $this->il_type;
    }

    /**
     * set link target frame (internal link only)
     *
     * @param	string		$a_target_frame		target frame (IL_TF_MEDIA |
     *											IL_TF_FAQ | IL_TF_GLOSSARY | IL_TF_NEW)
     */
    public function setTargetFrame(string $a_target_frame) : void
    {
        $this->il_target_frame = $a_target_frame;
    }

    public function getTargetFrame() : string
    {
        return $this->il_target_frame;
    }

    /**
     * @param resource|GdImage $a_image (GdImage comes with 8.0)
     */
    public function draw(
        $a_image,
        int $a_col1,
        int $a_col2,
        bool $a_close_poly = true,
        float $a_x_ratio = 1,
        float $a_y_ratio = 1
    ) : void {
        switch ($this->getShape()) {
            case "Rect":
                $this->drawRect(
                    $a_image,
                    $this->getCoords(),
                    $a_col1,
                    $a_col2,
                    $a_x_ratio,
                    $a_y_ratio
                );
                break;

            case "Circle":
                $this->drawCircle(
                    $a_image,
                    $this->getCoords(),
                    $a_col1,
                    $a_col2,
                    $a_x_ratio,
                    $a_y_ratio
                );
                break;

            case "Poly":
                $this->drawPoly(
                    $a_image,
                    $this->getCoords(),
                    $a_col1,
                    $a_col2,
                    $a_close_poly,
                    $a_x_ratio,
                    $a_y_ratio
                );
                break;
        }
    }

    /**
     * draws an outlined two color line in an image
     * @param resource|GdImage $im (GdImage comes with 8.0)
     * @param	int		$x1		x-coordinate of starting point
     * @param	int		$y1		y-coordinate of starting point
     * @param	int		$x2		x-coordinate of ending point
     * @param	int		$y2		y-coordinate of ending point
     * @param	int		$c1		color identifier 1
     * @param	int		$c2		color identifier 2
     */
    public function drawLine(
        $im,
        int $x1,
        int $y1,
        int $x2,
        int $y2,
        int $c1,
        int $c2
    ) : void {
        imageline($im, $x1 + 1, $y1, $x2 + 1, $y2, $c1);
        imageline($im, $x1 - 1, $y1, $x2 - 1, $y2, $c1);
        imageline($im, $x1, $y1 + 1, $x2, $y2 + 1, $c1);
        imageline($im, $x1, $y1 - 1, $x2, $y2 - 1, $c1);
        imageline($im, $x1, $y1, $x2, $y2, $c2);
    }

    /**
     * draws an outlined two color rectangle
     * @param resource|GdImage $im (GdImage comes with 8.0)
     * @param	string		$coords     coordinate string, format : "x1,y1,x2,y2" with (x1,y1) is top left
     *									and (x2,y2) is bottom right point of the rectangle
     * @param	int			$c1			color identifier 1
     * @param	int			$c2			color identifier 2
     */
    public function drawRect(
        $im,
        string $coords,
        int $c1,
        int $c2,
        float $a_x_ratio = 1,
        float $a_y_ratio = 1
    ) : void {
        $coord = explode(",", $coords);

        $this->drawLine(
            $im,
            $coord[0] / $a_x_ratio,
            $coord[1] / $a_y_ratio,
            $coord[0] / $a_x_ratio,
            $coord[3] / $a_y_ratio,
            $c1,
            $c2
        );
        $this->drawLine(
            $im,
            $coord[0] / $a_x_ratio,
            $coord[3] / $a_y_ratio,
            $coord[2] / $a_x_ratio,
            $coord[3] / $a_y_ratio,
            $c1,
            $c2
        );
        $this->drawLine(
            $im,
            $coord[2] / $a_x_ratio,
            $coord[3] / $a_y_ratio,
            $coord[2] / $a_x_ratio,
            $coord[1] / $a_y_ratio,
            $c1,
            $c2
        );
        $this->drawLine(
            $im,
            $coord[2] / $a_x_ratio,
            $coord[1] / $a_y_ratio,
            $coord[0] / $a_x_ratio,
            $coord[1] / $a_y_ratio,
            $c1,
            $c2
        );
    }


    /**
     * draws an outlined two color polygon
     * @param resource|GdImage $im (GdImage comes with 8.0)
     * @param	string		$coords     coordinate string, format : "x1,y1,x2,y2,..." with every (x,y) pair is
     *									an ending point of a line of the polygon
     * @param	int			$c1			color identifier 1
     * @param	int			$c2			color identifier 2
     * @param	bool		$closed		true: the first and the last point will be connected with a line
     */
    public function drawPoly(
        $im,
        string $coords,
        int $c1,
        int $c2,
        bool $closed,
        float $a_x_ratio = 1,
        float $a_y_ratio = 1
    ) : void {
        if ($closed) {
            $p = 0;
        } else {
            $p = 1;
        }

        $anz = ilMapArea::countCoords($coords);

        if ($anz < (3 - $p)) {
            return;
        }

        $c = explode(",", $coords);

        for ($i = 0; $i < $anz - $p; $i++) {
            $this->drawLine(
                $im,
                $c[$i * 2] / $a_x_ratio,
                $c[$i * 2 + 1] / $a_y_ratio,
                $c[($i * 2 + 2) % (2 * $anz)] / $a_x_ratio,
                $c[($i * 2 + 3) % (2 * $anz)] / $a_y_ratio,
                $c1,
                $c2
            );
        }
    }


    /**
     * draws an outlined two colored circle
     * @param resource|GdImage $im (GdImage comes with 8.0)
     * @param	string		$coords     coordinate string, format : "x,y,r" with (x,y) as center point
     *									and r as radius
     * @param	int			$c1			color identifier 1
     * @param	int			$c2			color identifier 2
     */
    public function drawCircle(
        $im,
        string $coords,
        int $c1,
        int $c2,
        float $a_x_ratio = 1,
        float $a_y_ratio = 1
    ) : void {
        $c = explode(",", $coords);
        imagearc(
            $im,
            $c[0] / $a_x_ratio,
            $c[1] / $a_y_ratio,
            ($c[2] + 1) * 2 / $a_x_ratio,
            ($c[2] + 1) * 2 / $a_y_ratio,
            1,
            360,
            $c1
        );
        imagearc(
            $im,
            $c[0] / $a_x_ratio,
            $c[1] / $a_y_ratio,
            ($c[2] - 1) * 2 / $a_x_ratio,
            ($c[2] - 1) * 2 / $a_y_ratio,
            1,
            360,
            $c1
        );
        imagearc(
            $im,
            $c[0] / $a_x_ratio,
            $c[1] / $a_y_ratio,
            $c[2] * 2 / $a_x_ratio,
            $c[2] * 2 / $a_y_ratio,
            1,
            360,
            $c2
        );
    }

    /**
     * count the number of coordinates (x,y) in a coordinate string (format: "x1,y1,x2,y2,x3,y3,...")
     * @param string $c coordinate string
     * @return int number of coordinates
     */
    public static function countCoords(string $c) : int
    {
        if ($c == "") {
            return 0;
        } else {
            $coord_array = explode(",", $c);
            return (count($coord_array) / 2);
        }
    }
}
