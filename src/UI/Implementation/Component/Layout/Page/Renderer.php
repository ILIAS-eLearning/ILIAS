<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Layout\Page;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\ilTemplateWrapper as UITemplateWrapper;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Image\Image;

class Renderer extends AbstractComponentRenderer
{
    public const COOKIE_NAME_SLATES_ENGAGED = 'il_mb_slates';
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        if ($component instanceof Component\Layout\Page\Standard) {
            return $this->renderStandardPage($component, $default_renderer);
        }
    }


    protected function renderStandardPage(Component\Layout\Page\Standard $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.standardpage.html", true, true);

        if ($component->hasMetabar()) {
            $tpl->setVariable('METABAR', $default_renderer->render($component->getMetabar()));
        }
        if ($component->hasMainbar()) {
            $tpl->setVariable('MAINBAR', $default_renderer->render($component->getMainbar()));
            $slates_cookie = $_COOKIE[self::COOKIE_NAME_SLATES_ENGAGED] ?? '';
            if ($slates_cookie && json_decode($slates_cookie, true)['engaged']) {
                $tpl->touchBlock('slates_engaged');
            }
        }
        if ($component->hasModeInfo()) {
            $tpl->setVariable('MODEINFO', $default_renderer->render($component->getModeInfo()));
        }
        if ($component->hasSystemInfos()) {
            $tpl->setVariable('SYSTEMINFOS', $default_renderer->render($component->getSystemInfos()));
        }

        $breadcrumbs = $component->getBreadcrumbs();
        if ($breadcrumbs && $breadcrumbs->getItems()) {
            $tpl->setVariable('BREADCRUMBS', $default_renderer->render($breadcrumbs));

            $dropdown = $this->convertBreadcrumbsToDropdownLocator($breadcrumbs);
            $tpl->setVariable('HEADER_BREADCRUMBS', $default_renderer->render($dropdown));
        }
        if ($component->hasLogo()) {
            $tpl->setVariable('LOGO', $default_renderer->render($component->getLogo()));
        }
        if ($component->hasResponsiveLogo()) {
            $tpl->setVariable('RESPONSIVE_LOGO', $default_renderer->render($component->getResponsiveLogo()));
        } elseif ($component->hasLogo()) {
            $tpl->setVariable('RESPONSIVE_LOGO', $default_renderer->render($component->getLogo()));
        }

        $tpl->setVariable("TITLE", $component->getTitle());
        $tpl->setVariable("SHORT_TITLE", $component->getShortTitle());
        $tpl->setVariable("VIEW_TITLE", $component->getViewTitle());
        $tpl->setVariable("LANGUAGE", $this->getLangKey());
        $tpl->setVariable("TEXT_DIRECTION", $component->getTextDirection());
        $tpl->setVariable('CONTENT', $default_renderer->render($component->getContent()));

        if ($component->hasFooter()) {
            $tpl->setVariable('FOOTER', $default_renderer->render($component->getFooter()));
        }

        if ($component->getWithHeaders()) {
            $tpl = $this->setHeaderVars($tpl, $component->getIsUIDemo());
        }
    
        foreach ($component->getMetaData() as $meta_key => $meta_value) {
            $tpl->setCurrentBlock('meta_datum');
            $tpl->setVariable('META_KEY', $meta_key);
            $tpl->setVariable('META_VALUE', $meta_value);
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    protected function convertBreadcrumbsToDropdownLocator(
        Component\Breadcrumbs\Breadcrumbs $breadcrumbs
    ) : Component\Dropdown\Dropdown {
        $f = $this->getUIFactory();
        $buttons = [];
        $items = array_reverse($breadcrumbs->getItems());
        $current = array_shift($items);
        foreach ($items as $item) {
            $button = $f->button()->shy(
                $item->getLabel(),
                $item->getAction()
            );
            $buttons[] = $button;
        }
        return $f->dropdown()->standard($buttons)->withLabel($current->getLabel());
    }

    /**
     * When rendering the whole page, all resources must be included.
     * This is for now and the page-demo to work, lateron this must be replaced
     * with resources set as properties at the page or similar mechanisms.
     * Please also see ROADMAP.md, "Page-Layout and ilTemplate, CSS/JS Header".
     *
     * @param \ilGlobalPageTemplate $tpl
     *
     * @return \ilGlobalPageTemplate
     * @throws \ILIAS\UI\NotImplementedException
     */
    protected function setHeaderVars($tpl, bool $for_ui_demo = false)
    {
        global $DIC;
        $il_tpl = $DIC["tpl"] ?? null;

        $js_files = [];
        $js_inline = [];
        $css_files = [];
        $css_inline = [];

        if ($il_tpl instanceof \ilGlobalPageTemplate) {
            $layout = $DIC->globalScreen()->layout();
            foreach ($layout->meta()->getJs()->getItemsInOrderOfDelivery() as $js) {
                $js_files[] = $js->getContent();
            }
            foreach ($layout->meta()->getCss()->getItemsInOrderOfDelivery() as $css) {
                $css_files[] = ['file' => $css->getContent(), 'media' => $css->getMedia()];
            }
            foreach ($layout->meta()->getInlineCss()->getItemsInOrderOfDelivery() as $inline_css) {
                $css_inline[] = $inline_css->getContent();
            }
            foreach ($layout->meta()->getOnloadCode()->getItemsInOrderOfDelivery() as $on_load_code) {
                $js_inline[] = $on_load_code->getContent();
            }
        }

        if ($for_ui_demo) {
            $base_url = '../../../../../../';
            $tpl->setVariable("BASE", $base_url);

            array_unshift($js_files, './Services/JavaScript/js/Basic.js');

            include_once("./Services/UICore/classes/class.ilUIFramework.php");
            foreach (\ilUIFramework::getJSFiles() as $il_js_file) {
                array_unshift($js_files, $il_js_file);
            }

            include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
            array_unshift($js_files, './libs/bower/bower_components/jquery-migrate/jquery-migrate.min.js');
            array_unshift($js_files, \iljQueryUtil::getLocaljQueryPath());
        }

        foreach ($js_files as $js_file) {
            $tpl->setCurrentBlock("js_file");
            $tpl->setVariable("JS_FILE", $js_file);
            $tpl->parseCurrentBlock();
        }
        foreach ($css_files as $css_file) {
            $tpl->setCurrentBlock("css_file");
            $tpl->setVariable("CSS_FILE", $css_file['file']);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("CSS_INLINE", implode(PHP_EOL, $css_inline));
        $tpl->setVariable("OLCODE", implode(PHP_EOL, $js_inline));


        return $tpl;
    }

    /**
     * @inheritdoc
     */
    public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Page/stdpage.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(
            Component\Layout\Page\Standard::class
        );
    }
}
