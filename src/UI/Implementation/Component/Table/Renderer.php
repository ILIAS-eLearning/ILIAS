<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use LogicException;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);
        if ($component instanceof Component\Table\Presentation) {
            return $this->renderPresentationTable($component, $default_renderer);
        }
        if ($component instanceof Component\Table\PresentationRow) {
            return $this->renderPresentationRow($component, $default_renderer);
        }
        throw new LogicException("Cannot render: " . get_class($component));
    }

    protected function renderPresentationTable(
        Component\Table\Presentation $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("tpl.presentationtable.html", true, true);

        $tpl->setVariable("TITLE", $component->getTitle());

        $vcs = $component->getViewControls();
        if ($vcs) {
            $tpl->touchBlock("viewcontrols");
            foreach ($vcs as $vc) {
                $tpl->setCurrentBlock("vc");
                $tpl->setVariable("VC", $default_renderer->render($vc));
                $tpl->parseCurrentBlock();
            }
        }
        $row_mapping = $component->getRowMapping();
        $data = $component->getData();

        foreach ($data as $record) {
            $row = $row_mapping(
                new PresentationRow($component->getSignalGenerator()),
                $record,
                $this->getUIFactory(),
                $component->getEnvironment()
            );

            $tpl->setCurrentBlock("row");
            $tpl->setVariable("ROW", $default_renderer->render($row));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    protected function renderPresentationRow(
        Component\Table\PresentationRow $component,
        RendererInterface $default_renderer
    ): string {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.presentationrow.html", true, true);

        $component = $this->registerSignals($component->withResetSignals());
        $sig_show = $component->getShowSignal();
        $sig_hide = $component->getCloseSignal();
        $sig_toggle = $component->getToggleSignal();
        $id = $this->bindJavaScript($component);

        $expander = $f->symbol()->glyph()->expand("#")
            ->withOnClick($sig_show);
        $collapser = $f->symbol()->glyph()->collapse("#")
            ->withOnClick($sig_hide);
        $shy_expander = $f->button()->shy($this->txt("presentation_table_more"), "#")
            ->withOnClick($sig_show);

        $tpl->setVariable("ID", $id);
        $tpl->setVariable("EXPANDER", $default_renderer->render($expander));
        $tpl->setVariable("COLLAPSER", $default_renderer->render($collapser));
        $tpl->setVariable("SHY_EXPANDER", $default_renderer->render($shy_expander));

        $tpl->setVariable("HEADLINE", $component->getHeadline());
        $tpl->setVariable("TOGGLE_SIGNAL", $sig_toggle);
        $subheadline = $component->getSubheadline();
        if ($subheadline) {
            $tpl->setVariable("SUBHEADLINE", $subheadline);
        }

        foreach ($component->getImportantFields() as $label => $value) {
            $tpl->setCurrentBlock("important_field");
            if (is_string($label)) {
                $tpl->setVariable("IMPORTANT_FIELD_LABEL", $label);
            }
            $tpl->setVariable("IMPORTANT_FIELD_VALUE", $value);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("DESCLIST", $default_renderer->render($component->getContent()));

        $further_fields_headline = $component->getFurtherFieldsHeadline();
        $further_fields = $component->getFurtherFields();

        if (count($further_fields) > 0) {
            $tpl->touchBlock("has_further_fields");

            if ($further_fields_headline) {
                $tpl->setVariable("FURTHER_FIELDS_HEADLINE", $further_fields_headline);
            }

            foreach ($further_fields as $label => $value) {
                $tpl->setCurrentBlock("further_field");
                if (is_string($label)) {
                    $tpl->setVariable("FIELD_LABEL", $label);
                }
                $tpl->setVariable("FIELD_VALUE", $value);
                $tpl->parseCurrentBlock();
            }
        }

        $action = $component->getAction();
        if (!is_null($action)) {
            $tpl->setCurrentBlock("button");
            $tpl->setVariable("BUTTON", $default_renderer->render($action));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Table/presentation.js');
    }

    protected function registerSignals(Component\Table\PresentationRow $component): Component\JavaScriptBindable
    {
        $show = $component->getShowSignal();
        $close = $component->getCloseSignal();
        $toggle = $component->getToggleSignal();
        return $component->withAdditionalOnLoadCode(fn ($id) => "$(document).on('$show', function() { il.UI.table.presentation.expandRow('$id'); return false; });" .
        "$(document).on('$close', function() { il.UI.table.presentation.collapseRow('$id'); return false; });" .
        "$(document).on('$toggle', function() { il.UI.table.presentation.toggleRow('$id'); return false; });");
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array(
            Component\Table\PresentationRow::class,
            Component\Table\Presentation::class
        );
    }
}
