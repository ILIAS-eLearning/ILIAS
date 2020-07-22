<?php

declare(strict_types=1);

use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Listing\Workflow\Workflow;

/**
 * Class ilKioskPageRenderer
 */
class ilKioskPageRenderer
{
    public function __construct(
        ilTemplate $il_global_template,
        Renderer $ui_renderer,
        ilTemplate $kiosk_template,
        ilLSTOCGUI $toc_gui,
        ilLSLocatorGUI $loc_gui,
        string $window_base_title
    ) {
        $this->il_tpl = $il_global_template;
        $this->ui_renderer = $ui_renderer;
        $this->tpl = $kiosk_template;
        $this->toc_gui = $toc_gui;
        $this->loc_gui = $loc_gui;
        $this->window_base_title = $window_base_title;
    }

    public function render(
        string $lso_title,
        LSControlBuilder $control_builder,
        string $obj_title,
        Component $icon,
        array $content,
        Workflow $curriculum
    ) : string {
        $this->tpl->setVariable(
            "HTML_PAGE_TITLE",
            $this->window_base_title . ' - ' . $lso_title
        );

        $this->tpl->setVariable(
            "TOPBAR_CONTROLS",
            $this->ui_renderer->render($control_builder->getExitControl())
        );

        $this->tpl->setVariable("TOPBAR_TITLE", $lso_title);

        $this->tpl->setVariable(
            "OBJECT_ICON",
            $this->ui_renderer->render($icon)
        );
        $this->tpl->setVariable("OBJECT_TITLE", $obj_title);

        $this->tpl->setVariable(
            "PLAYER_NAVIGATION",
            $this->ui_renderer->render([
                $control_builder->getPreviousControl(),
                $control_builder->getNextControl()
            ])
        );

        $controls = $control_builder->getControls();

        //ensure done control is first element
        if ($control_builder->getDoneControl()) {
            array_unshift($controls, $control_builder->getDoneControl());
        }
        //also shift start control up front - this is for legacy-views only!
        if ($control_builder->getStartControl()) {
            array_unshift($controls, $control_builder->getStartControl());
            $this->tpl->setVariable("JS_INLINE", $control_builder->getAdditionalJS());
        }


        //TODO: insert toggles

        $this->tpl->setVariable(
            "OBJ_NAVIGATION",
            $this->ui_renderer->render($controls)
        );


        $this->tpl->setVariable(
            "VIEW_MODES",
            $this->ui_renderer->render($control_builder->getModeControls())
        );

        if ($control_builder->getLocator()) {
            $this->tpl->setVariable(
                'LOCATOR',
                $this->ui_renderer->render(
                    $this->loc_gui
                        ->withItems($control_builder->getLocator()->getItems())
                        ->getComponent()
                )
            );
        }

        $this->tpl->setVariable(
            'CONTENT',
            $this->ui_renderer->render($content)
        );
        $this->tpl->setVariable(
            'CURRICULUM',
            $this->ui_renderer->render($curriculum)
        );

        if ($control_builder->getToc()) {
            $this->tpl->touchBlock("sidebar_space");
            $this->tpl->setVariable(
                "SIDEBAR",
                $this->toc_gui
                    ->withStructure($control_builder->getToc()->toJSON())
                    ->getHTML()
            );
        } else {
            $this->tpl->touchBlock("sidebar_disabled");
        }

        $tpl = $this->setHeaderVars($this->tpl);
        return $tpl->get();
    }

    protected function setHeaderVars(ilTemplate $tpl)
    {
        $this->initIlTemplate();
        $js_files = $this->getJsFiles();
        $js_olc = $this->getOnLoadCode();
        $css_files = $this->getCSSFiles();
        $css_inline = $this->getInlineCSS();

        foreach ($js_files as $js_file) {
            $tpl->setCurrentBlock("js_file");
            $tpl->setVariable("JS_FILE", $js_file);
            $tpl->parseCurrentBlock();
        }

        foreach ($css_files as $css_file) {
            $tpl->setCurrentBlock("css_file");
            $tpl->setVariable("CSS_FILE", $css_file);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("CSS_INLINE", implode(PHP_EOL, $css_inline));
        $tpl->setVariable("OLCODE", $js_olc);

        return $tpl;
    }

    protected function initIlTemplate()
    {
        iljQueryUtil::initjQuery($this->il_tpl);
        ilUIFramework::init($this->il_tpl);
    }

    protected function getJsFiles()
    {
        $js_files = $this->il_tpl->js_files_batch;
        $sorted = [
            [],[],[],[]
        ];
        foreach ($js_files as $file => $order) {
            $order = min($order, 3);
            $sorted[$order][] = $file;
        }
        $js_files = array_merge($sorted[0], $sorted[1], $sorted[2], $sorted[3]);
        $js_files = array_filter(
            $js_files,
            function ($js_file) {
                return strpos($js_file, 'Services/FileUpload/js') === false;
            }
        );
        return $js_files;
    }

    protected function getOnLoadCode()
    {
        $olc = '';
        if ($this->il_tpl->on_load_code) {
            foreach ($this->il_tpl->on_load_code as $key => $value) {
                $olc .= implode(PHP_EOL, $value);
            }
        }
        return $olc;
    }

    protected function getCSSFiles()
    {
        foreach ($this->il_tpl->css_files as $il_css_file) {
            $css_files[] = $il_css_file['file'];
        }
        foreach ($this->il_tpl->css_files as $il_css_file) {
            if (!in_array($il_css_file['file'], $css_files)) {
                $css_files[] = $il_css_file['file'];
            }
        }
        $css_files[] = \ilUtil::getStyleSheetLocation("filesystem", "delos.css");
        $css_files[] = \ilUtil::getStyleSheetLocation();
        $css_files[] = \ilUtil::getNewContentStyleSheetLocation();

        return $css_files;
    }
    protected function getInlineCSS()
    {
        return $this->il_tpl->inline_css;
    }
}
