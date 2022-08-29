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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\MessageBox;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Dropdown;
use ILIAS\UI\Component\Link;

/**
 * Class ilObjStudyProgrammeAutoMembershipsGUI
 *
 * @author: Nils Haagen  <nils.haagen@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjStudyProgrammeAutoMembershipsGUI: ilPropertyFormGUI
 */
class ilObjStudyProgrammeAutoMembershipsGUI
{
    private const ROLEFOLDER_REF_ID = 8;
    public const CHECKBOX_SOURCE_IDS = 'c_amsids';

    private const F_SOURCE_TYPE = 'f_st';
    private const F_SOURCE_ID = 'f_sid';
    private const F_ORIGINAL_SOURCE_TYPE = 'f_st_org';
    private const F_ORIGINAL_SOURCE_ID = 'f_sid_org';

    private const CMD_VIEW = 'view';
    private const CMD_SAVE = 'save';
    private const CMD_DELETE = 'delete';
    private const CMD_DELETE_CONFIRMATION = 'deleteConfirmation';
    private const CMD_GET_ASYNC_MODAL_OUTPUT = 'getAsynchModalOutput';
    private const CMD_NEXT_STEP = 'nextStep';
    private const CMD_ENABLE = 'enable';
    private const CMD_DISABLE = 'disable';
    private const CMD_PROFILE_NOT_PUBLIC = 'profile_not_public';

    private static array $switch_to_ref_id = [
        ilStudyProgrammeAutoMembershipSource::TYPE_COURSE,
        ilStudyProgrammeAutoMembershipSource::TYPE_GROUP
    ];

    public ilGlobalTemplateInterface $tpl;
    public ilCtrl $ctrl;
    public ilToolbarGUI $toolbar;
    public ilLanguage $lng;
    public ?int $prg_ref_id;
    public ?ilObjStudyProgramme $object = null;
    public ILIAS\UI\Factory $ui_factory;
    protected MessageBox\Factory $message_box_factory;
    protected Button\Factory $button_factory;
    public ILIAS\UI\Renderer $ui_renderer;
    protected Psr\Http\Message\ServerRequestInterface $request;
    protected ilTree $tree;
    protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper;
    protected ILIAS\Refinery\Factory $refinery;
    protected ilContainerGUI $parent_gui;

    public function __construct(
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilToolbarGUI $ilToolbar,
        ilLanguage $lng,
        Factory $ui_factory,
        MessageBox\Factory $message_box_factory,
        Button\Factory $button_factory,
        Renderer $ui_renderer,
        Psr\Http\Message\ServerRequestInterface $request,
        ilTree $tree,
        ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper,
        ILIAS\Refinery\Factory $refinery
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->toolbar = $ilToolbar;
        $this->lng = $lng;
        $this->ui_factory = $ui_factory;
        $this->message_box_factory = $message_box_factory;
        $this->button_factory = $button_factory;
        $this->ui_renderer = $ui_renderer;
        $this->request = $request;
        $this->tree = $tree;
        $this->request_wrapper = $request_wrapper;
        $this->refinery = $refinery;

        // Add this js manually here because the modal contains a form that is
        // loaded asynchronously later on, and this JS won't be pulled then for
        // some reason.
        $tpl->addJavaScript("Services/Form/js/Form.js");
    }
    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_VIEW:
            case self::CMD_DELETE:
            case self::CMD_DELETE_CONFIRMATION:
            case self::CMD_DISABLE:
            case self::CMD_GET_ASYNC_MODAL_OUTPUT:
            case self::CMD_NEXT_STEP:
            case self::CMD_SAVE:
                $this->$cmd();
                break;
            case self::CMD_ENABLE:
                $this->$cmd();
                $this->ctrl->redirect($this, self::CMD_VIEW);
                break;
            case self::CMD_PROFILE_NOT_PUBLIC:
                $this->view(true);
                break;
            default:
                throw new ilException("ilObjStudyProgrammeAutoMembershipsGUI: Command not supported: $cmd");
        }
    }

    protected function nextStep(): void
    {
        $current_src_type = null;
        if ($this->request_wrapper->has(self::F_ORIGINAL_SOURCE_TYPE)) {
            $current_src_type = $this->request_wrapper->retrieve(
                self::F_ORIGINAL_SOURCE_TYPE,
                $this->refinery->to()->string()
            );
        }

        $current_src_id = null;
        if ($this->request_wrapper->has(self::F_ORIGINAL_SOURCE_ID)) {
            $current_src_id = $this->request_wrapper->retrieve(
                self::F_ORIGINAL_SOURCE_ID,
                $this->refinery->to()->int()
            );
        }

        $selected_src_type = $this->request_wrapper->retrieve(self::F_SOURCE_TYPE, $this->refinery->kindlyTo()->string());
        $selected_src = $this->request_wrapper->retrieve(self::F_SOURCE_ID, $this->refinery->kindlyTo()->int());

        $form = $this->getSelectionForm(
            $selected_src_type,
            $selected_src,
            $current_src_type,
            $current_src_id
        );
        $form_id = "form_" . $form->getId();

        $modal = $this->ui_factory->modal()->roundtrip(
            $this->txt('modal_member_auto_select_title'),
            $this->ui_factory->legacy($form->getHtml())
        );

        $submit = $this->ui_factory->button()->primary($this->txt('add'), "#")->withOnLoadCode(
            function ($id) use ($form_id) {
                return "$('#$id').click(function() { $('#$form_id').submit(); return false; });";
            }
        );

        $modal = $modal->withActionButtons([$submit]);

        echo $this->ui_renderer->renderAsync($modal);
        exit;
    }

    /**
     * Render.
     */
    protected function view(bool $profile_not_public = false): void
    {
        if ($profile_not_public) {
            $this->tpl->setOnScreenMessage("info", $this->lng->txt('prg_profile_not_public'));
        }
        $collected_modals = [];
        $modal = $this->getModal();
        $this->getToolbar($modal->getShowSignal());
        $collected_modals[] = $modal;
        $data = [];
        foreach ($this->getObject()->getAutomaticMembershipSources() as $ams) {
            $title = $this->getTitleRepresentation($ams);
            $usr = $this->getUserRepresentation($ams->getLastEditorId());
            $modal = $this->getModal($ams->getSourceType(), $ams->getSourceId());
            $collected_modals[] = $modal;

            $src_id = $ams->getSourceType() . '-' . $ams->getSourceId();
            $actions = $this->getItemAction(
                $src_id,
                $modal->getShowSignal(),
                $ams->isEnabled()
            );

            $data[] = [
                $ams,
                $this->ui_renderer->render($title),
                $this->ui_renderer->render($usr),
                $this->ui_renderer->render($actions)
            ];
        }
        $table = new ilStudyProgrammeAutoMembershipsTableGUI($this, "view", "");
        $table->setData($data);
        $this->tpl->setContent(
            $this->ui_renderer->render($collected_modals)
            . $table->getHTML()
        );
    }

    protected function save(): void
    {
        $form = $this->getForm();
        $form->checkInput();

        $post = $this->request->getParsedBody();
        $form->setValuesByArray($post);
        $src_type = $post[self::F_SOURCE_TYPE];
        $src_id = $post[self::F_SOURCE_ID . $src_type];

        if (
            (is_null($src_type) || $src_type === "") ||
            (is_null($src_id) || $src_id === 0)
        ) {
            $this->tpl->setOnScreenMessage("failure", $this->txt('no_srctype_or_id'), true);
            $this->ctrl->redirect($this, self::CMD_VIEW);
            return;
        }

        if (in_array($src_type, self::$switch_to_ref_id)) {
            $refs = ilObject::_getAllReferences((int) $src_id);
            $src_id = (int) array_shift($refs);
        }

        if (
            array_key_exists(self::F_ORIGINAL_SOURCE_TYPE, $post) &&
            array_key_exists(self::F_ORIGINAL_SOURCE_ID, $post)
        ) {
            $this->getObject()->deleteAutomaticMembershipSource(
                (string) $post[self::F_ORIGINAL_SOURCE_TYPE],
                (int) $post[self::F_ORIGINAL_SOURCE_ID]
            );
        }

        $this->getObject()->storeAutomaticMembershipSource($src_type, (int) $src_id);
        $this->tpl->setOnScreenMessage("success", $this->txt("auto_add_success"), true);
        $this->ctrl->redirect($this, self::CMD_VIEW);
    }

    protected function deleteConfirmation(): void
    {
        $get = $this->request->getQueryParams();
        $post = $this->request->getParsedBody();
        $field = self::CHECKBOX_SOURCE_IDS;

        $field_ids_in_get = array_key_exists($field, $get);
        $field_ids_in_post = array_key_exists($field, $post);

        $type_ids = '';
        $msg = '';
        if ($field_ids_in_get) {
            $type_ids = $get[$field];
            $msg = $this->lng->txt('prg_delete_single_confirmation');
        } elseif ($field_ids_in_post) {
            $type_ids = implode(' ', $post[$field]);
            $msg = $this->lng->txt('prg_delete_confirmation');
        } else {
            $this->tpl->setOnScreenMessage("info", $this->lng->txt('prg_delete_nothing_selected'), true);
            $this->ctrl->redirect($this, self::CMD_VIEW);
        }

        $type_ids = base64_encode($type_ids);

        $this->ctrl->setParameterByClass(self::class, $field, $type_ids);
        $delete = $this->ctrl->getFormActionByClass(self::class, self::CMD_DELETE);
        $cancel = $this->ctrl->getFormActionByClass(self::class, self::CMD_VIEW);
        $this->ctrl->clearParameterByClass(self::class, $field);

        $buttons = [
            $this->button_factory->standard($this->lng->txt('prg_confirm_delete'), $delete),
            $this->button_factory->standard($this->lng->txt('prg_cancel'), $cancel)
        ];

        $message_box = $this->message_box_factory->confirmation($msg)->withButtons($buttons);

        $this->tpl->setContent($this->ui_renderer->render($message_box));
    }

    protected function delete(): void
    {
        $field = self::CHECKBOX_SOURCE_IDS;
        $get = $this->request->getQueryParams();

        if (!array_key_exists($field, $get)) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt('prg_delete_failure'), true);
            $this->ctrl->redirect($this, self::CMD_VIEW);
        }

        $type_ids = base64_decode($get[$field]);
        $type_ids = explode(' ', trim($type_ids));

        foreach ($type_ids as $src_id) {
            [$type, $id] = explode('-', $src_id);
            $this->getObject()->deleteAutomaticMembershipSource((string) $type, (int) $id);
        }

        $msg = $this->lng->txt('prg_delete_single_success');
        if (count($type_ids) > 1) {
            $msg = $this->lng->txt('prg_delete_success');
        }

        $this->tpl->setOnScreenMessage("success", $msg, true);
        $this->ctrl->redirect($this, self::CMD_VIEW);
    }

    /**
     * Enable single entry.
     */
    protected function enable(): void
    {
        $get = $this->request->getQueryParams();
        $field = self::CHECKBOX_SOURCE_IDS;
        if (array_key_exists($field, $get)) {
            [$type, $id] = explode('-', $get[$field]);
            $this->getObject()->enableAutomaticMembershipSource((string) $type, (int) $id);
        }
        $this->ctrl->redirect($this, self::CMD_VIEW);
    }

    /**
     * Disable single entry.
     */
    protected function disable(): void
    {
        $get = $this->request->getQueryParams();
        $field = self::CHECKBOX_SOURCE_IDS;
        if (array_key_exists($field, $get)) {
            [$type, $id] = explode('-', $get[$field]);
            $this->getObject()->disableAutomaticMembershipSource((string) $type, (int) $id);
        }
        $this->ctrl->redirect($this, self::CMD_VIEW);
    }

    /**
     * Set ref-id of StudyProgramme before using this GUI.
     */
    public function setRefId(int $prg_ref_id): void
    {
        $this->prg_ref_id = $prg_ref_id;
    }

    /**
     * Set this GUI's parent gui.
     */
    public function setParentGUI(ilContainerGUI $a_parent_gui): void
    {
        $this->parent_gui = $a_parent_gui;
    }

    /**
     * Get current StudyProgramme-object.
     */
    protected function getObject(): ilObjStudyProgramme
    {
        if ($this->object === null ||
            $this->object->getRefId() !== $this->prg_ref_id
        ) {
            $this->object = ilObjStudyProgramme::getInstanceByRefId($this->prg_ref_id);
        }
        return $this->object;
    }

    protected function getModal(string $source_type = null, int $source_id = null): RoundTrip
    {
        $this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_TYPE, $source_type);
        $this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_ID, $source_id);
        $link = $this->ctrl->getLinkTarget($this, "getAsynchModalOutput", "", true);
        $this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_TYPE, null);
        $this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_ID, null);

        return $this->ui_factory->modal()->roundtrip(
            '',
            []
        )->withAsyncRenderUrl(
            $link
        );
    }

    protected function getAsynchModalOutput(): void
    {
        $current_src_type = null;
        if ($this->request_wrapper->has(self::F_ORIGINAL_SOURCE_TYPE)) {
            $current_src_type = $this->request_wrapper->retrieve(
                self::F_ORIGINAL_SOURCE_TYPE,
                $this->refinery->to()->string()
            );
        }

        $current_src_id = null;
        if ($this->request_wrapper->has(self::F_ORIGINAL_SOURCE_ID)) {
            $current_src_id = $this->request_wrapper->retrieve(self::F_ORIGINAL_SOURCE_ID, $this->refinery->to()->int());
        }

        $form = $this->getForm($current_src_type, $current_src_id);
        $form_id = "form_" . $form->getId();

        $modal = $this->ui_factory->modal()->roundtrip(
            $this->txt('modal_member_auto_select_title'),
            $this->ui_factory->legacy($form->getHtml())
        );

        $this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_TYPE, $current_src_type);
        $this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_ID, $current_src_id);
        $link = $this->ctrl->getLinkTarget($this, "nextStep", "", true);
        $this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_TYPE, null);
        $this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_ID, null);

        $replaceSignal = $modal->getReplaceSignal();
        $signal_id = $replaceSignal->getId();
        $f_selected_type = self::F_SOURCE_TYPE;
        $f_selected_id = self::F_SOURCE_ID;
        $submit = $this->ui_factory->button()->primary($this->txt('search'), "#")->withOnLoadCode(
            function ($id) use ($form_id, $link, $signal_id, $f_selected_type, $f_selected_id) {
                return
                    "$('#$id').click(function() { 
						var checked = $(\"input[name='$f_selected_type']:checked\"). val();
						if(checked == 'orgu' || typeof(checked) == \"undefined\") {
							$('#$form_id').submit();
							return false;
						}
						
						var i_value = $(\"input[name='$f_selected_id\" + checked + \"']\"). val();
						if(i_value == '' || typeof(i_value) == \"undefined\") {
							$('#$form_id').submit();
							return false;
						}
						
						n_url = '$link' + '&$f_selected_type=' + checked + '&$f_selected_id=' + i_value;
						$('#$id').attr(\"onclick\", function(event) {
							$(this).trigger('$signal_id',
								{
									'id' : '$signal_id', 'event' : 'click',
									'triggerer' : $(this),
									'options' : JSON.parse('{\"url\":\"' + n_url + '\"}')
								}
							);
						});
						return false;
					}
				);"
                ;
            }
        );

        $modal = $modal->withActionButtons([$submit]);

        echo $this->ui_renderer->renderAsync($modal);
        exit;
    }

    protected function getForm(string $source_type = null, int $source_id = null): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        if (is_null($source_type)) {
            $source_type = "";
        }
        /** @var string $source_id */
        // TODO: This should not be necessary anymore when we change to new forms.
        $source_id = (string) $source_id ?? "";

        $form->setId(uniqid($source_type . $source_id, true));
        $form->setFormAction($this->ctrl->getFormAction($this, 'save'));

        $rgroup = new ilRadioGroupInputGUI($this->txt('membership_source_type'), self::F_SOURCE_TYPE);
        $rgroup->setValue($source_type);
        $form->addItem($rgroup);

        $radio_role = new ilRadioOption(
            $this->txt('select_' . ilStudyProgrammeAutoMembershipSource::TYPE_ROLE),
            ilStudyProgrammeAutoMembershipSource::TYPE_ROLE
        );

        $ni_role = new ilTextInputGUI(
            $this->txt('label_' . ilStudyProgrammeAutoMembershipSource::TYPE_ROLE),
            self::F_SOURCE_ID . ilStudyProgrammeAutoMembershipSource::TYPE_ROLE
        );
        $radio_role->addSubItem($ni_role);
        $rgroup->addOption($radio_role);

        $radio_grp = new ilRadioOption(
            $this->txt('select_' . ilStudyProgrammeAutoMembershipSource::TYPE_GROUP),
            ilStudyProgrammeAutoMembershipSource::TYPE_GROUP
        );
        $ni_grp = new ilTextInputGUI(
            $this->txt('label_' . ilStudyProgrammeAutoMembershipSource::TYPE_GROUP),
            self::F_SOURCE_ID . ilStudyProgrammeAutoMembershipSource::TYPE_GROUP
        );
        $radio_grp->addSubItem($ni_grp);
        $rgroup->addOption($radio_grp);

        $radio_crs = new ilRadioOption(
            $this->txt('select_' . ilStudyProgrammeAutoMembershipSource::TYPE_COURSE),
            ilStudyProgrammeAutoMembershipSource::TYPE_COURSE
        );
        $ni_crs = new ilTextInputGUI(
            $this->txt('label_' . ilStudyProgrammeAutoMembershipSource::TYPE_COURSE),
            self::F_SOURCE_ID . ilStudyProgrammeAutoMembershipSource::TYPE_COURSE
        );
        $radio_crs->addSubItem($ni_crs);
        $rgroup->addOption($radio_crs);

        $radio_orgu = new ilRadioOption(
            $this->txt('select_' . ilStudyProgrammeAutoMembershipSource::TYPE_ORGU),
            ilStudyProgrammeAutoMembershipSource::TYPE_ORGU
        );
        $orgu = new ilRepositorySelector2InputGUI(
            "",
            self::F_SOURCE_ID . ilStudyProgrammeAutoMembershipSource::TYPE_ORGU,
            false
        );
        $orgu->getExplorerGUI()->setSelectableTypes(["orgu"]);
        $orgu->getExplorerGUI()->setTypeWhiteList(["root", "orgu"]);

        $orgu->getExplorerGUI()->setRootId(ilObjOrgUnit::getRootOrgRefId());
        $orgu->getExplorerGUI()->setAjax(false);
        $radio_orgu->addSubItem($orgu);
        $rgroup->addOption($radio_orgu);

        if (
            !is_null($source_type) &&
            !is_null($source_id) &&
            $source_type !== "" &&
            $source_id !== ""
        ) {
            switch ($source_type) {
                case ilStudyProgrammeAutoMembershipSource::TYPE_ROLE:
                    $ni_role->setValue($source_id);
                    break;
                case ilStudyProgrammeAutoMembershipSource::TYPE_GROUP:
                    $ni_grp->setValue($source_id);
                    break;
                case ilStudyProgrammeAutoMembershipSource::TYPE_COURSE:
                    $ni_crs->setValue($source_id);
                    break;
                case ilStudyProgrammeAutoMembershipSource::TYPE_ORGU:
                    $orgu->setValue($source_id);
                    break;
                default:
            }
        }

        $hi = new ilHiddenInputGUI(self::F_ORIGINAL_SOURCE_TYPE);
        $hi->setValue($source_type);
        $form->addItem($hi);

        $hi = new ilHiddenInputGUI(self::F_ORIGINAL_SOURCE_ID);
        $hi->setValue($source_id);
        $form->addItem($hi);

        return $form;
    }

    protected function getSelectionForm(
        string $selected_source_type,
        string $selected_source,
        string $source_type = null,
        int $source_id = null
    ): ilPropertyFormGUI {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "save"));

        $query_parser = $this->parseQueryString($selected_source);
        $object_search = new ilLikeObjectSearch($query_parser);
        $object_search->setFilter(array($selected_source_type));
        $entries = $object_search->performSearch()->getEntries();

        $rgoup = new ilRadioGroupInputGUI(
            $this->txt("prg_auto_member_select_" . $selected_source_type),
            self::F_SOURCE_ID . $selected_source_type
        );
        $form->addItem($rgoup);
        foreach ($entries as $entry) {
            $obj_id = (int) $entry['obj_id'];
            $title = ilObject::_lookupTitle($obj_id);
            $description = ilObject::_lookupDescription($obj_id);

            $option = new ilRadioOption($title, (string) $obj_id, $description);
            $rgoup->addOption($option);
        }

        $hi = new ilHiddenInputGUI(self::F_ORIGINAL_SOURCE_TYPE);
        $hi->setValue($source_type);
        $form->addItem($hi);

        $hi = new ilHiddenInputGUI(self::F_ORIGINAL_SOURCE_ID);
        $hi->setValue((string) $source_id);
        $form->addItem($hi);

        $hi = new ilHiddenInputGUI(self::F_SOURCE_TYPE);
        $hi->setValue($selected_source_type);
        $form->addItem($hi);

        return $form;
    }

    /**
     * @return ilQueryParser|string
     */
    protected function parseQueryString(string $string)
    {
        $query_parser = new ilQueryParser(ilUtil::stripSlashes($string));
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
        $query_parser->setMinWordLength(1);
        $query_parser->setGlobalMinLength(3); // #14768
        $query_parser->parse();

        if (!$query_parser->validate()) {
            return $query_parser->getMessage();
        }
        return $query_parser;
    }

    /**
     * Setup toolbar.
     */
    protected function getToolbar(Signal $add_cat_signal): void
    {
        $btn = $this->ui_factory->button()->primary($this->txt('add_automembership_source'), '')
            ->withOnClick($add_cat_signal);
        $this->toolbar->addComponent($btn);
    }



    protected function getItemAction(
        string $src_id,
        Signal $signal,
        bool $is_enabled
    ): Dropdown\Standard {
        $items = [];

        $items[] = $this->ui_factory->button()->shy($this->txt('edit'), '')
            ->withOnClick($signal);

        $this->ctrl->setParameter($this, self::CHECKBOX_SOURCE_IDS, $src_id);

        if ($is_enabled) {
            $items[] = $this->ui_factory->button()->shy(
                $this->txt('deactivate'),
                $this->ctrl->getLinkTarget($this, self::CMD_DISABLE)
            );
        } else {
            $items[] = $this->ui_factory->button()->shy(
                $this->txt('activate'),
                $this->ctrl->getLinkTarget($this, self::CMD_ENABLE)
            );
        }

        $items[] = $this->ui_factory->button()->shy(
            $this->txt('delete'),
            $this->ctrl->getLinkTarget($this, self::CMD_DELETE_CONFIRMATION)
        );

        $this->ctrl->clearParameters($this);

        return $this->ui_factory->dropdown()->standard($items);
    }

    protected function getUserRepresentation(int $usr_id): Link\Standard
    {
        $username = ilObjUser::_lookupName($usr_id);
        $editor = implode(' ', [
            $username['firstname'],
            $username['lastname'],
            '(' . $username['login'] . ')'
        ]);
        $usr = ilObjectFactory::getInstanceByObjId($usr_id);
        $url = ilLink::_getStaticLink($usr_id, 'usr');

        if (!$usr->hasPublicProfile()) {
            $url = $this->ctrl->getLinkTarget($this, self::CMD_PROFILE_NOT_PUBLIC);
        }
        return $this->ui_factory->link()->standard($editor, $url);
    }


    protected function getTitleRepresentation(
        ilStudyProgrammeAutoMembershipSource $ams
    ): Link\Standard {
        $src_id = $ams->getSourceId();

        switch ($ams->getSourceType()) {
            case ilStudyProgrammeAutoMembershipSource::TYPE_ROLE:
                $title = ilObjRole::_lookupTitle($src_id) ?? "-";
                $this->ctrl->setParameterByClass('ilObjRoleGUI', 'obj_id', $src_id);
                $this->ctrl->setParameterByClass('ilObjRoleGUI', 'ref_id', self::ROLEFOLDER_REF_ID);
                $this->ctrl->setParameterByClass('ilObjRoleGUI', 'admin_mode', 'settings');
                $url = $this->ctrl->getLinkTargetByClass(['ilAdministrationGUI', 'ilObjRoleGUI'], 'userassignment');
                $this->ctrl->clearParametersByClass('ilObjRoleGUI');
                break;

            case ilStudyProgrammeAutoMembershipSource::TYPE_COURSE:
                $url = ilLink::_getStaticLink($src_id, 'crs');
                // no break
            case ilStudyProgrammeAutoMembershipSource::TYPE_GROUP:
                $url = ilLink::_getStaticLink($src_id, 'grp');
                $hops = array_map(
                    static function (array $c): string {
                        return ilObject::_lookupTitle((int) $c["obj_id"]);
                    },
                    $this->tree->getPathFull($src_id)
                );
                $hops = array_slice($hops, 1);
                $title = implode(' > ', $hops) ?? "-";
                break;

            case ilStudyProgrammeAutoMembershipSource::TYPE_ORGU:
                $hops = array_map(
                    static function (array $c): string {
                        return ilObject::_lookupTitle($c["obj_id"]);
                    },
                    $this->tree->getPathFull($src_id)
                );
                $hops = array_slice($hops, 3);
                $title = implode(' > ', $hops) ?? "-";
                $url = ilLink::_getStaticLink($src_id, $ams->getSourceType());
                break;
            default:
                throw new LogicException("This should not happen. Forgot a case in the switch?");
        }

        return $this->ui_factory->link()->standard($title, $url);
    }

    protected function txt(string $code): string
    {
        return $this->lng->txt($code);
    }
}
