<?php

declare(strict_types=1);

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
 * Class ilBuddyList
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationsTableGUI extends ilTable2GUI
{
    private const APPLY_FILTER_CMD = 'applyContactsTableFilter';
    private const RESET_FILTER_CMD = 'resetContactsTableFilter';
    public const STATE_FILTER_ELM_ID = 'relation_state_type';

    protected ilGlobalTemplateInterface $containerTemplate;
    protected bool $hasAccessToMailSystem = false;
    protected bool $isChatEnabled = false;
    protected ilObjUser $user;
    /** @var array<string, mixed>  */
    protected array $filter = [];

    public function __construct(object $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        $this->containerTemplate = $DIC['tpl'];
        $this->user = $DIC['ilUser'];

        $this->setId('buddy_system_tbl');
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->lng->loadLanguageModule('buddysystem');

        $this->hasAccessToMailSystem = $DIC->rbac()->system()->checkAccess(
            'internal_mail',
            ilMailGlobalServices::getMailObjectRefId()
        );

        $chatSettings = new ilSetting('chatroom');
        $this->isChatEnabled = (bool) $chatSettings->get('chat_enabled', '0');

        $this->setDefaultOrderDirection('ASC');
        $this->setDefaultOrderField('public_name');

        $this->setTitle($this->lng->txt('buddy_tbl_title_relations'));

        if ($this->hasAccessToMailSystem || $this->isChatEnabled) {
            $this->addColumn('', 'chb', '1%', true);
            $this->setSelectAllCheckbox('usr_id');
            if ($this->hasAccessToMailSystem) {
                $this->addMultiCommand('mailToUsers', $this->lng->txt('send_mail_to'));
            }
            if ($this->isChatEnabled) {
                $this->addMultiCommand('inviteToChat', $this->lng->txt('invite_to_chat'));
            }
        }

        $this->addColumn($this->lng->txt('name'), 'public_name');
        $this->addColumn($this->lng->txt('login'), 'login');
        $this->addColumn($this->lng->txt('buddy_tbl_state_actions_col_label'), '', '', false, 'ilRight');

        $this->setRowTemplate('tpl.buddy_system_relation_table_row.html', 'Services/Contact/BuddySystem');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setFilterCommand(self::APPLY_FILTER_CMD);
        $this->setResetCommand(self::RESET_FILTER_CMD);

        $this->initFilter();
    }

    /**
     * @inheritDoc
     */
    public function initFilter(): void
    {
        $this->filters = [];
        $this->filter = [];

        $relations_state_selection = new ilSelectInputGUI(
            $this->lng->txt('buddy_tbl_filter_state'),
            self::STATE_FILTER_ELM_ID
        );

        $options = [];
        $state_factory = ilBuddySystemRelationStateFactory::getInstance();
        foreach ($state_factory->getValidStates() as $state) {
            if ($state->isInitial()) {
                continue;
            }

            $state_filter_mapper = $state_factory->getTableFilterStateMapper($state);
            $options += $state_filter_mapper->optionsForState();
        }
        $relations_state_selection->setOptions(['' => $this->lng->txt('please_choose')] + $options);
        $this->addFilterItem($relations_state_selection);
        $relations_state_selection->readFromSession();
        $this->filter['relation_state_type'] = $relations_state_selection->getValue();

        $public_name = new ilTextInputGUI($this->lng->txt('name'), 'public_name');
        $this->addFilterItem($public_name);
        $public_name->readFromSession();
        $this->filter['public_name'] = $public_name->getValue();
    }

    /**
     * @param mixed $value
     */
    public function applyFilterValue(string $filterKey, $value): void
    {
        foreach ([$this->getFilterItems(), $this->getFilterItems(true)] as $filterItems) {
            foreach ($filterItems as $item) {
                /** @var ilTableFilterItem|ilFormPropertyGUI $item */
                if ($item->getPostVar() === $filterKey) {
                    $item->setValueByArray([$filterKey => $value]);
                    $item->writeToSession();
                    break 2;
                }
            }
        }
    }

    public function populate(): void
    {
        $this->setExternalSorting(false);
        $this->setExternalSegmentation(false);

        $data = [];

        $relations = ilBuddyList::getInstanceByGlobalUser()->getRelations();

        $state_filter = (string) $this->filter[self::STATE_FILTER_ELM_ID];
        $state_factory = ilBuddySystemRelationStateFactory::getInstance();
        $relations = $relations->filter(function (ilBuddySystemRelation $relation) use ($state_filter, $state_factory): bool {
            $state_filter_mapper = $state_factory->getTableFilterStateMapper($relation->getState());
            return $state_filter === '' || $state_filter_mapper->filterMatchesRelation($state_filter, $relation);
        });

        $public_names = ilUserUtil::getNamePresentation($relations->getKeys(), false, false, '', false, true, false);
        $logins = ilUserUtil::getNamePresentation($relations->getKeys(), false, false, '', false, false, false);

        $logins = array_map(static function (string $value): string {
            $matches = null;
            preg_match_all('/\[([^\[]+?)\]/', $value, $matches);
            return (
                is_array($matches) &&
                isset($matches[1]) &&
                is_array($matches[1]) &&
                isset($matches[1][count($matches[1]) - 1])
            ) ? $matches[1][count($matches[1]) - 1] : '';
        }, $logins);

        $public_name_query = (string) ($this->filter['public_name'] ?? '');
        $relations = $relations->filter(static function (ilBuddySystemRelation $relation) use (
            $public_name_query,
            $relations,
            $public_names,
            $logins
        ): bool {
            $usrId = $relations->getKey($relation);

            $hasMatchingName = (
                0 === ilStr::strlen($public_name_query) ||
                ilStr::strpos(
                    ilStr::strtolower($public_names[$usrId]),
                    ilStr::strtolower($public_name_query)
                ) !== false ||
                ilStr::strpos(ilStr::strtolower($logins[$usrId]), ilStr::strtolower($public_name_query)) !== false
            );

            if (!$hasMatchingName) {
                return false;
            }

            return ilObjUser::_lookupActive($usrId);
        });

        foreach (array_keys($relations->toArray()) as $usr_id) {
            $data[] = [
                'usr_id' => $usr_id,
                'public_name' => $public_names[$usr_id],
                'login' => $logins[$usr_id]
            ];
        }

        $this->setData($data);
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set): void
    {
        if ($this->hasAccessToMailSystem) {
            $a_set['chb'] = ilLegacyFormElementsUtil::formCheckbox(false, 'usr_id[]', (string) $a_set['usr_id']);
        }

        $public_profile = ilObjUser::_lookupPref($a_set['usr_id'], 'public_profile');
        if ((!$this->user->isAnonymous() && $public_profile === 'y') || $public_profile === 'g') {
            $this->ctrl->setParameterByClass(ilPublicUserProfileGUI::class, 'user', $a_set['usr_id']);
            $profile_target = $this->ctrl->getLinkTargetByClass(
                ilPublicUserProfileGUI::class,
                'getHTML'
            );
            $a_set['profile_link'] = $profile_target;
            $a_set['linked_public_name'] = $a_set['public_name'];

            $a_set['profile_link_login'] = $profile_target;
            $a_set['linked_login'] = $a_set['login'];
        } else {
            $a_set['unlinked_public_name'] = $a_set['public_name'];
            $a_set['unlinked_login'] = $a_set['login'];
        }

        $a_set['contact_actions'] = ilBuddySystemLinkButton::getInstanceByUserId((int) $a_set['usr_id'])->getHtml();
        parent::fillRow($a_set);
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $listener_tpl = new ilTemplate(
            'tpl.buddy_system_relation_table_listener.html',
            true,
            true,
            'Services/Contact/BuddySystem'
        );
        $listener_tpl->setVariable('TABLE_ID', $this->getId());
        $listener_tpl->setVariable('FILTER_ELM_ID', self::STATE_FILTER_ELM_ID);
        $listener_tpl->setVariable(
            'NO_ENTRIES_TEXT',
            $this->getNoEntriesText() ?: $this->lng->txt('no_items')
        );

        return parent::render() . $listener_tpl->get();
    }
}
