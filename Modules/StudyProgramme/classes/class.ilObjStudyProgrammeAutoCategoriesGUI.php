<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Dropdown\Standard;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\MessageBox;
use ILIAS\UI\Component\Button;

/**
 * Class ilObjStudyProgrammeAutoCategoriesGUI
 *
 * @author: Nils Haagen <nils.haagen@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjStudyProgrammeAutoCategoriesGUI: ilPropertyFormGUI
 */
class ilObjStudyProgrammeAutoCategoriesGUI
{
    const F_CATEGORY_REF = 'f_cr';
    const F_CATEGORY_ORIGINAL_REF = 'f_cr_org';
    const CHECKBOX_CATEGORY_REF_IDS = 'c_catids';
    const CMD_VIEW = 'view';
    const CMD_SAVE = 'save';
    const CMD_GET_ASYNC_MODAL = 'getAsyncModalOutput';
    const CMD_DELETE = 'delete';
    const CMD_DELETE_CONFIRMATION = 'deleteConfirmation';

    /**
     * @var ilTemplate
     */
    public $tpl;

    /**
     * @var ilCtrl
     */
    public $ctrl;

    /**
     * @var ilToolbarGUI
     */
    public $toolbar;

    /**
     * @var ilLng
     */
    public $lng;

    /**
     * @var int | null
     */
    public $prg_ref_id;

    /**
     * @var ilObjStudyProgramme | null
     */
    public $object;

    /**
     * @var MessageBox\Factory
     */
    protected $message_box_factory;

    /**
     * @var Button\Factory
     */
    protected $button_factory;

    /**
     * @var ILIAS\UI\Factory
     */
    public $ui_factory;

    /**
     * @var ILIAS\UI\Renderer
     */
    public $ui_renderer;

    /**
     * @var Psr\Http\Message\ServerRequestInterface
     */
    protected $request;


    public function __construct(
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilToolbarGUI $ilToolbar,
        ilLanguage $lng,
        Factory $ui_factory,
        MessageBox\Factory $message_box_factory,
        Button\Factory $button_factory,
        Renderer $ui_renderer,
        ServerRequest $request,
        ilTree $tree
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
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case "ilpropertyformgui":
                $form = $this->getForm($this->creation_mode ? self::MODE_CREATE : self::MODE_EDIT);
                $this->ctrl->forwardCommand($form);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_VIEW:
                    case self::CMD_DELETE:
                    case self::CMD_DELETE_CONFIRMATION:
                    case self::CMD_GET_ASYNC_MODAL:
                        $this->$cmd();
                        break;
                    case self::CMD_SAVE:
                        $this->$cmd();
                        $this->ctrl->redirect($this, 'view');
                        break;
                    default:
                        throw new ilException("ilObjStudyProgrammeAutoCategoriesGUI: " .
                                              "Command not supported: $cmd");
                }
        }
    }

    /**
     * Render.
     */
    protected function view()
    {
        $collected_modals = [];

        $modal = $this->getModal();
        $this->getToolbar($modal->getShowSignal());
        $collected_modals[] = $modal;

        $data = [];
        foreach ($this->getObject()->getAutomaticContentCategories() as $ac) {
            $ref_id = $ac->getCategoryRefId();
            if (ilObject::_lookupType($ref_id, true) !== 'cat' || $this->tree->isDeleted($ref_id)) {
                continue;
            }
            $title = $this->getItemPath($ref_id);
            $usr = $this->getUserRepresentation($ac->getLastEditorId());
            $modal = $this->getModal($ref_id);
            $collected_modals[] = $modal;
            $actions = $this->getItemAction(
                $ac->getCategoryRefId(),
                $modal->getShowSignal()
            );

            $data[] = [
                $ac,
                $this->ui_renderer->render($title),
                $this->ui_renderer->render($usr),
                $this->ui_renderer->render($actions)
            ];
        }
        $table = new ilStudyProgrammeAutoCategoriesTableGUI($this, "view", "");
        $table->setData($data);

        $this->tpl->setContent(
            $this->ui_renderer->render($collected_modals)
            . $table->getHTML()
        );
    }

    /**
     * Store data from (modal-)form.
     */
    protected function save()
    {
        $form = $this->getForm();
        $form->setValuesByPost();
        $form->checkInput();

        $cat_ref_id = $form->getInput(self::F_CATEGORY_REF);
        $current_ref_id = $form->getInput(self::F_CATEGORY_ORIGINAL_REF);

        if (ilObject::_lookupType((int) $cat_ref_id, true) !== 'cat') {
            \ilUtil::sendFailure(sprintf($this->lng->txt('not_a_valid_cat_id'), $cat_ref_id), true);
            return;
        }

        if (
            !is_null($current_ref_id) &&
            $current_ref_id !== $cat_ref_id
        ) {
            $ids = [(int) $current_ref_id];
            $this->getObject()->deleteAutomaticContentCategories($ids);
        }


        $this->getObject()->storeAutomaticContentCategory(
            (int) $cat_ref_id
        );
    }

    protected function deleteConfirmation()
    {
        $get = $this->request->getQueryParams();
        $post = $this->request->getParsedBody();
        $field = self::CHECKBOX_CATEGORY_REF_IDS;

        $field_ids_in_get = array_key_exists($field, $get);
        $field_ids_in_post = array_key_exists($field, $post);

        if ($field_ids_in_get) {
            $cat_ids = $get[$field];
            $msg = $this->lng->txt('prg_delete_single_confirmation');
        } elseif ($field_ids_in_post) {
            $cat_ids = implode(' ', $post[$field]);
            $msg = $this->lng->txt('prg_delete_confirmation');
        } else {
            ilUtil::sendInfo($this->lng->txt('prg_delete_nothing_selected'), true);
            $this->ctrl->redirect($this, self::CMD_VIEW);
        }

        $cat_ids = base64_encode($cat_ids);

        $this->ctrl->setParameterByClass(self::class, $field, $cat_ids);
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

    protected function delete()
    {
        $field = self::CHECKBOX_CATEGORY_REF_IDS;
        $get = $this->request->getQueryParams();

        if (!array_key_exists($field, $get)) {
            ilUtil::sendFailure($this->lng->txt('prg_delete_failure'), true);
            $this->ctrl->redirect($this, self::CMD_VIEW);
        }

        $cat_ids = base64_decode($get[$field]);
        $cat_ids = explode(' ', trim($cat_ids));
        $cat_ids = array_map('intval', $cat_ids);

        $this->getObject()->deleteAutomaticContentCategories($cat_ids);

        $msg = $this->lng->txt('prg_delete_single_success');
        if (count($cat_ids) > 1) {
            $msg = $this->lng->txt('prg_delete_success');
        }

        ilUtil::sendSuccess($msg, true);
        $this->ctrl->redirect($this, self::CMD_VIEW);
    }

    /**
     * Set ref-id of StudyProgramme before using this GUI.
     * @param int $prg_ref_id
     */
    public function setRefId(int $prg_ref_id)
    {
        $this->prg_ref_id = $prg_ref_id;
    }

    /**
     * Get current StudyProgramme-object.
     * @return ilObjStudyProgramme
     */
    protected function getObject() : ilObjStudyProgramme
    {
        if ($this->object === null ||
            (int) $this->object->getRefId() !== $this->prg_ref_id
        ) {
            $this->object = ilObjStudyProgramme::getInstanceByRefId($this->prg_ref_id);
        }
        return $this->object;
    }

    protected function getModal($current_ref_id = null)
    {
        if (!is_null($current_ref_id)) {
            $this->ctrl->setParameter($this, self::CHECKBOX_CATEGORY_REF_IDS, $current_ref_id);
        }
        $link = $this->ctrl->getLinkTarget($this, "getAsyncModalOutput", "", true);
        $this->ctrl->setParameter($this, self::CHECKBOX_CATEGORY_REF_IDS, null);
        $modal = $this->ui_factory->modal()->roundtrip(
            '',
            []
        )->withAsyncRenderUrl(
            $link
        );

        return $modal;
    }

    protected function getAsyncModalOutput()
    {
        $current_ref_id = null;
        if (array_key_exists(self::CHECKBOX_CATEGORY_REF_IDS, $_GET)) {
            $current_ref_id = $_GET[self::CHECKBOX_CATEGORY_REF_IDS];
        }
        $form = $this->getForm($current_ref_id);
        $form_id = "form_" . $form->getId();
        $submit = $this->ui_factory->button()->primary($this->lng->txt('search'), "#")->withOnLoadCode(
            function ($id) use ($form_id) {
                return "$('#{$id}').click(function() { $('#{$form_id}').submit(); return false; });";
            }
        );
        $modal = $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('modal_categories_title'),
            $this->ui_factory->legacy($form->getHtml())
        )->withActionButtons([$submit]);

        echo $this->ui_renderer->renderAsync($modal);
        exit;
    }

    protected function getForm($current_ref_id = null)
    {
        $form = new ilPropertyFormGUI();

        if (is_null($current_ref_id)) {
            $current_ref_id = "";
        }
        $form->setId(uniqid((string) $current_ref_id));

        $form->setFormAction($this->ctrl->getFormAction($this, "save"));
        $cat = new ilRepositorySelector2InputGUI(
            $this->lng->txt("category"),
            self::F_CATEGORY_REF,
            false
        );
        $cat->getExplorerGUI()->setSelectableTypes(["cat"]);
        $cat->getExplorerGUI()->setTypeWhiteList(["root", "cat"]);
        if ($current_ref_id != "") {
            $cat->getExplorerGUI()->setPathOpen($current_ref_id);
            $cat->setValue($current_ref_id);
        }
        $cat->getExplorerGUI()->setRootId(ROOT_FOLDER_ID);
        $cat->getExplorerGUI()->setAjax(false);
        $form->addItem($cat);

        $hi = new ilHiddenInputGUI(self::F_CATEGORY_ORIGINAL_REF);
        $hi->setValue($current_ref_id);
        $form->addItem($hi);

        return $form;
    }

    /**
     * Setup toolbar.
     */
    protected function getToolbar(Signal $add_cat_signal)
    {
        $btn = $this->ui_factory->button()->primary($this->lng->txt('add_category'), '')
                                ->withOnClick($add_cat_signal);
        $this->toolbar->addComponent($btn);
    }

    protected function getItemAction(
        int $cat_ref_id,
        Signal $signal
    ) : Standard {
        $items = [];
        $items[] = $this->ui_factory
            ->button()
            ->shy($this->lng->txt('edit'), '')
            ->withOnClick($signal)
        ;

        $this->ctrl->setParameterByClass(self::class, self::CHECKBOX_CATEGORY_REF_IDS, $cat_ref_id);
        $link = $this->ctrl->getLinkTarget($this, self::CMD_DELETE_CONFIRMATION);
        $this->ctrl->clearParameterByClass(self::class, self::CHECKBOX_CATEGORY_REF_IDS);

        $items[] = $this->ui_factory
            ->button()
            ->shy($this->lng->txt('delete'), $link)
        ;

        $dd = $this->ui_factory->dropdown()->standard($items);

        return $dd;
    }

    protected function getUserRepresentation(int $usr_id) : Shy
    {
        $username = ilObjUser::_lookupName($usr_id);
        $editor = implode(' ', [
            $username['firstname'],
            $username['lastname'],
            '(' . $username['login'] . ')'
        ]);
        $url = ilLink::_getStaticLink($usr_id, 'usr');
        return $this->ui_factory->button()->shy($editor, $url);
    }

    protected function getItemPath(int $cat_ref_id) : Shy
    {
        $url = ilLink::_getStaticLink($cat_ref_id, 'cat');

        $hops = array_map(
            function ($c) {
                return ilObject::_lookupTitle($c["obj_id"]);
            },
            $this->tree->getPathFull($cat_ref_id)
        );
        $path = implode(' > ', $hops);

        return $this->ui_factory->button()->shy($path, $url);
    }
}
