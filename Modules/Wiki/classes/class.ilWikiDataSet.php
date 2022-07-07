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
 * Wiki Data set class
 *
 * This class implements the following entities:
 * - wiki: data from il_wiki_data
 * - wpg: data from il_wiki_page
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiDataSet extends ilDataSet
{
    protected ?ilObjWiki $current_obj = null;
    protected ilLogger $wiki_log;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        parent::__construct();
        $this->wiki_log = ilLoggerFactory::getLogger('wiki');
    }

    public function getSupportedVersions() : array
    {
        return array("4.1.0", "4.3.0", "4.4.0", "5.1.0", "5.4.0");
    }
    
    protected function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "https://www.ilias.de/xml/Modules/Wiki/" . $a_entity;
    }
    
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        if ($a_entity === "wiki") {
            switch ($a_version) {
                case "4.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "StartPage" => "text",
                        "Short" => "text",
                        "Introduction" => "text",
                        "Rating" => "integer");
                    
                case "4.3.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "StartPage" => "text",
                        "Short" => "text",
                        "Introduction" => "text",
                        "Rating" => "integer",
                        "PublicNotes" => "integer",
                        // "ImpPages" => "integer",
                        "PageToc" => "integer",
                        "RatingSide" => "integer",
                        "RatingNew" => "integer",
                        "RatingExt" => "integer");
                    
                case "4.4.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "StartPage" => "text",
                        "Short" => "text",
                        "Introduction" => "text",
                        "Rating" => "integer",
                        "PublicNotes" => "integer",
                        // "ImpPages" => "integer",
                        "PageToc" => "integer",
                        "RatingSide" => "integer",
                        "RatingNew" => "integer",
                        "RatingExt" => "integer",
                        "RatingOverall" => "integer");

                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "StartPage" => "text",
                        "Short" => "text",
                        "Introduction" => "text",
                        "Rating" => "integer",
                        "PublicNotes" => "integer",
                        // "ImpPages" => "integer",
                        "PageToc" => "integer",
                        "RatingSide" => "integer",
                        "RatingNew" => "integer",
                        "RatingExt" => "integer",
                        "RatingOverall" => "integer",
                        "LinkMdValues" => "integer"
                    );

                case "5.4.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "StartPage" => "text",
                        "Short" => "text",
                        "Introduction" => "text",
                        "Rating" => "integer",
                        "PublicNotes" => "integer",
                        // "ImpPages" => "integer",
                        "PageToc" => "integer",
                        "RatingSide" => "integer",
                        "RatingNew" => "integer",
                        "RatingExt" => "integer",
                        "RatingOverall" => "integer",
                        "LinkMdValues" => "integer",
                        "EmptyPageTempl" => "integer"
                        );
            }
        }

        if ($a_entity === "wpg") {
            switch ($a_version) {
                case "4.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "WikiId" => "integer");
                    
                case "4.3.0":
                case "4.4.0":
                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "WikiId" => "integer",
                        "Blocked" => "integer",
                        "Rating" => "integer");

                case "5.4.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "WikiId" => "integer",
                        "Blocked" => "integer",
                        "Rating" => "integer",
                        "TemplateNewPages" => "integer",
                        "TemplateAddToPage" => "integer"
                    );
            }
        }

        if ($a_entity === "wiki_imp_page") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
                    return array(
                        "WikiId" => "integer",
                        "PageId" => "integer",
                        "Ord" => "integer",
                        "Indent" => "integer");
            }
        }
        return array();
    }

    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
                
        if ($a_entity === "wiki") {
            switch ($a_version) {
                case "4.1.0":
                    $this->getDirectDataFromQuery("SELECT id, title, description," .
                        " startpage start_page, short, rating, introduction" .
                        " FROM il_wiki_data JOIN object_data ON (il_wiki_data.id = object_data.obj_id)" .
                        " WHERE " . $ilDB->in("id", $a_ids, false, "integer"));
                    break;
                
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT id, title, description," .
                        " startpage start_page, short, rating, introduction," . // imp_pages,
                        " public_notes, page_toc, rating_side, rating_new, rating_ext" .
                        " FROM il_wiki_data JOIN object_data ON (il_wiki_data.id = object_data.obj_id)" .
                        " WHERE " . $ilDB->in("id", $a_ids, false, "integer"));
                    break;
                
                case "4.4.0":
                    $this->getDirectDataFromQuery("SELECT id, title, description," .
                        " startpage start_page, short, rating, rating_overall, introduction," . // imp_pages,
                        " public_notes, page_toc, rating_side, rating_new, rating_ext" .
                        " FROM il_wiki_data JOIN object_data ON (il_wiki_data.id = object_data.obj_id)" .
                        " WHERE " . $ilDB->in("id", $a_ids, false, "integer"));
                    break;

                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT id, title, description," .
                        " startpage start_page, short, rating, rating_overall, introduction," . // imp_pages,
                        " public_notes, page_toc, rating_side, rating_new, rating_ext, link_md_values" .
                        " FROM il_wiki_data JOIN object_data ON (il_wiki_data.id = object_data.obj_id)" .
                        " WHERE " . $ilDB->in("id", $a_ids, false, "integer"));
                    break;

                case "5.4.0":
                    $this->getDirectDataFromQuery("SELECT id, title, description," .
                        " startpage start_page, short, rating, rating_overall, introduction," . // imp_pages,
                        " public_notes, page_toc, rating_side, rating_new, rating_ext, link_md_values, empty_page_templ" .
                        " FROM il_wiki_data JOIN object_data ON (il_wiki_data.id = object_data.obj_id)" .
                        " WHERE " . $ilDB->in("id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity === "wpg") {
            switch ($a_version) {
                case "4.1.0":
                    $this->getDirectDataFromQuery("SELECT id, title, wiki_id" .
                        " FROM il_wiki_page" .
                        " WHERE " . $ilDB->in("wiki_id", $a_ids, false, "integer"));
                    break;
                
                case "4.3.0":
                case "4.4.0":
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT id, title, wiki_id," .
                        " blocked, rating" .
                        " FROM il_wiki_page" .
                        " WHERE " . $ilDB->in("wiki_id", $a_ids, false, "integer"));
                    break;

                case "5.4.0":
                    $this->getDirectDataFromQuery("SELECT id, title, wiki_id," .
                        " blocked, rating" .
                        " FROM il_wiki_page" .
                        " WHERE " . $ilDB->in("wiki_id", $a_ids, false, "integer"));
                    foreach ($this->data as $k => $v) {
                        $set = $ilDB->queryF(
                            "SELECT * FROM wiki_page_template " .
                            " WHERE wiki_id = %s " .
                            " AND wpage_id = %s ",
                            ["integer", "integer"],
                            [$v["WikiId"], $v["Id"]]
                        );
                        if ($rec = $ilDB->fetchAssoc($set)) {
                            $this->data[$k]["TemplateNewPages"] = $rec["new_pages"];
                            $this->data[$k]["TemplateAddToPage"] = $rec["add_to_page"];
                        }
                    }
                    break;
            }
        }

        if ($a_entity === "wiki_imp_page") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
                    $this->getDirectDataFromQuery("SELECT wiki_id, page_id, ord, indent " .
                        " FROM il_wiki_imp_pages " .
                        " WHERE " . $ilDB->in("wiki_id", $a_ids, false, "integer"));
                    break;
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
            case "wiki":
                return array(
                    "wpg" => array("ids" => $a_rec["Id"] ?? null),
                    "wiki_imp_page" => array("ids" => $a_rec["Id"] ?? null)
                );
        }

        return [];
    }
    
    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ) : void {
        switch ($a_entity) {
            case "wiki":

                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjWiki();
                    $newObj->setType("wiki");
                    $newObj->create(true);
                }
                    
                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setShortTitle($a_rec["Short"]);
                $newObj->setStartPage($a_rec["StartPage"]);
                $newObj->setRatingOverall($a_rec["RatingOverall"]);
                $newObj->setRating($a_rec["Rating"]);
                $newObj->setIntroduction($a_rec["Introduction"]);
                $newObj->setPublicNotes($a_rec["PublicNotes"]);
                
                // >= 4.3
                if (isset($a_rec["PageToc"])) {
                    $newObj->setPageToc($a_rec["PageToc"]);
                    $newObj->setRatingAsBlock($a_rec["RatingSide"]);
                    $newObj->setRatingForNewPages($a_rec["RatingNew"]);
                    $newObj->setRatingCategories($a_rec["RatingExt"]);
                }
                $newObj->setLinkMetadataValues($a_rec["LinkMdValues"]);
                $newObj->setEmptyPageTemplate((int) $a_rec["EmptyPageTempl"]);

                $newObj->update(true);
                $this->current_obj = $newObj;
                $a_mapping->addMapping("Modules/Wiki", "wiki", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping("Services/Object", "obj", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping("Services/Rating", "rating_category_parent_id", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping("Services/AdvancedMetaData", "parent", $a_rec["Id"], $newObj->getId());
                break;

            case "wpg":
                $wiki_id = $a_mapping->getMapping("Modules/Wiki", "wiki", $a_rec["WikiId"]);
                $wpage = new ilWikiPage();
                $wpage->setWikiId($wiki_id);
                $wpage->setTitle($a_rec["Title"]);
                
                // >= 4.3
                if (isset($a_rec["Blocked"])) {
                    $wpage->setBlocked($a_rec["Blocked"]);
                    $wpage->setRating($a_rec["Rating"]);
                }
                
                $wpage->create(true);

                if (isset($a_rec["TemplateNewPages"]) || isset($a_rec["TemplateAddToPage"])) {
                    $wtpl = new ilWikiPageTemplate($wiki_id);
                    $wtpl->save($wpage->getId(), (int) $a_rec["TemplateNewPages"], (int) $a_rec["TemplateAddToPage"]);
                }

                $a_mapping->addMapping("Modules/Wiki", "wpg", $a_rec["Id"], $wpage->getId());
                $a_mapping->addMapping("Services/COPage", "pg", "wpg:" . $a_rec["Id"], "wpg:" . $wpage->getId());
                $a_mapping->addMapping("Services/AdvancedMetaData", "advmd_sub_item", "advmd:wpg:" . $a_rec["Id"], $wpage->getId());
                break;

            case "wiki_imp_page":
                $wiki_id = $a_mapping->getMapping("Modules/Wiki", "wiki", $a_rec["WikiId"]);
                $page_id = $a_mapping->getMapping("Modules/Wiki", "wpg", $a_rec["PageId"]);
                if ($wiki_id > 0 && $page_id > 0 && is_object($this->current_obj) && $this->current_obj->getId() === (int) $wiki_id) {
                    $this->current_obj->addImportantPage((int) $page_id, (int) $a_rec["Ord"], (int) $a_rec["Indent"]);
                }
                break;
        }
    }
}
