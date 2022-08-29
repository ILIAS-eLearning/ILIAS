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

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use LogicException;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\ViewControl
 */
class Renderer extends AbstractComponentRenderer
{
    public const MODE_ROLE = "group";

    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        if ($component instanceof Component\ViewControl\Mode) {
            return $this->renderMode($component, $default_renderer);
        }
        if ($component instanceof Component\ViewControl\Section) {
            return $this->renderSection($component, $default_renderer);
        }
        if ($component instanceof Component\ViewControl\Sortation) {
            return $this->renderSortation($component, $default_renderer);
        }
        if ($component instanceof Component\ViewControl\Pagination) {
            return $this->renderPagination($component, $default_renderer);
        }
        throw new LogicException("Component '{$component->getCanonicalName()}' isn't supported by this renderer.");
    }

    protected function renderMode(Component\ViewControl\Mode $component, RendererInterface $default_renderer): string
    {
        $f = $this->getUIFactory();

        $tpl = $this->getTemplate("tpl.mode.html", true, true);

        $activate_first_item = false;
        $active = $component->getActive();
        if ($active == "") {
            $activate_first_item = true;
        }

        foreach ($component->getLabelledActions() as $label => $action) {
            $tpl->setVariable("ARIA", $this->txt($component->getAriaLabel()));
            $tpl->setVariable("ROLE", self::MODE_ROLE);

            $tpl->setCurrentBlock("view_control");

            //At this point we don't have a specific text for the button aria label.
            // component->getAriaLabel gets the main view control aria label.
            $button = $f->button()->standard($label, $action)->withAriaLabel($label);
            if ($activate_first_item) {
                $button = $button->withEngagedState(true);
                $activate_first_item = false;
            } elseif ($active == $label) {
                $button = $button->withEngagedState(true);
            } else {
                $button = $button->withEngagedState(false);
            }
            $tpl->setVariable("BUTTON", $default_renderer->render($button));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    protected function renderSection(
        Component\ViewControl\Section $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("tpl.section.html", true, true);

        // render middle button
        $tpl->setVariable("BUTTON", $default_renderer->render($component->getSelectorButton()));

        // previous button
        $this->renderSectionButton($component->getPreviousActions(), $tpl, "prev");

        // next button
        $this->renderSectionButton($component->getNextActions(), $tpl, "next");

        return $tpl->get();
    }

    protected function renderSectionButton(Component\Button\Button $component, Template $tpl, string $type): void
    {
        $uptype = strtoupper($type);

        $action = $component->getAction();
        $tpl->setVariable($uptype . "_ACTION", $action);
        $label = ($type == "next")
            ? $this->txt("next")
            : $this->txt("previous");
        $tpl->setVariable($uptype . "_LABEL", $label);
        if ($component->isActive()) {
            $tpl->setCurrentBlock($type . "_with_href");
            $tpl->setVariable($uptype . "_HREF", $action);
            $tpl->parseCurrentBlock();
        } else {
            $tpl->touchBlock($type . "_disabled");
        }
        $this->renderId($component, $tpl, $type . "_with_id", $uptype . "_ID");
    }

    protected function renderSortation(
        Component\ViewControl\Sortation $component,
        RendererInterface $default_renderer
    ): string {
        $f = $this->getUIFactory();

        $tpl = $this->getTemplate("tpl.sortation.html", true, true);

        $component = $component->withResetSignals();
        $triggeredSignals = $component->getTriggeredSignals();
        if ($triggeredSignals) {
            $internal_signal = $component->getSelectSignal();
            $signal = $triggeredSignals[0]->getSignal();

            $component = $component->withAdditionalOnLoadCode(fn ($id) => "$(document).on('$internal_signal', function(event, signalData) {
							il.UI.viewcontrol.sortation.onInternalSelect(event, signalData, '$signal', '$id');
							return false;
						})");
        }

        $this->renderId($component, $tpl, "id", "ID");

        //setup entries
        $options = $component->getOptions();
        $init_label = $component->getLabel();
        $items = array();
        foreach ($options as $val => $label) {
            if ($triggeredSignals) {
                $shy = $f->button()->shy($label, $val)->withOnClick($internal_signal);
            } else {
                $url = $component->getTargetURL() ?? '';
                $url .= (strpos($url, '?') === false) ? '?' : '&';
                $url .= $component->getParameterName() . '=' . $val;
                $shy = $f->button()->shy($label, $url);
            }
            $items[] = $shy;
        }

        $dd = $f->dropdown()->standard($items)
            ->withLabel($init_label);

        $tpl->setVariable('SORTATION_DROPDOWN', $default_renderer->render($dd));
        return $tpl->get();
    }

    protected function renderPagination(
        Component\ViewControl\Pagination $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("tpl.pagination.html", true, true);

        /**
         * @var $component Component\ViewControl\Pagination
         */
        $component = $component->withResetSignals();
        $triggeredSignals = $component->getTriggeredSignals();
        if ($triggeredSignals) {
            $internal_signal = $component->getInternalSignal();
            $signal = $triggeredSignals[0]->getSignal();
            $component = $component->withOnLoadCode(fn ($id) => "$(document).on('$internal_signal', function(event, signalData) {
							il.UI.viewcontrol.pagination.onInternalSelect(event, signalData, '$signal', '$id');
							return false;
						})");

            $id = $this->bindJavaScript($component);
            $tpl->setVariable('ID', $id);
        }

        $range = $this->getPaginationRange($component);
        $chunk_options = array();
        foreach ($range as $entry) {
            $shy = $this->getPaginationShyButton($entry, $component);
            if ($entry === $component->getCurrentPage()) {
                $shy = $shy->withEngagedState(true);
            }
            $chunk_options[] = $shy;
        }

        if ($component->getDropdownAt() == null ||
            $component->getDropdownAt() > $component->getNumberOfPages()) {
            foreach ($chunk_options as $entry) {
                $tpl->setCurrentBlock("entry");
                $tpl->setVariable('BUTTON', $default_renderer->render($entry));
                $tpl->parseCurrentBlock();
            }
        } else {
            //if threshold is reached, render as dropdown
            $f = $this->getUIFactory();

            $dd_label_template = $component->getDropdownLabel();
            if ($dd_label_template === $component->getDefaultDropdownLabel()) {
                $dd_label_template = $this->txt($dd_label_template);
            }
            $dd_label = sprintf(
                $dd_label_template,
                $component->getCurrentPage() + 1,
                $component->getNumberOfPages()
            );

            $dd = $f->dropdown()->standard($chunk_options)->withLabel($dd_label);
            $tpl->setCurrentBlock("entry");
            $tpl->setVariable('BUTTON', $default_renderer->render($dd));
            $tpl->parseCurrentBlock();
        }

        if ($component->getMaxPaginationButtons()) {
            $this->setPaginationFirstLast($component, $range, $default_renderer, $tpl);
        }

        $this->setPaginationBrowseControls($component, $default_renderer, $tpl);
        return $tpl->get();
    }

    /**
     * Get the range of pagination-buttons to show.
     *
     * @return  int[]
     */
    protected function getPaginationRange(Component\ViewControl\Pagination $component): array
    {
        if (!$component->getMaxPaginationButtons()) {
            $start = 0;
            $stop = max($component->getNumberOfPages() - 1, 0);
        } else {
            //current page should be in the middle, so start is half the amount of max entries:
            $start = (int) ($component->getCurrentPage() - floor($component->getMaxPaginationButtons() / 2));
            $start = max($start, 0); //0, if negative
            //stop is (calculated) start plus number of entries:
            $stop = $start + $component->getMaxPaginationButtons() - 1;
            //if stop exceeds max pages, recalculate both:
            if ($stop > $component->getNumberOfPages() - 1) {
                $stop = max($component->getNumberOfPages() - 1, 0); //0, if negative
                $start = $stop - $component->getMaxPaginationButtons();
                $start = max($start, 0); //0, if negative
            }
        }
        return range($start, $stop);
    }

    protected function getPaginationShyButton(
        int $val,
        Component\ViewControl\Pagination $component,
        string $label = ''
    ): Shy {
        $f = $this->getUIFactory();

        if ($label === '') {
            $label = (string) ($val + 1);
        }

        if ($component->getTriggeredSignals()) {
            $shy = $f->button()->shy($label, (string) $val)->withOnClick($component->getInternalSignal());
        } else {
            $url = $component->getTargetURL() ?? '';
            if (strpos($url, '?') === false) {
                $url .= '?' . $component->getParameterName() . '=' . $val;
            } else {
                $base = substr($url, 0, strpos($url, '?') + 1);
                $query = parse_url($url, PHP_URL_QUERY);
                parse_str($query, $params);
                $params[$component->getParameterName()] = $val;
                $url = $base . http_build_query($params);
            }
            $shy = $f->button()->shy($label, $url);
        }
        return $shy;
    }

    /**
     * Add back/next-glyphs to the template for left/right browsing in pagination
     */
    protected function setPaginationBrowseControls(
        Component\ViewControl\Pagination $component,
        RendererInterface $default_renderer,
        Template $tpl
    ): void {
        $prev = max(0, $component->getCurrentPage() - 1);
        $next = $component->getCurrentPage() + 1;

        $f = $this->getUIFactory();

        if ($component->getTriggeredSignals()) {
            $back = $f->symbol()->glyph()->back('')->withOnClick($component->getInternalSignal());
            $forward = $f->symbol()->glyph()->next('')->withOnClick($component->getInternalSignal());
        } else {
            $url = $component->getTargetURL() ?? '';
            if (strpos($url, '?') === false) {
                $url_prev = $url . '?' . $component->getParameterName() . '=' . $prev;
                $url_next = $url . '?' . $component->getParameterName() . '=' . $next;
            } else {
                $base = substr($url, 0, strpos($url, '?') + 1);
                $query = parse_url($url, PHP_URL_QUERY);
                parse_str($query, $params);

                $params[$component->getParameterName()] = $prev;
                $url_prev = $base . http_build_query($params);
                $params[$component->getParameterName()] = $next;
                $url_next = $base . http_build_query($params);
            }

            $back = $f->symbol()->glyph()->back($url_prev);
            $forward = $f->symbol()->glyph()->next($url_next);
        }

        if ($component->getCurrentPage() === 0) {
            $back = $back->withUnavailableAction();
        }
        if ($component->getCurrentPage() >= $component->getNumberOfPages() - 1) {
            $forward = $forward->withUnavailableAction();
        }

        $tpl->setVariable('PREVIOUS', $default_renderer->render($back));
        $tpl->setVariable('NEXT', $default_renderer->render($forward));
    }

    /**
     * Add quick-access to first/last pages in pagination.
     *
     * @param int[]	$range
     */
    protected function setPaginationFirstLast(
        Component\ViewControl\Pagination $component,
        array $range,
        RendererInterface $default_renderer,
        Template $tpl
    ): void {
        if (!in_array(0, $range)) {
            $shy = $this->getPaginationShyButton(0, $component);
            $tpl->setVariable('FIRST', $default_renderer->render($shy));
        }
        $last = max($component->getNumberOfPages() - 1, 0);
        if (!in_array($last, $range)) {
            $shy = $this->getPaginationShyButton($component->getNumberOfPages() - 1, $component);
            $tpl->setVariable('LAST', $default_renderer->render($shy));
        }
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/ViewControl/sortation.js');
        $registry->register('./src/UI/templates/js/ViewControl/pagination.js');
    }

    protected function renderId(
        Component\JavaScriptBindable $component,
        Template $tpl,
        string $block,
        string $template_var
    ): void {
        $id = $this->bindJavaScript($component);
        if (!$id) {
            $id = $this->createId();
        }
        $tpl->setCurrentBlock($block);
        $tpl->setVariable($template_var, $id);
        $tpl->parseCurrentBlock();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array(
            Component\ViewControl\Mode::class,
            Component\ViewControl\Section::class,
            Component\ViewControl\Sortation::class,
            Component\ViewControl\Pagination::class
        );
    }
}
