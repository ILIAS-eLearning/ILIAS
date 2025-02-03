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

namespace ILIAS\UICore;

use ilGlobalTemplateInterface;
use ilToolbarGUI;

class GlobalTemplateLegacyInitialisationAdapter implements GlobalTemplate
{
    public function hideFooter(): void
    {
        $this->getLegacyGlobalTemplateInstance()->hideFooter();
    }

    public function setOnScreenMessage(string $type, string $a_txt, bool $a_keep = false): void
    {
        $this->getLegacyGlobalTemplateInstance()->setOnScreenMessage($type, $a_txt, $a_keep);
    }

    public function addJavaScript(string $a_js_file, bool $a_add_version_parameter = true, int $a_batch = 2): void
    {
        $this->getLegacyGlobalTemplateInstance()->addJavaScript($a_js_file, $a_add_version_parameter, $a_batch);
    }

    public function addOnLoadCode(string $a_code, int $a_batch = 2): void
    {
        $this->getLegacyGlobalTemplateInstance()->addOnLoadCode($a_code, $a_batch);
    }

    public function getOnLoadCodeForAsynch(): string
    {
        return $this->getLegacyGlobalTemplateInstance()->getOnLoadCodeForAsynch();
    }

    public function resetJavascript(): void
    {
        $this->getLegacyGlobalTemplateInstance()->resetJavascript();
    }

    public function fillJavaScriptFiles(bool $a_force = false): void
    {
        $this->getLegacyGlobalTemplateInstance()->fillJavaScriptFiles($a_force);
    }

    public function addCss(string $a_css_file, string $media = "screen"): void
    {
        $this->getLegacyGlobalTemplateInstance()->addCss($a_css_file, $media);
    }

    public function addInlineCss(string $a_css, string $media = "screen"): void
    {
        $this->getLegacyGlobalTemplateInstance()->addInlineCss($a_css, $media);
    }

    public function setBodyClass(string $a_class = ""): void
    {
        $this->getLegacyGlobalTemplateInstance()->setBodyClass($a_class);
    }

    public function loadStandardTemplate(): void
    {
        $this->getLegacyGlobalTemplateInstance()->loadStandardTemplate();
    }

    public function setTitle(string $a_title, bool $hidden = false): void
    {
        $this->getLegacyGlobalTemplateInstance()->setTitle($a_title, $hidden);
    }

    public function setDescription(string $a_descr): void
    {
        $this->getLegacyGlobalTemplateInstance()->setDescription($a_descr);
    }

    public function setTitleIcon(string $a_icon_path, string $a_icon_desc = ""): void
    {
        $this->getLegacyGlobalTemplateInstance()->setTitleIcon($a_icon_path, $a_icon_desc);
    }

    public function setAlertProperties(array $alerts): void
    {
        $this->getLegacyGlobalTemplateInstance()->setAlertProperties($alerts);
    }

    public function clearHeader(): void
    {
        $this->getLegacyGlobalTemplateInstance()->clearHeader();
    }

    public function setHeaderActionMenu(string $a_header): void
    {
        $this->getLegacyGlobalTemplateInstance()->setHeaderActionMenu($a_header);
    }

    public function setHeaderPageTitle(string $a_title): void
    {
        $this->getLegacyGlobalTemplateInstance()->setHeaderPageTitle($a_title);
    }

    public function setLocator(): void
    {
        $this->getLegacyGlobalTemplateInstance()->setLocator();
    }

    public function setTabs(string $a_tabs_html): void
    {
        $this->getLegacyGlobalTemplateInstance()->setTabs($a_tabs_html);
    }

    public function setSubTabs(string $a_tabs_html): void
    {
        $this->getLegacyGlobalTemplateInstance()->setSubTabs($a_tabs_html);
    }

    public function setContent(string $a_html): void
    {
        $this->getLegacyGlobalTemplateInstance()->setContent($a_html);
    }

    public function setLeftContent(string $a_html): void
    {
        $this->getLegacyGlobalTemplateInstance()->setLeftContent($a_html);
    }

    public function setLeftNavContent(string $a_content): void
    {
        $this->getLegacyGlobalTemplateInstance()->setLeftNavContent($a_content);
    }

    public function setRightContent(string $a_html): void
    {
        $this->getLegacyGlobalTemplateInstance()->setRightContent($a_html);
    }

    public function setPageFormAction(string $a_action): void
    {
        $this->getLegacyGlobalTemplateInstance()->setPageFormAction($a_action);
    }

    public function setLoginTargetPar(string $a_val): void
    {
        $this->getLegacyGlobalTemplateInstance()->setLoginTargetPar($a_val);
    }

    public function getSpecial(
        string $part = self::DEFAULT_BLOCK,
        bool $add_error_mess = false,
        bool $handle_referer = false,
        bool $add_ilias_footer = false,
        bool $add_standard_elements = false,
        bool $a_main_menu = true,
        bool $a_tabs = true
    ): string {
        return $this->getLegacyGlobalTemplateInstance()->getSpecial(
            $part,
            $add_error_mess,
            $handle_referer,
            $add_ilias_footer,
            $add_standard_elements,
            $a_main_menu,
            $a_tabs
        );
    }

    public function printToStdout(
        string $part = self::DEFAULT_BLOCK,
        bool $has_tabs = true,
        bool $skip_main_menu = false
    ): void {
        $this->getLegacyGlobalTemplateInstance()->printToStdout($part, $has_tabs, $skip_main_menu);
    }

    public function printToString(): string
    {
        return $this->getLegacyGlobalTemplateInstance()->printToString();
    }

    public function setTreeFlatIcon(string $a_link, string $a_mode): void
    {
        $this->getLegacyGlobalTemplateInstance()->setTreeFlatIcon($a_link, $a_mode);
    }

    public function addAdminPanelToolbar(
        ilToolbarGUI $toolbar,
        bool $is_bottom_panel = true,
        bool $has_arrow = false
    ): void {
        $this->getLegacyGlobalTemplateInstance()->addAdminPanelToolbar($toolbar, $is_bottom_panel, $has_arrow);
    }

    public function setPermanentLink(
        string $a_type,
        ?int $a_id,
        string $a_append = "",
        string $a_target = "",
        string $a_title = ""
    ): void {
        $this->getLegacyGlobalTemplateInstance()->setPermanentLink($a_type, $a_id, $a_append, $a_target, $a_title);
    }

    public function resetHeaderBlock(bool $a_reset_header_action = true): void
    {
        $this->getLegacyGlobalTemplateInstance()->resetHeaderBlock($a_reset_header_action);
    }

    public function setFileUploadRefId(int $a_ref_id): void
    {
        $this->getLegacyGlobalTemplateInstance()->setFileUploadRefId($a_ref_id);
    }

    public function get(string $part = self::DEFAULT_BLOCK): string
    {
        return $this->getLegacyGlobalTemplateInstance()->get($part);
    }

    public function setVariable(string $variable, $value = ''): void
    {
        $this->getLegacyGlobalTemplateInstance()->setVariable($variable, $value);
    }

    public function setCurrentBlock(string $part = self::DEFAULT_BLOCK): bool
    {
        return $this->getLegacyGlobalTemplateInstance()->setCurrentBlock($part);
    }

    public function parseCurrentBlock(string $block_name = self::DEFAULT_BLOCK): bool
    {
        return $this->getLegacyGlobalTemplateInstance()->parseCurrentBlock($block_name);
    }

    public function touchBlock(string $block): bool
    {
        return $this->getLegacyGlobalTemplateInstance()->touchBlock($block);
    }

    public function addBlockFile(string $var, string $block, string $template_name, ?string $in_module = null): bool
    {
        return $this->getLegacyGlobalTemplateInstance()->addBlockFile($var, $block, $template_name, $in_module);
    }

    public function blockExists(string $block_name): bool
    {
        return $this->getLegacyGlobalTemplateInstance()->blockExists($block_name);
    }

    protected function getLegacyGlobalTemplateInstance(): GlobalTemplate
    {
        global $DIC;
        return $DIC->ui()->mainTemplate();
    }
}
