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

use ILIAS\MetaData\Editor\Http\Parameter;
use ILIAS\MetaData\Services\InternalServices;
use ILIAS\MetaData\Editor\Full\FullEditorInitiator;
use ILIAS\UI\Renderer;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Editor\Http\RequestParserInterface;
use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Editor\Observers\ObserverHandler;
use ILIAS\GlobalScreen\Services as GlobalScreen;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\MetaData\Editor\Http\RequestForFormInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Editor\Full\FullEditor;
use ILIAS\MetaData\Editor\Full\ContentType as FullContentType;
use ILIAS\MetaData\Editor\Digest\ContentType as DigestContentType;
use ILIAS\MetaData\Editor\Full\Services\Tables\Table;
use ILIAS\MetaData\Editor\Digest\DigestInitiator;
use ILIAS\MetaData\Editor\Digest\Digest;

/**
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls ilMDEditorGUI: ilFormPropertyDispatchGUI
 */
class ilMDEditorGUI
{
    public const SET_FOR_TREE = 'md_set_for_tree';
    public const PATH_FOR_TREE = 'md_path_for_tree';

    protected FullEditorInitiator $full_editor_initiator;
    protected DigestInitiator $digest_initiator;

    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected Renderer $ui_renderer;
    protected PresenterInterface $presenter;
    protected RepositoryInterface $repository;
    protected RequestParserInterface $request_parser;
    protected ObserverHandler $observer_handler;
    protected ilAccessHandler $access;
    protected ilToolbarGUI $toolbar;
    protected GlobalScreen $global_screen;
    protected ilTabsGUI $tabs;
    protected UIFactory $ui_factory;

    protected int $obj_id;
    protected int $sub_id;
    public string $type;

    public function __construct(int $obj_id, int $sub_id, string $type)
    {
        global $DIC;

        $services = new InternalServices($DIC);
        $this->full_editor_initiator = new FullEditorInitiator($services);
        $this->digest_initiator = new DigestInitiator($services);

        $this->ctrl = $services->dic()->ctrl();
        $this->tpl = $services->dic()->ui()->mainTemplate();
        $this->ui_renderer = $services->dic()->ui()->renderer();
        $this->presenter = $services->editor()->presenter();
        $this->request_parser = $services->editor()->requestParser();
        $this->repository = $services->repository()->repository();
        $this->observer_handler = $services->editor()->observerHandler();
        $this->access = $services->dic()->access();
        $this->toolbar = $services->dic()->toolbar();
        $this->global_screen = $services->dic()->globalScreen();
        $this->tabs = $services->dic()->tabs();
        $this->ui_factory = $services->dic()->ui()->factory();

        $this->obj_id = $obj_id;
        $this->sub_id = $sub_id === 0 ? $obj_id : $sub_id;
        $this->type = $type;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->ctrl->getCmd();
        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = "listQuickEdit";
                }
                $this->$cmd();
                break;
        }
    }

    public function debug(): bool
    {
        $xml_writer = new ilMD2XML($this->obj_id, $this->sub_id, $this->type);
        $xml_writer->startExport();

        $button = $this->renderButtonToFullEditor();

        $this->tpl->setContent($button . htmlentities($xml_writer->getXML()));
        return true;
    }

    public function listSection(): void
    {
        $this->listQuickEdit();
    }

    public function listQuickEdit(): void
    {
        $digest = $this->digest_initiator->init();
        $set = $this->repository->getMD(
            $this->obj_id,
            $this->sub_id,
            $this->type
        );

        $this->renderDigest($set, $digest);
    }

    public function updateQuickEdit(): void
    {
        $this->checkAccess();

        $digest = $this->digest_initiator->init();
        $set = $this->repository->getMD(
            $this->obj_id,
            $this->sub_id,
            $this->type
        );

        $request = $this->request_parser->fetchRequestForForm(false);
        if (!$digest->updateMD($set, $request)) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->presenter->utilities()->txt('msg_form_save_error'),
                true
            );
            $this->renderDigest($set, $digest, $request);
            return;
        }

        $this->callListeners('General');
        $this->callListeners('Rights');
        $this->callListeners('Educational');
        $this->callListeners('Lifecycle');

        // Redirect here to read new title and description
        $this->tpl->setOnScreenMessage(
            'success',
            $this->presenter->utilities()->txt("saved_successfully"),
            true
        );
        $this->ctrl->redirect($this, 'listQuickEdit');
    }

    protected function renderDigest(
        SetInterface $set,
        Digest $digest,
        ?RequestForFormInterface $request = null
    ): void {
        $content = $digest->getContent($set, $request);
        $template_content = [];
        foreach ($content as $type => $entity) {
            switch ($type) {
                case DigestContentType::FORM:
                case DigestContentType::MODAL:
                    $template_content[] = $entity;
                    break;

                case DigestContentType::JS_SOURCE:
                    $this->tpl->addJavaScript($entity);
                    break;
            }
        }
        $this->tpl->setContent(
            $this->renderButtonToFullEditor() .
            $this->ui_renderer->render($template_content)
        );
    }

    protected function fullEditorCreate(): void
    {
        $this->fullEditorEdit(true);
    }

    protected function fullEditorUpdate(): void
    {
        $this->fullEditorEdit(false);
    }

    protected function fullEditorEdit(bool $create): void
    {
        $this->checkAccess();

        // get the paths from the http request
        $base_path = $this->request_parser->fetchBasePath();
        $action_path = $this->request_parser->fetchActionPath();

        // get and prepare the MD
        $set = $this->repository->getMD(
            $this->obj_id,
            $this->sub_id,
            $this->type
        );
        $editor = $this->full_editor_initiator->init();
        $set = $editor->manipulateMD()->prepare($set, $base_path);

        // update or create
        $request = $this->request_parser->fetchRequestForForm(true);
        $success = $editor->manipulateMD()->createOrUpdate(
            $set,
            $base_path,
            $action_path,
            $request
        );
        if (!$success) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->presenter->utilities()->txt('msg_form_save_error'),
                true
            );
            $this->renderFullEditor($set, $base_path, $editor, $request);
            return;
        }

        // call listeners
        $this->observer_handler->callObserversByPath($action_path);

        // redirect back to the full editor
        $this->tpl->setOnScreenMessage(
            'success',
            $this->presenter->utilities()->txt(
                $create ?
                    'meta_add_element_success' :
                    'meta_edit_element_success'
            ),
            true
        );
        $this->ctrl->setParameter(
            $this,
            Parameter::BASE_PATH->value,
            urlencode($base_path->toString())
        );
        $this->ctrl->redirect($this, 'fullEditor');
    }

    protected function fullEditorDelete(): void
    {
        $this->checkAccess();

        // get the paths from the http request
        $base_path = $this->request_parser->fetchBasePath();
        $delete_path = $this->request_parser->fetchActionPath();

        // get the MD
        $set = $this->repository->getMD(
            $this->obj_id,
            $this->sub_id,
            $this->type
        );
        $editor = $this->full_editor_initiator->init();

        // delete
        $base_path = $editor->manipulateMD()->deleteAndTrimBasePath(
            $set,
            $base_path,
            $delete_path
        );

        // call listeners
        $this->observer_handler->callObserversByPath($delete_path);

        // redirect back to the full editor
        $this->tpl->setOnScreenMessage(
            'success',
            $this->presenter->utilities()->txt('meta_delete_element_success'),
            true
        );
        $this->ctrl->setParameter(
            $this,
            Parameter::BASE_PATH->value,
            urlencode($base_path->toString())
        );
        $this->ctrl->redirect($this, 'fullEditor');
    }

    protected function fullEditor(): void
    {
        $this->setTabsForFullEditor();

        // get the paths from the http request
        $base_path = $this->request_parser->fetchBasePath();

        // get and prepare the MD
        $set = $this->repository->getMD(
            $this->obj_id,
            $this->sub_id,
            $this->type
        );
        $editor = $this->full_editor_initiator->init();
        $set = $editor->manipulateMD()->prepare($set, $base_path);

        // add content for element
        $this->renderFullEditor($set, $base_path, $editor);
    }

    protected function renderFullEditor(
        SetInterface $set,
        PathInterface $base_path,
        FullEditor $full_editor,
        ?RequestForFormInterface $request = null
    ): void {
        // add slate with tree
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            self::SET_FOR_TREE,
            $set
        );
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            self::PATH_FOR_TREE,
            $base_path
        );

        // render toolbar, modals and main content
        $content = $full_editor->getContent($set, $base_path, $request);
        $template_content = [];
        foreach ($content as $type => $entity) {
            switch ($type) {
                case FullContentType::MAIN:
                    if ($entity instanceof Table) {
                        $entity = $this->ui_factory->legacy(
                            $entity->getHTML()
                        );
                    }
                    $template_content[] = $entity;
                    break;

                case FullContentType::MODAL:
                    if ($modal = $entity->getModal()) {
                        $template_content[] = $modal;
                    }
                    break;

                case FullContentType::TOOLBAR:
                    $this->toolbar->addComponent($entity);
                    break;
            }
        }
        $this->tpl->setContent($this->ui_renderer->render($template_content));
    }

    protected function setTabsForFullEditor(): void
    {
        $this->tabs->clearSubTabs();
        foreach ($this->tabs->target as $tab) {
            if (($tab['id'] ?? null) !== $this->tabs->getActiveTab()) {
                $this->tabs->removeTab($tab['id']);
            }
        }
        $this->tabs->removeNonTabbedLinks();
        $this->tabs->setBackTarget(
            $this->presenter->utilities()->txt('back'),
            $this->ctrl->getLinkTarget($this, 'listQuickEdit')
        );
    }

    protected function renderButtonToFullEditor(): string
    {
        $bulky = $this->ui_factory->button()->bulky(
            $this->ui_factory->symbol()->icon()->standard(
                'mds',
                $this->presenter->utilities()->txt('meta_button_to_full_editor_label'),
                'medium'
            ),
            $this->presenter->utilities()->txt('meta_button_to_full_editor_label'),
            $this->ctrl->getLinkTarget($this, 'fullEditor')
        );
        if (DEVMODE) {
            $debug = $this->ui_factory->button()->bulky(
                $this->ui_factory->symbol()->icon()->standard(
                    'adm',
                    'Debug'
                ),
                'Debug',
                $this->ctrl->getLinkTarget($this, 'debug')
            );
        }
        return  $this->ui_renderer->render($bulky) .
            (isset($debug) ? '</p>' . $this->ui_renderer->render($debug) : '');
    }

    protected function checkAccess(): void
    {
        // if there is no fixed parent (e.g. mob), then skip
        if ($this->obj_id === 0) {
            return;
        }
        $ref_ids = ilObject::_getAllReferences($this->obj_id);
        // if there are no references (e.g. in workspace), then skip
        if (empty($ref_ids)) {
            return;
        }
        foreach ($ref_ids as $ref_id) {
            if ($this->access->checkAccess(
                'write',
                '',
                $ref_id,
                '',
                $this->obj_id
            )) {
                return;
            }
        }
        throw new ilPermissionException($this->presenter->utilities()->txt('permission_denied'));
    }

    // Observer methods
    public function addObserver(object $a_class, string $a_method, string $a_element): void
    {
        $this->observer_handler->addObserver($a_class, $a_method, $a_element);
    }

    public function callListeners(string $a_element): void
    {
        $this->observer_handler->callObservers($a_element);
    }
}
