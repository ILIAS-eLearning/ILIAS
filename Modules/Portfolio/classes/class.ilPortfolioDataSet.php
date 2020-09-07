<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Portfolio Data set class
 *
 * Only for portfolio templates!
 *
 * This class implements the following entities:
 * - portfolio: object data (usr_portfolio)
 * - portfolio_page: data from table usr_portfolio_page
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ModulesPortfolio
 */
class ilPortfolioDataSet extends ilDataSet
{
    protected $current_portfolio;
    
    /**
     * Get supported versions
     */
    public function getSupportedVersions()
    {
        return array("4.4.0", "5.0.0");
    }
    
    /**
     * Get xml namespace
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return "http://www.ilias.de/xml/Modules/Portfolio/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     */
    protected function getTypes($a_entity, $a_version)
    {
        if ($a_entity == "prtt") {
            switch ($a_version) {
                case "4.4.0":
                case "5.0.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Comments" => "integer",
                        "BgColor" => "text",
                        "FontColor" => "text",
                        "Img" => "text",
                        "Ppic" => "integer",
                        "Dir" => "directory"
                        );
            }
        }
        
        if ($a_entity == "portfolio_page") {
            switch ($a_version) {
                case "4.4.0":
                case "5.0.0":
                    return array(
                        "Id" => "integer",
                        "PortfolioId" => "integer",
                        "Title" => "integer",
                        "OrderNr" => "integer",
                        "Type" => "text"
                    );
            }
        }
    }

    /**
     * Read data
     *
     * @param
     * @return
     */
    public function readData($a_entity, $a_version, $a_ids, $a_field = "")
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
        
        if ($a_entity == "prtt") {
            switch ($a_version) {
                case "4.4.0":
                    $this->getDirectDataFromQuery("SELECT prtf.id,od.title,od.description," .
                        "prtf.comments,prtf.bg_color,prtf.font_color,prtf.img,prtf.ppic" .
                        " FROM usr_portfolio prtf" .
                        " JOIN object_data od ON (od.obj_id = prtf.id)" .
                        " WHERE " . $ilDB->in("prtf.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("prtt", "text"));
                    break;
                
                case "5.0.0":
                    $this->getDirectDataFromQuery("SELECT prtf.id,od.title,od.description," .
                        "prtf.bg_color,prtf.font_color,prtf.img,prtf.ppic" .
                        " FROM usr_portfolio prtf" .
                        " JOIN object_data od ON (od.obj_id = prtf.id)" .
                        " WHERE " . $ilDB->in("prtf.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("prtt", "text"));
                    break;
            }
        }
        
        if ($a_entity == "portfolio_page") {
            switch ($a_version) {
                case "4.4.0":
                case "5.0.0":
                    $this->getDirectDataFromQuery("SELECT id,portfolio_id,title,order_nr,type" .
                        " FROM usr_portfolio_page" .
                        " WHERE " . $ilDB->in("portfolio_id", $a_ids, false, "integer"));
                    break;
            }
        }
    }
    
    /**
     * Determine the dependent sets of data
     */
    protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
    {
        switch ($a_entity) {
            case "prtt":
                return array(
                    "portfolio_page" => array("ids" => $a_rec["Id"])
                );
        }
        return false;
    }

    /**
     * Get xml record
     *
     * @param
     * @return
     */
    public function getXmlRecord($a_entity, $a_version, $a_set)
    {
        if ($a_entity == "prtt") {
            include_once("./Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php");
            $dir = ilObjPortfolioTemplate::initStorage($a_set["Id"]);
            $a_set["Dir"] = $dir;
            
            include_once("./Services/Notes/classes/class.ilNote.php");
            $a_set["Comments"] = ilNote::commentsActivated($a_set["Id"], 0, "prtt");
        }

        return $a_set;
    }
    
    /**
     * Import record
     *
     * @param
     * @return
     */
    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
    {
        switch ($a_entity) {
            case "prtt":
                include_once("./Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php");
                
                // container copy
                if ($new_id = $a_mapping->getMapping("Services/Container", "objs", $a_rec["Id"])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjPortfolioTemplate();
                    $newObj->create();
                }
                                
                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setPublicComments($a_rec["Comments"]);
                $newObj->setBackgroundColor($a_rec["BgColor"]);
                $newObj->setFontColor($a_rec["FontColor"]);
                $newObj->setProfilePicture($a_rec["Ppic"]);
                $newObj->setImage($a_rec["Img"]);
                $newObj->update();
                
                // handle image(s)
                if ($a_rec["Img"]) {
                    $dir = str_replace("..", "", $a_rec["Dir"]);
                    if ($dir != "" && $this->getImportDirectory() != "") {
                        $source_dir = $this->getImportDirectory() . "/" . $dir;
                        $target_dir = ilObjPortfolioTemplate::initStorage($newObj->getId());
                        ilUtil::rCopy($source_dir, $target_dir);
                    }
                }

                $a_mapping->addMapping("Modules/Portfolio", "prtt", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping("Services/Object", "obj", $a_rec["Id"], $newObj->getId());
                break;

            case "portfolio_page":
                $prtt_id = (int) $a_mapping->getMapping("Modules/Portfolio", "prtt", $a_rec["PortfolioId"]);
                if ($prtt_id) {
                    include_once("./Modules/Portfolio/classes/class.ilPortfolioTemplatePage.php");
                    $newObj = new ilPortfolioTemplatePage();
                    $newObj->setPortfolioId($prtt_id);
                    $newObj->setTitle($a_rec["Title"]);
                    $newObj->setType($a_rec["Type"]);
                    $newObj->setOrderNr($a_rec["OrderNr"]);
                    $newObj->create(true);
                    
                    $a_mapping->addMapping("Services/COPage", "pg", "prtt:" . $a_rec["Id"], "prtt:" . $newObj->getId());
                }
                break;
        }
    }
}
