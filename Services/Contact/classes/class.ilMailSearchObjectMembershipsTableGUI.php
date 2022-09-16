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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Jan Posselt <jposselt@databay.de>
 * @ingroup ServicesMail
 */
class ilMailSearchObjectMembershipsTableGUI extends ilTable2GUI
{
    private GlobalHttpState $http;
    private Refinery $refinery;
    protected ilObjUser $user;
    /** @var array<string, string>  */
    protected array $mode;
    protected bool $mailing_allowed;

    /**
     * @param ilMailSearchCoursesGUI|ilMailSearchGroupsGUI $a_parent_obj $a_parent_obj
     */
    public function __construct(
        $a_parent_obj,
        string $type = 'crs',
        string $context = 'mail',
        array $contextObjects = []
    ) {
        global $DIC;

        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->user = $DIC['ilUser'];
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $tableId = ilStr::subStr($type . '_cml_' . md5(implode('_', $contextObjects)), 0, 30);
        $this->setId($tableId);
        parent::__construct($a_parent_obj, 'showMembers');

        $this->context = $context;
        if ($this->context === 'mail') {
            $mail = new ilMail($this->user->getId());
            $this->mailing_allowed = $DIC->rbac()->system()->checkAccess('internal_mail', $mail->getMailObjectReferenceId());
        }

        $this->setDefaultOrderDirection('ASC');
        $this->setDefaultOrderField('members_login');

        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('buddysystem');

        $mode = [];
        if ($type === 'crs') {
            $mode['checkbox'] = 'search_crs';
            $mode['short'] = 'crs';
            $mode['long'] = 'course';
            $mode['lng_type'] = $this->lng->txt('course');
            $mode['view'] = 'crs_members';
        } elseif ($type === 'grp') {
            $mode['checkbox'] = 'search_grp';
            $mode['short'] = 'grp';
            $mode['long'] = 'group';
            $mode['lng_type'] = $this->lng->txt('group');
            $mode['view'] = 'grp_members';
        }

        $this->setTitle($this->lng->txt('members'));
        $this->mode = $mode;

        $this->ctrl->setParameter($a_parent_obj, 'view', $mode['view']);

        if (
            $this->http->wrapper()->query()->has('ref') &&
            $this->http->wrapper()->query()->retrieve('ref', $this->refinery->kindlyTo()->string()) !== ''
        ) {
            $this->ctrl->setParameter(
                $a_parent_obj,
                'ref',
                $this->http->wrapper()->query()->retrieve('ref', $this->refinery->kindlyTo()->string())
            );
        }

        if ($this->http->wrapper()->post()->has($mode['checkbox'])) {
            $ids = $this->http->wrapper()->post()->retrieve(
                $mode['checkbox'],
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );

            if ($ids !== []) {
                $this->ctrl->setParameter(
                    $a_parent_obj,
                    $mode['checkbox'],
                    implode(', ', $ids)
                );
            }
        }

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));

        $this->setRowTemplate('tpl.mail_search_objects_members_row.html', 'Services/Contact');

        $this->addColumn('', '', '1%', true);
        $this->addColumn($this->lng->txt('login'), 'members_login', '22%');
        $this->addColumn($this->lng->txt('name'), 'members_name', '22%');
        $this->addColumn($this->lng->txt($mode['long']), 'members_crs_grp', '22%');
        if (ilBuddySystem::getInstance()->isEnabled()) {
            $this->addColumn($this->lng->txt('buddy_tbl_filter_state'), 'status', '23%');
        }
        $this->addColumn($this->lng->txt('actions'), '', '10%');

        if ($this->context === 'mail') {
            if ($this->mailing_allowed) {
                $this->setSelectAllCheckbox('search_members[]');
                $this->addMultiCommand('mail', $this->lng->txt('mail_members'));
            }
        } else {
            $this->setSelectAllCheckbox('search_members[]');
            $this->lng->loadLanguageModule('wsp');
            $this->addMultiCommand('share', $this->lng->txt('wsp_share_with_members'));
        }
        $this->lng->loadLanguageModule('buddysystem');
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }

    protected function getRequestValue(string $key, \ILIAS\Refinery\Transformation $trafo, $default = null)
    {
        $value = $default;
        if ($this->http->wrapper()->query()->has($key)) {
            $value = $this->http->wrapper()->query()->retrieve($key, $trafo);
        }

        if ($this->http->wrapper()->post()->has($key)) {
            $value = $this->http->wrapper()->post()->retrieve($key, $trafo);
        }

        return $value;
    }

    protected function fillRow(array $a_set): void
    {
        $trafo = $this->refinery->custom()->transformation(function ($value): string {
            if (is_string($value)) {
                return $this->refinery
                    ->custom()
                    ->transformation(fn (string $value): string => ilUtil::stripSlashes($value))
                    ->transform($value);
            }

            return implode(
                ',',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->in()->series([
                        $this->refinery->kindlyTo()->int(),
                        $this->refinery->kindlyTo()->string()
                    ])
                )->transform($value)
            );
        });

        $current_selection_list = new ilAdvancedSelectionListGUI();
        $current_selection_list->setListTitle($this->lng->txt('actions'));
        $current_selection_list->setId('act_' . md5($a_set['members_id'] . '::' . $a_set['search_' . $this->mode['short']]));

        $this->ctrl->setParameter($this->parent_obj, 'search_members', $a_set['members_id']);
        $this->ctrl->setParameter(
            $this->parent_obj,
            'search_' . $this->mode['short'],
            $this->getRequestValue('search_' . $this->mode['short'], $trafo)
        );
        $this->ctrl->setParameter($this->parent_obj, 'view', $this->mode['view']);

        $action_html = '';
        if ($this->context === 'mail') {
            if ($this->mailing_allowed) {
                $current_selection_list->addItem(
                    $this->lng->txt('mail_member'),
                    '',
                    $this->ctrl->getLinkTarget($this->parent_obj, 'mail')
                );
            }
        } else {
            $current_selection_list->addItem(
                $this->lng->txt('wsp_share_with_members'),
                '',
                $this->ctrl->getLinkTarget($this->parent_obj, 'share')
            );
        }

        if ($this->context === 'mail' && ilBuddySystem::getInstance()->isEnabled()) {
            $relation = ilBuddyList::getInstanceByGlobalUser()->getRelationByUserId((int) $a_set['members_id']);
            if (
                $a_set['members_id'] !== $this->user->getId() &&
                $relation->isUnlinked() &&
                ilUtil::yn2tf(ilObjUser::_lookupPref($a_set['members_id'], 'bs_allow_to_contact_me'))
            ) {
                $this->ctrl->setParameterByClass(ilBuddySystemGUI::class, 'user_id', $a_set['members_id']);
                $current_selection_list->addItem(
                    $this->lng->txt('buddy_bs_btn_txt_unlinked_a'),
                    '',
                    $this->ctrl->getLinkTargetByClass(ilBuddySystemGUI::class, 'request')
                );
            }
        }

        if ($current_selection_list->getItems()) {
            $action_html = $current_selection_list->getHTML();
        }
        $this->tpl->setVariable('CURRENT_ACTION_LIST', $action_html);

        foreach ($a_set as $key => $value) {
            $this->tpl->setVariable(strtoupper($key), $value);
        }
    }
}
