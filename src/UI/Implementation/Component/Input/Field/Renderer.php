<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input\Field\Password;
use ILIAS\UI\Component\Input\Field\Select;
use ILIAS\UI\Component\Input\Field\MultiSelect;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Component;
use \ILIAS\UI\Implementation\Render\Template;
use ILIAS\Data\DateFormat as DateFormat;

/**
 * Class Renderer
 *
 * @package ILIAS\UI\Implementation\Component\Input
 */
class Renderer extends AbstractComponentRenderer
{
	const DATEPICKER_MINMAX_FORMAT = 'Y/m/d';

	const DATEPICKER_FORMAT_MAPPING = [
		'd' => 'DD',
		'jS' => 'Do',
		'l' => 'dddd',
		'D' => 'dd',
		'S' => 'o',
		'W' => '',
		'm' => 'MM',
		'F' => 'MMMM',
		'M' => 'MMM',
		'Y' => 'YYYY',
		'y' => 'YY'
	];


	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		/**
		 * @var $component Input
		 */
		$this->checkComponent($component);

		$input_tpl = null;
		$id = null;
		$dependant_group_html = null;

		if ($component instanceof Component\Input\Field\Text) {
			$input_tpl = $this->getTemplate("tpl.text.html", true, true);
		} elseif ($component instanceof Component\Input\Field\Numeric) {
			$input_tpl = $this->getTemplate("tpl.numeric.html", true, true);
		} elseif ($component instanceof Component\Input\Field\Checkbox) {
			$input_tpl = $this->getTemplate("tpl.checkbox.html", true, true);
		} elseif ($component instanceof Component\Input\Field\OptionalGroup) {
			$input_tpl = $this->getTemplate("tpl.checkbox.html", true, true);
			$component = $component->withAdditionalOnLoadCode(function($id) {
				return $this->getOptionalGroupOnLoadCode($id);
			});
			$dependant_group_html = $this->renderFieldGroups($component, $default_renderer);
			$id = $this->bindJavaScript($component);
			return $this->renderInputFieldWithContext($input_tpl, $component, $id, $dependant_group_html);
		} elseif ($component instanceof Component\Input\Field\SwitchableGroup) {
			return $this->renderSwitchableGroupField($component, $default_renderer);
		} elseif ($component instanceof Component\Input\Field\Tag) {
			$input_tpl = $this->getTemplate("tpl.tag_input.html", true, true);
		} elseif ($component instanceof Password) {
			$input_tpl = $this->getTemplate("tpl.password.html", true, true);
		} else if ($component instanceof Select) {
			$input_tpl = $this->getTemplate("tpl.select.html", true, true);
		} else if ($component instanceof Component\Input\Field\Textarea) {
			$input_tpl = $this->getTemplate("tpl.textarea.html", true, true);
		} elseif ($component instanceof Component\Input\Field\Radio) {
			return $this->renderRadioField($component, $default_renderer);
		} else if ($component instanceof MultiSelect) {
			$input_tpl = $this->getTemplate("tpl.multiselect.html", true, true);
		} else if ($component instanceof Component\Input\Field\DateTime) {
			$input_tpl = $this->getTemplate("tpl.datetime.html", true, true);
		} else if ($component instanceof Component\Input\Field\Group) {
			return $this->renderFieldGroups($component, $default_renderer);
		}
		else {
			throw new \LogicException("Cannot render '" . get_class($component) . "'");
		}

		return $this->renderInputFieldWithContext($input_tpl, $component, $id);
	}


	/**
	 * @inheritdoc
	 */
	public function registerResources(ResourceRegistry $registry) {
		parent::registerResources($registry);
		$registry->register('./libs/bower/bower_components/typeahead.js/dist/typeahead.bundle.js');
		$registry->register('./libs/bower/bower_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js');
		$registry->register('./libs/bower/bower_components/bootstrap-tagsinput/dist/bootstrap-tagsinput-typeahead.css');
		$registry->register('./src/UI/templates/js/Input/Field/tagInput.js');
		$registry->register('./src/UI/templates/js/Input/Field/textarea.js');
		$registry->register('./src/UI/templates/js/Input/Field/input.js');
		$registry->register('./src/UI/templates/js/Input/Field/duration.js');
	}


	/**
	 * @param Input $input
	 * @return Input|\ILIAS\UI\Implementation\Component\JavaScriptBindable
	 */
	protected function setSignals(Input $input) {
		$signals = null;
		foreach ($input->getTriggeredSignals() as $s)
		{
			$signals[] = [
				"signal_id" => $s->getSignal()->getId(),
				"event" => $s->getEvent(),
				"options" => $s->getSignal()->getOptions()
			];
		}
		if ($signals !== null) {
			$signals = json_encode($signals);


			$input = $input->withAdditionalOnLoadCode(function ($id) use ($signals) {
				$code = "il.UI.input.setSignalsForId('$id', $signals);";
				return $code;
			});

			$input = $input->withAdditionalOnLoadCode($input->getUpdateOnLoadCode());
		}
		return $input;
	}


	/**
	 * @param Group             $group
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderFieldGroups(Group $group, RendererInterface $default_renderer) {
		if ($group instanceof Component\Input\Field\Section) {
			/**
			 * @var $group Section
			 */
			return $this->renderSection($group, $default_renderer);

		} elseif ($group instanceof Component\Input\Field\Duration) {
			/**
			 * @var $group Duration
			 */
			return $this->renderDurationInput($group, $default_renderer);
		}

		$inputs = "";
		foreach ($group->getInputs() as $input) {
			$inputs .= $default_renderer->render($input);
		}

		return $inputs;
	}

	/**
	 * @param Component\JavascriptBindable $component
	 * @param                              $tpl
	 */
	protected function maybeRenderId(Component\JavascriptBindable $component, Template $tpl) {
		$id = $this->bindJavaScript($component);
		if ($id !== null) {
			$tpl->setCurrentBlock("id");
			$tpl->setVariable("ID", $id);
			$tpl->parseCurrentBlock();
		}
	}


	/**
	 * @param Section           $section
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderSection(Section $section, RendererInterface $default_renderer) {
		$section_tpl = $this->getTemplate("tpl.section.html", true, true);
		$section_tpl->setVariable("LABEL", $section->getLabel());

		if ($section->getByline() !== null) {
			$section_tpl->setCurrentBlock("byline");
			$section_tpl->setVariable("BYLINE", $section->getByline());
			$section_tpl->parseCurrentBlock();
		}

		if ($section->getError() !== null) {
			$section_tpl->setCurrentBlock("error");
			$section_tpl->setVariable("ERROR", $section->getError());
			$section_tpl->parseCurrentBlock();
		}
		$inputs_html = "";

		foreach ($section->getInputs() as $input) {
			$inputs_html .= $default_renderer->render($input);
		}
		$section_tpl->setVariable("INPUTS", $inputs_html);

		return $section_tpl->get();
	}

	/**
	 * @param Template $input_tpl
	 * @param Input    $input
	 * @param null     $id
	 * @param null     $dependant_group_html
	 *
	 * @return string
	 */
	protected function renderInputFieldWithContext(Template $input_tpl, Input $input, $id = null, $dependant_group_html = null) {
		$tpl = $this->getTemplate("tpl.context_form.html", true, true);
		/**
		 * TODO: should we throw an error in case for no name or render without name?
		 *
		 * if(!$input->getName()){
		 * throw new \LogicException("Cannot render '".get_class($input)."' no input name given.
		 * Is there a name source attached (is this input packed into a container attaching
		 * a name source)?");
		 * } */
		if ($input->getName()) {
			$tpl->setVariable("NAME", $input->getName());
		} else {
			$tpl->setVariable("NAME", "");
		}

		$tpl->setVariable("LABEL", $input->getLabel());
		$tpl->setVariable("INPUT", $this->renderInputField($input_tpl, $input, $id));

		if ($input->getByline() !== null) {
			$tpl->setCurrentBlock("byline");
			$tpl->setVariable("BYLINE", $input->getByline());
			$tpl->parseCurrentBlock();
		}

		if ($input->isRequired()) {
			$tpl->touchBlock("required");
		}

		if ($input->getError() !== null) {
			$tpl->setCurrentBlock("error");
			$tpl->setVariable("ERROR", $input->getError());
			$tpl->parseCurrentBlock();
		}

		if ($dependant_group_html !== null) {
			$tpl->setVariable("DEPENDANT_GROUP", $dependant_group_html);
		}


		return $tpl->get();
	}


	/**
	 * @param Template $tpl
	 * @param Input    $input
	 * @param          $id
	 *
	 * @return string
	 */
	protected function renderInputField(Template $tpl, Input $input, $id) {

		$input = $this->setSignals($input);

		if($input instanceof Component\Input\Field\Password) {
			$id = $this->additionalRenderPassword($tpl, $input);
		}

		if($input instanceof Textarea){
			$this->renderTextareaField($tpl, $input);
		}

		$tpl->setVariable("NAME", $input->getName());

		switch (true) {
			case ($input instanceof Checkbox || $input instanceof OptionalGroup):
				if ($input->getValue()) {
					$tpl->touchBlock("value");
				}
			case ($input instanceof Text):
			case ($input instanceof Numeric):
			case ($input instanceof Password):
			case ($input instanceof Textarea):
				$tpl->setVariable("NAME", $input->getName());

				if ($input->getValue() !== null && !($input instanceof Checkbox)) {
					$tpl->setCurrentBlock("value");
					$tpl->setVariable("VALUE", $input->getValue());
					$tpl->parseCurrentBlock();
				}
				if ($input->isDisabled()) {
					$tpl->setCurrentBlock("disabled");
					$tpl->setVariable("DISABLED", 'disabled="disabled"');
					$tpl->parseCurrentBlock();
				}
				if ($id) {
					$tpl->setCurrentBlock("id");
					$tpl->setVariable("ID", $id);
					$tpl->parseCurrentBlock();
				}
				break;
			case ($input instanceof Select):
				$tpl = $this->renderSelectInput($tpl, $input);
				break;
			case ($input instanceof MultiSelect):
				$tpl = $this->renderMultiSelectInput($tpl, $input);
				break;

			case ($input instanceof Tag):
				$configuration = $input->getConfiguration();
				$input = $input->withAdditionalOnLoadCode(
					function ($id) use ($configuration) {
						$encoded = json_encode($configuration);

						return "il.UI.Input.tagInput.init('{$id}', {$encoded});";
					}
				);
				$id = $this->bindJavaScript($input);
				/**
				 * @var $input \ILIAS\UI\Implementation\Component\Input\Field\Tag
				 */
				$tpl->setVariable("ID", $id);
				$tpl->setVariable("NAME", $input->getName());
				if ($input->isDisabled()) {
					$tpl->setCurrentBlock("disabled");
					$tpl->setVariable("DISABLED", "disabled");
					$tpl->parseCurrentBlock();
				}
				if ($input->getValue()) {
					$value = $input->getValue();
					$tpl->setVariable("VALUE_COMMA_SEPARATED", implode(",", $value));
					foreach ($value as $tag) {
						$tpl->setCurrentBlock('existing_tags');
						$tpl->setVariable("FIELD_ID", $id);
						$tpl->setVariable("FIELD_NAME", $input->getName());
						$tpl->setVariable("TAG_NAME", $tag);
						$tpl->parseCurrentBlock();
					}
				}
				break;
			case ($input instanceof DateTime):
				return $this->renderDateTimeInput($tpl, $input);
				break;
		}

		if ($id === null) {
			$this->maybeRenderId($input, $tpl);
		}

		return $tpl->get();
	}

	public function renderSelectInput(Template $tpl, Select $input)
	{
		if ($input->isDisabled()) {
			$tpl->setCurrentBlock("disabled");
			$tpl->setVariable("DISABLED", 'disabled="disabled"');
			$tpl->parseCurrentBlock();
		}
		$value = $input->getValue();
		//disable first option if required.
		$tpl->setCurrentBlock("options");
		if(!$value) {
			$tpl->setVariable("SELECTED", "selected");
		}
		if($input->isRequired()) {
			$tpl->setVariable("DISABLED_OPTION", "disabled");
			$tpl->setVariable("HIDDEN", "hidden");
		}
		$tpl->setVariable("VALUE", NULL);
		$tpl->setVariable("VALUE_STR", "-");
		$tpl->parseCurrentBlock();
		//rest of options.
		foreach ($input->getOptions() as $option_key => $option_value)
		{
			$tpl->setCurrentBlock("options");
			if($value == $option_key) {
				$tpl->setVariable("SELECTED", "selected");
			}
			$tpl->setVariable("VALUE", $option_key);
			$tpl->setVariable("VALUE_STR", $option_value);
			$tpl->parseCurrentBlock();
		}

		return $tpl;
	}

	public function renderMultiSelectInput(Template $tpl, MultiSelect $input) : Template	{
		$value = $input->getValue();
		$name = $input->getName();

		foreach ($input->getOptions() as $opt_value => $opt_label) {
			$tpl->setCurrentBlock("option");
			$tpl->setVariable("NAME", $name);
			$tpl->setVariable("VALUE", $opt_value);
			$tpl->setVariable("LABEL", $opt_label);

			if($value && in_array($opt_value, $value)) {
				$tpl->setVariable("CHECKED", 'checked="checked"');
			}
			if ($input->isDisabled()) {
				$tpl->setVariable("DISABLED", 'disabled="disabled"');
			}

			$tpl->parseCurrentBlock();
		}
		return $tpl;
	}


	/**
	 * Render revelation-glyphs for password and register signals/functions
	 * @param Template $tpl
	 * @param Password $input
	 *
	 * @return string | false
	 */
	protected function additionalRenderPassword(Template $tpl, Component\Input\Field\Password $input) {
		$id = null;
		if($input->getRevelation()) {
			global $DIC;
			$f = $this->getUIFactory();
			$renderer = $DIC->ui()->renderer();

			$input = $input->withResetSignals();
			$sig_reveal = $input->getRevealSignal();
			$sig_mask = $input->getMaskSignal();

			$input = $input->withAdditionalOnLoadCode(function($id) use ($sig_reveal, $sig_mask) {
				return
					"$(document).on('{$sig_reveal}', function() {
						$('#{$id}').addClass('revealed');
						$('#{$id}')[0].getElementsByTagName('input')[0].type='text';
					});".
					"$(document).on('{$sig_mask}', function() {
						$('#{$id}').removeClass('revealed');
						$('#{$id}')[0].getElementsByTagName('input')[0].type='password';
					});"
					;
				});
			$id = $this->bindJavaScript($input);
			$tpl->setVariable("ID", $id);

			$glyph_reveal = $f->symbol()->glyph()->eyeopen("#")
				->withOnClick($sig_reveal);
			$glyph_mask = $f->symbol()->glyph()->eyeclosed("#")
				->withOnClick($sig_mask);
			$tpl->setCurrentBlock('revelation');
			$tpl->setVariable('PASSWORD_REVEAL', $renderer->render($glyph_reveal));
			$tpl->setVariable('PASSWORD_MASK', $renderer->render($glyph_mask));
			$tpl->parseCurrentBlock();
		}
		return $id;
	}


	protected function renderTextareaField(Template $tpl, Textarea $input)
	{
		if($input->isLimited())
		{
			$this->toJS("ui_chars_remaining");
			$this->toJS("ui_chars_min");
			$this->toJS("ui_chars_max");

			$counter_id_prefix = "textarea_feedback_";
			$min = $input->getMinLimit();
			$max = $input->getMaxLimit();

			$input = $input->withAdditionalOnLoadCode(function($id) use($counter_id_prefix, $min, $max) {
				return "il.UI.textarea.changeCounter('$id','$counter_id_prefix','$min','$max');";
			});

			$textarea_id = $this->bindJavaScript($input);
			$tpl->setCurrentBlock("id");
			$tpl->setVariable("ID", $textarea_id);
			$tpl->parseCurrentBlock();
			$tpl->setCurrentBlock("limit");
			$tpl->setVariable("COUNT_ID", $textarea_id);
			$tpl->setVariable("FEEDBACK_MAX_LIMIT", $max);
			$tpl->parseCurrentBlock();
		}
	}


	/**
	 * @param Radio $input
	 * @param RendererInterface    $default_renderer
	 *
	 * @return string
	 */
	protected function renderRadioField(Component\Input\Field\Radio $input, RendererInterface $default_renderer) {
		$input_tpl = $this->getTemplate("tpl.radio.html", true, true);

		//monitor change-events
		$input = $this->setSignals($input);
		$id = $this->bindJavaScript($input) ?? $this->createId();
		$input_tpl->setVariable("ID", $id);

		foreach ($input->getOptions() as $value => $label) {
			$opt_id = $id .'_' .$value .'_opt';

			$input_tpl->setCurrentBlock('optionblock');
			$input_tpl->setVariable("NAME", $input->getName());
			$input_tpl->setVariable("OPTIONID", $opt_id);
			$input_tpl->setVariable("VALUE", $value);
			$input_tpl->setVariable("LABEL", $label);

			if ($input->getValue() !== null && $input->getValue()===$value) {
				$input_tpl->setVariable("CHECKED", 'checked="checked"');
			}
			if ($input->isDisabled()) {
				$input_tpl->setVariable("DISABLED", 'disabled="disabled"');
			}

			$byline = $input->getBylineFor($value);
			if (!empty($byline)) {
				$input_tpl->setVariable("BYLINE", $byline);
			}

			$input_tpl->parseCurrentBlock();
		}
		$options_html = $input_tpl->get();

		//render with context:
		$tpl = $this->getTemplate("tpl.context_form.html", true, true);
		$tpl->setVariable("LABEL", $input->getLabel());
		$tpl->setVariable("INPUT", $options_html);

		if ($input->getByline() !== null) {
			$tpl->setCurrentBlock("byline");
			$tpl->setVariable("BYLINE", $input->getByline());
			$tpl->parseCurrentBlock();
		}
		if ($input->isRequired()) {
			$tpl->touchBlock("required");
		}
		if ($input->getError() !== null) {
			$tpl->setCurrentBlock("error");
			$tpl->setVariable("ERROR", $input->getError());
			$tpl->parseCurrentBlock();
		}
		return $tpl->get();
	}

	/**
	 * @param Radio $input
	 * @param RendererInterface    $default_renderer
	 *
	 * @return string
	 */
	protected function renderSwitchableGroupField(Component\Input\Field\SwitchableGroup $input, RendererInterface $default_renderer) {
		$input_tpl = $this->getTemplate("tpl.radio.html", true, true);

		$input = $input->withAdditionalOnLoadCode(function($id) {
			return $this->getSwitchableGroupOnLoadCode($id);
		});
		$id = $this->bindJavaScript($input);
		$input_tpl->setVariable("ID", $id);

		foreach ($input->getInputs() as $key => $group) {
			$opt_id = $id .'_' .$key.'_opt';

			$input_tpl->setCurrentBlock('optionblock');
			$input_tpl->setVariable("NAME", $input->getName());
			$input_tpl->setVariable("OPTIONID", $opt_id);
			$input_tpl->setVariable("VALUE", $key);
			$input_tpl->setVariable("LABEL", $group->getLabel());

			if ($group->getValue() !== null) {
				list($index, $subvalues) = $group->getValue();
				if((int)$index === $key) {
					$input_tpl->setVariable("CHECKED", 'checked="checked"');
				}
			}
			if ($input->isDisabled()) {
				$input_tpl->setVariable("DISABLED", 'disabled="disabled"');
			}

			$dependant_group_html = $this->renderFieldGroups($group, $default_renderer);
			$input_tpl->setVariable("DEPENDANT_FIELDS", $dependant_group_html);
			$input_tpl->parseCurrentBlock();
		}
		$options_html = $input_tpl->get();

		//render with context:
		$tpl = $this->getTemplate("tpl.context_form.html", true, true);
		$tpl->setVariable("LABEL", $input->getLabel());
		$tpl->setVariable("INPUT", $options_html);

		if ($input->getByline() !== null) {
			$tpl->setCurrentBlock("byline");
			$tpl->setVariable("BYLINE", $input->getByline());
			$tpl->parseCurrentBlock();
		}
		if ($input->isRequired()) {
			$tpl->touchBlock("required");
		}
		if ($input->getError() !== null) {
			$tpl->setCurrentBlock("error");
			$tpl->setVariable("ERROR", $input->getError());
			$tpl->parseCurrentBlock();
		}
		return $tpl->get();
	}


	protected function getOptionalGroupOnLoadCode($id) {
		return <<<JS
var $id = $("#$id");
var {$id}_group = $id.siblings(".form-group").show();
var {$id}_adjust = function() {
	if ({$id}[0].checked) {
		{$id}_group.show();
	}
	else {
		{$id}_group.hide()
	}
}
$id.change({$id}_adjust);
{$id}_adjust();
JS;
	}

	protected function getSwitchableGroupOnLoadCode($id) {
		return <<<JS
var radio = $("#$id");
radio.change(function(event){
	var r = $(this),
		options = r.children('.il-input-radiooption').children('input');

	options.each(function(index, opt) {
		var group = $(opt).siblings('.form-group');
		if(opt.checked) {
			group.show();
		} else {
			group.hide();
		}
	});
});
radio.trigger('change');

JS;
	}

	/**
	 * Return the datetime format in a form fit for the JS-component of this input.
	 * Currently, this means transforming the elements of DateFormat to momentjs.
	 *
	 * http://eonasdan.github.io/bootstrap-datetimepicker/Options/#format
	 * http://momentjs.com/docs/#/displaying/format/
	*/
	protected function getTransformedDateFormat(
		DateFormat\DateFormat $origin,
		array $mapping
	): string {
		$ret = '';
		foreach ($origin->toArray() as $element) {
			if(array_key_exists($element, $mapping)) {
				$ret .= $mapping[$element];
			} else {
				$ret .= $element;
			}
		}
		return $ret;
	}

	/**
	 * @param Template $tpl
	 * @param DateTime $input
	 *
	 * @return string
	 */
	protected function renderDateTimeInput(Template $tpl, DateTime $input): string
	{
		global $DIC;
		$f = $this->getUIFactory();
		$renderer = $DIC->ui()->renderer()->withAdditionalContext($input);
		if($input->getTimeOnly() === true) {
			$cal_glyph = $f->symbol()->glyph()->time("#");
			$format = $input::TIME_FORMAT;
		} else {
			$cal_glyph = $f->symbol()->glyph()->calendar("#");

			$format = $this->getTransformedDateFormat(
				$input->getFormat(),
				self::DATEPICKER_FORMAT_MAPPING
			);

			if($input->getUseTime() === true) {
				$format .= ' ' .$input::TIME_FORMAT;
			}
		}

		$tpl->setVariable("CALENDAR_GLYPH", $renderer->render($cal_glyph));

		$config = [
			'showClear' => true,
			'sideBySide' => true,
			'format' => $format,
		];
		$config = array_merge($config, $input->getAdditionalPickerConfig());

		$min_date = $input->getMinValue();
		if(! is_null($min_date)) {
			$config['minDate'] = date_format($min_date, self::DATEPICKER_MINMAX_FORMAT);
		}
		$max_date = $input->getMaxValue();
		if(! is_null($max_date)) {
			$config['maxDate'] = date_format($max_date, self::DATEPICKER_MINMAX_FORMAT);
		}
		require_once("./Services/Calendar/classes/class.ilCalendarUtil.php");
		\ilCalendarUtil::initDateTimePicker();
		$input = $this->setSignals($input);
		$input = $input->withAdditionalOnLoadCode(function($id) use ($config) {
			return '$("#'.$id.'").datetimepicker('.json_encode($config).')';
		});
		$id = $this->bindJavaScript($input);
		$tpl->setVariable("ID", $id);

		$tpl->setVariable("NAME", $input->getName());
		$tpl->setVariable("PLACEHOLDER", $format);

		if ($input->getValue() !== null) {
			$tpl->setCurrentBlock("value");
			$tpl->setVariable("VALUE", $input->getValue());
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}


	protected function renderDurationInput(Duration $input, RendererInterface $default_renderer) :string {
		$tpl = $this->getTemplate("tpl.context_form.html", true, true);
		$tpl_duration = $this->getTemplate("tpl.duration.html", true, true);

		if ($input->getName()) {
			$tpl->setVariable("NAME", $input->getName());
		} else {
			$tpl->setVariable("NAME", "");
		}

		$tpl->setVariable("LABEL", $input->getLabel());

		if ($input->getByline() !== null) {
			$tpl->setCurrentBlock("byline");
			$tpl->setVariable("BYLINE", $input->getByline());
			$tpl->parseCurrentBlock();
		}

		if ($input->isRequired()) {
			$tpl->touchBlock("required");
		}

		if ($input->getError() !== null) {
			$tpl->setCurrentBlock("error");
			$tpl->setVariable("ERROR", $input->getError());
			$tpl->parseCurrentBlock();
		}

		$input = $this->setSignals($input);
		$input = $input->withAdditionalOnLoadCode(
			function($id) {
				return "$(document).ready(function() {
					il.UI.Input.duration.init('$id');
				});";
			}
		);
		$id = $this->bindJavaScript($input);
		$tpl_duration->setVariable("ID", $id);

		$input_html = '';
		$inputs = $input->getInputs();

		$inpt = array_shift($inputs); //from
		$input_html .= $default_renderer->render($inpt);

		$inpt = array_shift($inputs)->withAdditionalPickerconfig([ //until
			'useCurrent' => false
		]);
		$input_html .= $default_renderer->render($inpt);

		$tpl_duration->setVariable('DURATION', $input_html);
		$tpl->setVariable("INPUT", $tpl_duration->get());
		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return [
			Component\Input\Field\Text::class,
			Component\Input\Field\Numeric::class,
			Component\Input\Field\Group::class,
			Component\Input\Field\OptionalGroup::class,
			Component\Input\Field\SwitchableGroup::class,
			Component\Input\Field\Section::class,
			Component\Input\Field\Checkbox::class,
			Component\Input\Field\Tag::class,
			Component\Input\Field\Password::class,
			Component\Input\Field\Select::class,
			Component\Input\Field\Radio::class,
			Component\Input\Field\Textarea::class,
			Component\Input\Field\MultiSelect::class,
			Component\Input\Field\DateTime::class,
			Component\Input\Field\Duration::class
		];
	}
}
