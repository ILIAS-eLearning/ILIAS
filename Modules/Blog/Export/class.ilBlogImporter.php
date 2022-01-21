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
 * Importer class for blog
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBlogImporter extends ilXmlImporter
{
    protected ilBlogDataSet $ds;
    
    public function init() : void
    {
        $this->ds = new ilBlogDataSet();
        $this->ds->setDSPrefix("ds");
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
        $this->ds->setImportDirectory($this->getImportDirectory());
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
    
    public function finalProcessing(
        ilImportMapping $a_mapping
    ) : void {
        $blp_map = $a_mapping->getMappingsOfEntity("Services/COPage", "pg");
        foreach ($blp_map as $blp_id) {
            $blp_id = substr($blp_id, 4);
            $blog_id = ilBlogPosting::lookupBlogId($blp_id);
            ilBlogPosting::_writeParentId("blp", $blp_id, $blog_id);
        }
                
        $sty_map = $a_mapping->getMappingsOfEntity("Services/Style", "sty");
        foreach ($sty_map as $old_sty_id => $new_sty_id) {
            if (is_array(ilBlogDataSet::$style_map[$old_sty_id])) {
                foreach (ilBlogDataSet::$style_map[$old_sty_id] as $blog_id) {
                    ilObjStyleSheet::writeStyleUsage($blog_id, $new_sty_id);
                }
            }
        }
    }
}
