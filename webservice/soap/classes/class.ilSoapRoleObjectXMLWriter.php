<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * XML writer class
 * Class to simplify manual writing of xml documents.
 * It only supports writing xml sequentially, because the xml document
 * is saved in a string with no additional structure information.
 * The author is responsible for well-formedness and validity
 * of the xml document.
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.ilObjectXMLWriter.php,v 1.3 2005/11/04 12:50:24 smeyer Exp $
 */
class ilSoapRoleObjectXMLWriter extends ilXmlWriter
{
    public string $xml = '';
    private array $roles = [];
    private string $role_type = '';

    public function __construct()
    {
        global $DIC;

        $ilUser = $DIC->user();

        parent::__construct();
    }

    public function setObjects(array $roles) : void
    {
        $this->roles = $roles;
    }

    public function setType(string $type) : void
    {
        $this->role_type = $type;
    }

    public function start() : bool
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        if ($this->roles === []) {
            return false;
        }

        $this->buildHeader();

        include_once './Services/AccessControl/classes/class.ilObjRole.php';
        include_once './webservice/soap/classes/class.ilObjectXMLWriter.php';

        foreach ($this->roles as $role) {
            // if role type is not empty and does not match, then continue;
            if (!empty($this->role_type) && strcasecmp($this->role_type, $role["role_type"]) !== 0) {
                continue;
            }
            if ($rbacreview->isRoleDeleted($role["obj_id"])) {
                continue;
            }

            $attrs = [
                'role_type' => ucwords($role["role_type"]),
                'id' => "il_" . IL_INST_ID . "_role_" . $role["obj_id"]
            ];

            $this->xmlStartTag("Role", $attrs);
            $this->xmlElement('Title', null, $role["title"]);
            $this->xmlElement('Description', null, $role["description"]);
            $this->xmlElement('Translation', null, ilObjRole::_getTranslation($role["title"]));

            if ($ref_id = ilUtil::__extractRefId($role["title"])) {
                $ownerObj = ilObjectFactory::getInstanceByRefId($ref_id, false);

                if (is_object($ownerObj)) {
                    $attrs = [
                        "obj_id" => implode(
                            '_',
                            [
                                "il",
                                IL_INST_ID,
                                $ownerObj->getType(),
                                $ownerObj->getId()
                            ]
                        ),
                        "ref_id" => $ownerObj->getRefId(),
                        "type" => $ownerObj->getType()
                    ];
                    $this->xmlStartTag('AssignedObject', $attrs);
                    $this->xmlElement('Title', null, $ownerObj->getTitle());
                    $this->xmlElement('Description', null, $ownerObj->getDescription());
                    ilObjectXMLWriter::appendPathToObject($this, $ref_id);
                    $this->xmlEndTag('AssignedObject');
                }
            }
            $this->xmlEndTag("Role");
        }
        $this->buildFooter();
        return true;
    }

    public function getXML() : string
    {
        return $this->xmlDumpMem(false);
    }

    private function buildHeader() : void
    {
        $this->xmlSetDtdDef("<!DOCTYPE Roles PUBLIC \"-//ILIAS//DTD ILIAS Roles//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_role_object_3_10.dtd\">");
        $this->xmlSetGenCmt("Roles information of ilias system");
        $this->xmlHeader();
        $this->xmlStartTag('Roles');
    }

    private function buildFooter() : void
    {
        $this->xmlEndTag('Roles');
    }
}
