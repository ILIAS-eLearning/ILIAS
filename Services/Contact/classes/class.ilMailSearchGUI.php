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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\Refinery\Factory as Refinery;

/**
* @author Jens Conze
* @ingroup ServicesMail
*/
class ilMailSearchGUI
{
    private ilGlobalTemplateInterface $tpl;
    private ilCtrlInterface $ctrl;
    protected ilRbacReview $rbacreview;
    protected ilObjectDataCache $object_data_cache;
    private ilLanguage $lng;
    private ilFormatMail $umail;
    private bool $errorDelete = false;
    /**
     * @var ilWorkspaceAccessHandler|null|ilPortfolioAccessHandler
     */
    private $wsp_access_handler = null;
    private ?int $wsp_node_id = null;
    private GlobalHttpState $http;
    private Refinery $refinery;

    /**
     * @param ilWorkspaceAccessHandler|null|ilPortfolioAccessHandler $wsp_access_handler
     */
    public function __construct($wsp_access_handler = null, ?int $wsp_node_id = null)
    {
        /** @var $DIC \ILIAS\DI\Container */
        global $DIC;

        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->rbacreview = $DIC['rbacreview'];
        $this->object_data_cache = $DIC['ilObjDataCache'];
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->wsp_access_handler = $wsp_access_handler;
        $this->wsp_node_id = $wsp_node_id;
        
        $this->ctrl->saveParameter($this, 'mobj_id');
        $this->ctrl->saveParameter($this, 'ref');

        $this->umail = new ilFormatMail($DIC->user()->getId());
    }

    public function executeCommand() : bool
    {
        $forward_class = $this->ctrl->getNextClass($this);
        switch ($forward_class) {
            default:
                if (!($cmd = $this->ctrl->getCmd())) {
                    $cmd = "showResults";
                }

                $this->$cmd();
                break;
        }

        return true;
    }

    private function isDefaultRequestContext() : bool
    {
        return (
            !$this->http->wrapper()->query()->has('ref') ||
            $this->http->wrapper()->query()->retrieve('ref', $this->refinery->kindlyTo()->string()) !== 'wsp'
        );
    }

    public function adopt() : void
    {
        $trafo = $this->refinery->kindlyTo()->int();
        if ($this->isDefaultRequestContext()) {
            $trafo = $this->refinery->kindlyTo()->string();
        }
        
        $recipients_to = [];
        foreach (['addr', 'usr', 'grp'] as $search_type) {
            if ($this->http->wrapper()->post()->has('search_name_to_' . $search_type)) {
                $recipients_to[] = $this->http->wrapper()->post()->retrieve(
                    'search_name_to_' . $search_type,
                    $this->refinery->kindlyTo()->listOf($trafo)
                );
            }
        }
        $recipients_to = array_unique(array_merge(...$recipients_to));
        ilSession::set('mail_search_results_to', $recipients_to);

        $recipients_cc = [];
        if ($this->http->wrapper()->post()->has('search_name_cc')) {
            $recipients_cc = array_unique($this->http->wrapper()->post()->retrieve(
                'search_name_cc',
                $this->refinery->kindlyTo()->listOf($trafo)
            ));
        }
        ilSession::set('mail_search_results_cc', $recipients_cc);

        $recipients_bcc = [];
        if ($this->http->wrapper()->post()->has('search_name_bcc')) {
            $recipients_bcc = array_unique($this->http->wrapper()->post()->retrieve(
                'search_name_bcc',
                $this->refinery->kindlyTo()->listOf($trafo)
            ));
        }
        ilSession::set('mail_search_results_bcc', $recipients_bcc);
        
        if ($this->isDefaultRequestContext()) {
            $this->saveMailData();
        } else {
            $this->addPermission($recipients_to);
        }

        $this->ctrl->returnToParent($this);
    }

    private function saveMailData() : void
    {
        $mail_data = $this->umail->getSavedData();

        $this->umail->savePostData(
            (int) $mail_data['user_id'],
            $mail_data['attachments'],
            $mail_data['rcp_to'],
            $mail_data['rcp_cc'],
            $mail_data['rcp_bcc'],
            $mail_data['m_subject'],
            $mail_data['m_message'],
            $mail_data['use_placeholders'],
            $mail_data['tpl_ctx_id'],
            $mail_data['tpl_ctx_params']
        );
    }

    public function cancel() : void
    {
        $this->ctrl->returnToParent($this);
    }

    public function search() : bool
    {
        $search = '';
        if ($this->http->wrapper()->post()->has('search')) {
            $search = $this->http->wrapper()->post()->retrieve('search', $this->refinery->kindlyTo()->string());
        }

        ilSession::set('mail_search_search', trim($search));

        if (ilSession::get('mail_search_search') === '') {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_insert_query'));
        } elseif (strlen(ilSession::get('mail_search_search')) < 3) {
            $this->lng->loadLanguageModule('search');
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('search_minimum_three'));
        }

        $this->showResults();

        return true;
    }

    protected function initSearchForm() : ilPropertyFormGUI
    {
        if ($this->isDefaultRequestContext()) {
            $this->saveMailData();
            $title = $this->lng->txt('search_recipients');
        } else {
            $this->lng->loadLanguageModule('wsp');
            $title = $this->lng->txt('wsp_share_search_users');
        }

        $form = new ilPropertyFormGUI();
        $form->setTitle($title);
        $form->setId('search_rcp');
        $form->setFormAction($this->ctrl->getFormAction($this, 'search'));

        $inp = new ilTextInputGUI($this->lng->txt('search_for'), 'search');
        $inp->setSize(30);
        $dsDataLink = $this->ctrl->getLinkTarget($this, 'lookupRecipientAsync', '', true, false);
        $inp->setDataSource($dsDataLink);

        if (
            ilSession::get('mail_search_search') &&
            is_string(ilSession::get('mail_search_search')) &&
            ilSession::get('mail_search_search') !== ''
        ) {
            $inp->setValue(
                ilLegacyFormElementsUtil::prepareFormOutput(trim(ilSession::get('mail_search_search')), true)
            );
        }
        $form->addItem($inp);

        $form->addCommandButton('search', $this->lng->txt('search'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }

    public function lookupRecipientAsync() : void
    {
        $search = '';
        if ($this->http->wrapper()->query()->has('term')) {
            $search = $this->http->wrapper()->query()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
        }
        if ($this->http->wrapper()->post()->has('term')) {
            $search = $this->http->wrapper()->post()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
        }

        $search = trim($search);

        $result = [];
        if (ilStr::strLen($search) >= 3) {
            // #14768
            $quoted = ilUtil::stripSlashes($search);
            $quoted = str_replace(['%', '_'], ['\%', '\_'], $quoted);

            $mailFormObj = new ilMailForm;
            $result = $mailFormObj->getRecipientAsync(
                "%" . $quoted . "%",
                ilUtil::stripSlashes($search),
                $this->isDefaultRequestContext()
            );
        }

        $this->http->saveResponse(
            $this->http->response()
                ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
                ->withBody(\ILIAS\Filesystem\Stream\Streams::ofString(json_encode($result, JSON_THROW_ON_ERROR)))
        );
        $this->http->sendResponse();
        $this->http->close();
    }


    public function showResults() : void
    {
        $form = $this->initSearchForm();

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_search.html', 'Services/Contact');
        $this->tpl->setVariable('ACTION', $this->ctrl->getFormAction($this));
        $this->tpl->setTitle($this->lng->txt('mail'));
        $this->tpl->setVariable('SEARCHFORM', $form->getHtml());

        // #14109
        if (
            !ilSession::get('mail_search_search') ||
            !is_string(ilSession::get('mail_search_search')) ||
            strlen(ilSession::get('mail_search_search')) < 3
        ) {
            if ($this->isDefaultRequestContext()) {
                $this->tpl->printToStdout();
            }
            return;
        }

        $relations = ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations();
        if (count($relations)) {
            $contacts_search_result = new ilSearchResult();

            $query_parser = new ilQueryParser(addcslashes(ilSession::get('mail_search_search'), '%_'));
            $query_parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
            $query_parser->setMinWordLength(3);
            $query_parser->parse();

            $user_search = ilObjectSearchFactory::_getUserSearchInstance($query_parser);
            $user_search->enableActiveCheck(true);
            $user_search->setFields(['login']);
            $result_obj = $user_search->performSearch();
            $contacts_search_result->mergeEntries($result_obj);

            $user_search->setFields(['firstname']);
            $result_obj = $user_search->performSearch();
            $contacts_search_result->mergeEntries($result_obj);

            $user_search->setFields(['lastname']);
            $result_obj = $user_search->performSearch();
            $contacts_search_result->mergeEntries($result_obj);

            $contacts_search_result->setMaxHits(100000);
            $contacts_search_result->preventOverwritingMaxhits(true);
            $contacts_search_result->filter(ROOT_FOLDER_ID, true);

            // Filter users (depends on setting in user accounts)
            $users = ilUserFilter::getInstance()->filter($contacts_search_result->getResultIds());
            $users = array_intersect($users, $relations->getKeys());

            $tbl_contacts = new ilMailSearchResultsTableGUI($this, 'contacts');
            $tbl_contacts->setTitle($this->lng->txt('mail_addressbook'));
            $tbl_contacts->setRowTemplate('tpl.mail_search_addr_row.html', 'Services/Contact');

            $has_mail_addr = false;
            $result = [];
            $counter = 0;
            foreach ($users as $user) {
                $login = ilObjUser::_lookupLogin($user);

                if ($this->isDefaultRequestContext()) {
                    $result[$counter]['check'] =
                        ilLegacyFormElementsUtil::formCheckbox(false, 'search_name_to_addr[]', $login) .
                        ilLegacyFormElementsUtil::formCheckbox(false, 'search_name_cc[]', $login) .
                        ilLegacyFormElementsUtil::formCheckbox(false, 'search_name_bcc[]', $login);
                } else {
                    $result[$counter]['check'] = ilLegacyFormElementsUtil::formCheckbox(
                        false,
                        'search_name_to_addr[]',
                        $user
                    );
                }

                $result[$counter]['login'] = $login;
                if (ilObjUser::_lookupPref($user, 'public_email') === 'y') {
                    $has_mail_addr = true;
                    $result[$counter]['email'] = ilObjUser::_lookupEmail($user);
                }

                if (in_array(ilObjUser::_lookupPref($user, 'public_profile'), ['y', "g"])) {
                    $name = ilObjUser::_lookupName($user);
                    $result[$counter]['firstname'] = $name['firstname'];
                    $result[$counter]['lastname'] = $name['lastname'];
                } else {
                    $result[$counter]['firstname'] = '';
                    $result[$counter]['lastname'] = '';
                }

                ++$counter;
            }

            if ($this->isDefaultRequestContext()) {
                $tbl_contacts->addColumn(
                    $this->lng->txt('mail_to') . '/' . $this->lng->txt('cc') . '/' . $this->lng->txt('bc'),
                    'check',
                    '10%'
                );
            } else {
                $tbl_contacts->addColumn('', '', '1%', true);
            }
            $tbl_contacts->addColumn($this->lng->txt('login'), 'login', '15%');
            $tbl_contacts->addColumn($this->lng->txt('firstname'), 'firstname', '15%');
            $tbl_contacts->addColumn($this->lng->txt('lastname'), 'lastname', '15%');
            if ($has_mail_addr) {
                foreach ($result as $key => $val) {
                    if (!isset($val['email']) || (string) $val['email'] === '') {
                        $result[$key]['email'] = '&nbsp;';
                    }
                }

                $tbl_contacts->addColumn($this->lng->txt('email'), 'email', "15%");
            }
            $tbl_contacts->setData($result);

            $tbl_contacts->setDefaultOrderField('login');
            $tbl_contacts->enable('select_all');
            $tbl_contacts->setSelectAllCheckbox('search_name_to_addr');
            $tbl_contacts->setFormName('recipients');

            $this->tpl->setVariable('TABLE_ADDR', $tbl_contacts->getHTML());
        }

        $all_results = new ilSearchResult();

        $query_parser = new ilQueryParser(addcslashes(ilSession::get('mail_search_search'), '%_'));
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
        $query_parser->setMinWordLength(3);
        $query_parser->parse();

        $user_search = ilObjectSearchFactory::_getUserSearchInstance($query_parser);
        $user_search->enableActiveCheck(true);
        $user_search->setFields(['login']);
        $result_obj = $user_search->performSearch();
        $all_results->mergeEntries($result_obj);

        $user_search->setFields(['firstname']);
        $result_obj = $user_search->performSearch();
        $all_results->mergeEntries($result_obj);

        $user_search->setFields(['lastname']);
        $result_obj = $user_search->performSearch();
        $all_results->mergeEntries($result_obj);

        $all_results->setMaxHits(100000);
        $all_results->preventOverwritingMaxhits(true);
        $all_results->filter(ROOT_FOLDER_ID, true);

        // Filter users (depends on setting in user accounts)
        $has_mail_usr = false;
        $users = ilUserFilter::getInstance()->filter($all_results->getResultIds());
        if (count($users)) {
            $tbl_users = new ilMailSearchResultsTableGUI($this, 'usr');
            $tbl_users->setTitle($this->lng->txt('system') . ': ' . $this->lng->txt('persons'));
            $tbl_users->setRowTemplate('tpl.mail_search_users_row.html', 'Services/Contact');

            $result = [];
            $counter = 0;
            foreach ($users as $user) {
                $login = ilObjUser::_lookupLogin($user);

                if ($this->isDefaultRequestContext()) {
                    $result[$counter]['check'] = ilLegacyFormElementsUtil::formCheckbox(
                        false,
                        'search_name_to_usr[]',
                        $login
                    ) .
                        ilLegacyFormElementsUtil::formCheckbox(false, 'search_name_cc[]', $login) .
                        ilLegacyFormElementsUtil::formCheckbox(false, 'search_name_bcc[]', $login);
                } else {
                    $result[$counter]['check'] = ilLegacyFormElementsUtil::formCheckbox(
                        false,
                        'search_name_to_usr[]',
                        (string) $user
                    );
                }
                $result[$counter]['login'] = $login;

                if (in_array(ilObjUser::_lookupPref($user, 'public_profile'), ['y', "g"])) {
                    $name = ilObjUser::_lookupName($user);
                    $result[$counter]['firstname'] = $name['firstname'];
                    $result[$counter]['lastname'] = $name['lastname'];
                } else {
                    $result[$counter]['firstname'] = '';
                    $result[$counter]['lastname'] = '';
                }

                if (ilObjUser::_lookupPref($user, 'public_email') === 'y') {
                    $has_mail_usr = true;
                    $result[$counter]['email'] = ilObjUser::_lookupEmail($user);
                }

                ++$counter;
            }

            if ($this->isDefaultRequestContext()) {
                $tbl_users->addColumn(
                    $this->lng->txt('mail_to') . '/' . $this->lng->txt('cc') . '/' . $this->lng->txt('bc'),
                    'check',
                    '10%'
                );
            } else {
                $tbl_users->addColumn('', '', '1%');
            }
            $tbl_users->addColumn($this->lng->txt('login'), 'login', '15%');
            $tbl_users->addColumn($this->lng->txt('firstname'), 'firstname', '15%');
            $tbl_users->addColumn($this->lng->txt('lastname'), 'lastname', '15%');
            if ($has_mail_usr === true) {
                foreach ($result as $key => $val) {
                    if (!isset($val['email']) || (string) $val['email'] === '') {
                        $result[$key]['email'] = '&nbsp;';
                    }
                }

                $tbl_users->addColumn($this->lng->txt('email'), 'email', '15%');
            }
            $tbl_users->setData($result);

            $tbl_users->setDefaultOrderField('login');
            $tbl_users->enable('select_all');
            $tbl_users->setSelectAllCheckbox('search_name_to_usr');
            $tbl_users->setFormName('recipients');

            $this->tpl->setVariable('TABLE_USERS', $tbl_users->getHTML());
        }

        $group_results = new ilSearchResult();

        $query_parser = new ilQueryParser(addcslashes(ilSession::get('mail_search_search'), '%_'));
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
        $query_parser->setMinWordLength(3);
        $query_parser->parse();

        $search = ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
        $search->setFilter(['grp']);
        $result = $search->performSearch();
        $group_results->mergeEntries($result);
        $group_results->setMaxHits(PHP_INT_MAX);
        $group_results->preventOverwritingMaxhits(true);
        $group_results->setRequiredPermission('read');
        $group_results->filter(ROOT_FOLDER_ID, true);

        $visible_groups = [];
        if ($group_results->getResults()) {
            $tbl_grp = new ilMailSearchResultsTableGUI($this, 'grp');
            $tbl_grp->setTitle($this->lng->txt('system') . ': ' . $this->lng->txt('groups'));
            $tbl_grp->setRowTemplate('tpl.mail_search_groups_row.html', 'Services/Contact');

            $result = [];
            $counter = 0;

            $this->object_data_cache->preloadReferenceCache(array_keys($group_results->getResults()));

            $groups = $group_results->getResults();
            foreach ($groups as $grp) {
                if (!ilParticipants::hasParticipantListAccess($grp['obj_id'])) {
                    continue;
                }

                if ($this->isDefaultRequestContext()) {
                    $members = [];
                    $roles = $this->rbacreview->getAssignableChildRoles($grp['ref_id']);
                    foreach ($roles as $role) {
                        if (
                            strpos($role['title'], 'il_grp_member_') === 0 ||
                            strpos($role['title'], 'il_grp_admin_') === 0
                        ) {
                            // FIX for Mantis: 7523
                            $members[] = '#' . $role['title'];
                        }
                    }
                    $str_members = implode(',', $members);

                    $result[$counter]['check'] =
                        ilLegacyFormElementsUtil::formCheckbox(false, 'search_name_to_grp[]', $str_members) .
                        ilLegacyFormElementsUtil::formCheckbox(false, 'search_name_cc[]', $str_members) .
                        ilLegacyFormElementsUtil::formCheckbox(false, 'search_name_bcc[]', $str_members);
                } else {
                    $result[$counter]['check'] = ilLegacyFormElementsUtil::formCheckbox(
                        false,
                        'search_name_to_grp[]',
                        (string) $grp['obj_id']
                    );
                }
                $result[$counter]['title'] = $this->object_data_cache->lookupTitle((int) $grp['obj_id']);
                $result[$counter]['description'] = $this->object_data_cache->lookupDescription((int) $grp['obj_id']);

                ++$counter;
                $visible_groups[] = $grp;
            }

            if ($visible_groups) {
                $tbl_grp->setData($result);

                if ($this->isDefaultRequestContext()) {
                    $tbl_grp->addColumn(
                        $this->lng->txt('mail_to') . '/' . $this->lng->txt('cc') . '/' . $this->lng->txt('bc'),
                        'check',
                        '10%'
                    );
                } else {
                    $tbl_grp->addColumn('', '', '1%');
                }
                $tbl_grp->addColumn($this->lng->txt('title'), 'title', '15%');
                $tbl_grp->addColumn($this->lng->txt('description'), 'description', '15%');

                $tbl_grp->setDefaultOrderField('title');
                $tbl_grp->enable('select_all');
                $tbl_grp->setSelectAllCheckbox('search_name_to_grp');
                $tbl_grp->setFormName('recipients');

                $this->tpl->setVariable('TABLE_GRP', $tbl_grp->getHTML());
            }
        }

        if (count($users) || count($visible_groups) || count($relations)) {
            $this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
            $this->tpl->setVariable("ALT_ARROW", '');
            $this->tpl->setVariable("IMG_ARROW_UP", ilUtil::getImagePath("arrow_upright.svg"));
            $this->tpl->setVariable("ALT_ARROW_UP", '');

            if ($this->isDefaultRequestContext()) {
                $this->tpl->setVariable('BUTTON_ADOPT', $this->lng->txt('adopt'));
            } else {
                $this->tpl->setVariable('BUTTON_ADOPT', $this->lng->txt('wsp_share_with_users'));
            }
        } else {
            $this->lng->loadLanguageModule('search');
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('search_no_match'));
        }

        if ($this->isDefaultRequestContext()) {
            $this->tpl->printToStdout();
        }
    }

    /**
     * @param int[] $a_obj_ids
     */
    protected function addPermission(array $a_obj_ids) : void
    {
        if (!is_array($a_obj_ids)) {
            $a_obj_ids = [$a_obj_ids];
        }

        $existing = $this->wsp_access_handler->getPermissions($this->wsp_node_id);
        $added = false;
        foreach ($a_obj_ids as $object_id) {
            if (!in_array($object_id, $existing, true)) {
                $added = $this->wsp_access_handler->addPermission($this->wsp_node_id, $object_id);
            }
        }

        if ($added) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('wsp_share_success'), true);
        }
    }
}
