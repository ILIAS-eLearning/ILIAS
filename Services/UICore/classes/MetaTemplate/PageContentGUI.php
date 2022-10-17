<?php

declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Services\UICore\MetaTemplate;

use ilGlobalTemplateInterface;
use InvalidArgumentException;
use ilTemplateException;
use ilToolbarGUI;
use ilTemplate;
use ilSession;
use ilSystemStyleException;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ilObjFileUploadDropzone;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class PageContentGUI
{
    protected ilTemplate $template;
    protected ?ilToolbarGUI $admin_panel_commands_toolbar = null;

    /**
     * @var array<string, string>
     */
    protected array $lightbox = [];

    /**
     * @var array<string, string>
     */
    protected array $messages = [];

    /**
     * @var array<int, array>
     */
    protected array $title_alerts = [];

    protected ?string $page_form_action = null;
    protected ?string $title = null;
    protected ?string $title_desc = null;
    protected ?string $header_action = null;
    protected ?string $tabs_html = null;
    protected ?string $sub_tabs_html = null;
    protected ?string $main_content = null;
    protected ?string $right_content = null;
    protected ?string $left_content = null;
    protected ?string $icon_path = null;
    protected ?string $icon_desc = null;
    protected ?string $filter = null;
    protected ?string $banner_image_src = null;
    protected ?int $file_upload_ref_id = null;
    protected bool $is_title_hidden = false;
    protected bool $should_display_admin_panel_arrow = false;
    protected bool $is_admin_panel_for_bottom = false;
    private \ILIAS\DI\UIServices $ui_service;

    /**
     * @throws ilTemplateException|ilSystemStyleException
     */
    public function __construct(
        string $file,
        bool $flag1,
        bool $flag2,
        string $in_module = '',
        string $vars = ilGlobalTemplateInterface::DEFAULT_BLOCK,
        bool $plugin = false,
        bool $a_use_cache = true
    ) {
        global $DIC;
        $this->template = new ilTemplate(
            $file,
            $flag1,
            $flag2,
            $in_module,
            $vars,
            $plugin,
            $a_use_cache
        );
        $this->ui_service = $DIC->ui();
    }

    public function addBlockFile(string $var, string $block, string $template_name, string $in_module = null): bool
    {
        return $this->template->addBlockFile($var, $block, $template_name, $in_module);
    }

    public function blockExists(string $block_name): bool
    {
        return $this->template->blockExists($block_name);
    }

    public function removeBlockData(string $block_name): void
    {
        $this->template->removeBlockData($block_name);
    }

    public function setVariable(string $variable, string $value = ''): void
    {
        $this->template->setVariable($variable, $value);
    }

    public function setCurrentBlock(string $block_name = ilGlobalTemplateInterface::DEFAULT_BLOCK): bool
    {
        return $this->template->setCurrentBlock($block_name);
    }

    public function touchBlock(string $block_name): void
    {
        $this->template->touchBlock($block_name);
    }

    public function parseCurrentBlock(string $block_name = ilGlobalTemplateInterface::DEFAULT_BLOCK): bool
    {
        return $this->template->parseCurrentBlock($block_name);
    }

    public function setPageFormAction(string $page_form_action): void
    {
        if (!empty($page_form_action)) {
            $this->page_form_action = $page_form_action;
        }
    }

    public function setMainContent(string $main_content): void
    {
        if (!empty($main_content)) {
            $this->main_content = $main_content;
        }
    }

    public function addLightbox(string $lightbox_html, string $id): void
    {
        if (!empty($lightbox_html)) {
            $this->lightbox[$id] = $lightbox_html;
        }
    }

    public function setHeaderPageTitle(string $header_page_title): void
    {
        // property is never used.
    }

    public function setBanner(string $image_src): void
    {
        if (!empty($image_src)) {
            $this->banner_image_src = $image_src;
        }
    }

    public function getBanner(): ?string
    {
        return $this->banner_image_src;
    }

    public function setTitle(string $title, bool $is_hidden = false): void
    {
        if (!empty($title)) {
            $this->title = $title;
            $this->is_title_hidden = $is_hidden;
        }
    }

    public function setTitleDesc(string $title_desc): void
    {
        if (!empty($title_desc)) {
            $this->title_desc = $title_desc;
        }
    }

    public function setTitleAlerts(array $title_alerts): void
    {
        $this->title_alerts = $title_alerts;
    }

    public function setHeaderAction(string $header_action): void
    {
        if (!empty($header_action)) {
            $this->header_action = $header_action;
        }
    }

    public function setAdminPanelCommandsToolbar(ilToolbarGUI $admin_panel_commands_toolbar): void
    {
        $this->admin_panel_commands_toolbar = $admin_panel_commands_toolbar;
    }

    public function setAdminPanelArrow(bool $should_display_admin_panel_arrow): void
    {
        $this->should_display_admin_panel_arrow = $should_display_admin_panel_arrow;
    }

    public function setAdminPanelBottom(bool $is_admin_panel_for_bottom): void
    {
        $this->is_admin_panel_for_bottom = $is_admin_panel_for_bottom;
    }

    public function setRightContent(string $content): void
    {
        if (!empty($content)) {
            $this->right_content = $content;
        }
    }

    public function setLeftContent(string $content): void
    {
        if (!empty($content)) {
            $this->left_content = $content;
        }
    }

    public function setFilter(string $filter): void
    {
        if (!empty($filter)) {
            $this->filter = $filter;
        }
    }

    protected function fillFilter(): void
    {
        if (null !== $this->filter) {
            $this->template->setCurrentBlock("filter");
            $this->template->setVariable("FILTER", trim($this->filter));
            $this->template->parseCurrentBlock();
        }
    }

    public function setIconPath(string $icon_path): void
    {
        if (!empty($icon_path)) {
            $this->icon_path = $icon_path;
        }
    }

    public function setIconDesc(string $icon_desc): void
    {
        if (!empty($icon_desc)) {
            $this->icon_desc = $icon_desc;
        }
    }

    public function setFileUploadRefId(int $upload_ref_id): void
    {
        $this->file_upload_ref_id = $upload_ref_id;
    }

    public function setOnScreenMessage(string $type, string $message, bool $should_keep = false): void
    {
        if (!in_array($type, ilGlobalTemplateInterface::MESSAGE_TYPES, true)) {
            throw new InvalidArgumentException("Type '$type' is not declared in " . self::class . "::MESSAGE_TYPES and is therefore invalid.");
        }

        if (!$should_keep) {
            $this->messages[$type] = $message;
        } else {
            ilSession::set($type, $message);
        }
    }

    public function get(string $part = ilGlobalTemplateInterface::DEFAULT_BLOCK): string
    {
        return $this->template->get($part);
    }

    public function renderPage(string $part, bool $a_fill_tabs): string
    {
        global $DIC;

        $this->fillMessage();
        $this->fillPageFormAction();

        if ($a_fill_tabs) {
            if ($this->template->blockExists("content")) {
                // determine default screen id
                $this->getTabsHTML();
            }

            $this->fillHeader();
            $this->fillLeftContent();
            $this->fillRightContent();
            $this->fillAdminPanel();
            $this->fillToolbar();
            $this->fillFilter();
            $this->setCenterColumnClass();

            // these fill just plain placeholder variables in tpl.adm_content.html
            if ($this->template->blockExists("content")) {
                $this->template->setCurrentBlock("content");
                $this->fillTabs();
                $this->fillMainContent();
                $this->fillLightbox();
                $this->template->parseCurrentBlock();
            }
        }

        if (ilGlobalTemplateInterface::DEFAULT_BLOCK === $part) {
            $html = $this->template->getUnmodified();
        } else {
            $html = $this->template->getUnmodified($part);
        }

        // Modification of html is done inline here and can't be done
        // by ilTemplate, because the "phase" is template_show in this
        // case here.
        $component_factory = $DIC["component.factory"];
        foreach ($component_factory->getActivePluginsInSlot("uihk") as $ui_plugin) {
            $gui_class = $ui_plugin->getUIClassInstance();

            $resp = $gui_class->getHTML(
                "",
                "template_show",
                [
                    "tpl_id" => '',
                    "tpl_obj" => $this,
                    "html" => $html
                ]
            );

            if (\ilUIHookPluginGUI::KEEP !== $resp["mode"]) {
                $html = $gui_class->modifyHTML($html, $resp);
            }
        }

        // save language usages as late as possible
        \ilObjLanguageAccess::_saveUsages();

        return $html;
    }

    protected function fillMessage(): void
    {
        $messages = [];
        foreach (ilGlobalTemplateInterface::MESSAGE_TYPES as $type) {
            $message = $this->getMessageTextForType($type);
            if (null !== $message) {
                $messages[] = $this->getMessageBox($type, $message);
            }
            ilSession::clear($type);
        }

        if (count($messages) > 0) {
            $this->template->setVariable("MESSAGE", $this->ui_service->renderer()->render($messages));
        }
    }

    private function getMessageBox(string $type, string $message): MessageBox
    {
        $box_factory = $this->ui_service->factory()->messageBox();
        switch ($type) {
            case 'info':
                $box = $box_factory->info($message);
                break;
            case 'success':
                $box = $box_factory->success($message);
                break;
            case 'question':
                $box = $box_factory->confirmation($message);
                break;
            case 'failure':
                $box = $box_factory->failure($message);
                break;
            default:
                throw new InvalidArgumentException();
        }

        return $box;
    }

    protected function getMessageTextForType(string $type): ?string
    {
        if (ilSession::has($type)) {
            return (string) ilSession::get($type);
        }

        return $this->messages[$type] ?? null;
    }

    protected function getTabsHTML(): void
    {
        global $DIC;

        $ilTabs = $DIC["ilTabs"];

        if ($this->template->blockExists("tabs_outer_start")) {
            $this->sub_tabs_html = $ilTabs->getSubTabHTML();
            $this->tabs_html = $ilTabs->getHTML(true);
        }
    }

    protected function initHelp(): void
    {
        //\ilHelpGUI::initHelp($this);
    }

    protected function fillHeader(): void
    {
        global $DIC;

        $lng = $DIC->language();

        $header_tpl = new ilTemplate('tpl.il_header.html', true, true);

        $header = false;
        if (null !== $this->banner_image_src && $this->template->blockExists("banner_bl")) {
            $header_tpl->setCurrentBlock("banner_bl");
            $header_tpl->setVariable("BANNER_URL", $this->banner_image_src);
            $header = true;
            $header_tpl->parseCurrentBlock();
        }

        if (null !== $this->icon_path) {
            $header_tpl->setCurrentBlock("header_image");
            if (null !== $this->icon_desc) {
                $header_tpl->setVariable("IMAGE_DESC", $lng->txt("icon") . " " . $this->icon_desc);
            }

            $header_tpl->setVariable("IMG_HEADER", $this->icon_path);
            $header_tpl->parseCurrentBlock();
            $header = true;
        }

        if (null !== $this->title) {
            $title = \ilUtil::stripScriptHTML($this->title);
            $header_tpl->setVariable("HEADER", $title);
            if ($this->is_title_hidden) {
                $header_tpl->touchBlock("hidden_title");
            }

            $header = true;
        }

        if ($header && !$this->is_title_hidden) {
            $header_tpl->setCurrentBlock("header_image");
            $header_tpl->parseCurrentBlock();
        }

        if (null !== $this->title_desc) {
            $header_tpl->setCurrentBlock("header_desc");
            $header_tpl->setVariable("H_DESCRIPTION", $this->title_desc);
            $header_tpl->parseCurrentBlock();
        }

        if (null !== $this->header_action) {
            $header_tpl->setCurrentBlock("head_action_inner");
            $header_tpl->setVariable("HEAD_ACTION", $this->header_action);
            $header_tpl->parseCurrentBlock();
        }

        foreach ($this->title_alerts as $alert) {
            $header_tpl->setCurrentBlock('header_alert');
            if (!(bool) ($alert['propertyNameVisible'] ?? false)) {
                $this->template->setVariable('H_PROP', $alert['property'] . ':');
            }
            $header_tpl->setVariable('H_VALUE', $alert['value']);
            $header_tpl->parseCurrentBlock();
        }

        // add file upload drop zone in header
        if ($this->file_upload_ref_id !== null) {
            $file_upload = new ilObjFileUploadDropzone(
                $this->file_upload_ref_id,
                $header_tpl->get()
            );

            $this->template->setVariable(
                "IL_DROPZONE_HEADER",
                $file_upload->getDropzoneHtml()
            );
        } else {
            $this->template->setVariable("IL_HEADER", $header_tpl->get());
        }
    }


    protected function setCenterColumnClass(): void
    {
        if (!$this->template->blockExists("center_col_width")) {
            return;
        }

        switch (true) {
            case (null !== $this->left_content && null !== $this->right_content):
                $center_column_class = 'col-sm-6';
                break;

            case (null !== $this->left_content || null !== $this->right_content):
                $center_column_class = 'col-sm-9';
                break;

            default:
                $center_column_class = 'col-sm-12';
                break;
        }

        if (null !== $this->left_content) {
            $center_column_class .= " col-sm-push-3";
        }

        $this->template->setCurrentBlock("center_col_width");
        $this->template->setVariable("CENTER_COL", $center_column_class);
        $this->template->parseCurrentBlock();
    }

    protected function fillMainContent(): void
    {
        if (null !== $this->main_content) {
            $this->template->setVariable("ADM_CONTENT", trim($this->main_content));
        }
    }

    protected function fillLeftContent(): void
    {
        if (null !== $this->left_content) {
            $this->template->setCurrentBlock("left_column");
            $this->template->setVariable("LEFT_CONTENT", trim($this->left_content));

            $left_col_class = (null === $this->right_content)
                ? "col-sm-3 col-sm-pull-9"
                : "col-sm-3 col-sm-pull-6";

            $this->template->setVariable("LEFT_COL_CLASS", $left_col_class);
            $this->template->parseCurrentBlock();
        }
    }

    protected function fillRightContent(): void
    {
        if (null !== $this->right_content) {
            $this->template->setCurrentBlock("right_column");
            $this->template->setVariable("RIGHT_CONTENT", trim($this->right_content));
            $this->template->parseCurrentBlock();
        }
    }

    protected function fillAdminPanel(): void
    {
        global $DIC;
        $lng = $DIC->language();

        if (null === $this->admin_panel_commands_toolbar) {
            return;
        }

        $current_toolbar = $this->admin_panel_commands_toolbar;

        // Add arrow if desired.
        if ($this->should_display_admin_panel_arrow) {
            $current_toolbar->setLeadingImage(\ilUtil::getImagePath("arrow_upright.svg"), $lng->txt("actions"));
        }

        $this->fillPageFormAction();

        // Add top admin bar.
        $this->template->setCurrentBlock("adm_view_components");
        $this->template->setVariable("ADM_PANEL1", $current_toolbar->getHTML());
        $this->template->parseCurrentBlock();

        // Add bottom admin bar if user wants one.
        if ($this->is_admin_panel_for_bottom) {
            $this->template->setCurrentBlock("adm_view_components2");

            // Replace previously set arrow image.
            if ($this->should_display_admin_panel_arrow) {
                $current_toolbar->setLeadingImage(\ilUtil::getImagePath("arrow_downright.svg"), $lng->txt("actions"));
            }

            $this->template->setVariable("ADM_PANEL2", $current_toolbar->getHTML());
            $this->template->parseCurrentBlock();
        }
    }

    protected function fillPageFormAction(): void
    {
        if (null !== $this->page_form_action) {
            $this->template->setCurrentBlock("page_form_start");
            $this->template->setVariable("PAGE_FORM_ACTION", $this->page_form_action);
            $this->template->parseCurrentBlock();
            $this->template->touchBlock("page_form_end");
        }
    }

    protected function fillToolbar(): void
    {
        global $DIC;
        $ilToolbar = $DIC["ilToolbar"];

        $toolbar_html = $ilToolbar->getHTML();
        if (!empty($toolbar_html)) {
            $this->template->setCurrentBlock("toolbar_buttons");
            $this->template->setVariable("BUTTONS", $toolbar_html);
            $this->template->parseCurrentBlock();
        }
    }

    protected function fillTabs(): void
    {
        if ($this->template->blockExists("tabs_outer_start")) {
            $this->template->touchBlock("tabs_outer_start");
            $this->template->touchBlock("tabs_outer_end");
            $this->template->touchBlock("tabs_inner_start");
            $this->template->touchBlock("tabs_inner_end");

            if (null !== $this->tabs_html) {
                $this->template->setVariable("TABS", $this->tabs_html);
            }

            if (null !== $this->sub_tabs_html) {
                $this->template->setVariable("SUB_TABS", $this->sub_tabs_html);
            }
        }
    }

    protected function fillLightbox(): void
    {
        $this->template->setVariable('LIGHTBOX', implode('', $this->lightbox));
    }
}
