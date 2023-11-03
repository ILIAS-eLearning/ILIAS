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

use ILIAS\UI\Implementation\Factory as UIImplementationFactory;
use ILIAS\UI\Renderer as UIRenderer;

/*
 * Abstract base class for course, group participants table guis
 * @author Stefan Meyer <smeyer.ilias@gmx.de
 */
abstract class ilParticipantTableGUI extends ilTable2GUI
{
    protected static bool $export_allowed = false;
    protected static bool $confirmation_required = true;
    /**
     * @var int[] | null
     */
    protected static ?array $accepted_ids = null;
    protected static ?array $all_columns = null;
    protected static bool $has_odf_definitions = false;

    protected ?ilParticipants $participants = null;
    protected array $current_filter = [];
    protected ilObject $rep_object;

    private UIRenderer $renderer;
    private UIImplementationFactory $uiFactory;

    public function __construct(mixed $a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
    {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

        global $DIC;
        $this->renderer = $DIC->ui()->renderer();
        $this->uiFactory = $DIC->ui()->factory();
    }

    /**
     * Init table filter
     */
    public function initFilter(): void
    {
        $this->setDefaultFilterVisiblity(true);

        $login = $this->addFilterItemByMetaType(
            'login',
            ilTable2GUI::FILTER_TEXT,
            false,
            $this->lng->txt('name')
        );
        $this->current_filter['login'] = (string) $login->getValue();
        $this->current_filter['roles'] = 0;
        if ($this->isColumnSelected('roles')) {
            $role = $this->addFilterItemByMetaType(
                'roles',
                ilTable2GUI::FILTER_SELECT,
                false,
                $this->lng->txt('objs_role')
            );

            $options = array();
            $options[0] = $this->lng->txt('all_roles');
            $role->setOptions($options + $this->getParentObject()->getLocalRoles());
            $this->current_filter['roles'] = (int) $role->getValue();
        }

        if ($this->isColumnSelected('org_units')) {
            $paths = ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits();

            $options[0] = $this->lng->txt('select_one');
            foreach ($paths as $org_ref_id => $path) {
                $options[$org_ref_id] = $path;
            }

            $org = $this->addFilterItemByMetaType(
                'org_units',
                ilTable2GUI::FILTER_SELECT,
                false,
                $this->lng->txt('org_units')
            );
            $org->setOptions($options);
            $this->current_filter['org_units'] = $org->getValue();
        }
    }

    public function getSelectableColumns(): array
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $GLOBALS['DIC']['lng']->loadLanguageModule('ps');
        if (self::$all_columns) {
            # return self::$all_columns;
        }

        $ef = ilExportFieldsInfo::_getInstanceByType($this->getRepositoryObject()->getType());
        self::$all_columns = $ef->getSelectableFieldsInfo($this->getRepositoryObject()->getId());

        if ($ilSetting->get('user_portfolios')) {
            self::$all_columns['prtf'] = array(
                'txt' => $this->lng->txt('obj_prtf'),
                'default' => false
            );
        }

        $login = array_splice(self::$all_columns, 0, 1);
        self::$all_columns = array_merge(
            array(
                'roles' =>
                    array(
                        'txt' => $this->lng->txt('objs_role'),
                        'default' => true
                    )
            ),
            self::$all_columns
        );
        self::$all_columns = array_merge($login, self::$all_columns);
        return self::$all_columns;
    }

    protected function getRepositoryObject(): ilObject
    {
        return $this->rep_object;
    }

    protected function getParticipants(): ?\ilParticipants
    {
        return $this->participants;
    }

    public function checkAcceptance(int $a_usr_id): bool
    {
        if (!self::$confirmation_required) {
            return true;
        }
        if (!self::$export_allowed) {
            return false;
        }
        return in_array($a_usr_id, self::$accepted_ids);
    }

    protected function initSettings(): void
    {
        if (self::$accepted_ids !== null) {
            return;
        }
        self::$export_allowed = ilPrivacySettings::getInstance()->checkExportAccess($this->getRepositoryObject()->getRefId());

        self::$confirmation_required = ($this->getRepositoryObject()->getType() === 'crs')
            ? ilPrivacySettings::getInstance()->courseConfirmationRequired()
            : ilPrivacySettings::getInstance()->groupConfirmationRequired();

        self::$accepted_ids = ilMemberAgreement::lookupAcceptedAgreements($this->getRepositoryObject()->getId());

        self::$has_odf_definitions = (bool) ilCourseDefinedFieldDefinition::_hasFields($this->getRepositoryObject()->getId());
    }

    protected function showActionLinks($a_set): void
    {
        $loc_enabled = (
            $this->getRepositoryObject()->getType() === 'crs' and
            $this->getRepositoryObject()->getViewMode() === ilCourseConstants::IL_CRS_VIEW_OBJECTIVE
        );

        $this->ctrl->setParameter($this->parent_obj, 'member_id', $a_set['usr_id']);

        $dropDownItems = array();
        $dropDownItems[] = $this->uiFactory->button()->shy(
            $this->lng->txt('edit'),
            $this->ctrl->getLinkTarget($this->parent_obj, 'editMember')
        );

        if (self::$has_odf_definitions) {
            $this->ctrl->setParameterByClass('ilobjectcustomuserfieldsgui', 'member_id', $a_set['usr_id']);
            $dropDownItems[] = $this->uiFactory->button()->shy(
                $this->lng->txt($this->getRepositoryObject()->getType() . '_cdf_edit_member'),
                $this->ctrl->getLinkTargetByClass('ilobjectcustomuserfieldsgui', 'editMember')
            );
        }

        if ($loc_enabled) {
            $this->ctrl->setParameterByClass('illomembertestresultgui', 'uid', $a_set['usr_id']);
            $dropDownItems[] = $this->uiFactory->button()->shy(
                $this->lng->txt('crs_loc_mem_show_res'),
                $this->ctrl->getLinkTargetByClass('illomembertestresultgui', '')
            );
        }

        $dropDown = $this->uiFactory->dropdown()->standard($dropDownItems)
                ->withLabel($this->lng->txt('actions'));
        $this->tpl->setVariable('ACTION_USER', $this->renderer->render($dropDown));
    }
}
