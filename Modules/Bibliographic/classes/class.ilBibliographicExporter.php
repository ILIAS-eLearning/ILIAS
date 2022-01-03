<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exporter class for Bibliographic class
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 *
 * @version $Id: $
 * @ingroup ModulesBibliographic
 */
class ilBibliographicExporter extends ilXmlExporter
{

    protected ?\ilBibliographicDataSet $ds = null;
    /**
     * @var mixed|null
     */
    protected $db;


    public function init() : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $this->ds = new ilBibliographicDataSet();
        $this->ds->setDSPrefix('ds');
        $this->db = $ilDB;
    }


    /**
     * @param string $a_entity
     * @return array
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            '4.5.0' => array(
                'namespace' => 'http://www.ilias.de/Modules/DataCollection/dcl/4_5',
                'xsd_file" => "ilias_dcl_4_5.xsd',
                'uses_dataset' => true,
                'min' => '4.5.0',
                'max' => '',
            ),
        );
    }


    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        ilUtil::makeDirParents($this->getAbsoluteExportDirectory());
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->exportLibraryFile($a_id);

        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], '', true, true);
    }
}
