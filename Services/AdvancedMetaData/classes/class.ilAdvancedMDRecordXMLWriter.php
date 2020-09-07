<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesAdvancedMetaData
*/

include_once('./Services/Xml/classes/class.ilXmlWriter.php');

class ilAdvancedMDRecordXMLWriter extends ilXmlWriter
{
    protected $record_ids = array();
    protected $settings = null;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct($a_record_ids)
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        parent::__construct();
        $this->settings = $ilSetting;
        
        $this->record_ids = $a_record_ids ? $a_record_ids : array();
    }
    
    /**
     * generate xml
     *
     * @access public
     *
     */
    public function write()
    {
        $this->buildHeader();
        
        $this->xmlStartTag('AdvancedMetaDataRecords');
        foreach ($this->record_ids as $record_id) {
            $record_obj = ilAdvancedMDRecord::_getInstanceByrecordId($record_id);
            $record_obj->toXML($this);
        }
        $this->xmlEndTag('AdvancedMetaDataRecords');
    }
    
    /**
     * build header
     *
     * @access protected
     */
    protected function buildHeader()
    {
        $this->xmlSetDtdDef("<!DOCTYPE AdvancedMetaDataRecords PUBLIC \"-//ILIAS//DTD AdvancedMetaDataRecords//EN\" \"" .
            ILIAS_HTTP_PATH . "/Services/AdvancedMetaData/xml/ilias_advanced_meta_data_records_3_9.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS Advanced meta data records of installation " . $this->settings->get('inst_id') . ".");
        $this->xmlHeader();
    }
}
