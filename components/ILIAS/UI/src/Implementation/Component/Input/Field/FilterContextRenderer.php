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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Input\Field as F;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Implementation\Component\Input\Container\Filter\ProxyFilterField;
use LogicException;
use Closure;
use ILIAS\UI\Component\Input\Container\Filter\FilterInput;
use ILIAS\UI\Component\Input\Container\Form\FormInput;

/**
 * Class FilterContextRenderer
 * @package ILIAS\UI\Implementation\Component\Input
 */
class FilterContextRenderer extends Renderer
{
    protected RendererInterface $original_default_renderer;

    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof FilterInput) {
            $component = $this->setSignals($component);
        }

        $this->original_default_renderer = $default_renderer;

        switch (true) {
            case ($component instanceof F\Duration):
                return $this->renderDurationField($component, $default_renderer);

            case ($component instanceof F\Group):
                return $this->renderFieldGroups($component, $default_renderer);

            case ($component instanceof F\Text):
                return $this->renderTextField($component, $default_renderer);

            case ($component instanceof F\Numeric):
                return $this->renderNumericField($component, $default_renderer);

            case ($component instanceof F\Select):
                return $this->renderSelectField($component, $default_renderer);

            case ($component instanceof F\MultiSelect):
                return $this->renderMultiSelectField($component, $default_renderer);

            case ($component instanceof F\DateTime):
                return $this->renderDateTimeField($component, $default_renderer);

            default:
                $this->cannotHandleComponent($component);
        }
    }

    protected function getOriginalDefaultRenderer(): RendererInterface
    {
        return $this->original_default_renderer;
    }


    protected function renderFieldGroups(Group $group, RendererInterface $default_renderer): string
    {
        $inputs = "";
        $input_labels = array();
        foreach ($group->getInputs() as $input) {
            $inputs .= $default_renderer->render($input);
            $input_labels[] = $input->getLabel();
        }
        $inputs .= $this->renderAddField($input_labels, $default_renderer);

        return $inputs;
    }

    protected function renderAddField(array $input_labels, RendererInterface $default_renderer): string
    {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.context_filter.html", true, true);
        $add_tpl = $this->getTemplate("tpl.filter_add_list.html", true, true);

        $links = array();
        foreach ($input_labels as $label) {
            $links[] = $f->button()->shy($label, "")->withAdditionalOnLoadCode(fn($id) => "$('#$id').on('click', function(event) {
						il.UI.filter.onAddClick(event, '$id');
						return false; // stop event propagation
				});");
        }
        $add_tpl->setVariable("LIST", $default_renderer->render($f->listing()->unordered($links)));
        $list = $f->legacy()->content($add_tpl->get());
        $popover = $f->popover()->standard($list)->withVerticalPosition();
        $tpl->setVariable("POPOVER", $default_renderer->render($popover));
        $add = $f->button()->bulky($f->symbol()->glyph()->add(), "", "")->withOnClick($popover->getShowSignal());

        $tpl->setCurrentBlock("filter_field");
        $tpl->setVariable("FILTER_FIELD", $default_renderer->render($add));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    protected function wrapInFormContext(
        FormInput $component,
        string $label,
        string $input_html,
        ?string $id_for_label = null,
        ?string $dependant_group_html = null
    ): string {
        return $this->wrapInFilterContext($component, $input_html, $this->getOriginalDefaultRenderer(), $id_for_label);
    }

    protected function wrapInFilterContext(
        FormInput $component,
        string $input_html,
        RendererInterface $default_renderer,
        ?string $id_pointing_to_input = null,
        string $dependant_group_html = ''
    ): string {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.context_filter.html", true, true);

        /**
         * @var $remove_glyph Component\Symbol\Glyph\Glyph
         */
        $remove_glyph = $f->symbol()->glyph()->remove("")->withAdditionalOnLoadCode(fn($id) => "$('#$id').on('click', function(event) {
							il.UI.filter.onRemoveClick(event, '$id');
							return false; // stop event propagation
					});");

        $tpl->setCurrentBlock("addon_left");
        $tpl->setVariable("LABEL", $component->getLabel());
        if ($id_pointing_to_input) {
            $tpl->setCurrentBlock("for");
            $tpl->setVariable("ID", $id_pointing_to_input);
            $tpl->parseCurrentBlock();
        }
        $tpl->parseCurrentBlock();
        $tpl->setCurrentBlock("filter_field");
        if ($component->isComplex()) {
            $tpl->setVariable("FILTER_FIELD", $this->renderProxyField($input_html, $default_renderer));
        } else {
            $tpl->setVariable("FILTER_FIELD", $input_html);
        }
        $tpl->parseCurrentBlock();
        $tpl->setCurrentBlock("addon_right");
        $tpl->setVariable("DELETE", $default_renderer->render($remove_glyph));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    protected function maybeDisable(FormInput $component, Template $tpl): void
    {
        // Do nothing, because Filter Inputs should not be deactivatable
    }

    protected function renderProxyField(
        string $input_html,
        RendererInterface $default_renderer
    ): string {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.filter_field.html", true, true);

        $popover = $f->popover()->standard($f->legacy()->content($input_html))->withVerticalPosition();
        $tpl->setVariable("POPOVER", $default_renderer->render($popover));

        $prox = new ProxyFilterField();
        $prox = $prox->withOnClick($popover->getShowSignal());
        $tpl->touchBlock("tabindex");

        $this->bindJSandApplyId($prox, $tpl);
        return $tpl->get();
    }

    protected function renderDurationField(F\Duration $component, RendererInterface $default_renderer): string
    {
        $inputs = $component->getInputs();

        $input = array_shift($inputs); //from
        list($input, $tpl) = $this->internalRenderDateTimeField($input, $default_renderer);
        $first_input_id = $this->bindJSandApplyId($input, $tpl);
        $input_html = $default_renderer->render($input);

        $input = array_shift($inputs) //until
        ->withAdditionalPickerconfig(['useCurrent' => false]);
        $input_html .= $default_renderer->render($input);

        $tpl = $this->getTemplate("tpl.duration.html", true, true);
        $id = $this->bindJSandApplyId($component, $tpl);
        $tpl->setVariable('DURATION', $input_html);

        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get());
    }

    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('assets/js/filter.js');
    }
}
