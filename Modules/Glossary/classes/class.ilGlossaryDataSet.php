<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Glossary Data set class
 *
 * This class implements the following entities:
 * - glo: data from glossary
 * - glo_term: data from glossary_term
 * - glo_definition: data from glossary_definition
 * - glo_advmd_col_order: ordering md fields
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesGlossary
 */
class ilGlossaryDataSet extends ilDataSet
{
    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->log = ilLoggerFactory::getLogger('glo');
        parent::__construct();
    }

    /**
     * Get supported versions
     *
     * @return string version
     */
    public function getSupportedVersions()
    {
        return array("5.1.0");
    }
    
    /**
     * Get xml namespace
     *
     * @param
     * @return
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return "http://www.ilias.de/xml/Modules/Glossary/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     *
     * @param string $a_entity entity
     * @param string $a_version version number
     * @return array types array
     */
    protected function getTypes($a_entity, $a_version)
    {
        if ($a_entity == "glo") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Virtual" => "text",
                        "PresMode" => "text",
                        "SnippetLength" => "integer",
                        "GloMenuActive" => "text",
                        "ShowTax" => "integer"
                    );
            }
        }

        if ($a_entity == "glo_term") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "GloId" => "integer",
                        "Term" => "text",
                        "Language" => "text",
                        "ImportId" => "text"
                    );
            }
        }

        if ($a_entity == "glo_definition") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "TermId" => "integer",
                        "ShortText" => "text",
                        "Nr" => "integer",
                        "ShortTextDirty" => "integer"
                    );
            }
        }

        if ($a_entity == "glo_advmd_col_order") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "GloId" => "integer",
                        "FieldId" => "text",
                        "OrderNr" => "integer"
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
                
        if ($a_entity == "glo") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT o.title, o.description, g.id, g.virtual, pres_mode, snippet_length, show_tax, glo_menu_active" .
                        " FROM glossary g JOIN object_data o " .
                        " ON (g.id = o.obj_id) " .
                        " WHERE " . $ilDB->in("g.id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "glo_term") {
            switch ($a_version) {
                case "5.1.0":
                    // todo: how does import id needs to be set?
                    $this->getDirectDataFromQuery("SELECT id, glo_id, term, language" .
                        " FROM glossary_term " .
                        " WHERE " . $ilDB->in("glo_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "glo_definition") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT id, term_id, short_text, nr, short_text_dirty" .
                        " FROM glossary_definition " .
                        " WHERE " . $ilDB->in("term_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "glo_advmd_col_order") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT glo_id, field_id, order_nr" .
                        " FROM glo_advmd_col_order " .
                        " WHERE " . $ilDB->in("glo_id", $a_ids, false, "integer"));
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
            case "glo":
                return array(
                    "glo_term" => array("ids" => $a_rec["Id"]),
                    "glo_advmd_col_order" => array("ids" => $a_rec["Id"])
                );

            case "glo_term":
                return array(
                    "glo_definition" => array("ids" => $a_rec["Id"])
                );
        }

        return false;
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
            case "glo":

                include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjGlossary();
                    $newObj->create(true);
                }
                    
                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setVirtualMode($a_rec["Virtual"]);
                $newObj->setPresentationMode($a_rec["PresMode"]);
                $newObj->setSnippetLength($a_rec["SnippetLength"]);
                $newObj->setActiveGlossaryMenu($a_rec["GloMenuActive"]);
                $newObj->setShowTaxonomy($a_rec["ShowTax"]);
                $newObj->update(true);

                $this->current_obj = $newObj;
                $this->old_glo_id = $a_rec["Id"];
                $a_mapping->addMapping("Modules/Glossary", "glo", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping("Services/Object", "obj", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping(
                    "Services/MetaData",
                    "md",
                    $a_rec["Id"] . ":0:glo",
                    $newObj->getId() . ":0:glo"
                );
                $a_mapping->addMapping("Services/AdvancedMetaData", "parent", $a_rec["Id"], $newObj->getId());
                break;

            case "glo_term":

                // id, glo_id, term, language, import_id

                include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
                $glo_id = (int) $a_mapping->getMapping("Modules/Glossary", "glo", $a_rec["GloId"]);
                $term = new ilGlossaryTerm();
                $term->setGlossaryId($glo_id);
                $term->setTerm($a_rec["Term"]);
                $term->setLanguage($a_rec["Language"]);
                if ($this->getCurrentInstallationId() > 0) {
                    $term->setImportId("il_" . $this->getCurrentInstallationId() . "_git_" . $a_rec["Id"]);
                }
                $term->create();
                $term_id = $term->getId();
                $this->log->debug("glo_term, import id: " . $term->getImportId() . ", term id: " . $term_id);

                $a_mapping->addMapping(
                    "Modules/Glossary",
                    "term",
                    $a_rec["Id"],
                    $term_id
                );

                $a_mapping->addMapping(
                    "Services/Taxonomy",
                    "tax_item",
                    "glo:term:" . $a_rec["Id"],
                    $term_id
                );

                $a_mapping->addMapping(
                    "Services/Taxonomy",
                    "tax_item_obj_id",
                    "glo:term:" . $a_rec["Id"],
                    $glo_id
                );

                $a_mapping->addMapping(
                    "Services/AdvancedMetaData",
                    "advmd_sub_item",
                    "advmd:term:" . $a_rec["Id"],
                    $term_id
                );
                break;

            case "glo_definition":

                // id, term_id, short_text, nr, short_text_dirty

                include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
                $term_id = (int) $a_mapping->getMapping("Modules/Glossary", "term", $a_rec["TermId"]);
                if ((int) $term_id == 0) {
                    $this->log->debug("ERROR: Did not find glossary term glo_term id '" . $a_rec["TermId"] . "' for definition id '" . $a_rec["Id"] . "'.");
                } else {
                    $def = new ilGlossaryDefinition();
                    $def->setTermId($term_id);
                    $def->setShortText($a_rec["ShortText"]);
                    $def->setNr($a_rec["Nr"]);
                    $def->setShortTextDirty($a_rec["ShortTextDirty"]);
                    // no metadata, no page creation
                    $def->create(true, true);

                    $a_mapping->addMapping("Modules/Glossary", "def", $a_rec["Id"], $def->getId());
                    $a_mapping->addMapping(
                        "Services/COPage",
                        "pg",
                        "gdf:" . $a_rec["Id"],
                        "gdf:" . $def->getId()
                    );
                    $a_mapping->addMapping(
                        "Services/MetaData",
                        "md",
                        $this->old_glo_id . ":" . $a_rec["Id"] . ":gdf",
                        $this->current_obj->getId() . ":" . $def->getId() . ":gdf"
                    );
                }
                break;

            case "glo_advmd_col_order":
                // glo_id, field_id, order_nr
                // we save the ordering in the mapping, the glossary importer needs to fix this in the final
                // processing
                $a_mapping->addMapping("Modules/Glossary", "advmd_col_order", $a_rec["GloId"] . ":" . $a_rec["FieldId"], $a_rec["OrderNr"]);
                break;
        }
    }
}
