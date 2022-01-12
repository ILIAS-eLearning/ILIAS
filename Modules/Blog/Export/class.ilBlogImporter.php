<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Importer class for blog
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBlogImporter extends ilXmlImporter
{
    protected $ds;
    
    /**
     * Initialisation
     */
    public function init() : void
    {
        $this->ds = new ilBlogDataSet();
        $this->ds->setDSPrefix("ds");
    }

    /**
     * Import XML
     *
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        $this->ds->setImportDirectory($this->getImportDirectory());
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
    
    /**
     * Final processing
     *
     * @param	array		mapping array
     */
    public function finalProcessing(ilImportMapping $a_mapping) : void
    {
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
