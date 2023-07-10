<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";

/**
 * XML writer class
 * Class to simplify manual writing of xml documents.
 * It only supports writing xml sequentially, because the xml document
 * is saved in a string with no additional structure information.
 * The author is responsible for well-formedness and validity
 * of the xml document.
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjectXMLWriter extends ilXmlWriter
{
    public const MODE_SEARCH_RESULT = 1;

    public const TIMING_DEACTIVATED = 0;
    public const TIMING_TEMPORARILY_AVAILABLE = 1;
    public const TIMING_PRESETTING = 2;

    public const TIMING_VISIBILITY_OFF = 0;
    public const TIMING_VISIBILITY_ON = 1;

    private int $mode = 0;
    private ?ilLuceneHighlighterResultParser $highlighter = null;

    protected string $xml;
    protected bool $enable_operations = false;
    protected bool $enable_references = true;
    protected array $objects = array();
    protected int $user_id = 0;
    protected bool $check_permission = false;

    public function __construct()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        parent::__construct();
        $this->user_id = $ilUser->getId();
    }

    public function setMode(int $a_mode): void
    {
        $this->mode = $a_mode;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function setHighlighter(ilLuceneHighlighterResultParser $a_highlighter): void
    {
        $this->highlighter = $a_highlighter;
    }

    public function getHighlighter(): ?ilLuceneHighlighterResultParser
    {
        return $this->highlighter;
    }

    public function enablePermissionCheck(bool $a_status): void
    {
        $this->check_permission = $a_status;
    }

    public function isPermissionCheckEnabled(): bool
    {
        return $this->check_permission;
    }

    public function setUserId(int $a_id): void
    {
        $this->user_id = $a_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function enableOperations(bool $a_status): void
    {
        $this->enable_operations = $a_status;
    }

    public function enabledOperations(): bool
    {
        return $this->enable_operations;
    }

    public function enableReferences(bool $a_stat): void
    {
        $this->enable_references = $a_stat;
    }

    public function enabledReferences(): bool
    {
        return $this->enable_references;
    }

    public function setObjects(array $objects): void
    {
        $this->objects = $objects;
    }

    public function getObjects(): array
    {
        return $this->objects;
    }

    public function start(): bool
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $objDefinition = $DIC['objDefinition'];

        $this->buildHeader();
        foreach ($this->getObjects() as $object) {
            if (method_exists($object, 'getType') &&
                $this->isPermissionCheckEnabled() &&
                $objDefinition->isRBACObject($object->getType()) &&
                !$ilAccess->checkAccessOfUser(
                    $this->getUserId(),
                    'read',
                    '',
                    $object->getRefId()
                )) {
                continue;
            }
            $this->appendObject($object);
        }
        $this->buildFooter();
        return true;
    }

    public function getXML(): string
    {
        return $this->xmlDumpMem(false);
    }

    private function appendObject(ilObject $object): void
    {
        global $DIC;

        $tree = $DIC['tree'];
        $rbacreview = $DIC['rbacreview'];

        /** @var ilObjectDefinition */
        $objectDefinition = $DIC['objDefinition'];

        $id = $object->getId();
        if ($object->getType() === "role" && $rbacreview->isRoleDeleted($id)) {
            return;
        }

        $attrs = [
            'type' => $object->getType(),
            'obj_id' => $id
        ];

        if ($objectDefinition->supportsOfflineHandling($object->getType())) {
            $attrs['offline'] = (int) $object->getOfflineStatus();
        }

        $this->xmlStartTag('Object', $attrs);
        $this->xmlElement('Title', null, $object->getTitle());
        $this->xmlElement('Description', null, $object->getDescription());
        $this->xmlElement('Owner', null, $object->getOwner());
        $this->xmlElement('CreateDate', null, $object->getCreateDate());
        $this->xmlElement('LastUpdate', null, $object->getLastUpdateDate());
        $this->xmlElement('ImportId', null, $object->getImportId());

        $this->appendObjectProperties($object);

        if ($this->enabledReferences()) {
            $refs = ilObject::_getAllReferences($object->getId());
        } else {
            $refs = [$object->getRefId()];
        }

        foreach ($refs as $ref_id) {
            if (!$tree->isInTree($ref_id)) {
                continue;
            }

            $attr = array(
                'ref_id' => $ref_id,
                'parent_id' => $tree->getParentId($ref_id)
            );
            $attr['accessInfo'] = $this->getAccessInfo($object, $ref_id);
            $this->xmlStartTag('References', $attr);
            $this->appendTimeTargets($ref_id);
            $this->appendOperations($ref_id, $object->getType());
            $this->appendPath($ref_id);
            $this->xmlEndTag('References');
        }
        $this->xmlEndTag('Object');
    }

    /**
     * Append time target settings for items inside of courses
     * @param int $ref_id Reference id of object
     * @return void
     */
    private function appendTimeTargets(int $a_ref_id): void
    {
        global $DIC;

        $tree = $DIC['tree'];

        if (!$tree->checkForParentType($a_ref_id, 'crs')) {
            return;
        }
        include_once('./Services/Object/classes/class.ilObjectActivation.php');
        $time_targets = ilObjectActivation::getItem($a_ref_id);

        switch ($time_targets['timing_type']) {
            case ilObjectActivation::TIMINGS_ACTIVATION:
                $type = self::TIMING_TEMPORARILY_AVAILABLE;
                break;
            case ilObjectActivation::TIMINGS_PRESETTING:
                $type = self::TIMING_PRESETTING;
                break;
            case ilObjectActivation::TIMINGS_DEACTIVATED:
            default:
                $type = self::TIMING_DEACTIVATED;
                break;
        }

        $this->xmlStartTag('TimeTarget', array('type' => $type));

        $vis = (int) $time_targets['visible'] === 0 ? self::TIMING_VISIBILITY_OFF : self::TIMING_VISIBILITY_ON;
        $this->xmlElement(
            'Timing',
            array('starting_time' => $time_targets['timing_start'],
                  'ending_time' => $time_targets['timing_end'],
                  'visibility' => $vis
            )
        );

        $this->xmlElement(
            'Suggestion',
            array(
                'starting_time' => $time_targets['suggestion_start'],
                'ending_time' => $time_targets['suggestion_end'],
                'changeable' => $time_targets['changeable']
            )
        );
        $this->xmlEndTag('TimeTarget');
    }

    private function appendObjectProperties(ilObject $obj): void
    {
        switch ($obj->getType()) {
            case 'file':
                if (!$obj instanceof ilObjFile) {
                    break;
                }
                $size = $obj->getFileSize();
                $extension = $obj->getFileExtension();
                $this->xmlStartTag('Properties');
                $this->xmlElement("Property", array('name' => 'fileSize'), (string) $size);
                $this->xmlElement("Property", array('name' => 'fileExtension'), (string) $extension);
                $this->xmlElement(
                    'Property',
                    array('name' => 'fileVersion'),
                    (string) $obj->getVersion()
                );
                $this->xmlEndTag('Properties');
                break;
        }
    }

    private function appendOperations(int $a_ref_id, string $a_type): void
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

                    if (count($operation) && $ilAccess->checkAccessOfUser(
                        $this->getUserId(),
                        $operation['operation'],
                        'view',
                        $a_ref_id
                    )) {
                        $this->xmlElement('Operation', null, $operation['operation']);
                    }
                }
            }

            $objects = $objDefinition->getCreatableSubObjects($a_type);
            $ops_ids = ilRbacReview::lookupCreateOperationIds(array_keys($objects));
            $creation_operations = array();
            foreach ($objects as $type => $info) {
                $ops_id = $ops_ids[$type];

                if (!$ops_id) {
                    continue;
                }

                $operation = $rbacreview->getOperation($ops_id);

                if (count($operation) && $ilAccess->checkAccessOfUser(
                    $this->getUserId(),
                    $operation['operation'],
                    'view',
                    $a_ref_id
                )) {
                    $this->xmlElement('Operation', null, $operation['operation']);
                }
            }
        }
    }

    private function appendPath(int $refid): void
    {
        self::appendPathToObject($this, $refid);
    }

    private function buildHeader(): void
    {
        $this->xmlSetDtdDef("<!DOCTYPE Objects PUBLIC \"-//ILIAS//DTD ILIAS Repositoryobjects//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_object_4_0.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS objects");
        $this->xmlHeader();
        $this->xmlStartTag("Objects");
    }

    private function buildFooter(): void
    {
        $this->xmlEndTag('Objects');
    }

    private function getAccessInfo(ilObject $object, int $ref_id): string
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];

        include_once 'Services/AccessControl/classes/class.ilAccess.php';

        $ilAccess->checkAccessOfUser($this->getUserId(), 'read', 'view', $ref_id, $object->getType(), $object->getId());

        if (!$info = $ilAccess->getInfo()) {
            return 'granted';
        }

        return $info[0]['type'];
    }

    public static function appendPathToObject(ilXmlWriter $writer, int $refid): void
    {
        global $DIC;

        $tree = $DIC['tree'];
        $lng = $DIC['lng'];
        $items = $tree->getPathFull($refid);
        $writer->xmlStartTag("Path");
        foreach ($items as $item) {
            if ((int) $item["ref_id"] === $refid) {
                continue;
            }
            if ($item["type"] === "root") {
                $item["title"] = $lng->txt("repository");
            }
            $writer->xmlElement("Element", array("ref_id" => $item["ref_id"], "type" => $item["type"]), $item["title"]);
        }
        $writer->xmlEndTag("Path");
    }
}
