<?php declare(strict_types=1);

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
use ILIAS\UI\Component\Input\Field\FilterInput;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

/**
 * Class FilterContextRenderer
 * @package ILIAS\UI\Implementation\Component\Input
 */
class FilterContextRenderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        /**
         * @var $component Input
         */
        $this->checkComponent($component);

        if (!$component instanceof F\Group) {
            $component = $this->setSignals($component);
        }

        switch (true) {
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

            default:
                throw new LogicException("Cannot render '" . get_class($component) . "'");
        }
    }

    protected function renderFieldGroups(Group $group, RendererInterface $default_renderer) : string
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

    protected function renderAddField(array $input_labels, RendererInterface $default_renderer) : string
    {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.context_filter.html", true, true);
        $add_tpl = $this->getTemplate("tpl.filter_add_list.html", true, true);

        $links = array();
        foreach ($input_labels as $label) {
            $links[] = $f->button()->shy($label, "")->withAdditionalOnLoadCode(fn ($id) => "$('#$id').on('click', function(event) {
						il.UI.filter.onAddClick(event, '$id');
						return false; // stop event propagation
				});");
        }
        $add_tpl->setVariable("LIST", $default_renderer->render($f->listing()->unordered($links)));
        $list = $f->legacy($add_tpl->get());
        $popover = $f->popover()->standard($list)->withVerticalPosition();
        $tpl->setVariable("POPOVER", $default_renderer->render($popover));
        $add = $f->button()->bulky($f->symbol()->glyph()->add(), "", "")->withOnClick($popover->getShowSignal());

        $tpl->setCurrentBlock("filter_field");
        $tpl->setVariable("FILTER_FIELD", $default_renderer->render($add));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    protected function wrapInFilterContext(
        FilterInput $component,
        string $input_html,
        RendererInterface $default_renderer,
        string $id_pointing_to_input = ''
    ) : string {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.context_filter.html", true, true);

        /**
         * @var $remove_glyph Component\Symbol\Glyph\Glyph
         */
        $remove_glyph = $f->symbol()->glyph()->remove("")->withAdditionalOnLoadCode(fn ($id) => "$('#$id').on('click', function(event) {
							il.UI.filter.onRemoveClick(event, '$id');
							return false; // stop event propagation
					});");

        $tpl->setCurrentBlock("addon_left");
        $tpl->setVariable("LABEL", $component->getLabel());
        if ($id_pointing_to_input) {
            $tpl->setVariable("ID", $id_pointing_to_input);
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

    protected function renderProxyField(
        string $input_html,
        RendererInterface $default_renderer
    ) : string {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.filter_field.html", true, true);

        $popover = $f->popover()->standard($f->legacy($input_html))->withVerticalPosition();
        $tpl->setVariable("POPOVER", $default_renderer->render($popover));

        $prox = new ProxyFilterField();
        $prox = $prox->withOnClick($popover->getShowSignal());
        $tpl->touchBlock("tabindex");

        $this->bindJSandApplyId($prox, $tpl);
        return $tpl->get();
    }

    protected function applyName(FilterInput $component, Template $tpl) : ?string
    {
        $name = $component->getName();
        $tpl->setVariable("NAME", $name);
        return $name;
    }

    protected function bindJSandApplyId($component, Template $tpl) : string
    {
        $id = $this->bindJavaScript($component) ?? $this->createId();
        $tpl->setVariable("ID", $id);
        return $id;
    }

    /**
     * Escape values for rendering with a Callable "$escape"
     * In order to prevent XSS-attacks, values need to be stripped of
     * special chars (such as quotes or tags).
     * Needs vary according to the type of component, i.e.the html generated
     * for this specific component and the placement of {VALUE} in its template.
     * Please note: this may not work for customized templates!
     */
    protected function applyValue(FilterInput $component, Template $tpl, callable $escape = null) : void
    {
        $value = $component->getValue();
        if (!is_null($escape)) {
            $value = $escape($value);
        }
        if ($value) {
            $tpl->setVariable("VALUE", $value);
        }
    }

    protected function escapeSpecialChars() : Closure
    {
        return fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES);
    }

    protected function renderTextField(F\Text $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.text.html", true, true);
        $this->applyName($component, $tpl);

        if ($component->getMaxLength()) {
            $tpl->setVariable("MAX_LENGTH", $component->getMaxLength());
        }

        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        $id = $this->bindJSandApplyId($component, $tpl);
        return $this->wrapInFilterContext($component, $tpl->get(), $default_renderer, $id);
    }

    protected function renderNumericField(F\Numeric $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.numeric.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        $id = $this->bindJSandApplyId($component, $tpl);
        return $this->wrapInFilterContext($component, $tpl->get(), $default_renderer, $id);
    }

    public function renderSelectField(F\Select $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.select.html", true, true);
        $this->applyName($component, $tpl);

        $value = $component->getValue();
        //disable first option if required.
        $tpl->setCurrentBlock("options");
        if (!$value) {
            $tpl->setVariable("SELECTED", 'selected="selected"');
        }
        if ($component->isRequired()) {
            $tpl->setVariable("DISABLED_OPTION", "disabled");
            $tpl->setVariable("HIDDEN", "hidden");
        }
        $tpl->setVariable("VALUE", null);
        $tpl->setVariable("VALUE_STR", "-");
        $tpl->parseCurrentBlock();

        foreach ($component->getOptions() as $option_key => $option_value) {
            $tpl->setCurrentBlock("options");
            if ($value == $option_key) {
                $tpl->setVariable("SELECTED", 'selected="selected"');
            }
            $tpl->setVariable("VALUE", $option_key);
            $tpl->setVariable("VALUE_STR", $option_value);
            $tpl->parseCurrentBlock();
        }

        $id = $this->bindJSandApplyId($component, $tpl);

        return $this->wrapInFilterContext($component, $tpl->get(), $default_renderer, $id);
    }

    protected function renderMultiSelectField(F\MultiSelect $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.multiselect.html", true, true);
        $name = $this->applyName($component, $tpl);

        $value = $component->getValue();
        $tpl->setVariable("VALUE", $value);

        $id = $this->bindJSandApplyId($component, $tpl);
        $tpl->setVariable("ID", $id);

        foreach ($component->getOptions() as $opt_value => $opt_label) {
            $tpl->setCurrentBlock("option");
            $tpl->setVariable("NAME", $name);
            $tpl->setVariable("VALUE", $opt_value);
            $tpl->setVariable("LABEL", $opt_label);

            if ($value && in_array($opt_value, $value)) {
                $tpl->setVariable("CHECKED", 'checked="checked"');
            }

            $tpl->parseCurrentBlock();
        }

        return $this->wrapInFilterContext($component, $tpl->get(), $default_renderer);
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry) : void
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Input/Container/dist/filter.js');
        $registry->register('./src/UI/templates/js/Input/Field/input.js');
        $registry->register('./src/UI/templates/js/Input/Field/groups.js');
    }

    /**
     * @return FilterInput|JavaScriptBindable
     */
    protected function setSignals(Input $input) : \ILIAS\UI\Implementation\Component\Input\Field\Input
    {
        $signals = null;
        foreach ($input->getTriggeredSignals() as $s) {
            $signals[] = [
                "signal_id" => $s->getSignal()->getId(),
                "event" => $s->getEvent(),
                "options" => $s->getSignal()->getOptions()
            ];
        }
        if ($signals !== null) {
            $signals = json_encode($signals);

            /**
             * @var $input FilterInput
             */
            $input = $input->withAdditionalOnLoadCode(fn ($id) => "il.UI.input.setSignalsForId('$id', $signals);");

            $input = $input->withAdditionalOnLoadCode($input->getUpdateOnLoadCode());
        }
        return $input;
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName() : array
    {
        return [
            Component\Input\Field\Text::class,
            Component\Input\Field\Numeric::class,
            Component\Input\Field\Group::class,
            Component\Input\Field\Select::class,
            Component\Input\Field\MultiSelect::class
        ];
    }
}
