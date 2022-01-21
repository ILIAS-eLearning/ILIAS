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
 * Blog Data set class
 *
 * This class implements the following entities:
 * - blog: object data
 * - blog_posting: data from table il_blog_posting
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBlogDataSet extends ilDataSet
{
    protected ilObjBlog $current_blog;
    public static array $style_map = array();
    
    public function getSupportedVersions() : array
    {
        return array("4.3.0", "5.0.0", "5.3.0");
    }
    
    protected function getXmlNamespace(
        string $a_entity,
        string $a_schema_version
    ) : string {
        return "https://www.ilias.de/xml/Modules/Blog/" . $a_entity;
    }
    
    protected function getTypes(
        string $a_entity,
        string $a_version
    ) : array {
        if ($a_entity == "blog") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Notes" => "integer",
                        "BgColor" => "text",
                        "FontColor" => "text",
                        "Img" => "text",
                        "Ppic" => "integer",
                        "RssActive" => "integer",
                        "Approval" => "integer",
                        "Dir" => "directory"
                        );
                    
                case "5.0.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Notes" => "integer",
                        "BgColor" => "text",
                        "FontColor" => "text",
                        "Img" => "text",
                        "Ppic" => "integer",
                        "RssActive" => "integer",
                        "Approval" => "integer",
                        "Dir" => "directory",
                        "AbsShorten" => "integer",
                        "AbsShortenLen" => "integer",
                        "AbsImage" => "integer",
                        "AbsImgWidth" => "integer",
                        "AbsImgHeight" => "integer",
                        "NavMode" => "integer",
                        "NavListPost" => "integer",
                        "NavListMon" => "integer",
                        "Keywords" => "integer",
                        "Authors" => "integer",
                        "NavOrder" => "text",
                        "OvPost" => "integer",
                        "Style" => "integer"
                        );

                case "5.3.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Notes" => "integer",
                        "BgColor" => "text",
                        "FontColor" => "text",
                        "Img" => "text",
                        "Ppic" => "integer",
                        "RssActive" => "integer",
                        "Approval" => "integer",
                        "Dir" => "directory",
                        "AbsShorten" => "integer",
                        "AbsShortenLen" => "integer",
                        "AbsImage" => "integer",
                        "AbsImgWidth" => "integer",
                        "AbsImgHeight" => "integer",
                        "NavMode" => "integer",
                        "NavListMonWithPost" => "integer",
                        "NavListMon" => "integer",
                        "Keywords" => "integer",
                        "Authors" => "integer",
                        "NavOrder" => "text",
                        "OvPost" => "integer",
                        "Style" => "integer"
                    );

            }
        }
        
        if ($a_entity == "blog_posting") {
            switch ($a_version) {
                case "4.3.0":
                case "5.0.0":
                case "5.3.0":
                    return array(
                        "Id" => "integer",
                        "BlogId" => "integer",
                        "Title" => "integer",
                        "Created" => "text",
                        "Author" => "text",
                        "Approved" => "integer",
                        "LastWithdrawn" => "text"
                    );
            }
        }
        return [];
    }

    public function readData(
        string $a_entity,
        string $a_version,
        array $a_ids
    ) : void {
        $ilDB = $this->db;

        if ($a_entity == "blog") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT bl.id,od.title,od.description," .
                        "bl.notes,bl.bg_color,bl.font_color,bl.img,bl.ppic,bl.rss_active,bl.approval" .
                        " FROM il_blog bl" .
                        " JOIN object_data od ON (od.obj_id = bl.id)" .
                        " WHERE " . $ilDB->in("bl.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("blog", "text"));
                    break;
                
                case "5.0.0":
                    $this->getDirectDataFromQuery("SELECT bl.id,od.title,od.description," .
                        "bl.bg_color,bl.font_color,bl.img,bl.ppic,bl.rss_active,bl.approval," .
                        "bl.abs_shorten,bl.abs_shorten_len,bl.abs_image,bl.abs_img_width,bl.abs_img_height," .
                        "bl.nav_mode,bl.nav_list_post,bl.nav_list_mon,bl.keywords,bl.authors,bl.nav_order," .
                        "bl.ov_post" .
                        " FROM il_blog bl" .
                        " JOIN object_data od ON (od.obj_id = bl.id)" .
                        " WHERE " . $ilDB->in("bl.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("blog", "text"));
                    break;

                case "5.3.0":
                    $this->getDirectDataFromQuery("SELECT bl.id,od.title,od.description," .
                        "bl.bg_color,bl.font_color,bl.img,bl.ppic,bl.rss_active,bl.approval," .
                        "bl.abs_shorten,bl.abs_shorten_len,bl.abs_image,bl.abs_img_width,bl.abs_img_height," .
                        "bl.nav_mode,bl.nav_list_mon_with_post,bl.nav_list_mon,bl.keywords,bl.authors,bl.nav_order," .
                        "bl.ov_post" .
                        " FROM il_blog bl" .
                        " JOIN object_data od ON (od.obj_id = bl.id)" .
                        " WHERE " . $ilDB->in("bl.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("blog", "text"));
                    break;
            }
        }
        
        if ($a_entity == "blog_posting") {
            switch ($a_version) {
                case "4.3.0":
                case "5.0.0":
                case "5.3.0":
                    $this->getDirectDataFromQuery("SELECT id,blog_id,title,created,author,approved,last_withdrawn" .
                        " FROM il_blog_posting WHERE " .
                        $ilDB->in("blog_id", $a_ids, false, "integer"));
                    foreach ($this->data as $idx => $item) {
                        // create full export id
                        $this->data[$idx]["Author"] = $this->createObjectExportId("usr", $item["Author"]);
                    }
                    break;
            }
            
            // keywords
            foreach ($this->data as $idx => $item) {
                $blog_id = ilBlogPosting::lookupBlogId($item["Id"]);
                $keywords = ilBlogPosting::getKeywords($blog_id, $item["Id"]);
                if ($keywords) {
                    foreach ($keywords as $kidx => $keyword) {
                        $this->data[$idx]["Keyword" . $kidx] = $keyword;
                    }
                }
            }
        }
    }
    
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ) : array {
        switch ($a_entity) {
            case "blog":
                return array(
                    "blog_posting" => array("ids" => $a_rec["Id"])
                );
        }
        return [];
    }

    public function getXmlRecord(
        string $a_entity,
        string $a_version,
        array $a_set
    ) : array {
        if ($a_entity == "blog") {
            $dir = ilObjBlog::initStorage($a_set["Id"]);
            $a_set["Dir"] = $dir;
            
            $a_set["Style"] = ilObjStyleSheet::lookupObjectStyle($a_set["Id"]);
            
            // #14734
            $a_set["Notes"] = ilNote::commentsActivated($a_set["Id"], 0, "blog");
        }

        return $a_set;
    }
    
    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ) : void {
        switch ($a_entity) {
            case "blog":

                // container copy
                if ($new_id = $a_mapping->getMapping("Services/Container", "objs", $a_rec["Id"])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjBlog();
                    $newObj->create();
                }
                                
                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setNotesStatus($a_rec["Notes"]);
                $newObj->setBackgroundColor($a_rec["BgColor"]);
                $newObj->setFontColor($a_rec["FontColor"]);
                $newObj->setProfilePicture($a_rec["Ppic"]);
                $newObj->setRSS($a_rec["RssActive"]);
                $newObj->setApproval($a_rec["Approval"]);
                $newObj->setImage($a_rec["Img"]);
                
                $newObj->setAbstractShorten($a_rec["AbsShorten"]);
                $newObj->setAbstractShortenLength($a_rec["AbsShortenLen"]);
                $newObj->setAbstractImage($a_rec["AbsImage"]);
                $newObj->setAbstractImageWidth($a_rec["AbsImgWidth"]);
                $newObj->setAbstractImageHeight($a_rec["AbsImgHeight"]);
                $newObj->setNavMode($a_rec["NavMode"]);
                if ($a_rec["NavListMonWithPost"] == 0) {
                    $newObj->setNavModeListMonthsWithPostings(3);
                } else {
                    $newObj->setNavModeListMonthsWithPostings($a_rec["NavListMonWithPost"]);
                }
                //$newObj->setNavModeListPostings($a_rec["NavListPost"]);
                $newObj->setNavModeListMonths($a_rec["NavListMon"]);
                $newObj->setKeywords($a_rec["Keywords"]);
                $newObj->setAuthors($a_rec["Authors"]);
                $newObj->setOrder(trim($a_rec["NavOrder"])
                    ? explode(";", $a_rec["NavOrder"])
                    : null);
                $newObj->setOverviewPostings($a_rec["OvPost"]);
                
                $newObj->update();
                
                // handle image(s)
                if ($a_rec["Img"]) {
                    $dir = str_replace("..", "", $a_rec["Dir"]);
                    if ($dir != "" && $this->getImportDirectory() != "") {
                        $source_dir = $this->getImportDirectory() . "/" . $dir;
                        $target_dir = ilObjBlog::initStorage($newObj->getId());
                        ilUtil::rCopy($source_dir, $target_dir);
                    }
                }

                if ($a_rec["Style"]) {
                    self::$style_map[$a_rec["Style"]][] = $newObj->getId();
                }
                $a_mapping->addMapping("Modules/Blog", "blog", $a_rec["Id"], $newObj->getId());
                break;

            case "blog_posting":
                $blog_id = (int) $a_mapping->getMapping("Modules/Blog", "blog", $a_rec["BlogId"]);
                if ($blog_id) {
                    $newObj = new ilBlogPosting();
                    $newObj->setBlogId($blog_id);
                    $newObj->setTitle($a_rec["Title"]);
                    $newObj->setCreated(new ilDateTime($a_rec["Created"], IL_CAL_DATETIME));
                    $newObj->setApproved($a_rec["Approved"]);
                    $newObj->setWithdrawn(new ilDateTime($a_rec["LastWithdrawn"], IL_CAL_DATETIME));
                    
                    // parse export id into local id (if possible)
                    $author = $this->parseObjectExportId($a_rec["Author"], -1);
                    $newObj->setAuthor($author["id"]);
                    
                    $newObj->create(false);
                    
                    // keywords
                    $keywords = array();
                    for ($loop = 0; $loop < 1000; $loop++) {
                        if (isset($a_rec["Keyword" . $loop])) {
                            $keyword = trim($a_rec["Keyword" . $loop]);
                            if (strlen($keyword)) {
                                $keywords[] = $keyword;
                            }
                        }
                    }
                    if (sizeof($keywords)) {
                        $newObj->updateKeywords($keywords);
                    }
                    
                    $a_mapping->addMapping("Services/COPage", "pg", "blp:" . $a_rec["Id"], "blp:" . $newObj->getId());
                }
                break;
        }
    }
}
