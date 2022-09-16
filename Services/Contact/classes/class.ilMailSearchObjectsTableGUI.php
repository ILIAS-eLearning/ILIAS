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

class ilMailSearchObjectsTableGUI extends ilTable2GUI
{
    protected ilObjUser $user;
    protected ilRbacSystem $rbacsystem;
    protected object $parentObject;
    protected array $mode;
    protected bool $mailing_allowed;

    /**
     * @param ilMailSearchCoursesGUI|ilMailSearchGroupsGUI $a_parent_obj
     * @param string $type
     * @param string $context
     */
    public function __construct(object $a_parent_obj, string $type = 'crs', string $context = 'mail')
    {
        global $DIC;

        $this->lng = $DIC['lng'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->user = $DIC['ilUser'];
        $this->rbacsystem = $DIC['rbacsystem'];

        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('buddysystem');

        if ($context === 'mail') {
            $mail = new ilMail($this->user->getId());
            $this->mailing_allowed = $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId());
        }

        $mode = [];
        if ($type === 'crs') {
            $mode['short'] = 'crs';
            $mode['long'] = 'course';
            $mode['checkbox'] = 'search_crs';
            $mode['tableprefix'] = 'crstable';
            $mode['lng_mail'] = $this->lng->txt('mail_my_courses');
            $mode['view'] = 'myobjects';
            $this->setTitle($mode['lng_mail']);
        } elseif ($type === 'grp') {
            $mode['short'] = 'grp';
            $mode['long'] = 'group';
            $mode['checkbox'] = 'search_grp';
            $mode['tableprefix'] = 'grptable';
            $mode['lng_mail'] = $this->lng->txt('mail_my_groups');
            $mode['view'] = 'myobjects';
            $this->setTitle($mode['lng_mail']);
        }

        $this->setId('search_' . $mode['short']);
        parent::__construct($a_parent_obj);

        $this->parentObject = $a_parent_obj;
        $this->mode = $mode;
        $this->context = $context;

        $this->ctrl->setParameter($a_parent_obj, 'view', $mode['view']);

        $http = $DIC['http'];
        $refinery = $DIC->refinery();


        if (
            $http->wrapper()->query()->has('ref') &&
            $http->wrapper()->query()->retrieve('ref', $refinery->kindlyTo()->string()) !== ''
        ) {
            $this->ctrl->setParameter(
                $a_parent_obj,
                'ref',
                $http->wrapper()->query()->retrieve('ref', $refinery->kindlyTo()->string())
            );
        }

        if ($http->wrapper()->post()->has($mode['checkbox'])) {
            $ids = $http->wrapper()->post()->retrieve(
                $mode['checkbox'],
                $refinery->kindlyTo()->listOf(
                    $refinery->in()->series([
                        $refinery->kindlyTo()->int(),
                        $refinery->kindlyTo()->string()
                    ])
                )
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
        $this->ctrl->clearParameters($a_parent_obj);

        $this->setSelectAllCheckbox($mode["checkbox"] . '[]');
        $this->setRowTemplate('tpl.mail_search_objects_row.html', 'Services/Contact');

        $this->setShowRowsSelector(true);

        $this->addColumn('', '', '1px', true);
        $this->addColumn($mode["lng_mail"], 'CRS_NAME', '30%');
        $this->addColumn($this->lng->txt('path'), 'CRS_PATH', '30%');
        $this->addColumn($this->lng->txt('crs_count_members'), 'OBJECT_NO_MEMBERS', '20%');
        $this->addColumn($this->lng->txt('actions'), '', '19%');

        if ($context === "mail") {
            if ($this->mailing_allowed) {
                $this->addMultiCommand('mail', $this->lng->txt('mail_members'));
            }
        } elseif ($context === "wsp") {
            $this->lng->loadLanguageModule("wsp");
            $this->addMultiCommand('share', $this->lng->txt('wsp_share_with_members'));
        }
        $this->addMultiCommand('showMembers', $this->lng->txt('mail_list_members'));

        if (
            $http->wrapper()->query()->has('ref') &&
            $http->wrapper()->query()->retrieve('ref', $refinery->to()->string()) === 'mail'
        ) {
            $this->addCommandButton('cancel', $this->lng->txt('cancel'));
        }
    }

    protected function fillRow(array $a_set): void
    {
        if ($a_set['hidden_members']) {
            $this->tpl->setCurrentBlock('caption_asterisk');
            $this->tpl->touchBlock('caption_asterisk');
            $this->tpl->parseCurrentBlock();
        }
        foreach ($a_set as $key => $value) {
            $this->tpl->setVariable(strtoupper($key), $value);
        }
        $this->tpl->setVariable('SHORT', $this->mode["short"]);
    }

    public function numericOrdering(string $a_field): bool
    {
        return $a_field === 'OBJECT_NO_MEMBERS';
    }
}
