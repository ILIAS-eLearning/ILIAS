<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Meta Data class
* always instantiate this class first to set/get single meta data elements
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMD extends ilMDBase
{
    /*
     * meta elements
     *
     */
    public function &getGeneral()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDGeneral.php';

        if ($id = ilMDGeneral::_getId($this->getRBACId(), $this->getObjId())) {
            $gen = new ilMDGeneral();
            $gen->setMetaId($id);

            return $gen;
        }
        return false;
    }
    public function &addGeneral()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDGeneral.php';

        $gen = new ilMDGeneral($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $gen;
    }


    public function &getLifecycle()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDLifecycle.php';
        
        if ($id = ilMDLifecycle::_getId($this->getRBACId(), $this->getObjId())) {
            $lif = new ilMDLifecycle();
            $lif->setMetaId($id);

            return $lif;
        }
        return false;
    }
    public function &addLifecycle()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDLifecycle.php';

        $lif = new ilMDLifecycle($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $lif;
    }

    public function &getMetaMetadata()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDMetaMetadata.php';

        if ($id = ilMDMetaMetadata::_getId($this->getRBACId(), $this->getObjId())) {
            $met = new ilMDMetaMetadata();
            $met->setMetaId($id);
            
            return $met;
        }
        return false;
    }
    public function &addMetaMetadata()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDMetaMetadata.php';

        $met = new ilMDMetaMetadata($this->getRBACId(), $this->getObjId(), $this->getObjType());
        
        return $met;
    }

    public function &getTechnical()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDTechnical.php';

        if ($id = ilMDTechnical::_getId($this->getRBACId(), $this->getObjId())) {
            $tec = new ilMDTechnical();
            $tec->setMetaId($id);
            
            return $tec;
        }
        return false;
    }
    public function &addTechnical()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDTechnical.php';

        $tec = new ilMDTechnical($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $tec;
    }

    public function &getEducational()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDEducational.php';

        if ($id = ilMDEducational::_getId($this->getRBACId(), $this->getObjId())) {
            $edu = new ilMDEducational();
            $edu->setMetaId($id);
            
            return $edu;
        }
        return false;
    }
    public function &addEducational()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDEducational.php';

        $edu = new ilMDEducational($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $edu;
    }
    public function &getRights()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDRights.php';

        if ($id = ilMDRights::_getId($this->getRBACId(), $this->getObjId())) {
            $rig = new ilMDRights();
            $rig->setMetaId($id);
            
            return $rig;
        }
        return false;
    }
    public function &addRights()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDRights.php';

        $rig = new ilMDRights($this->getRBACId(), $this->getObjId(), $this->getObjType());
        
        return $rig;
    }

    public function &getRelationIds()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDRelation.php';

        return ilMDRelation::_getIds($this->getRBACId(), $this->getObjId());
    }
    public function &getRelation($a_relation_id)
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDRelation.php';

        if (!$a_relation_id) {
            return false;
        }

        $rel = new ilMDRelation();
        $rel->setMetaId($a_relation_id);
        
        return $rel;
    }
    public function &addRelation()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDRelation.php';

        $rel = new ilMDRelation($this->getRBACId(), $this->getObjId(), $this->getObjType());
        
        return $rel;
    }


    public function &getAnnotationIds()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDAnnotation.php';

        return ilMDAnnotation::_getIds($this->getRBACId(), $this->getObjId());
    }
    public function &getAnnotation($a_annotation_id)
    {
        if (!$a_annotation_id) {
            return false;
        }
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDAnnotation.php';

        $ann = new ilMDAnnotation();
        $ann->setMetaId($a_annotation_id);

        return $ann;
    }
    public function &addAnnotation()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDAnnotation.php';
        
        $ann = new ilMDAnnotation($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $ann;
    }

    public function &getClassificationIds()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDClassification.php';

        return ilMDClassification::_getIds($this->getRBACId(), $this->getObjId());
    }
    public function &getClassification($a_classification_id)
    {
        if (!$a_classification_id) {
            return false;
        }

        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDClassification.php';

        $cla = new ilMDClassification();
        $cla->setMetaId($a_classification_id);

        return $cla;
    }
    public function &addClassification()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDClassification.php';

        $cla = new ilMDClassification($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $cla;
    }

    /*
     * XML Export of all meta data
     * @param object (xml writer) see class.ilMD2XML.php
     *
     */
    public function toXML(&$writer)
    {
        $writer->xmlStartTag('MetaData');

        // General
        if (is_object($gen = &$this->getGeneral())) {
            $gen->setExportMode($this->getExportMode());
            $gen->toXML($writer);
        } else {
            // Defaults
            include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDGeneral.php';
            $gen = new ilMDGeneral($this->getRBACId(), $this->getObjId(), $this->getObjType());	// added type, alex, 31 Oct 2007
            $gen->setExportMode($this->getExportMode());
            $gen->toXML($writer);
        }
            

        // Lifecycle
        if (is_object($lif = &$this->getLifecycle())) {
            $lif->toXML($writer);
        }

        // Meta-Metadata
        if (is_object($met = &$this->getMetaMetadata())) {
            $met->toXML($writer);
        }

        // Technical
        if (is_object($tec = &$this->getTechnical())) {
            $tec->toXML($writer);
        }

        // Educational
        if (is_object($edu = &$this->getEducational())) {
            $edu->toXML($writer);
        }

        // Rights
        if (is_object($rig = &$this->getRights())) {
            $rig->toXML($writer);
        }

        // Relations
        foreach ($this->getRelationIds() as $id) {
            $rel = &$this->getRelation($id);
            $rel->toXML($writer);
        }

        // Annotations
        foreach ($this->getAnnotationIds() as $id) {
            $ann = &$this->getAnnotation($id);
            $ann->toXML($writer);
        }
        
        // Classification
        foreach ($this->getClassificationIds() as $id) {
            $cla = &$this->getClassification($id);
            $cla->toXML($writer);
        }
        
        $writer->xmlEndTag('MetaData');
    }

    /*
     * Clone all meta data of an object
     * @param int rbac_id obj_id of rbac object
     * @param int obj_id obj_id of meta object
     * @param string type of meta object
     * @return object new cloned md object
     *
     */
    public function &cloneMD($a_rbac_id, $a_obj_id, $a_obj_type)
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMD2XML.php';

        // this method makes an xml export of the original meta data set
        // and uses this xml string to clone the object
        $md2xml = new ilMD2XML($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $md2xml->startExport();
        
        // Create copier instance. For pg objects one could instantiate a ilMDXMLPageCopier class
        switch ($a_obj_type) {
            default:
                include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDXMLCopier.php';
                $mdxmlcopier = new ilMDXMLCopier($md2xml->getXML(), $a_rbac_id, $a_obj_id, $a_obj_type);
                break;
        }
        $mdxmlcopier->startParsing();

        return $mdxmlcopier->getMDObject();
    }

    public function deleteAll()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $tables = array('il_meta_annotation',
                        'il_meta_classification',
                        'il_meta_contribute',
                        'il_meta_description',
                        'il_meta_educational',
                        'il_meta_entity',
                        'il_meta_format',
                        'il_meta_general',
                        'il_meta_identifier',
                        'il_meta_identifier_',
                        'il_meta_keyword',
                        'il_meta_language',
                        'il_meta_lifecycle',
                        'il_meta_location',
                        'il_meta_meta_data',
                        'il_meta_relation',
                        'il_meta_requirement',
                        'il_meta_rights',
                        'il_meta_taxon',
                        'il_meta_taxon_path',
                        'il_meta_technical',
                        'il_meta_tar');

        foreach ($tables as $table) {
            $query = "DELETE FROM " . $table . " " .
                "WHERE rbac_id = " . $ilDB->quote($this->getRBACId()) . " " .
                "AND obj_id = " . $ilDB->quote($this->getObjId());

            $this->db->query($query);
        }
        
        return true;
    }

    public static function _deleteAllByType($a_type)
    {
        global $ilDB;

        $tables = array('il_meta_annotation',
            'il_meta_classification',
            'il_meta_contribute',
            'il_meta_description',
            'il_meta_educational',
            'il_meta_entity',
            'il_meta_format',
            'il_meta_general',
            'il_meta_identifier',
            'il_meta_identifier_',
            'il_meta_keyword',
            'il_meta_language',
            'il_meta_lifecycle',
            'il_meta_location',
            'il_meta_meta_data',
            'il_meta_relation',
            'il_meta_requirement',
            'il_meta_rights',
            'il_meta_taxon',
            'il_meta_taxon_path',
            'il_meta_technical',
            'il_meta_tar');

        foreach ($tables as $table) {
            $query = "DELETE FROM " . $table . " " .
                "WHERE obj_type = " . $ilDB->quote($a_type, 'text') . " ";
            $res = $ilDB->query($query);
        }

        return true;
    }
}
