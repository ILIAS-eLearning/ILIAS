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

use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntries as Entries;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\GlobalScreen\Services;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\FileUpload\FileUpload;

/**
 * Settings UI class for system styles. Acts as main router for the systems styles and handles permissions checks,
 * sets tabs and title as well as description of the content section.
 * @ilCtrl_Calls ilSystemStyleMainGUI: ilSystemStyleOverviewGUI,ilSystemStyleSettingsGUI
 * @ilCtrl_Calls ilSystemStyleMainGUI: ilSystemStyleLessGUI,ilSystemStyleIconsGUI,ilSystemStyleDocumentationGUI
 */
class ilSystemStyleMainGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilRbacSystem $rbacsystem;
    protected string $ref_id;
    protected ilGlobalTemplateInterface $tpl;
    protected ilHelpGUI $help;
    protected Factory $ui_factory;
    protected Renderer $renderer;
    protected ilIniFile $ilIniFile;
    protected ilLocatorGUI $locator;
    protected Services $global_screen;
    protected WrapperFactory $request_wrapper;
    protected RefineryFactory $refinery;
    protected ServerRequestInterface $request;
    protected ilToolbarGUI $toolbar;
    protected ilSkinFactory $skin_factory;
    protected FileUpload $upload;
    protected ilTree $tree;
    protected ilObjUser $user;
    protected ilSystemStyleMessageStack $message_stack;

    public function __construct()
    {
        /**
         * @var ILIAS\DI\Container $DIC
         */
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->help = $DIC->help();
        $this->ui_factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->locator = $DIC['ilLocator'];
        $this->ilIniFile = $DIC->iliasIni();
        $this->global_screen = $DIC->globalScreen();
        $this->request_wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->request = $DIC->http()->request();
        $this->toolbar = $DIC->toolbar();
        $this->upload = $DIC->upload();
        $this->tree = $DIC->repositoryTree();
        $this->skin_factory = new ilSkinFactory($this->lng);
        $this->user = $DIC->user();

        $this->message_stack = new ilSystemStyleMessageStack($this->tpl);
        $this->ref_id = $this->request_wrapper->query()->retrieve('ref_id', $this->refinery->kindlyTo()->string());
    }

    /**
     * Main routing of the system styles. Resets ilCtrl Parameter for all subsequent generation of links.
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);

        $this->help->setScreenIdComponent('sty');
        $this->help->setScreenId('system_styles');

        $config = new ilSystemStyleConfig();
        $skin_factory = new ilSkinFactory($this->lng);

        if ($this->request_wrapper->query()->has('skin_id') && $this->request_wrapper->query()->has('style_id')) {
            $skin_id = $this->request_wrapper->query()->retrieve('skin_id', $this->refinery->kindlyTo()->string());
            $style_id = $this->request_wrapper->query()->retrieve('style_id', $this->refinery->kindlyTo()->string());
        } else {
            $skin_id = $config->getDefaultSkinId();
            $style_id = $config->getDefaultStyleId();
        }

        $this->ctrl->setParameterByClass('ilsystemstylesettingsgui', 'skin_id', $skin_id);
        $this->ctrl->setParameterByClass('ilsystemstylesettingsgui', 'style_id', $style_id);
        $this->ctrl->setParameterByClass('ilsystemstylelessgui', 'skin_id', $skin_id);
        $this->ctrl->setParameterByClass('ilsystemstylelessgui', 'style_id', $style_id);
        $this->ctrl->setParameterByClass('ilsystemstyleiconsgui', 'skin_id', $skin_id);
        $this->ctrl->setParameterByClass('ilsystemstyleiconsgui', 'style_id', $style_id);
        $this->ctrl->setParameterByClass('ilsystemstyledocumentationgui', 'skin_id', $skin_id);
        $this->ctrl->setParameterByClass('ilsystemstyledocumentationgui', 'style_id', $style_id);

        try {
            switch ($next_class) {

                case 'ilsystemstylesettingsgui':
                    $this->help->setSubScreenId('settings');
                    $this->checkPermission('sty_management');
                    $this->setUnderworldTabs($skin_id, 'settings');
                    $this->setUnderworldTitle($skin_id, $style_id);
                    $system_styles_settings = new ilSystemStyleSettingsGUI(
                        $this->ctrl,
                        $this->lng,
                        $this->tpl,
                        $this->tabs,
                        $this->ui_factory,
                        $this->renderer,
                        $skin_factory,
                        $this->request_wrapper,
                        $this->refinery,
                        $this->toolbar,
                        $this->user,
                        $this->request,
                        $this->tree,
                        $skin_id,
                        $style_id
                    );
                    $this->ctrl->forwardCommand($system_styles_settings);
                    break;
                case 'ilsystemstylelessgui':
                    $this->help->setSubScreenId('less');
                    $this->checkPermission('sty_management');
                    $this->setUnderworldTabs($skin_id, 'less');
                    $this->setUnderworldTitle($skin_id, $style_id);
                    $system_styles_less = new ilSystemStyleLessGUI(
                        $this->ctrl,
                        $this->lng,
                        $this->tpl,
                        $this->ui_factory,
                        $this->renderer,
                        $this->request,
                        $this->toolbar,
                        $this->refinery,
                        $skin_factory,
                        $skin_id,
                        $style_id
                    );
                    $this->ctrl->forwardCommand($system_styles_less);
                    break;
                case 'ilsystemstyleiconsgui':
                    $this->help->setSubScreenId('icons');
                    $this->checkPermission('sty_management');
                    $this->setUnderworldTabs($skin_id, 'icons');
                    $this->setUnderworldTitle($skin_id, $style_id);
                    $system_styles_icons = new ilSystemStyleIconsGUI(
                        $this->ctrl,
                        $this->lng,
                        $this->tpl,
                        $this->ui_factory,
                        $this->renderer,
                        $this->request_wrapper,
                        $this->toolbar,
                        $this->refinery,
                        $skin_factory,
                        $this->tabs,
                        $this->upload,
                        $skin_id,
                        $style_id
                    );
                    $this->ctrl->forwardCommand($system_styles_icons);
                    break;
                case 'ilsystemstyledocumentationgui':
                    $this->help->setSubScreenId('documentation');
                    $read_only = !$this->checkPermission('sty_management', false);
                    $this->setUnderworldTabs($skin_id, 'documentation', $read_only);
                    $this->setUnderworldTitle($skin_id, $style_id, $read_only);
                    $node_id = '';
                    if ($this->request_wrapper->query()->has('node_id')) {
                        $node_id = $this->request_wrapper->query()->retrieve(
                            'node_id',
                            $this->refinery->kindlyTo()->string()
                        );
                    }
                    $goto_link = (new ilKSDocumentationGotoLink())->generateGotoLink($node_id, $skin_id, $style_id);
                    $this->global_screen->tool()->context()->current()->addAdditionalData(
                        ilSystemStyleDocumentationGUI::SHOW_TREE,
                        true
                    );
                    $this->tpl->setPermanentLink('stys', (int) $this->ref_id, $goto_link);
                    $entries = new Entries();
                    $entries->addEntriesFromArray(include ilSystemStyleDocumentationGUI::DATA_PATH);
                    $documentation_gui = new ilSystemStyleDocumentationGUI(
                        $this->tpl,
                        $this->ctrl,
                        $this->ui_factory,
                        $this->renderer
                    );
                    $documentation_gui->show($entries, $node_id);
                    break;
                case 'ilsystemstyleoverviewgui':
                default:
                    $this->executeDefaultCommand($skin_factory, $skin_id, $style_id);
                    break;
            }
        } catch (ilObjectException $e) {
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $e->getMessage(),
                ilSystemStyleMessage::TYPE_ERROR
            ));
            $this->message_stack->sendMessages();
            $this->executeDefaultCommand($skin_factory, $skin_id, $style_id);
        }
    }

    protected function executeDefaultCommand(ilSkinFactory $skin_factory, string $skin_id, string $style_id): void
    {
        $this->help->setSubScreenId('overview');
        $this->checkPermission('visible,read');
        $read_only = !$this->checkPermission('sty_write_system', false);
        $management_enabled = $this->checkPermission('sty_management', false);
        $system_styles_overview = new ilSystemStyleOverviewGUI(
            $this->ctrl,
            $this->lng,
            $this->tpl,
            $this->ui_factory,
            $this->renderer,
            $this->request_wrapper,
            $this->toolbar,
            $this->refinery,
            $skin_factory,
            $this->upload,
            $this->tabs,
            $this->help,
            $skin_id,
            $style_id,
            $this->ref_id,
            $read_only,
            $management_enabled
        );

        $this->ctrl->forwardCommand($system_styles_overview);
    }

    /**
     * Checks permission for system styles. Permissions work on two levels, ordinary rbac and the
     * 'enable_system_styles_management' setting in the tools section of the ilias.ini.php
     * @throws ilObjectException
     */
    public function checkPermission(string $a_perm, bool $a_throw_exc = true): bool
    {
        $has_perm = true;

        $config = new ilSystemStyleConfig();
        if ($a_perm == 'sty_management') {
            $has_perm = $this->ilIniFile->readVariable('tools', 'enable_system_styles_management') == '1';
            $a_perm = 'sty_write_system';
            if ($has_perm && !is_writable($config->getCustomizingSkinPath())) {
                $this->message_stack->addMessage(new ilSystemStyleMessage(
                    $this->lng->txt('enable_system_styles_management_no_write_perm'),
                    ilSystemStyleMessage::TYPE_ERROR
                ));
                $this->message_stack->sendMessages();
                $has_perm = false;
            }
        }

        if ($has_perm) {
            $has_perm = $this->rbacsystem->checkAccess($a_perm, (int) $this->ref_id);
        }

        if (!$has_perm) {
            if ($a_throw_exc) {
                throw new ilObjectException($this->lng->txt('sty_permission_denied'));
            }
            return false;
        }
        return true;
    }

    /**
     * Sets the tab correctly if one system style is open (navigational underworld opened)
     * @throws ilCtrlException
     */
    protected function setUnderworldTabs(string $sking_id, string $active = '', bool $read_only = false): void
    {
        $this->tabs->clearTargets();

        if ($read_only) {
            $this->locator->clearItems();
            $this->tpl->setLocator();
            return;
        }

        /**
         * Since clearTargets also clears the help screen ids
         */
        $this->help->setScreenIdComponent('sty');
        $this->help->setScreenId('system_styles');
        $this->tabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this));
        $config = new ilSystemStyleConfig();
        if ($sking_id != $config->getDefaultSkinId()) {
            $this->tabs->addTab(
                'settings',
                $this->lng->txt('settings'),
                $this->ctrl->getLinkTargetByClass('ilsystemstylesettingsgui')
            );
            $this->tabs->addTab(
                'less',
                $this->lng->txt('less'),
                $this->ctrl->getLinkTargetByClass('ilsystemstylelessgui')
            );
            $this->tabs->addTab(
                'icons',
                $this->lng->txt('icons'),
                $this->ctrl->getLinkTargetByClass('ilsystemstyleiconsgui')
            );
        }

        $this->tabs->addTab(
            'documentation',
            $this->lng->txt('documentation'),
            $this->ctrl->getLinkTargetByClass('ilsystemstyledocumentationgui')
        );

        $this->tabs->activateTab($active);
    }

    /**
     * Sets title correctly if one system style is opened
     * @throws ilSystemStyleException
     */
    protected function setUnderworldTitle(string $skin_id, string $style_id, bool $read_only = false): void
    {
        $skin = $this->skin_factory->skinStyleContainerFromId($skin_id, $this->message_stack)->getSkin();
        $style = $skin->getStyle($style_id);

        if ($read_only) {
            $this->tpl->setTitle($this->lng->txt('documentation'));

            if ($style->isSubstyle()) {
                $this->tpl->setDescription(
                    $this->lng->txt('ks_documentation_of_substyle')
                    . " '"
                    . $style->getName() . "' " .
                    $this->lng->txt('of_parent') . " '" . $skin->getStyle($style->getSubstyleOf())->getName() . "' " .
                    $this->lng->txt('from_skin') . ' ' . $skin->getName()
                );
            } else {
                $this->tpl->setDescription(
                    $this->lng->txt('ks_documentation_of_style') . " '" . $style->getName() . "' " .
                    $this->lng->txt('from_skin') . " '" . $skin->getName() . "'"
                );
            }
        } else {
            $this->tpl->setTitle($style->getName());
            if ($style->isSubstyle()) {
                $this->tpl->setDescription(
                    $this->lng->txt('settings_of_substyle') . " '" . $style->getName() . "' " .
                    $this->lng->txt('of_parent') . " '" . $skin->getStyle($style->getSubstyleOf())->getName() . "' " .
                    $this->lng->txt('from_skin') . ' ' . $skin->getName()
                );
            } else {
                $this->tpl->setDescription(
                    $this->lng->txt('settings_of_style') . " '" . $style->getName() . "' " .
                    $this->lng->txt('from_skin') . " '" . $skin->getName() . "'"
                );
            }
        }
    }
}
