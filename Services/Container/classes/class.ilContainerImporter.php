<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
* container xml importer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesFolder
*/
class ilContainerImporter extends ilXmlImporter
{
    /**
     * @var string
     */
    private $structure_xml;

    /**
     * @var ilLogger
     */
    protected $cont_log;

    public function init()
    {
        $this->cont_log = ilLoggerFactory::getLogger('cont');
    }
    
    /**
     * Import XML
     *
     * @inheritdoc
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        include_once './Services/Container/classes/class.ilContainerXmlParser.php';

        $this->structure_xml = $a_xml;
        $this->cont_log->debug('Import xml: ' . $a_xml);
        $this->cont_log->debug('Using id: ' . $a_id);
        
        $parser = new ilContainerXmlParser($a_mapping, trim($a_xml));
        $parser->parse($a_id);
    }

    /**
     * @inheritdoc
     */
    public function finalProcessing($a_mapping)
    {
        $this->handleOfflineStatus($this->structure_xml, $a_mapping);
        // pages
        include_once('./Services/COPage/classes/class.ilPageObject.php');
        $page_map = $a_mapping->getMappingsOfEntity('Services/COPage', 'pg');
        foreach ($page_map as $old_pg_id => $new_pg_id) {
            $parts = explode(':', $old_pg_id);
            $pg_type = $parts[0];
            $old_obj_id = $parts[1];
            $new_pg_id = array_pop(explode(':', $new_pg_id));
            $new_obj_id = $a_mapping->getMapping('Services/Container', 'objs', $old_obj_id);
            // see bug #22718, this missed a check for the pg type
            if (in_array($pg_type, array("crs", "grp", "fold", "cont"))) {
                if ($new_obj_id > 0) {
                    ilPageObject::_writeParentId($pg_type, $new_pg_id, $new_obj_id);
                    $this->cont_log->debug('write parent id, type: ' . $pg_type . ", page id: " . $new_pg_id . ", parent id: " . $new_obj_id);
                }
            }
        }
        
        // style
        include_once('./Services/Style/Content/classes/class.ilObjStyleSheet.php');
        $sty_map = $a_mapping->getMappingsOfEntity('Services/Style', 'sty');
        foreach ($sty_map as $old_sty_id => $new_sty_id) {
            if (is_array(ilContainerXmlParser::$style_map[$old_sty_id])) {
                foreach (ilContainerXmlParser::$style_map[$old_sty_id] as $obj_id) {
                    ilObjStyleSheet::writeStyleUsage($obj_id, $new_sty_id);
                }
            }
        }

        // skills
        $new_crs_obj_id = end($a_mapping->getMappingsOfEntity('Modules/Course', 'crs'));
        $new_crs_ref_id = ilObject::_getAllReferences($new_crs_obj_id);
        $new_crs_ref_id = end($new_crs_ref_id);

        $skl_local_prof_map = $a_mapping->getMappingsOfEntity('Services/Skill', 'skl_local_prof');
        foreach ($skl_local_prof_map as $old_prof_id => $new_prof_id) {
            $prof = new ilSkillProfile($new_prof_id);
            $prof->updateRefIdAfterImport((int) $new_crs_ref_id);
            $prof->addRoleToProfile(ilParticipants::getDefaultMemberRole($new_crs_ref_id));
        }
    }

    /**
     * @param string $xml
     */
    protected function handleOfflineStatus(string $xml, ilImportMapping $mapping)
    {
        libxml_use_internal_errors(true);
        $root = simplexml_load_string($xml);
        if ($root === false) {
            $errors = '';
            foreach (libxml_get_errors() as $err) {
                $errors .= $err->code . '<br/>';
            }
            $this->cont_log->error($xml);
            $this->cont_log->error('Cannot parse xml: ' . $errors);
        }
        foreach ($root->xpath('//Item') as $item) {
            $ref_id = 0;
            $offline = null;
            foreach ($item->attributes() as $name => $value) {
                if ((string) $name == 'Offline') {
                    $offline = $value;
                }
                if ((string) $name == 'RefId') {
                    $ref_id = (string) $value;
                }
            }
            if (is_null($offline)) {
                $this->cont_log->debug('No offline handling for ref_id: ' . $ref_id);
                continue;
            }
            $new_ref_id = $mapping->getMapping('Services/Container', 'refs', $ref_id);
            $obj = ilObjectFactory::getInstanceByRefId($new_ref_id, false);
            if (!$obj instanceof ilObject) {
                $this->cont_log->warning('Cannot create instance for ref_id: ' . $new_ref_id);
                continue;
            }
            if ($obj->supportsOfflineHandling()) {
                if ($this->isRootNode($obj->getRefId(), $mapping)) {
                    $obj->setOfflineStatus(true);
                } else {
                    // use the offline status of the imported XML file and set the offline status for the new container objects accordingly
                    $obj->setOfflineStatus(!((string) $offline === "0"));
                }
                $obj->update();
            }
        }
    }

    /**
     * @param int             $ref_id
     * @param ilImportMapping $mapping
     * @return bool
     */
    protected function isRootNode(int $ref_id, ilImportMapping $mapping) : bool
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $parent_id = $tree->getParentId($ref_id);
        if ($parent_id) {
            return (int) $parent_id === (int) $mapping->getTargetId();
        }
        return false;
    }
}
