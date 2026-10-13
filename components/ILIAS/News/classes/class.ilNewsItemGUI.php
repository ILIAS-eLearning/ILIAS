<?php

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

use ILIAS\News\Table\Action\DeleteNewsItemTableAction;
use ILIAS\News\Table\Action\EditNewsItemTableAction;
use ILIAS\News\Access\NewsAccess;
use ILIAS\News\Common\HttpService;
use ILIAS\News\Common\Table\TableActions;
use ILIAS\News\Domain\NewsCollectionService;
use ILIAS\News\StandardGUIRequest;
use ILIAS\News\Table\NewsItemTable;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * User Interface for NewsItem entities.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNewsItemGUI
{
    public const FORM_EDIT = 0;
    public const FORM_CREATE = 1;
    public const FORM_RE_CREATE = 2;
    protected NewsAccess $news_access;
    protected ?ilNewsItem $news_item = null;

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilObjUser $user;
    protected ilToolbarGUI $toolbar;
    protected Renderer $renderer;
    protected Factory $ui_factory;
    private Refinery $refinery;
    protected ilSetting $setting;
    protected ServerRequestInterface $http_request;
    protected ilUIFilterService $filter_service;
    protected NewsCollectionService $news_collection_service;
    protected DataFactory $data_factory;
    protected HttpService $http_service;

    protected bool $enable_edit = false;
    protected int $context_obj_id = 0;
    protected string $context_obj_type = '';
    protected int $context_sub_obj_id = 0;
    protected string $context_sub_obj_type = '';
    protected int $requested_ref_id;
    protected int $requested_news_item_id;
    protected string $add_mode;
    protected StandardGUIRequest $std_request;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
        $this->toolbar = $DIC->toolbar();
        $this->ctrl = $DIC->ctrl();
        $this->renderer = $DIC->ui()->renderer();
        $this->ui_factory = $DIC->ui()->factory();
        $this->http_request = $DIC->http()->request();
        $this->setting = $DIC->settings();
        $this->filter_service = $DIC->uiService()->filter();
        $this->news_collection_service = $DIC->news()->internal()->domain()->collection();
        $this->refinery = $DIC->refinery();
        $this->http_service = new HttpService($DIC->http(), $this->refinery);
        $this->data_factory = new DataFactory();

        $this->std_request = $DIC->news()
            ->internal()
            ->gui()
            ->standardRequest();

        $query = $DIC->http()->wrapper()->query();

        $this->requested_ref_id = $this->std_request->getRefId();
        $this->requested_news_item_id = $query->has('news_item_id')
            ? $query->retrieve('news_item_id', $this->refinery->kindlyTo()->int())
            : 0;
        $this->add_mode = $query->has('add_mode')
            ? $query->retrieve('add_mode', $this->refinery->kindlyTo()->string())
            : '';

        $this->news_access = new NewsAccess($this->requested_ref_id);

        if ($this->requested_news_item_id > 0) {
            $this->news_item = new ilNewsItem($this->requested_news_item_id);
        }

        $this->ctrl->saveParameter($this, ['news_item_id']);

        // Init EnableEdit.
        $this->setEnableEdit(false);

        // Init Context.
        $this->setContextObjId($this->ctrl->getContextObjId());
        $this->setContextObjType($this->ctrl->getContextObjType());
        //$this->setContextSubObjId($ilCtrl->getContextSubObjId());
        //$this->setContextSubObjType($ilCtrl->getContextSubObjType());

        $this->lng->loadLanguageModule('news');

        $this->ctrl->saveParameter($this, 'add_mode');
    }

    public function executeCommand(): string
    {
        // check, if news item id belongs to context
        if (
            ($this->news_item?->getId() ?? 0) > 0
            && ilNewsItem::_lookupContextObjId($this->news_item->getId()) !== $this->getContextObjId()
        ) {
            throw new ilException('News ID does not match object context.');
        }

        // get next class and command
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = "{$this->ctrl->getCmd()}Cmd";

        switch ($next_class) {
            default:
                $html = $this->$cmd();
                break;
        }

        return $html;
    }

    public function setEnableEdit(bool $a_enable_edit = false): void
    {
        $this->enable_edit = $a_enable_edit;
    }

    public function getEnableEdit(): bool
    {
        return $this->enable_edit;
    }

    public function setContextObjId(int $a_context_obj_id): void
    {
        $this->context_obj_id = $a_context_obj_id;
    }

    public function getContextObjId(): int
    {
        return $this->context_obj_id;
    }

    public function setContextObjType(string $a_context_obj_type): void
    {
        $this->context_obj_type = $a_context_obj_type;
    }

    public function getContextObjType(): string
    {
        return $this->context_obj_type;
    }

    public function setContextSubObjId(int $a_context_sub_obj_id): void
    {
        $this->context_sub_obj_id = $a_context_sub_obj_id;
    }

    public function getContextSubObjId(): int
    {
        return $this->context_sub_obj_id;
    }

    public function setContextSubObjType(string $a_context_sub_obj_type): void
    {
        $this->context_sub_obj_type = $a_context_sub_obj_type;
    }

    public function getContextSubObjType(): string
    {
        return $this->context_sub_obj_type;
    }

    public function createNewsItemCmd(): string
    {
        return $this->initFormNewsItem(self::FORM_CREATE)->getHTML();
    }

    public function editNewsItemCmd(): string
    {
        $form = $this->initFormNewsItem(self::FORM_EDIT);
        $this->getValuesNewsItem($form);
        return $form->getHTML();
    }

    protected function initFormNewsItem(int $a_mode): ilPropertyFormGUI
    {
        $this->tabs->clearTargets();
        $form = self::getEditForm($a_mode, $this->requested_ref_id);
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    public static function getEditForm(
        int $a_mode,
        int $a_ref_id
    ): ilPropertyFormGUI {
        global $DIC;

        $lng = $DIC->language();

        $lng->loadLanguageModule('news');

        $form = new ilPropertyFormGUI();

        // Property Title
        $text_input = new ilTextInputGUI($lng->txt('news_news_item_title'), 'news_title');
        $text_input->setInfo('');
        $text_input->setRequired(true);
        $text_input->setMaxLength(200);
        $form->addItem($text_input);

        // Property Content
        $text_area = new ilTextAreaInputGUI($lng->txt('news_news_item_content'), 'news_content');
        $text_area->setInfo('');
        $text_area->setRequired(false);
        $text_area->setRows(4);
        $form->addItem($text_area);

        // Property Visibility
        $radio_group = new ilRadioGroupInputGUI($lng->txt('news_news_item_visibility'), 'news_visibility');
        $radio_option = new ilRadioOption($lng->txt('news_visibility_users'), 'users');
        $radio_group->addOption($radio_option);
        $radio_option = new ilRadioOption($lng->txt('news_visibility_public'), 'public');
        $radio_group->addOption($radio_option);
        $radio_group->setInfo($lng->txt('news_news_item_visibility_info'));
        $radio_group->setRequired(false);
        $radio_group->setValue('users');
        $form->addItem($radio_group);

        // media
        $media = new ilFileInputGUI($lng->txt('news_media'), 'media');
        $media->setSuffixes(['jpeg', 'jpg', 'png', 'gif', 'mp4', 'mp3', 'pdf']);
        $media->setRequired(false);
        $media->setAllowDeletion(true);
        $media->setValue(' ');
        $form->addItem($media);

        // save and cancel commands
        if (in_array($a_mode, [self::FORM_CREATE, self::FORM_RE_CREATE])) {
            $form->addCommandButton('saveNewsItem', $lng->txt('save'), 'news_btn_create');
            $form->addCommandButton('cancelSaveNewsItem', $lng->txt('cancel'), 'news_btn_cancel_create');
        } else {
            $form->addCommandButton('updateNewsItem', $lng->txt('save'), 'news_btn_update');
            $form->addCommandButton('cancelUpdateNewsItem', $lng->txt('cancel'), 'news_btn_cancel_update');
        }

        $form->setTitle($lng->txt('news_news_item_head'));

        $news_set = new ilSetting('news');
        if (!$news_set->get('enable_rss_for_internal')) {
            $form->removeItemByPostVar('news_visibility');
            return $form;
        }

        $nv = $form->getItemByPostVar('news_visibility');
        if ($nv instanceof ilRadioGroupInputGUI) {
            $nv->setValue(ilNewsItem::_getDefaultVisibilityForRefId($a_ref_id));
        }

        return $form;
    }

    // FORM NewsItem: Get current values for NewsItem form.
    public function getValuesNewsItem(ilPropertyFormGUI $a_form): void
    {
        $values = [];

        $values['news_title'] = $this->news_item->getTitle();
        $values['news_content'] = $this->news_item->getContent() . $this->news_item->getContentLong();
        $values['news_visibility'] = $this->news_item->getVisibility();
        //$values['news_content_long'] = $this->news_item->getContentLong();
        $values['news_content_long'] = '';

        $a_form->setValuesByArray($values);

        if ($this->news_item->getMobId() > 0) {
            $fi = $a_form->getItemByPostVar('media');
            $fi->setValue(ilObject::_lookupTitle($this->news_item->getMobId()));
        }
    }

    // FORM NewsItem: Save NewsItem.
    public function saveNewsItemCmd(): string
    {
        if (!$this->news_access->canAdd()) {
            return '';
        }

        $form = $this->initFormNewsItem(self::FORM_CREATE);
        if ($form->checkInput()) {
            $this->news_item = new ilNewsItem();
            $this->news_item->setTitle($form->getInput('news_title'));
            $this->news_item->setContent($form->getInput('news_content'));
            $this->news_item->setVisibility($form->getInput('news_visibility'));

            $media_paths = $this->getUploadedMediaTempPath();
            if (isset($media_paths['client_filename'], $media_paths['client_filename'])) {
                $mob = ilObjMediaObject::_saveTempFileAsMediaObject(
                    $media_paths['client_filename'],
                    $media_paths['tmp_path'],
                    true
                );
                $this->news_item->setMobId($mob->getId());
            }

            $this->news_item->setContentLong('');
            if (self::isRteActivated()) {
                $this->news_item->setContentHtml(true);
            }

            // changed
            $this->news_item->setContextObjId($this->getContextObjId());
            $this->news_item->setContextObjType($this->getContextObjType());
            $this->news_item->setContextSubObjId($this->getContextSubObjId());
            $this->news_item->setContextSubObjType($this->getContextSubObjType());
            $this->news_item->setUserId($this->user->getId());

            $news_set = new ilSetting('news');
            if (!$news_set->get('enable_rss_for_internal')) {
                $this->news_item->setVisibility('users');
            }

            $this->news_item->create();
            $this->main_tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->lng->txt('msg_obj_created'), true);
            $this->exitSaveNewsItemCmd();
        } else {
            $form->setValuesByPost();
            return $form->getHTML();
        }
        return '';
    }

    public function exitSaveNewsItemCmd(): void
    {
        if ($this->add_mode === 'block') {
            $this->ctrl->returnToParent($this);
        } else {
            $this->ctrl->redirect($this, 'editNews');
        }
    }

    public function updateNewsItemCmd(): string
    {
        if (!$this->news_access->canEdit($this->news_item)) {
            return '';
        }

        $form = $this->initFormNewsItem(self::FORM_EDIT);
        if ($form->checkInput()) {
            $this->news_item->setUpdateUserId($this->user->getId());
            $this->news_item->setTitle($form->getInput('news_title'));
            $this->news_item->setContent($form->getInput('news_content'));
            $this->news_item->setVisibility($form->getInput('news_visibility'));
            //$this->news_item->setContentLong($form->getInput('news_content_long'));
            $this->news_item->setContentLong('');

            $media_paths = $this->getUploadedMediaTempPath();
            $media_delete = $this->std_request->getDeleteMedia();
            $has_new_media_upload = isset($media_paths['client_filename'], $media_paths['client_filename']);
            $old_mob_id = 0;

            // delete old media object
            if ($has_new_media_upload || $media_delete) {
                if ($this->news_item->getMobId() > 0 && ilObject::_lookupType($this->news_item->getMobId()) === 'mob') {
                    $old_mob_id = $this->news_item->getMobId();
                }
                $this->news_item->setMobId(0);
            }

            if ($has_new_media_upload) {
                $mob = ilObjMediaObject::_saveTempFileAsMediaObject(
                    $media_paths['client_filename'],
                    $media_paths['tmp_path'],
                    true
                );
                $this->news_item->setMobId($mob->getId());
            }

            if (self::isRteActivated()) {
                $this->news_item->setContentHtml(true);
            }
            $this->news_item->update();

            if ($old_mob_id > 0) {
                $old_mob = new ilObjMediaObject($old_mob_id);
                $old_mob->delete();
            }

            $this->main_tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->lng->txt('msg_obj_modified'), true);
            $this->exitUpdateNewsItemCmd();
        } else {
            $form->setValuesByPost();
            return $form->getHTML();
        }
        return '';
    }

    public function exitUpdateNewsItemCmd(): void
    {
        $this->ctrl->redirect($this, 'editNews');
    }

    public function cancelUpdateNewsItemCmd(): void
    {
        $this->ctrl->redirect($this, 'editNews');
    }

    public function cancelSaveNewsItemCmd(): void
    {
        if ($this->add_mode === 'block') {
            $this->ctrl->returnToParent($this);
            return;
        }

        $this->ctrl->redirect($this, 'editNews');
    }

    public function editNewsCmd(): string
    {
        $this->setTabs();

        if (!$this->news_access->canAccessManageOverview()) {
            return '';
        }

        if ($this->news_access->canAdd()) {
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('news_add_news'),
                    $this->ctrl->getLinkTarget($this, 'createNewsItem')
                )
            );
        }

        $url_builder = new URLBuilder($this->data_factory->uri($this->http_request->getUri()->__toString()));

        $table = new NewsItemTable(
            $this->ui_factory,
            $this->lng,
            $this->http_request,
            $this->ctrl,
            $this->setting,
            $this->renderer,
            $this->news_access,
            $this->filter_service,
            $this->news_collection_service,
            $this->user,
            $this->http_service,
            $this->main_tpl,
            $this->refinery
        );

        $table->execute($url_builder);

        return $this->renderer->render($table->getComponents($url_builder));
    }

    public function cancelUpdate(): string
    {
        return $this->editNewsCmd();
    }

    public function confirmDeletionNewsItemsCmd(): string
    {
        if (!$this->news_access->canAccessManageOverview()) {
            return '';
        }

        // check whether at least one item is selected
        if (count($this->std_request->getNewsIds()) === 0) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'));
            return $this->editNewsCmd();
        }

        $this->tabs->clearTargets();

        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, 'deleteNewsItems'));
        $c_gui->setHeaderText($this->lng->txt('info_delete_sure'));
        $c_gui->setCancel($this->lng->txt('cancel'), 'editNews');
        $c_gui->setConfirm($this->lng->txt('confirm'), 'deleteNewsItems');

        // add items to delete
        foreach ($this->std_request->getNewsIds() as $news_id) {
            $news = new ilNewsItem($news_id);
            if ($this->news_access->canDelete($news)) {
                $c_gui->addItem('news_id[]', $news_id, $news->getTitle());
            }
        }

        return $c_gui->getHTML();
    }

    public function deleteNewsItemsCmd(): string
    {
        if (!$this->news_access->canAccessManageOverview()) {
            return '';
        }

        $deleted_count = 0;
        $failed_count = 0;
        foreach ($this->std_request->getNewsIds() as $news_id) {
            $news = new ilNewsItem($news_id);
            if (!$this->news_access->canDelete($news)) {
                $failed_count++;
                continue;
            }

            try {
                $news->delete();
                $deleted_count++;
            } catch (Exception) {
                $failed_count++;
            }
        }

        if ($deleted_count > 0) {
            $this->main_tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->lng->txt('deleted'), true);
        }
        if ($failed_count > 0) {
            $this->main_tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE, $this->lng->txt('msg_obj_already_deleted'), true);
        }

        return $this->editNewsCmd();
    }

    public function setTabs(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            (string) $this->ctrl->getParentReturn($this)
        );
    }

    public static function isRteActivated(): bool
    {
        return false;
    }

    /**
     * @return array{client_filename: string, tmp_path: string}|null
     */
    private function getUploadedMediaTempPath(): ?array
    {
        $files = $this->http_request->getUploadedFiles();
        if (!isset($files['media']) || !$files['media'] instanceof UploadedFileInterface) {
            return null;
        }

        /** @var UploadedFileInterface $upload */
        $upload = $files['media'];
        if ($upload->getError() !== UPLOAD_ERR_OK) {
            return null;
        }

        $client_filename = trim($upload->getClientFilename() ?? '');
        if ($client_filename === '') {
            return null;
        }

        $stream = $upload->getStream();
        $temp_path = $stream->getMetadata('uri');
        if (!is_string($temp_path) || $temp_path === '' || !$stream->isReadable()) {
            return null;
        }

        return [
            'client_filename' => $client_filename,
            'tmp_path' => $temp_path,
        ];
    }
}
