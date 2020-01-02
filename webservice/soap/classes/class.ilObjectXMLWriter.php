<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";

/**
* XML writer class
*
* Class to simplify manual writing of xml documents.
* It only supports writing xml sequentially, because the xml document
* is saved in a string with no additional structure information.
* The author is responsible for well-formedness and validity
* of the xml document.
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*/
class ilObjectXMLWriter extends ilXmlWriter
{
    // begin-patch fm
    const MODE_SEARCH_RESULT = 1;

    private $mode = 0;
    private $highlighter = null;
    // end-patch fm


    const TIMING_DEACTIVATED = 0;
    const TIMING_TEMPORARILY_AVAILABLE = 1;
    const TIMING_PRESETTING = 2;
    
    const TIMING_VISIBILITY_OFF = 0;
    const TIMING_VISIBILITY_ON = 1;
    
    
    public $ilias;

    public $xml;
    public $enable_operations = false;
    // begin-patch filemanager
    private $enable_references = true;
    // end-patch filemanager
    public $objects = array();
    public $user_id = 0;
    
    protected $check_permission = false;

    /**
    * constructor
    * @param	string	xml version
    * @param	string	output encoding
    * @param	string	input encoding
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $ilUser = $DIC['ilUser'];

        parent::__construct();

        $this->ilias =&$ilias;
        $this->user_id = $ilUser->getId();
    }

    // begin-patch fm
    public function setMode($a_mode)
    {
        $this->mode = $a_mode;
    }

    public function setHighlighter($a_highlighter)
    {
        $this->highlighter = $a_highlighter;
    }
    // end-patch fm

    
    public function enablePermissionCheck($a_status)
    {
        $this->check_permission = $a_status;
    }
    
    public function isPermissionCheckEnabled()
    {
        return $this->check_permission;
    }

    public function setUserId($a_id)
    {
        $this->user_id = $a_id;
    }
    public function getUserId()
    {
        return $this->user_id;
    }

    public function enableOperations($a_status)
    {
        $this->enable_operations = $a_status;

        return true;
    }

    public function enabledOperations()
    {
        return $this->enable_operations;
    }

    // begin-patch filemanager
    public function enableReferences($a_stat)
    {
        $this->enable_references = $a_stat;
    }

    public function enabledReferences()
    {
        return $this->enable_references;
    }
    // end-patch filemanager


    public function setObjects($objects)
    {
        $this->objects = $objects;
    }

    public function __getObjects()
    {
        return $this->objects ? $this->objects : array();
    }

    public function start()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $objDefinition = $DIC['objDefinition'];
        
        $this->__buildHeader();

        foreach ($this->__getObjects() as $object) {
            if (method_exists($object, 'getType') and $objDefinition->isRBACObject($object->getType())) {
                if ($this->isPermissionCheckEnabled() and !$ilAccess->checkAccessOfUser($this->getUserId(), 'read', '', $object->getRefId())) {
                    continue;
                }
            }
            $this->__appendObject($object);
        }
        $this->__buildFooter();

        return true;
    }

    public function getXML()
    {
        return $this->xmlDumpMem(false);
    }


    // PRIVATE
    public function __appendObject(ilObject $object)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $rbacreview = $DIC['rbacreview'];

        /**
         * @var ilObjectDefinition
         */
        $objectDefinition = $DIC['objDefinition'];

        $id = $object->getId();
        if ($object->getType() == "role" && $rbacreview->isRoleDeleted($id)) {
            return;
        }

        $attrs = array(
            'type' => $object->getType(),
            'obj_id' => $id
        );

        if ($objectDefinition->supportsOfflineHandling($object->getType())) {
            $attrs['offline'] = (int) $object->getOfflineStatus();
        }

        $this->xmlStartTag('Object', $attrs);
        //$this->xmlElement('Title',null,$object->getTitle());
        //$this->xmlElement('Description',null,$object->getDescription());

        // begin-patch fm
        if ($this->mode == self::MODE_SEARCH_RESULT) {
            $title = $object->getTitle();
            if ($this->highlighter->getTitle($object->getId(), 0)) {
                $title = $this->highlighter->getTitle($object->getId(), 0);
            }
            $description = $object->getDescription();
            if ($this->highlighter->getDescription($object->getId(), 0)) {
                $description = $this->highlighter->getDescription($object->getId(), 0);
            }

            // Currently disabled
            #$this->xmlElement('Title', null, $title);
            #$this->xmlElement('Description',null,$description);
            #$this->xmlElement('SearchResultContent', null, $this->highlighter->getContent($object->getId(),0));

            $this->xmlElement('Title', null, $object->getTitle());
            $this->xmlElement('Description', null, $object->getDescription());
        } else {
            $this->xmlElement('Title', null, $object->getTitle());
            $this->xmlElement('Description', null, $object->getDescription());
        }
        // end-patch fm

        $this->xmlElement('Owner', null, $object->getOwner());
        $this->xmlElement('CreateDate', null, $object->getCreateDate());
        $this->xmlElement('LastUpdate', null, $object->getLastUpdateDate());
        $this->xmlElement('ImportId', null, $object->getImportId());

        $this->__appendObjectProperties($object);

        // begin-patch filemanager
        if ($this->enabledReferences()) {
            $refs = ilObject::_getAllReferences($object->getId());
        } else {
            $refs = array($object->getRefId());
        }

        foreach ($refs as $ref_id) {
            // end-patch filemanager
            if (!$tree->isInTree($ref_id)) {
                continue;
            }
            
            $attr = array(
                'ref_id' => $ref_id,
                'parent_id'=> $tree->getParentId(intval($ref_id))
            );
            $attr['accessInfo'] = $this->__getAccessInfo($object, $ref_id);
            $this->xmlStartTag('References', $attr);
            $this->__appendTimeTargets($ref_id);
            $this->__appendOperations($ref_id, $object->getType());
            $this->__appendPath($ref_id);
            $this->xmlEndTag('References');
        }
        $this->xmlEndTag('Object');
    }
    
    /**
     * Append time target settings for items inside of courses
     * @param int $ref_id Reference id of object
     * @return void
     */
    public function __appendTimeTargets($a_ref_id)
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        if (!$tree->checkForParentType($a_ref_id, 'crs')) {
            return;
        }
        include_once('./Services/Object/classes/class.ilObjectActivation.php');
        $time_targets = ilObjectActivation::getItem($a_ref_id);
        
        switch ($time_targets['timing_type']) {
            case ilObjectActivation::TIMINGS_DEACTIVATED:
                $type = self::TIMING_DEACTIVATED;
                break;
            case ilObjectActivation::TIMINGS_ACTIVATION:
                $type = self::TIMING_TEMPORARILY_AVAILABLE;
                break;
            case ilObjectActivation::TIMINGS_PRESETTING:
                $type = self::TIMING_PRESETTING;
                break;
            default:
                $type = self::TIMING_DEACTIVATED;
                break;
        }
        
        $this->xmlStartTag('TimeTarget', array('type' => $type));
        
        #		if($type == self::TIMING_TEMPORARILY_AVAILABLE)
        {
            $vis = $time_targets['visible'] == 0 ? self::TIMING_VISIBILITY_OFF : self::TIMING_VISIBILITY_ON;
            $this->xmlElement(
                'Timing',
                array('starting_time' => $time_targets['timing_start'],
                    'ending_time' => $time_targets['timing_end'],
                    'visibility' => $vis)
            );
                
        }
        #		if($type == self::TIMING_PRESETTING)
        {
                $this->xmlElement(
                    'Suggestion',
                    array(
                    'starting_time' => $time_targets['suggestion_start'],
                        'ending_time' => $time_targets['suggestion_end'],
                    'changeable' => $time_targets['changeable'])
                );
        }
        $this->xmlEndTag('TimeTarget');
        return;
    }


    /**
     * Append object properties
     * @param ilObject $obj
     */
    public function __appendObjectProperties(ilObject $obj)
    {
        switch ($obj->getType()) {

            case 'file':
                include_once './Modules/File/classes/class.ilObjFileAccess.php';
                $size = ilObjFileAccess::_lookupFileSize($obj->getId());
                $extension = ilObjFileAccess::_lookupSuffix($obj->getId());
                $this->xmlStartTag('Properties');
                $this->xmlElement("Property", array('name' => 'fileSize'), (int) $size);
                $this->xmlElement("Property", array('name' => 'fileExtension'), (string) $extension);
                // begin-patch fm
                $this->xmlElement('Property', array('name' => 'fileVersion'), (string) ilObjFileAccess::_lookupVersion($obj->getId()));
                // end-patch fm
                $this->xmlEndTag('Properties');
                break;
        }
    }
    

    public function __appendOperations($a_ref_id, $a_type)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $rbacreview = $DIC['rbacreview'];
        $objDefinition = $DIC['objDefinition'];

        if ($this->enabledOperations()) {
            $ops = $rbacreview->getOperationsOnTypeString($a_type);
            if (is_array($ops)) {
                foreach ($ops as $ops_id) {
                    $operation = $rbacreview->getOperation($ops_id);
                    
                    if (count($operation) && $ilAccess->checkAccessOfUser($this->getUserId(), $operation['operation'], 'view', $a_ref_id)) {
                        $this->xmlElement('Operation', null, $operation['operation']);
                    }
                }
            }

            // Create operations
            // Get creatable objects
            $objects = $objDefinition->getCreatableSubObjects($a_type);
            $ops_ids = ilRbacReview::lookupCreateOperationIds(array_keys($objects));
            $creation_operations = array();
            foreach ($objects as $type => $info) {
                $ops_id = $ops_ids[$type];

                if (!$ops_id) {
                    continue;
                }

                $operation = $rbacreview->getOperation($ops_id);

                if (count($operation) && $ilAccess->checkAccessOfUser($this->getUserId(), $operation['operation'], 'view', $a_ref_id)) {
                    $this->xmlElement('Operation', null, $operation['operation']);
                }
            }
        }
        return true;
    }


    public function __appendPath($refid)
    {
        ilObjectXMLWriter::appendPathToObject($this, $refid);
    }
    
    public function __buildHeader()
    {
        $this->xmlSetDtdDef("<!DOCTYPE Objects PUBLIC \"-//ILIAS//DTD ILIAS Repositoryobjects//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_object_4_0.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS objects");
        $this->xmlHeader();
        $this->xmlStartTag("Objects");
        return true;
    }

    public function __buildFooter()
    {
        $this->xmlEndTag('Objects');
    }

    public function __getAccessInfo(&$object, $ref_id)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];

        include_once 'Services/AccessControl/classes/class.ilAccess.php';

        $ilAccess->checkAccessOfUser($this->getUserId(), 'read', 'view', $ref_id, $object->getType(), $object->getId());

        if (!$info = $ilAccess->getInfo()) {
            return 'granted';
        } else {
            return $info[0]['type'];
        }
    }

    
    public static function appendPathToObject($writer, $refid)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $lng = $DIC['lng'];
        $items = $tree->getPathFull($refid);
        $writer->xmlStartTag("Path");
        foreach ($items as $item) {
            if ($item["ref_id"] == $refid) {
                continue;
            }
            if ($item["type"] == "root") {
                $item["title"] = $lng->txt("repository");
            }
            $writer->xmlElement("Element", array("ref_id" => $item["ref_id"], "type" => $item["type"]), $item["title"]);
        }
        $writer->xmlEndTag("Path");
    }
}
