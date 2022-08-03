<?php declare(strict_types=1);

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
 * Meta Data class
 * always instantiate this class first to set/get single meta data elements
 * @package ilias-core
 * @version $Id$
 */
class ilMD extends ilMDBase
{
    public function read() : bool
    {
        return true;
    }
    
    public function getGeneral() : ?ilMDGeneral
    {
        if ($id = ilMDGeneral::_getId($this->getRBACId(), $this->getObjId())) {
            $gen = new ilMDGeneral();
            $gen->setMetaId($id);
            return $gen;
        }
        return null;
    }

    public function addGeneral() : ?ilMDGeneral
    {
        $gen = new ilMDGeneral($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $gen;
    }

    public function getLifecycle() : ?ilMDLifecycle
    {
        if ($id = ilMDLifecycle::_getId($this->getRBACId(), $this->getObjId())) {
            $lif = new ilMDLifecycle();
            $lif->setMetaId($id);

            return $lif;
        }
        return null;
    }

    public function addLifecycle() : ilMDLifecycle
    {
        $lif = new ilMDLifecycle($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $lif;
    }

    public function getMetaMetadata() : ?ilMDMetaMetadata
    {
        if ($id = ilMDMetaMetadata::_getId($this->getRBACId(), $this->getObjId())) {
            $met = new ilMDMetaMetadata();
            $met->setMetaId($id);

            return $met;
        }
        return null;
    }

    public function addMetaMetadata() : ilMDMetaMetadata
    {
        $met = new ilMDMetaMetadata($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $met;
    }

    public function getTechnical() : ?ilMDTechnical
    {
        if ($id = ilMDTechnical::_getId($this->getRBACId(), $this->getObjId())) {
            $tec = new ilMDTechnical();
            $tec->setMetaId($id);

            return $tec;
        }
        return null;
    }

    public function addTechnical() : ilMDTechnical
    {
        $tec = new ilMDTechnical($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $tec;
    }

    public function getEducational() : ?ilMDEducational
    {
        if ($id = ilMDEducational::_getId($this->getRBACId(), $this->getObjId())) {
            $edu = new ilMDEducational();
            $edu->setMetaId($id);

            return $edu;
        }
        return null;
    }

    public function addEducational() : ilMDEducational
    {
        $edu = new ilMDEducational($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $edu;
    }

    public function getRights() : ?ilMDRights
    {
        if ($id = ilMDRights::_getId($this->getRBACId(), $this->getObjId())) {
            $rig = new ilMDRights();
            $rig->setMetaId($id);

            return $rig;
        }
        return null;
    }

    public function addRights() : ilMDRights
    {
        $rig = new ilMDRights($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $rig;
    }

    /**
     * @return int[]
     */
    public function getRelationIds() : array
    {
        return ilMDRelation::_getIds($this->getRBACId(), $this->getObjId());
    }

    public function getRelation(int $a_relation_id) : ?ilMDRelation
    {
        if (!$a_relation_id) {
            return null;
        }

        $rel = new ilMDRelation();
        $rel->setMetaId($a_relation_id);

        return $rel;
    }

    public function addRelation() : ilMDRelation
    {
        $rel = new ilMDRelation($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $rel;
    }

    /**
     * @return int[]
     */
    public function getAnnotationIds() : array
    {
        return ilMDAnnotation::_getIds($this->getRBACId(), $this->getObjId());
    }

    public function getAnnotation(int $a_annotation_id) : ?ilMDAnnotation
    {
        if (!$a_annotation_id) {
            return null;
        }

        $ann = new ilMDAnnotation();
        $ann->setMetaId($a_annotation_id);

        return $ann;
    }

    public function addAnnotation() : ilMDAnnotation
    {
        $ann = new ilMDAnnotation($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $ann;
    }

    /**
     * @return int[]
     */
    public function getClassificationIds() : array
    {
        return ilMDClassification::_getIds($this->getRBACId(), $this->getObjId());
    }

    public function getClassification(int $a_classification_id) : ?ilMDClassification
    {
        if (!$a_classification_id) {
            return null;
        }

        $cla = new ilMDClassification();
        $cla->setMetaId($a_classification_id);

        return $cla;
    }

    public function addClassification() : ilMDClassification
    {
        $cla = new ilMDClassification($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $cla;
    }

    public function toXML(ilXmlWriter $writer) : void
    {
        $writer->xmlStartTag('MetaData');

        // General
        if (is_object($gen = $this->getGeneral())) {
            $gen->setExportMode($this->getExportMode());
            $gen->toXML($writer);
        } else {
            // Defaults

            $gen = new ilMDGeneral(
                $this->getRBACId(),
                $this->getObjId(),
                $this->getObjType()
            ); // added type, alex, 31 Oct 2007
            $gen->setExportMode($this->getExportMode());
            $gen->toXML($writer);
        }

        // Lifecycle
        if (is_object($lif = $this->getLifecycle())) {
            $lif->toXML($writer);
        }

        // Meta-Metadata
        if (is_object($met = $this->getMetaMetadata())) {
            $met->toXML($writer);
        }

        // Technical
        if (is_object($tec = $this->getTechnical())) {
            $tec->toXML($writer);
        }

        // Educational
        if (is_object($edu = $this->getEducational())) {
            $edu->toXML($writer);
        }

        // Rights
        if (is_object($rig = $this->getRights())) {
            $rig->toXML($writer);
        }

        // Relations
        foreach ($this->getRelationIds() as $id) {
            $rel = $this->getRelation($id);
            $rel->toXML($writer);
        }

        // Annotations
        foreach ($this->getAnnotationIds() as $id) {
            $ann = $this->getAnnotation($id);
            $ann->toXML($writer);
        }

        // Classification
        foreach ($this->getClassificationIds() as $id) {
            $cla = $this->getClassification($id);
            $cla->toXML($writer);
        }

        $writer->xmlEndTag('MetaData');
    }

    public function cloneMD(int $a_rbac_id, int $a_obj_id, string $a_obj_type) : ilMD
    {
        // this method makes an xml export of the original meta data set
        // and uses this xml string to clone the object
        $md2xml = new ilMD2XML($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $md2xml->startExport();

        // Create copier instance. For pg objects one could instantiate a ilMDXMLPageCopier class
        switch ($a_obj_type) {
            default:
                // delete existing entries from creations process
                $clone = new ilMD($a_rbac_id, $a_obj_id, $a_obj_type);
                $clone->deleteAll();

                $mdxmlcopier = new ilMDXMLCopier($md2xml->getXML(), $a_rbac_id, $a_obj_id, $a_obj_type);

                // rewrite autogenerated entry
                $identifier = new ilMDIdentifier($a_rbac_id, $a_obj_id, $a_obj_type);
                $identifier->setEntry('il__' . $a_obj_type . '_' . $a_obj_id);
                $identifier->update();
                break;
        }
        $mdxmlcopier->startParsing();

        return $mdxmlcopier->getMDObject();
    }

    public function deleteAll() : bool
    {
        $tables = [
            'il_meta_annotation',
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
            'il_meta_tar'
        ];

        foreach ($tables as $table) {
            $query = "DELETE FROM " . $table . " " .
                "WHERE rbac_id = " . $this->db->quote($this->getRBACId(), ilDBConstants::T_INTEGER) . " " .
                "AND obj_id = " . $this->db->quote($this->getObjId(), ilDBConstants::T_INTEGER);

            $this->db->query($query);
        }

        return true;
    }
}
