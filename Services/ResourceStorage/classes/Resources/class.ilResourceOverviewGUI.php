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

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Services\ResourceStorage\Resources\DataSource\AllResourcesDataSource;
use ILIAS\Services\ResourceStorage\Resources\DataSource\TableDataSource;
use ILIAS\Services\ResourceStorage\Resources\Listing\ViewDefinition;
use ILIAS\Services\ResourceStorage\Resources\UI\Actions\OverviewActionGenerator;
use ILIAS\Services\ResourceStorage\Resources\UI\ResourceListingUI;
use ILIAS\Services\ResourceStorage\Resources\UI\RevisionListingUI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_IsCalledBy ilResourceOverviewGUI: ilObjFileServicesGUI
 */
class ilResourceOverviewGUI
{
    use Hasher;

    public const CMD_INDEX = 'index';
    public const CMD_REMOVE = 'remove';
    public const CMD_DOWNLOAD = 'download';
    public const CMD_SHOW_REVISIONS = 'showRevisions';
    public const CMD_GOTO_RESOURCE = 'gotoResource';


    public const P_RESOURCE_ID = 'resource_id';


    protected ilCtrlInterface $ctrl;
    protected ilLanguage $language;
    protected \ILIAS\UI\Renderer $ui_renderer;
    protected ilGlobalTemplateInterface $main_tpl;
    protected \ILIAS\ResourceStorage\Services $irss;
    protected \ILIAS\FileUpload\FileUpload $upload;
    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\Refinery\Factory $refinery;
    protected \ILIAS\ResourceStorage\Collection\ResourceCollection $collection;
    protected \ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder $stakeholder;
    protected \ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $query;
    private \ILIAS\HTTP\Wrapper\WrapperFactory $wrapper;
    private ilTabsGUI $tabs;

    final public function __construct()
    {
        global $DIC;
        // Services
        $this->irss = $DIC->resourceStorage();
        $this->ctrl = $DIC->ctrl();
        $this->language = $DIC->language();
        $this->language->loadLanguageModule('irss');
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->upload = $DIC->upload();
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->tabs = $DIC->tabs();
    }


    final public function executeCommand(): void
    {
        switch ($this->ctrl->getCmd(self::CMD_INDEX)) {
            case self::CMD_INDEX:
                $this->index();
                break;
            case self::CMD_DOWNLOAD:
                $this->download();
                break;
            case self::CMD_SHOW_REVISIONS:
                $this->showRevisions();
                break;
            case self::CMD_GOTO_RESOURCE:
                $this->gotoResource();
                break;
        }
    }

    private function index(): void
    {
        $listing = new ResourceListingUI(
            new ViewDefinition(
                self::class,
                self::CMD_INDEX,
                $this->language->txt('resource_overview')
            ),
            new AllResourcesDataSource(),
            new OverviewActionGenerator()
        );

        $this->main_tpl->setContent(
            $this->ui_renderer->render($listing->getComponents())
        );
    }


    private function gotoResource(): void
    {
        $rid = $this->getResourceIdFromRequest();
        $resource = $this->irss->manage()->getResource($rid);
        $stakeholders = $resource->getStakeholders();
        if (count($stakeholders) === 1) { // Onyl one Stakeholder, we redirect to it
            /** @var ResourceStakeholder $stakeholder */
            $stakeholder = array_shift($stakeholders);
            $uri = $stakeholder->getLocationURIForResourceUsage($rid);
            if ($uri !== null) {
                $this->ctrl->redirectToURL($uri);
            }
            $this->main_tpl->setOnScreenMessage('info', $this->language->txt('resource_no_stakeholder_uri'));
        } else {
            // TODO list all stakeholders and it's locations
            $this->main_tpl->setOnScreenMessage('failure', 'Multiple Stakeholders found, can\'t redirect', true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }
    }

    private function showRevisions(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->language->txt('back'),
            $this->ctrl->getLinkTarget($this, self::CMD_INDEX)
        );

        $rid = $this->getResourceIdFromRequest();
        $resource = $this->irss->manage()->getResource($rid);

        $view_definition = new ViewDefinition(
            self::class,
            self::CMD_SHOW_REVISIONS,
            $this->language->txt('resource_overview')
        );
        $view_definition->setMode(ViewDefinition::MODE_AS_TABLE);
        $listing = new RevisionListingUI(
            $view_definition,
            $resource
        );


        $this->main_tpl->setContent(
            $this->ui_renderer->render($listing->getComponents())
        );
    }


    private function download(): void
    {
        $rid = $this->getResourceIdFromRequest();
        if (!$rid instanceof \ILIAS\ResourceStorage\Identification\ResourceIdentification) {
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt('msg_no_perm_read'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
            return;
        }
        $this->irss->consume()->download($rid)->run();
    }


    private function getResourceIdFromRequest(): ?ResourceIdentification
    {
        $rid = $this->wrapper->query()->has(self::P_RESOURCE_ID) ? $this->wrapper->query()->retrieve(
            self::P_RESOURCE_ID,
            $this->refinery->to()->string()
        ) : ($this->wrapper->post()->has(self::P_RESOURCE_ID)
            ? $this->wrapper->post()->retrieve(self::P_RESOURCE_ID, $this->refinery->to()->string())
            : null);

        if ($rid === null) {
            return null;
        }

        return $this->irss->manage()->find($rid);
    }
}
