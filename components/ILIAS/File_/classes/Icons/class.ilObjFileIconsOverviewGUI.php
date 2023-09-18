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

declare(strict_types=1);

namespace ILIAS\File\Icon;

use ILIAS\UI\Component\MessageBox\MessageBox;

/**
 * @property \ilFileServicesSettings $file_settings
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
class ilObjFileIconsOverviewGUI
{
    public const CMD_INDEX = 'index';
    public const CMD_OPEN_CREATION_FORM = 'openCreationForm';
    public const CMD_OPEN_UPDATING_FORM = 'openUpdatingForm';
    public const CMD_CHANGE_ACTIVATION = 'changeActivation';
    public const CMD_CREATE = 'create';
    public const CMD_UPDATE = 'update';
    public const CMD_DELETE = 'delete';
    public const P_RID = 'rid';

    private \ilCtrl $ctrl;
    private \ilLanguage $lng;
    private \ilToolbarGUI $toolbar;
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;
    private \ilGlobalTemplateInterface $main_tpl;
    private \ILIAS\HTTP\Wrapper\WrapperFactory $wrapper;
    private \Psr\Http\Message\RequestInterface $http_request;
    private \ILIAS\Refinery\Factory $refinery;
    private \ILIAS\ResourceStorage\Services $storage;
    private IconRepositoryInterface $icon_repo;
    private \ilFileServicesSettings $file_service_settings;

    final public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('file');
        $this->toolbar = $DIC->toolbar();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->http_request = $DIC->http()->request();
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->storage = $DIC->resourceStorage();
        $this->icon_repo = new IconDatabaseRepository();
        $this->file_service_settings = $DIC->fileServiceSettings();
    }

    final public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass(self::class);
        if ($next_class === strtolower(ilIconUploadHandlerGUI::class)) {
            $upload_handler = new ilIconUploadHandlerGUI();
            $this->ctrl->forwardCommand($upload_handler);
        }

        switch ($cmd = $this->ctrl->getCmd(self::CMD_INDEX)) {
            case self::CMD_OPEN_CREATION_FORM:
                $this->openCreationForm();
                break;
            case self::CMD_OPEN_UPDATING_FORM:
                $this->openUpdatingForm();
                break;
            case self::CMD_CHANGE_ACTIVATION:
                $this->changeActivation();
                break;
            case self::CMD_CREATE:
                $this->create();
                break;
            case self::CMD_UPDATE:
                $this->update();
                break;
            case self::CMD_DELETE:
                $this->delete();
                break;
            default:
                $this->index();
                break;
        }
    }

    private function index(): void
    {
        // toolbar: add new icon button
        $btn_new_icon = $this->ui_factory->button()->standard(
            $this->lng->txt('add_icon'),
            $this->ctrl->getLinkTargetByClass(self::class, self::CMD_OPEN_CREATION_FORM)
        );
        $this->toolbar->addComponent($btn_new_icon);

        // Listing of icons
        $listing = new IconListingUI(
            $this->icon_repo,
            $this
        );

        $content = [];
        $content[] = $listing->getFilter();
        $content[] = $listing->getIconList();
        $content[] = $listing->getDeletionModals();

        $this->main_tpl->setContent(
            $this->ui_renderer->render($content)
        );
    }

    private function openCreationForm(): void
    {
        $icon_form_ui = new IconFormUI(new NullIcon('', true), IconFormUI::MODE_CREATE, $this->icon_repo);
        $icon_form = $icon_form_ui->getIconForm();
        $this->main_tpl->setContent(
            $this->ui_renderer->render($icon_form)
        );
    }

    private function openUpdatingForm(): void
    {
        $to_str = $this->refinery->to()->string();
        $rid = $this->wrapper->query()->has(self::P_RID) ? $rid = $this->wrapper->query()->retrieve(self::P_RID, $to_str) : "";
        $this->ctrl->setParameter($this, self::P_RID, $rid); //store rid for giving icon to form in update function
        $icon = $this->icon_repo->getIconByRid($rid);
        $icon_form_ui = new IconFormUI($icon, IconFormUI::MODE_EDIT, $this->icon_repo);
        $icon_form = $icon_form_ui->getIconForm();
        $this->main_tpl->setContent(
            $this->ui_renderer->render($icon_form)
        );
    }

    public function changeActivation(): void
    {
        $to_str = $this->refinery->to()->string();
        $rid = $this->wrapper->query()->has(self::P_RID) ? $rid = $this->wrapper->query()->retrieve(self::P_RID, $to_str) : "";
        $icon = $this->icon_repo->getIconByRid($rid);
        $suffixes = $icon->getSuffixes();
        $icon->isActive();
        $is_default_icon = $icon->isDefaultIcon();

        if (!$icon->isActive()) {
            // in case of a change from deactivated to activated no two icons with overlapping suffixes must be active at the same time
            if ($this->icon_repo->causesNoActiveSuffixesConflict($suffixes, true, $icon)) {
                $this->icon_repo->updateIcon($rid, true, $is_default_icon, $suffixes);

                $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_icon_activated'), true);
            } else {
                $this->main_tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('msg_error_active_suffixes_conflict'),
                    true
                );
            }
        } else {
            $this->icon_repo->updateIcon($rid, false, $is_default_icon, $suffixes);
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_icon_deactivated'), true);
        }

        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    public function create(): void
    {
        $ui = new IconFormUI(new NullIcon(), IconFormUI::MODE_CREATE, $this->icon_repo);
        $form = $ui->getIconForm();

        if ($this->http_request->getMethod() === "POST") {
            $form = $form->withRequest($this->http_request);
            $result = $form->getData();

            if ($result !== null) {
                $rid = $result[0][IconFormUI::INPUT_ICON][0];
                $active = $result[0][IconFormUI::INPUT_ACTIVE];
                $suffixes = $result[0][IconFormUI::INPUT_SUFFIXES];

                $this->icon_repo->createIcon($rid, $active, false, $suffixes);

                // check if one of the suffixes is not whitelisted
                if (array_diff($suffixes, $this->file_service_settings->getWhiteListedSuffixes()) !== []) {
                    $this->main_tpl->setOnScreenMessage(
                        'info',
                        $this->lng->txt('msg_error_active_suffixes_not_whitelisted'),
                        true
                    );
                }

                // check if one of the suffixes is blacklisted
                if (array_intersect($suffixes, $this->file_service_settings->getBlackListedSuffixes()) !== []) {
                    $this->main_tpl->setOnScreenMessage(
                        'info',
                        $this->lng->txt('msg_error_active_suffixes_blacklisted'),
                        true
                    );
                }


                $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_icon_created'), true);
                $this->ctrl->redirect($this, self::CMD_INDEX);
            } else {
                $this->main_tpl->setContent(
                    $this->ui_renderer->render([$form])
                );
            }
        }
    }

    public function update(): void
    {
        $to_str = $this->refinery->to()->string();
        $rid = $this->wrapper->query()->has(self::P_RID) ? $rid = $this->wrapper->query()->retrieve(self::P_RID, $to_str) : "";
        $this->ctrl->saveParameter(
            $this,
            self::P_RID
        ); //save rid to still have it when re-submitting a previously wrongly filled out form
        $icon = $this->icon_repo->getIconByRid($rid);
        $ui = new IconFormUI($icon, IconFormUI::MODE_EDIT, $this->icon_repo);
        $form = $ui->getIconForm();

        if ($this->http_request->getMethod() === "POST") {
            $form = $form->withRequest($this->http_request);
            $result = $form->getData();

            if ($result !== null) {
                $rid = $result[0][IconFormUI::INPUT_ICON][0];
                $active = $result[0][IconFormUI::INPUT_ACTIVE];
                $suffixes = $result[0][IconFormUI::INPUT_SUFFIXES];

                $this->icon_repo->updateIcon($rid, $active, false, $suffixes);
                $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_icon_updated'), true);
                $this->ctrl->redirect($this, self::CMD_INDEX);
            } else {
                $this->main_tpl->setContent(
                    $this->ui_renderer->render([$form])
                );
            }
        }
    }

    public function delete(): void
    {
        $to_str = $this->refinery->to()->string();
        $rid = $this->wrapper->query()->has(self::P_RID) ? $rid = $this->wrapper->query()->retrieve(self::P_RID, $to_str) : "";

        // delete icon from irss
        $is_deleted_from_irss = false;
        $id = $this->storage->manage()->find($rid);
        if ($id !== null) {
            $this->storage->manage()->remove($id, new ilObjFileIconStakeholder());
            $is_deleted_from_irss = true;
        }
        // delete icon from db
        $is_deleted_from_db = $this->icon_repo->deleteIconByRid($rid);

        if ($is_deleted_from_irss && $is_deleted_from_db) {
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_icon_deletion'), true);
        } elseif ($is_deleted_from_irss) {
            $this->main_tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('msg_error_icon_deletion') . " " . $this->lng->txt('msg_icon_missing_from_db'),
                true
            );
        } elseif ($is_deleted_from_db) {
            $this->main_tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('msg_error_icon_deletion') . " " . $this->lng->txt('msg_icon_missing_from_irss'),
                true
            );
        } else {
            $this->main_tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('msg_error_icon_deletion') . " " . $this->lng->txt(
                    'msg_icon_missing_from_db'
                ) . " " . $this->lng->txt('msg_icon_missing_from_irss'),
                true
            );
        }
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }
}
