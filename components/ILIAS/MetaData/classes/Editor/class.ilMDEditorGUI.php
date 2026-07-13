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
use ILIAS\MetaData\Editor\Full\Services\Services as FullEditorServices;
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
use ILIAS\MetaData\Editor\Full\Components\Tables\Table;
use ILIAS\MetaData\Editor\Digest\Services\Services as DigestServices;
use ILIAS\MetaData\Editor\Digest\Digest;
use ILIAS\MetaData\XML\Writer\WriterInterface as XMLWriter;
use ILIAS\MetaData\OERHarvester\Services\Services as PublishingServices;
use ILIAS\MetaData\OERHarvester\ControlCenter\ControlCenterGUI;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Component\Prompt\Prompt;
use ILIAS\MetaData\Editor\Http\Command;

/**
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ilCtrl_Calls ilMDEditorGUI: ILIAS\MetaData\OERHarvester\ControlCenter\ControlCenterGUI
 */
class ilMDEditorGUI
{
    public const string SET_FOR_TREE = 'md_set_for_tree';
    public const string PATH_FOR_TREE = 'md_path_for_tree';

    protected FullEditorServices $full_editor_services;
    protected DigestServices $digest_services;
    protected PublishingServices $publishing_services;

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
    protected XMLWriter $xml_writer;

    protected int $obj_id;
    protected int $sub_id;
    public string $type;
    protected int $ref_id;

    public function __construct(int $obj_id, int $sub_id, string $type, int $ref_id = 0)
    {
        global $DIC;

        $services = new InternalServices($DIC);

        $this->full_editor_services = $services->editor()->fullEditor();
        $this->digest_services = $services->editor()->digest();
        $this->publishing_services = $services->OERHarvester();

        $this->ctrl = $services->dic()->ctrl();
        $this->tpl = $services->dic()->ui()->mainTemplate();
        $this->ui_renderer = $services->dic()->ui()->renderer();
        $this->presenter = $services->editor()->internal()->presenter();
        $this->request_parser = $services->editor()->internal()->requestParser();
        $this->repository = $services->repository()->repository();
        $this->observer_handler = $services->editor()->internal()->observerHandler();
        $this->access = $services->dic()->access();
        $this->toolbar = $services->dic()->toolbar();
        $this->global_screen = $services->dic()->globalScreen();
        $this->tabs = $services->dic()->tabs();
        $this->ui_factory = $services->dic()->ui()->factory();
        $this->xml_writer = $services->xml()->standardWriter();

        $this->obj_id = $obj_id;
        $this->sub_id = $sub_id === 0 ? $obj_id : $sub_id;
        $this->ref_id = $ref_id;
        $this->type = $type;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->ctrl->getCmd();
        switch ($next_class) {
            case strtolower(ControlCenterGUI::class):
                $back_link = $this->ctrl->getLinkTarget($this, 'listQuickEdit');
                $gui = $this->publishing_services->controlCenterGUI($back_link);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $valid_cmd = (Command::tryFrom($cmd) ?? Command::SHOW_DIGEST)->value;
                $this->$valid_cmd();
                break;
        }
    }

    public function debug(): bool
    {
        $xml = $this->xml_writer->write($this->repository->getMD($this->obj_id, $this->sub_id, $this->type));
        $dom = new DOMDocument('1.0');
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml->asXML());

        $this->addButtonToFullEditor();
        $this->tpl->setContent('<pre>' . htmlentities($dom->saveXML()) . '</pre>');
        return true;
    }

    public function listSection(): void
    {
        $this->listQuickEdit();
    }

    public function listQuickEdit(): void
    {
        $digest = $this->digest_services->digest();
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

        $digest = $this->digest_services->digest();
        $manipulator = $this->digest_services->manipulatorAdapter();
        $set = $this->repository->getMD(
            $this->obj_id,
            $this->sub_id,
            $this->type
        );

        $request = $this->request_parser->fetchRequestForForm(false);
        if (!$manipulator->update($set, $request)) {
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
        $template_content = $this->getButtonToControlCenter();
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
        $this->addButtonToFullEditor();
        $this->tpl->setContent($this->ui_renderer->render($template_content));
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
        $editor = $this->full_editor_services->fullEditor();
        $manipulator = $this->full_editor_services->manipulatorAdapter();
        $set = $manipulator->prepare($set, $base_path);

        // update or create
        $request = $this->request_parser->fetchRequestForForm(true);
        $success = $manipulator->createOrUpdate(
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
        $editor = $this->full_editor_services->fullEditor();
        $manipulator = $this->full_editor_services->manipulatorAdapter();

        // delete
        $base_path = $manipulator->deleteAndTrimBasePath(
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
        $editor = $this->full_editor_services->fullEditor();
        $manipulator = $this->full_editor_services->manipulatorAdapter();
        $set = $manipulator->prepare($set, $base_path);

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
                        $entity = $this->ui_factory->legacy()->content(
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

    protected function addButtonToFullEditor(): void
    {
        $editor = $this->ui_factory->button()->standard(
            $this->presenter->utilities()->txt('meta_button_to_full_editor_label'),
            $this->ctrl->getLinkTarget($this, 'fullEditor')
        );
        $this->toolbar->addComponent($editor);
        if (DEVMODE) {
            $debug = $this->ui_factory->button()->standard(
                'Debug',
                $this->ctrl->getLinkTarget($this, 'debug')
            );
            $this->toolbar->addComponent($debug);
        }
    }

    /**
     * @return array{0:MessageBox, 1:Prompt}
     */
    protected function getButtonToControlCenter(): array
    {
        // will also exclude subtypes
        if (!$this->publishing_services->stateInfoFetcher()->isPublishingRelevantForObject(
            $this->ref_id,
            $this->type,
            $this->obj_id
        )) {
            return [];
        }
        $status = $this->publishing_services->stateInfoFetcher()->getStatusForObject($this->obj_id);
        return $this->publishing_services->controlCenterComponentFactory()->getButtonToControlCenter(
            $status,
            $this->ref_id,
            $this->obj_id,
            $this->type
        );
    }

    protected function checkAccess(): void
    {
        // if there is no fixed parent (e.g. mob), then skip
        if ($this->obj_id === 0 || $this->ref_id === 0) {
            return;
        }
        if ($this->access->checkAccess(
            'write',
            '',
            $this->ref_id,
            '',
            $this->obj_id
        )) {
            return;
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
