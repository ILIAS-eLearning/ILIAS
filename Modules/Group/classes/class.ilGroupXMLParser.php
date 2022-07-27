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
 * Group Import Parser
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.ilGroupXMLParser.php 15678 2008-01-06 20:40:55Z akill $
 *
 * @extends ilSaxParser
 */
class ilGroupXMLParser extends ilMDSaxParser implements ilSaxSubsetParser
{
    public static int $CREATE = 1;
    public static int $UPDATE = 2;

    private $after_parsing_status = null;

    /**
     * @var ilLogger
     */
    private ilLogger $log;
    private ilObjGroup $group_obj;
    protected ilObjUser $user;
    protected ilRbacReview $rbacreview;
    protected ilSetting $settings;


    /**
     * @var bool
     */
    private bool $lom_parsing_active = false;

    protected ?ilSaxController $sax_controller;

    protected ?ilAdvancedMDValueParser $advanced_md_value_parser = null;

    private ?ilGroupParticipants $participants = null;
    private string $current_container_setting = '';
    private ?array $sort = null;

    private array $group_data = [];
    private bool $group_imported = false;
    private bool $in_period = false;
    private string $cdata = '';


    protected array $parent = [];
    protected int $counter = 0;

    protected int $mode;


    public function __construct(ilObjGroup $group, string $a_xml, int $a_parent_id)
    {
        global $DIC;

        parent::__construct(null);
        $this->user = $DIC->user();
        $this->rbacreview = $DIC->rbac()->review();
        $this->settings = $DIC->settings();
        $this->sax_controller = new ilSaxController();
        $this->mode = ilGroupXMLParser::$CREATE;
        $this->group_obj = $group;
        $this->log = $DIC->logger()->grp();
        $this->setXMLContent($a_xml);

        // init md parsing
        $this->setMDObject(
            new ilMD(
                $this->group_obj->getId(),
                $this->group_obj->getId(),
                $this->group_obj->getType()
            )
        );

        // SET MEMBER VARIABLES
        $this->pushParentId($a_parent_id);
    }

    public function pushParentId(int $a_id) : void
    {
        $this->parent[] = $a_id;
    }
    public function popParentId() : void
    {
        array_pop($this->parent);
    }

    public function getParentId() : int
    {
        return $this->parent[count($this->parent) - 1];
    }


    /**
     * @inheritDoc
     */
    public function setHandlers($a_xml_parser) : void
    {
        $this->sax_controller->setHandlers($a_xml_parser);
        $this->sax_controller->setDefaultElementHandler($this);

        $this->advanced_md_value_parser = new ilAdvancedMDValueParser(
            $this->group_obj->getId()
        );

        $this->sax_controller->setElementHandler(
            $this->advanced_md_value_parser,
            'AdvancedMetaData'
        );
    }



    /**
     * @inheritDoc
     */
    public function startParsing() : void
    {
        parent::startParsing();

        if ($this->mode == ilGroupXMLParser::$CREATE) {
            $status = is_object($this->group_obj) ? $this->group_obj->getRefId() : false;
        } else {
            $status = is_object($this->group_obj) ? $this->group_obj->update() : false;
        }
        $this->after_parsing_status = $status;
    }

    public function getObjectRefId()
    {
        return $this->after_parsing_status;
    }


    /**
     * @inheritDoc
     * @param mixed|null $a_attribs
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs) : void
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];

        if ($this->lom_parsing_active) {
            parent::handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
            return;
        }

        switch ($a_name) {
            case "MetaData":
                $this->lom_parsing_active = true;
                parent::handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;


            // GROUP DATA
            case "group":
                $this->group_data["admin"] = array();
                $this->group_data["member"] = array();

                $this->group_data["type"] = $a_attribs["type"];
                $this->group_data["id"] = $a_attribs["id"];

                break;

            case 'title':
                break;

            case "owner":
                $this->group_data["owner"] = $a_attribs["id"];
                break;

            case 'registration':
                $this->group_data['registration_type'] = $a_attribs['type'];
                $this->group_data['waiting_list_enabled'] = $a_attribs['waitingList'] == 'Yes' ? true : false;
                break;

            case 'period':
                $this->in_period = true;
                $this->group_data['period_with_time'] = $a_attribs['withTime'];
                break;

            case 'maxMembers':
                $this->group_data['max_members_enabled'] = $a_attribs['enabled'] == 'Yes' ? true : false;
                break;

            case "admin":
                if (!isset($a_attribs['action']) || $a_attribs['action'] == "Attach") {
                    $this->group_data["admin"]["attach"][] = $a_attribs["id"];
                } elseif (isset($a_attribs['action']) || $a_attribs['action'] == "Detach") {
                    $this->group_data["admin"]["detach"][] = $a_attribs["id"];
                }

                if (isset($a_attribs['notification']) and $a_attribs['notification'] == 'Yes') {
                    $this->group_data['notifications'][] = $a_attribs['id'];
                }

                break;

            case "member":
                if (!isset($a_attribs['action']) || $a_attribs['action'] == "Attach") {
                    $GLOBALS['DIC']->logger()->grp()->debug('New member with id ' . $a_attribs['id']);
                    $this->group_data["member"]["attach"][] = $a_attribs["id"];
                } elseif (isset($a_attribs['action']) || $a_attribs['action'] == "Detach") {
                    $GLOBALS['DIC']->logger()->grp()->debug('Deprecated member with id ' . $a_attribs['id']);
                    $this->group_data["member"]["detach"][] = $a_attribs["id"];
                }

                break;

            case 'ContainerSetting':
                $this->current_container_setting = $a_attribs['id'];
                break;

            case 'Sort':

                if ($this->group_imported) {
                    $this->initContainerSorting($a_attribs, $this->group_obj->getId());
                } else {
                    $this->sort = $a_attribs;
                }
                break;


            case 'SessionLimit':
                if (isset($a_attribs['active'])) {
                    $this->group_data['session_limit'] = (bool) $a_attribs['active'];
                }
                if (isset($a_attribs['previous'])) {
                    $this->group_data['session_previous'] = (int) $a_attribs['previous'];
                }
                if (isset($a_attribs['next'])) {
                    $this->group_data['session_next'] = (int) $a_attribs['next'];
                }
                break;


            case 'WaitingListAutoFill':
            case 'CancellationEnd':
            case 'minMembers':
            case 'mailMembersType':
                break;
        }
    }

    public function handlerEndTag($a_xml_parser, string $a_name) : void
    {
        if ($this->lom_parsing_active) {
            parent::handlerEndTag($a_xml_parser, $a_name);
        }

        switch ($a_name) {
            case 'MetaData':
                $this->lom_parsing_active = false;
                parent::handlerEndTag($a_xml_parser, $a_name);
                break;


            case "title":
                $this->group_data["title"] = trim($this->cdata);
                break;

            case "description":
                $this->group_data["description"] = trim($this->cdata);
                break;

            case 'information':
                $this->group_data['information'] = trim($this->cdata);
                break;

            case 'password':
                $this->group_data['password'] = trim($this->cdata);
                break;

            case 'maxMembers':
                $this->group_data['max_members'] = trim($this->cdata);
                break;

            case 'expiration':
                $this->group_data['expiration_end'] = trim($this->cdata);
                break;

            case 'start':
                if ($this->in_period) {
                    $this->group_data['period_start'] = trim($this->cdata);
                } else {
                    $this->group_data['expiration_start'] = trim($this->cdata);
                }
                break;

            case 'end':
                if ($this->in_period) {
                    $this->group_data['period_end'] = trim($this->cdata);
                } else {
                    $this->group_data['expiration_end'] = trim($this->cdata);
                }
                break;

            case 'period':
                $this->in_period = false;
                break;

            case "group":
                $this->save();
                break;

            case 'ContainerSetting':
                if ($this->current_container_setting) {
                    ilContainer::_writeContainerSetting(
                        $this->group_obj->getId(),
                        $this->current_container_setting,
                        $this->cdata
                    );
                }
                break;

            case 'WaitingListAutoFill':
                $this->group_data['auto_wait'] = trim($this->cdata);
                break;

            case 'CancellationEnd':
                if ((int) $this->cdata) {
                    $this->group_data['cancel_end'] = new ilDate((int) $this->cdata, IL_CAL_UNIX);
                }
                break;

            case 'minMembers':
                if ((int) $this->cdata) {
                    $this->group_data['min_members'] = (int) $this->cdata;
                }
                break;

            case 'showMembers':
                if ((int) $this->cdata) {
                    $this->group_data['show_members'] = (int) $this->cdata;
                }
                break;

            case 'admissionNotification':
                if ((int) $this->cdata) {
                    $this->group_data['auto_notification'] = (bool) $this->cdata;
                }
                break;

            case 'mailMembersType':
                $this->group_data['mail_members_type'] = (int) $this->cdata;
                break;

            case 'ViewMode':
                $this->group_data['view_mode'] = (int) $this->cdata;
                break;

        }
        $this->cdata = '';
    }


    /**
     * @inheritDoc
     */
    public function handlerCharacterData($a_xml_parser, string $a_data) : void
    {
        if ($this->lom_parsing_active) {
            parent::handlerCharacterData($a_xml_parser, $a_data);
        }

        $a_data = str_replace("<", "&lt;", $a_data);
        $a_data = str_replace(">", "&gt;", $a_data);

        if (!empty($a_data)) {
            $this->cdata .= $a_data;
        }
    }

    public function save() : bool
    {
        if ($this->group_imported) {
            return true;
        }

        $this->group_obj->setImportId($this->group_data["id"] ?? '');
        $this->group_obj->setTitle($this->group_data["title"] ?? '');
        $this->group_obj->setDescription($this->group_data["description"] ?? '');
        $this->group_obj->setInformation((string) $this->group_data['information']);

        if (isset($this->group_data['period_start']) && isset($this->group_data['period_end'])) {
            try {
                if ($this->group_data['period_with_time'] ?? false) {
                    $this->group_obj->setPeriod(
                        new \ilDateTime($this->group_data['period_start'], IL_CAL_UNIX),
                        new \ilDateTime($this->group_data['period_end'], IL_CAL_UNIX)
                    );
                } else {
                    $this->group_obj->setPeriod(
                        new \ilDateTime($this->group_data['period_start'], IL_CAL_UNIX),
                        new \ilDateTime($this->group_data['period_end'], IL_CAL_UNIX)
                    );
                }
            } catch (Exception $e) {
                $this->log->warning('Ignoring invalid group period settings: ');
                $this->log->warning('Period start: ' . $this->group_data['period_start']);
                $this->log->warning('Period end: ' . $this->group_data['period_end']);
            }
        }

        $ownerChanged = false;
        if (isset($this->group_data["owner"])) {
            $owner = $this->group_data["owner"];
            if (!is_numeric($owner)) {
                $owner = ilUtil::__extractId($owner, (int) IL_INST_ID);
            }
            if (is_numeric($owner) && $owner > 0) {
                $this->group_obj->setOwner($owner);
                $ownerChanged = true;
            }
        }

        /**
         * mode can be create or update
         */
        if ($this->mode == ilGroupXMLParser::$CREATE) {
            $this->group_obj->createReference();
            $this->group_obj->putInTree($this->getParentId());
            $this->group_obj->setPermissions($this->getParentId());
            if (
                array_key_exists('type', $this->group_data) &&
                $this->group_data['type'] == 'closed'
            ) {
                $this->group_obj->updateGroupType(
                    ilGroupConstants::GRP_TYPE_CLOSED
                );
            }
        } else {
            if (
                array_key_exists('type', $this->group_data) &&
                $this->group_data['type'] == 'closed'
            ) {
                $this->group_obj->updateGroupType(
                    ilGroupConstants::GRP_TYPE_CLOSED
                );
            } elseif (
                array_key_exists('type', $this->group_data) &&
                $this->group_data['type'] == 'open'
            ) {
                $this->group_obj->updateGroupType(
                    ilGroupConstants::GRP_TYPE_OPEN
                );
            }
        }
        // SET GROUP SPECIFIC DATA
        switch ($this->group_data['registration_type'] ?? '') {
            case 'direct':
            case 'enabled':
                $flag = ilGroupConstants::GRP_REGISTRATION_DIRECT;
                break;

            case 'disabled':
                $flag = ilGroupConstants::GRP_REGISTRATION_DEACTIVATED;
                break;

            case 'confirmation':
                $flag = ilGroupConstants::GRP_REGISTRATION_REQUEST;
                break;

            case 'password':
                $flag = ilGroupConstants::GRP_REGISTRATION_PASSWORD;
                break;

            default:
                $flag = ilGroupConstants::GRP_REGISTRATION_DIRECT;
        }
        $this->group_obj->setRegistrationType($flag);


        $registration_end = null;
        if ($this->group_data['expiration_end'] ?? false) {
            $registration_end = new ilDateTime($this->group_data['expiration_end'], IL_CAL_UNIX);
        }

        $registration_start = null;
        if ($this->group_data['expiration_start'] ?? false) {
            $registration_start = new ilDateTime($this->group_data['expiration_start'], IL_CAL_UNIX);
        }
        if (
            $registration_start instanceof ilDateTime &&
            $registration_end instanceof ilDateTime
        ) {
            $this->group_obj->enableUnlimitedRegistration(false);
            $this->group_obj->setRegistrationStart($registration_start);
            $this->group_obj->setRegistrationEnd($registration_end);
        } else {
            $this->group_obj->enableUnlimitedRegistration(true);
        }
        $this->group_obj->setPassword($this->group_data['password'] ?? '');
        $this->group_obj->enableMembershipLimitation((bool) ($this->group_data['max_members_enabled'] ?? false));
        $this->group_obj->setMaxMembers($this->group_data['max_members'] ?: 0);
        $this->group_obj->enableWaitingList((bool) ($this->group_data['waiting_list_enabled'] ?? false));

        $this->group_obj->setWaitingListAutoFill((bool) ($this->group_data['auto_wait'] ?? false));
        $this->group_obj->setCancellationEnd($this->group_data['cancel_end'] ?? null);
        $this->group_obj->setMinMembers((int) ($this->group_data['min_members'] ?? 0));
        $this->group_obj->setShowMembers((bool) ($this->group_data['show_members'] ?? false));
        $this->group_obj->setAutoNotification($this->group_data['auto_notification'] ? true : false);
        $this->group_obj->setMailToMembersType((int) $this->group_data['mail_members_type']);
        if (isset($this->group_data['view_mode'])) {
            $this->group_obj->setViewMode((int) $this->group_data['view_mode']);
        }
        if (isset($this->group_data['session_limit'])) {
            $this->group_obj->enableSessionLimit((bool) $this->group_data['session_limit']);
        }
        if (isset($this->group_data['session_previous'])) {
            $this->group_obj->setNumberOfPreviousSessions((int) $this->group_data['session_previous']);
        }
        if (isset($this->group_data['session_next'])) {
            $this->group_obj->setNumberOfNextSessions((int) $this->group_data['session_next']);
        }
        $this->group_obj->update();

        // ASSIGN ADMINS/MEMBERS
        $this->assignMembers();

        $this->pushParentId($this->group_obj->getRefId());

        if ($this->sort) {
            $this->initContainerSorting($this->sort, $this->group_obj->getId());
        }

        $this->group_imported = true;

        return true;
    }

    public function assignMembers() : void
    {
        $this->participants = new ilGroupParticipants($this->group_obj->getId());
        $this->participants->add($this->user->getId(), ilParticipants::IL_GRP_ADMIN);
        $this->participants->updateNotification($this->user->getId(), (bool) $this->settings->get('mail_grp_admin_notification', "1"));

        // attach ADMINs
        if (isset($this->group_data["admin"]["attach"]) && count($this->group_data["admin"]["attach"])) {
            foreach ($this->group_data["admin"]["attach"] as $user) {
                if ($id_data = $this->parseId($user)) {
                    if ($id_data['local'] or $id_data['imported']) {
                        $this->participants->add($id_data['usr_id'], ilParticipants::IL_GRP_ADMIN);
                        if (in_array($user, (array) $this->group_data['notifications'])) {
                            $this->participants->updateNotification($id_data['usr_id'], true);
                        }
                    }
                }
            }
        }
        // detach ADMINs
        if (isset($this->group_data["admin"]["detach"]) && count($this->group_data["admin"]["detach"])) {
            foreach ($this->group_data["admin"]["detach"] as $user) {
                if ($id_data = $this->parseId($user)) {
                    if ($id_data['local'] or $id_data['imported']) {
                        if ($this->participants->isAssigned($id_data['usr_id'])) {
                            $this->participants->delete($id_data['usr_id']);
                        }
                    }
                }
            }
        }
        // MEMBER
        if (isset($this->group_data["member"]["attach"]) && count($this->group_data["member"]["attach"])) {
            foreach ($this->group_data["member"]["attach"] as $user) {
                if ($id_data = $this->parseId($user)) {
                    if ($id_data['local'] or $id_data['imported']) {
                        $this->participants->add($id_data['usr_id'], ilParticipants::IL_GRP_MEMBER);
                    }
                }
            }
        }

        if (isset($this->group_data["member"]["detach"]) && count($this->group_data["member"]["detach"])) {
            foreach ($this->group_data["member"]["detach"] as $user) {
                if ($id_data = $this->parseId($user)) {
                    if ($id_data['local'] or $id_data['imported']) {
                        if ($this->participants->isAssigned($id_data['usr_id'])) {
                            $this->participants->delete($id_data['usr_id']);
                        }
                    }
                }
            }
        }
    }

    public function parseId(string $a_id) : array
    {
        $fields = explode('_', $a_id);

        if (!is_array($fields) or
           $fields[0] != 'il' or
           !is_numeric($fields[1]) or
           $fields[2] != 'usr' or
           !is_numeric($fields[3])) {
            return [];
        }
        if ($id = ilObjUser::_getImportedUserId($a_id)) {
            return array('imported' => true,
                         'local' => false,
                         'usr_id' => $id);
        }
        if (($fields[1] == $this->settings->get('inst_id', "0")) and ($user = ilObjUser::_lookupName($fields[3]))) {
            if (strlen($user['login'])) {
                return array('imported' => false,
                             'local' => true,
                             'usr_id' => $fields[3]);
            }
        }
        $this->log->warning('Parsing id failed: ' . $a_id);
        return [];
    }


    public function setMode(int $mode) : void
    {
        $this->mode = $mode;
    }

    public function initContainerSorting(array $a_attribs, int $a_group_id) : void
    {
        ilContainerSortingSettings::_importContainerSortingSettings($a_attribs, $a_group_id);
    }
}
