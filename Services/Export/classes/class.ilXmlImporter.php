<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Xml importer class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesExport
 */
abstract class ilXmlImporter
{
    protected $skip_entities = array();
    protected $imp; // import object
    
    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct()
    {
    }

    /**
     * Set import
     *
     * @param ilImport $a_val import object
     */
    public function setImport($a_val)
    {
        $this->imp = $a_val;
    }

    /**
     * Get import
     *
     * @return ilImport import object
     */
    public function getImport()
    {
        return $this->imp;
    }
    /**
     * Init
     */
    public function init()
    {
    }

    /**
     * Set installation id
     *
     * @param	string	installation id
     */
    public function setInstallId($a_val)
    {
        $this->install_id = $a_val;
    }

    /**
     * Get installation id
     *
     * @return	string	installation id
     */
    public function getInstallId()
    {
        return $this->install_id;
    }

    /**
     * Set installation url
     *
     * @param	string	installation url
     */
    public function setInstallUrl($a_val)
    {
        $this->install_url = $a_val;
    }

    /**
     * Get installation url
     *
     * @return	string	installation url
     */
    public function getInstallUrl()
    {
        return $this->install_url;
    }

    /**
     * Set schema version
     *
     * @param	string	schema version
     */
    public function setSchemaVersion($a_val)
    {
        $this->schema_version = $a_val;
    }

    /**
     * Get schema version
     *
     * @return	string	schema version
     */
    public function getSchemaVersion()
    {
        return $this->schema_version;
    }

    /**
     * Set import directory
     *
     * @param	string	import directory
     */
    public function setImportDirectory($a_val)
    {
        $this->import_directory = $a_val;
    }

    /**
     * Get import directory
     *
     * @return	string	import directory
     */
    public function getImportDirectory()
    {
        return $this->import_directory;
    }
    
    /**
     * Set skip entities
     *
     * @param array $a_val entities to skip
     */
    public function setSkipEntities($a_val)
    {
        $this->skip_entities = $a_val;
    }
    
    /**
     * Get skip entities
     *
     * @return array entities to skip
     */
    public function getSkipEntities()
    {
        return $this->skip_entities;
    }

    /**
     * Is exporting and importing installation identical?
     *
     * @param
     * @return
     */
    public function exportedFromSameInstallation()
    {
        if ($this->getInstallId() > 0 && ($this->getInstallId() == IL_INST_ID)) {
            return true;
        }
        return false;
    }


    /**
     * Import xml representation
     *
     * @param	string		entity
     * @param	string		target release
     * @param	string		id
     * @return	string		xml string
     */
    abstract public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping);

    /**
     * Final processing
     *
     * @param	array		mapping array
     */
    public function finalProcessing($a_mapping)
    {
    }
    
    // begin-patch optes_lok_export
    /**
     * Called after all container objects have been implemented.
     * @param ilImportMapping $mapping
     */
    public function afterContainerImportProcessing(ilImportMapping $mapping)
    {
    }
    // end-patch optes_lok_export
}
