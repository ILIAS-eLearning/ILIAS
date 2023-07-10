<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Soap methods for adminstrating web links
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSoapWebLinkAdministration extends ilSoapAdministration
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public function readWebLink(string $sid, int $request_ref_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        if (!$request_ref_id) {
            return $this->raiseError(
                'No ref id given. Aborting!',
                'Client'
            );
        }
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $tree = $DIC['tree'];
        $ilLog = $DIC['ilLog'];

        // get obj_id
        if (!$obj_id = ilObject::_lookupObjectId($request_ref_id)) {
            return $this->raiseError(
                'No weblink found for id: ' . $request_ref_id,
                'Client'
            );
        }

        if (ilObject::_isInTrash($request_ref_id)) {
            return $this->raiseError("Parent with ID $request_ref_id has been deleted.", 'Client');
        }

        // Check access
        $permission_ok = false;
        $write_permission_ok = false;
        $ref_ids = ilObject::_getAllReferences($obj_id);
        foreach ($ref_ids as $ref_id) {
            if ($rbacsystem->checkAccess('edit', $ref_id)) {
                $write_permission_ok = true;
                break;
            }
            if ($rbacsystem->checkAccess('read', $ref_id)) {
                $permission_ok = true;
                break;
            }
        }

        if (!$permission_ok && !$write_permission_ok) {
            return $this->raiseError(
                'No permission to edit the object with id: ' . $request_ref_id,
                'Server'
            );
        }

        try {
            include_once './Modules/WebResource/classes/class.ilWebLinkXmlWriter.php';
            $writer = new ilWebLinkXmlWriter(true);
            $writer->setObjId($obj_id);
            $writer->write();

            return $writer->xmlDumpMem(true);
        } catch (UnexpectedValueException $e) {
            return $this->raiseError($e->getMessage(), 'Client');
        }
    }

    /**
     * @return int|soap_fault|SoapFault|null
     */
    public function createWebLink(string $sid, int $target_id, string $weblink_xml)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $tree = $DIC['tree'];
        $ilLog = $DIC['ilLog'];

        if (!$target_obj = ilObjectFactory::getInstanceByRefId($target_id, false)) {
            return $this->raiseError('No valid target given.', 'Client');
        }

        if (ilObject::_isInTrash($target_id)) {
            return $this->raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_OBJECT_DELETED');
        }

        // Check access
        // TODO: read from object definition
        $allowed_types = array('cat', 'grp', 'crs', 'fold', 'root');
        if (!in_array($target_obj->getType(), $allowed_types)) {
            return $this->raiseError(
                'No valid target type. Target must be reference id of "course, group, root, category or folder"',
                'Client'
            );
        }

        if (!$rbacsystem->checkAccess('create', $target_id, "webr")) {
            return $this->raiseError('No permission to create weblink in target  ' . $target_id . '!', 'Client');
        }

        // create object, put it into the tree and use the parser to update the settings
        include_once './Modules/WebResource/classes/class.ilObjLinkResource.php';
        include_once './Modules/WebResource/classes/class.ilWebLinkXmlParser.php';

        $webl = new ilObjLinkResource();
        $webl->setTitle('XML Import');
        $webl->create(true);
        $webl->createReference();
        $webl->putInTree($target_id);
        $webl->setPermissions($target_id);

        try {
            $parser = new ilWebLinkXmlParser($webl, $weblink_xml);
            $parser->setMode(ilWebLinkXmlParser::MODE_CREATE);
            $parser->start();
        } catch (ilSaxParserException | ilWebLinkXmlParserException $e) {
            return $this->raiseError($e->getMessage(), 'Client');
        }

        // Check if required
        return $webl->getRefId();
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function updateWebLink(string $sid, int $request_ref_id, string $weblink_xml)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $tree = $DIC['tree'];
        $ilLog = $DIC['ilLog'];

        if (ilObject::_isInTrash($request_ref_id)) {
            return $this->raiseError(
                'Cannot perform update since weblink has been deleted.',
                'CLIENT_OBJECT_DELETED'
            );
        }
        // get obj_id
        if (!$obj_id = ilObject::_lookupObjectId($request_ref_id)) {
            return $this->raiseError(
                'No weblink found for id: ' . $request_ref_id,
                'CLIENT_OBJECT_NOT_FOUND'
            );
        }

        // Check access
        $permission_ok = false;
        foreach ($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id) {
            if ($rbacsystem->checkAccess('edit', $ref_id)) {
                $permission_ok = true;
                break;
            }
        }

        if (!$permission_ok) {
            return $this->raiseError(
                'No permission to edit the weblink with id: ' . $request_ref_id,
                'Server'
            );
        }

        $webl = ilObjectFactory::getInstanceByObjId($obj_id, false);
        if (!$webl instanceof ilObjLinkResource) {
            return $this->raiseError(
                'Wrong obj id or type for weblink with id ' . $request_ref_id,
                'Client'
            );
        }

        try {
            include_once './Modules/WebResource/classes/class.ilWebLinkXmlParser.php';
            /** @noinspection PhpParamsInspection */
            $parser = new ilWebLinkXmlParser($webl, $weblink_xml);
            $parser->setMode(ilWebLinkXmlParser::MODE_UPDATE);
            $parser->start();
        } catch (ilSaxParserException | ilWebLinkXmlParserException $e) {
            return $this->raiseError($e->getMessage(), 'Client');
        }

        // Check if required
        return true;
    }
}
