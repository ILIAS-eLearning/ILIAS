<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;

/**
 * Class ilMMAbstractItemGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMAbstractItemGUI
{
    const IDENTIFIER = 'identifier';
    use Hasher;
    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;
    /**
     * @var \ILIAS\DI\HTTPServices
     */
    protected $http;
    /**
     * @var ilMMItemRepository
     */
    protected $repository;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;
    /**
     * @var ilMMTabHandling
     */
    protected $tab_handling;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var ilLanguage
     */
    public $lng;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilTemplate
     */
    public $tpl;
    /**
     * @var ilTree
     */
    public $tree;
    /**
     * @var ilObjMainMenuAccess
     */
    protected $access;


    /**
     * ilMMAbstractItemGUI constructor.
     *
     * @param ilMMTabHandling $tab_handling
     *
     * @throws Throwable
     */
    public function __construct(ilMMTabHandling $tab_handling)
    {
        global $DIC;

        $this->repository = new ilMMItemRepository();
        $this->tab_handling = $tab_handling;
        $this->tabs = $DIC['ilTabs'];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->tree = $DIC['tree'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->http = $DIC->http();
        $this->ui = $DIC->ui();
        $this->access = new ilObjMainMenuAccess();
    }


    /**
     * @param string $standard
     * @param string $delete
     *
     * @return string
     * @throws ilException
     */
    protected function determineCommand(string $standard, string $delete) : string
    {
        $this->access->checkAccessAndThrowException('visible,read');
        $cmd = $this->ctrl->getCmd();
        if ($cmd !== '') {
            return $cmd;
        }

        $r = $this->http->request();
        $post = $r->getParsedBody();

        if ($cmd == "" && isset($post['interruptive_items'])) {
            $cmd = $delete;
        } else {
            $cmd = $standard;
        }

        return $cmd;
    }


    /**
     * @return ilMMItemFacadeInterface
     * @throws Throwable
     */
    protected function getMMItemFromRequest() : ilMMItemFacadeInterface
    {
        $r = $this->http->request();
        $get = $r->getQueryParams();
        $post = $r->getParsedBody();

        if (!isset($post['cmd']) && isset($post['interruptive_items'])) {
            $string = $post['interruptive_items'][0];
            $identification = $this->unhash($string);
        } else {
            $identification = $this->unhash($get[self::IDENTIFIER]);
        }

        return $this->repository->getItemFacadeForIdentificationString($identification);
    }


    public function renderInterruptiveModal()
    {
        $f = $this->ui->factory();
        $r = $this->ui->renderer();

        $form_action = $this->ctrl->getFormActionByClass(self::class, self::CMD_DELETE);
        $delete_modal = $f->modal()->interruptive(
            $this->lng->txt("delete"),
            $this->lng->txt(self::CMD_CONFIRM_DELETE),
            $form_action
        );

        echo $r->render([$delete_modal]);
        exit;
    }
}
