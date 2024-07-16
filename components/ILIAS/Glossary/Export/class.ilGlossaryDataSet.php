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
 * Glossary Data set class
 *
 * This class implements the following entities:
 * - glo: data from glossary
 * - glo_term: data from glossary_term
 * - glo_definition: data from glossary_definition
 * - glo_advmd_col_order: ordering md fields
 * - glo_auto_glossaries: automatically linked glossaries
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryDataSet extends ilDataSet
{
    protected int $old_glo_id;
    protected ilObjGlossary $current_obj;
    protected array $used_term_ids = [];
    protected ilLogger $log;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->log = ilLoggerFactory::getLogger('glo');
        parent::__construct();
    }

    public function getSupportedVersions(): array
    {
        return array("5.1.0", "5.4.0", "9.0.0");
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "https://www.ilias.de/xml/Modules/Glossary/" . $a_entity;
    }

    protected function getTypes(string $a_entity, string $a_version): array
    {
        if ($a_entity == "glo") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
                case "9.0.0":
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
                case "5.4.0":
                case "9.0.0":
                    return array(
                        "Id" => "integer",
                        "GloId" => "integer",
                        "Term" => "text",
                        "Language" => "text",
                        "ImportId" => "text",
                        "ShortText" => "text",
                        "ShortTextDirty" => "text"
                    );
            }
        }

        if ($a_entity == "glo_definition") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
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
                case "5.4.0":
                case "9.0.0":
                    return array(
                        "GloId" => "integer",
                        "FieldId" => "text",
                        "OrderNr" => "integer"
                    );
            }
        }

        if ($a_entity == "glo_auto_glossaries") {
            switch ($a_version) {
                case "5.4.0":
                case "9.0.0":
                    return array(
                        "GloId" => "integer",
                        "AutoGloId" => "text"
                    );
            }
        }
        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $ilDB = $this->db;

        if ($a_entity == "glo") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
                case "9.0.0":
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
                    $this->getDirectDataFromQuery("SELECT id, glo_id, term, language" .
                        " FROM glossary_term " .
                        " WHERE " . $ilDB->in("glo_id", $a_ids, false, "integer"));
                    break;

                case "5.4.0":
                    $this->getDirectDataFromQuery("SELECT id, glo_id, term, language" .
                        " FROM glossary_term " .
                        " WHERE " . $ilDB->in("glo_id", $a_ids, false, "integer"));

                    $set = $ilDB->query("SELECT r.term_id, r.glo_id, t.term, t.language " .
                        "FROM glo_term_reference r JOIN glossary_term t ON (r.term_id = t.id) " .
                        " WHERE " . $ilDB->in("r.glo_id", $a_ids, false, "integer"));
                    while ($rec = $ilDB->fetchAssoc($set)) {
                        $this->data[] = [
                            "Id" => $rec["term_id"],
                            "GloId" => $rec["glo_id"],
                            "Term" => $rec["term"],
                            "Language" => $rec["language"],
                        ];
                    }
                    break;
                case "9.0.0":
                    $this->getDirectDataFromQuery("SELECT id, glo_id, term, language" .
                        " FROM glossary_term " .
                        " WHERE " . $ilDB->in("glo_id", $a_ids, false, "integer"));

                    $set = $ilDB->query("SELECT r.term_id, r.glo_id, t.term, t.language, " .
                        "t.short_text, t.short_text_dirty " .
                        "FROM glo_term_reference r JOIN glossary_term t ON (r.term_id = t.id) " .
                        " WHERE " . $ilDB->in("r.glo_id", $a_ids, false, "integer"));
                    while ($rec = $ilDB->fetchAssoc($set)) {
                        $this->data[] = [
                            "Id" => $rec["term_id"],
                            "GloId" => $rec["glo_id"],
                            "Term" => $rec["term"],
                            "Language" => $rec["language"],
                            "ShortText" => $rec["short_text"],
                            "ShortTextDirty" => $rec["short_text_dirty"]
                        ];
                    }
                    break;
            }
        }

        if ($a_entity == "glo_definition") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
                    $this->getDirectDataFromQuery("SELECT id, term_id, short_text, nr, short_text_dirty" .
                        " FROM glossary_definition " .
                        " WHERE " . $ilDB->in("term_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "glo_advmd_col_order") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
                case "9.0.0":
                    $this->getDirectDataFromQuery("SELECT glo_id, field_id, order_nr" .
                        " FROM glo_advmd_col_order " .
                        " WHERE " . $ilDB->in("glo_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "glo_auto_glossaries") {
            switch ($a_version) {
                case "5.4.0":
                case "9.0.0":
                    $set = $ilDB->query("SELECT * FROM glo_glossaries " .
                        " WHERE " . $ilDB->in("id", $a_ids, false, "integer"));
                    $this->data = [];
                    while ($rec = $ilDB->fetchAssoc($set)) {
                        $this->data[] = [
                            "GloId" => $rec["id"],
                            "AutoGloId" => "il_" . IL_INST_ID . "_glo_" . $rec["glo_id"]
                        ];
                    }
                    break;
            }
        }
    }

    /**
     * Determine the dependent sets of data
     */
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ): array {
        switch ($a_entity) {
            case "glo":
                return array(
                    "glo_term" => array("ids" => $a_rec["Id"] ?? null),
                    "glo_advmd_col_order" => array("ids" => $a_rec["Id"] ?? null),
                    "glo_auto_glossaries" => array("ids" => $a_rec["Id"] ?? null)
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
    ): void {
        $a_rec = $this->stripTags($a_rec);
        switch ($a_entity) {
            case "glo":

                if ($new_id = $a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_rec['Id'])) {
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
                $newObj->setActiveFlashcards((bool) ($a_rec["FlashActive"] ?? false));
                $newObj->setFlashcardsMode($a_rec["FlashMode"] ?? "");
                if ($this->getCurrentInstallationId() > 0) {
                    $newObj->setImportId("il_" . $this->getCurrentInstallationId() . "_glo_" . $a_rec["Id"]);
                }
                $newObj->update();

                $this->current_obj = $newObj;
                $this->old_glo_id = $a_rec["Id"];
                $a_mapping->addMapping("components/ILIAS/Glossary", "glo", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping("components/ILIAS/Object", "obj", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping(
                    "components/ILIAS/MetaData",
                    "md",
                    $a_rec["Id"] . ":0:glo",
                    $newObj->getId() . ":0:glo"
                );
                $a_mapping->addMapping("components/ILIAS/AdvancedMetaData", "parent", $a_rec["Id"], $newObj->getId());
                break;

            case "glo_definition":

                // note: this must be kept for older glossaries <= 8

                $term_id = (int) $a_mapping->getMapping("Modules/Glossary", "term", $a_rec["TermId"]);
                if ($term_id == 0) {
                    $this->log->debug("ERROR: Did not find glossary term glo_term id '" . $a_rec["TermId"] . "' for definition id '" . $a_rec["Id"] . "'.");
                } elseif (!in_array($term_id, $this->used_term_ids)) {
                    $a_mapping->addMapping(
                        "Services/COPage",
                        "pg",
                        "gdf:" . $a_rec["Id"],
                        "term:" . $term_id
                    );
                    // use only first definition for term, because multiple definitons are abandoned since ILIAS 9
                    $this->used_term_ids[] = $term_id;
                }
                break;

            case "glo_term":

                // id, glo_id, term, language, import_id, short_text, short_text_dirty

                $glo_id = (int) $a_mapping->getMapping("components/ILIAS/Glossary", "glo", $a_rec["GloId"]);
                $term = new ilGlossaryTerm();
                $term->setGlossaryId($glo_id);
                $term->setTerm($a_rec["Term"]);
                $term->setLanguage($a_rec["Language"]);
                $term->setShortText($a_rec["ShortText"] ?? "");
                $term->setShortTextDirty($a_rec["ShortTextDirty"] ?? true);
                if ($this->getCurrentInstallationId() > 0) {
                    $term->setImportId("il_" . $this->getCurrentInstallationId() . "_git_" . $a_rec["Id"]);
                }
                $term->create(true);
                $term_id = $term->getId();
                $this->log->debug("glo_term, import id: " . $term->getImportId() . ", term id: " . $term_id);

                $a_mapping->addMapping(
                    "components/ILIAS/Glossary",
                    "term",
                    $a_rec["Id"],
                    $term_id
                );

                $a_mapping->addMapping(
                    "components/ILIAS/Taxonomy",
                    "tax_item",
                    "glo:term:" . $a_rec["Id"],
                    $term_id
                );

                $a_mapping->addMapping(
                    "components/ILIAS/Taxonomy",
                    "tax_item_obj_id",
                    "glo:term:" . $a_rec["Id"],
                    $glo_id
                );

                $a_mapping->addMapping(
                    "components/ILIAS/AdvancedMetaData",
                    "advmd_sub_item",
                    "advmd:term:" . $a_rec["Id"],
                    $term_id
                );

                $a_mapping->addMapping(
                    "components/ILIAS/COPage",
                    "pg",
                    "term:" . $a_rec["Id"],
                    "term:" . $term_id
                );
                break;

            case "glo_advmd_col_order":
                // glo_id, field_id, order_nr
                // we save the ordering in the mapping, the glossary importer needs to fix this in the final
                // processing
                $a_mapping->addMapping("components/ILIAS/Glossary", "advmd_col_order", $a_rec["GloId"] . ":" . $a_rec["FieldId"], $a_rec["OrderNr"]);
                break;

            case "glo_auto_glossaries":
                $auto_glo_id = ilObject::_lookupObjIdByImportId($a_rec["AutoGloId"]);
                $glo_id = (int) $a_mapping->getMapping("components/ILIAS/Glossary", "glo", $a_rec["GloId"]);
                if ($glo_id > 0 && $auto_glo_id > 0 && ilObject::_lookupType($auto_glo_id) == "glo") {
                    $glo = new ilObjGlossary($glo_id, false);
                    $glo->addAutoGlossary($auto_glo_id);
                    $glo->updateAutoGlossaries();
                }
                break;
        }
    }
}
