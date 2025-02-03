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

namespace ILIAS\components\OrgUnit\ARHelper;

use ILIAS\DI\Container;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * Interface BaseCommands
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class BaseCommands
{
    public const CMD_INDEX = "index";
    public const CMD_DEFAULT_PERMISSIONS = "defaultPermissions";
    public const CMD_ADD = "add";
    public const CMD_CREATE = "create";
    public const CMD_EDIT = "edit";
    public const CMD_UPDATE = "update";
    public const CMD_CONFIRM = "confirm";
    public const CMD_DELETE = "delete";
    public const CMD_CANCEL = "cancel";
    public const AR_ID = "arid";

    protected \ilLanguage $lng;
    protected \ilCtrl $ctrl;
    private \ilTabsGUI $tabsGUI;
    protected \ilAccess $access;
    protected \ILIAS\HTTP\Services $http;
    protected \ilGlobalTemplateInterface $tpl;
    protected ?BaseCommands $parent_gui = null;

    protected URLBuilder $url_builder;
    protected ?URLBuilderToken $action_token = null;
    protected ?URLBuilderToken $row_id_token;
    protected DataFactory $data_factory;
    protected Refinery $refinery;
    protected ServerRequestInterface $request;
    protected \ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $query;

    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;

    protected function __construct(
        protected array $query_namespace = ['orgu', 'posedit']
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("orgu");
        $this->ctrl = $DIC->ctrl();
        $this->tabsGUI = $DIC->tabs();
        $this->access = $DIC->access();
        $this->http = $DIC->http();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->refinery = $DIC['refinery'];
        $this->query = $DIC->http()->wrapper()->query();
        $this->request = $DIC->http()->request();
        $this->data_factory = new DataFactory();

        $here_uri = $this->data_factory->uri(
            $this->request->getUri()->__toString()
        );
        $this->url_builder = new URLBuilder($here_uri);
        list($url_builder, $action_token, $row_id_token) =
            $this->url_builder->acquireParameters($this->query_namespace, "action", "rowid");
        $this->url_builder = $url_builder;
        $this->action_token = $action_token;
        $this->row_id_token = $row_id_token;

        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
    }

    public function getParentGui(): ?BaseCommands
    {
        return $this->parent_gui;
    }

    public function setParentGui(BaseCommands $parent_gui)
    {
        $this->parent_gui = $parent_gui;
    }

    abstract protected function index(): void;

    protected function getPossibleNextClasses(): array
    {
        return array();
    }

    protected function getActiveTabId(): ?string
    {
        return null;
    }

    /**
     * @throws \ilCtrlException
     */
    protected function cancel(): void
    {
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function setContent(string $html)
    {
        $this->tpl->setContent($html);
    }

    /**
     * @throws \ilCtrlException
     */
    public function executeCommand()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->access = $DIC->access();
        $this->tabsGUI = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng->loadLanguageModule("orgu");

        $cmd = $this->ctrl->getCmd(self::CMD_INDEX);

        if ($this->action_token &&
            $this->query->has($this->action_token->getName())
        ) {
            $cmd = $this->query->retrieve(
                $this->action_token->getName(),
                $this->refinery->to()->string()
            );
        }

        $next_class = $this->ctrl->getNextClass();
        if ($next_class) {
            foreach ($this->getPossibleNextClasses() as $class) {
                if (strtolower($class) === $next_class) {
                    $instance = new $class();
                    if ($instance instanceof BaseCommands) {
                        $instance->setParentGui($this);
                        $this->ctrl->forwardCommand($instance);
                    }

                    return;
                }
            }
        }

        if ($this->getActiveTabId()) {
            $this->tabsGUI->activateTab($this->getActiveTabId());
        }

        switch ($cmd) {
            default:
                if ($this->checkRequestReferenceId()) {
                    $this->{$cmd}();
                }
                break;
        }
    }

    protected function pushSubTab(string $subtab_id, string $url)
    {
        $this->tabsGUI->addSubTab($subtab_id, $this->lng->txt($subtab_id), $url);
    }

    protected function activeSubTab(string $subtab_id)
    {
        $this->tabsGUI->activateSubTab($subtab_id);
    }

    protected function checkRequestReferenceId()
    {
        /**
         * @var $ilAccess \ilAccessHandler
         */
        $ref_id = $this->getParentRefId();
        if ($ref_id) {
            return $this->access->checkAccess("read", "", $ref_id);
        }

        return true;
    }

    protected function getParentRefId(): ?int
    {
        $ref_id = $this->http->request()->getQueryParams()["ref_id"];

        return $ref_id;
    }

    public function addSubTabs(): void
    {
    }

    protected function getRowIdFromQuery(): int
    {
        if($this->query->has($this->row_id_token->getName())) {
            return $this->query->retrieve(
                $this->row_id_token->getName(),
                $this->refinery->custom()->transformation(fn($v) => (int)array_shift($v))
            );
        }
        throw new \Exception('no position-id in query');
    }
}
