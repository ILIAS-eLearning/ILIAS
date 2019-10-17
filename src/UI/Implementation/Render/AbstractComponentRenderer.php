<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Triggerer;
use ILIAS\UI\Factory;

/**
 * Base class for all component renderers.
 *
 * Offers some convenience methods for renderes, users only needs to implement
 * ComponentRenderer::render. Assumes that there is no special resource the
 * component requires.
 */
abstract class AbstractComponentRenderer implements ComponentRenderer
{
    /**
     * @var	Factory
     */
    private $ui_factory;

    /**
     * @var	TemplateFactory
     */
    private $tpl_factory;

    /**
     * @var	\ilLanguage
     */
    private $lng;

    /**
     * @var	JavaScriptBinding
     */
    private $js_binding;
    /**
     * @var array
     */
    private static $component_storage = [];

    /**
     * Component renderers must only depend on a UI-Factory and a Template Factory.
     */
    final public function __construct(Factory $ui_factory, TemplateFactory $tpl_factory, \ilLanguage $lng, JavaScriptBinding $js_binding)
    {
        $this->ui_factory = $ui_factory;
        $this->tpl_factory = $tpl_factory;
        $this->lng = $lng;
        $this->js_binding = $js_binding;
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry)
    {
        $registry->register('./src/UI/templates/js/Core/ui.js');
    }

    /**
     * Get a UI factory.
     *
     * This could be used to create and render subcomponents like close buttons, etc.
     *
     * @return	Factory
     */
    final protected function getUIFactory()
    {
        return $this->ui_factory;
    }

    /**
     * Get a text from the language file.
     *
     * @param	string	$id
     * @return	string
     */
    final public function txt($id)
    {
        return $this->lng->txt($id);
    }

    /**
     * Add language var to client side (il.Language)
     * @param $key
     */
    final public function toJS($key)
    {
        $this->lng->toJS($key);
    }

    /**
     * Get current language key
     *
     * @return string
     */
    public function getLangKey()
    {
        return $this->lng->getLangKey();
    }


    /**
     * @return JavaScriptBinding
     */
    final protected function getJavascriptBinding()
    {
        return $this->js_binding;
    }

    /**
     * Get template of component this renderer is made for.
     *
     * Serves as a wrapper around instantiation of ilTemplate, exposes
     * a smaller interface.
     *
     * @param	string	$name
     * @param	bool	$purge_unfilled_vars
     * @param	bool	$purge_unused_blocks
     * @throws	\InvalidArgumentException	if there is no such template
     * @return	\ILIAS\UI\Implementation\Render\Template
     */
    final protected function getTemplate($name, $purge_unfilled_vars, $purge_unused_blocks)
    {
        $path = $this->getTemplatePath($name);
        return $this->tpl_factory->getTemplate($path, $purge_unfilled_vars, $purge_unused_blocks);
    }

    /**
     * Get the path to the template of this component.
     *
     * @param	string	$name
     * @return	string
     */
    protected function getTemplatePath($name)
    {
        $component = $this->getMyComponent();
        return "src/UI/templates/default/$component/$name";
    }

    /**
     * Bind the component to JavaScript.
     *
     * ATTENTION: If this returns an id, the returned id has to be included as id-attribute
     * into the HTML of your component.
     *
     * @param	JavaScriptBindable	$component
     * @return	string|null
     */
    final protected function bindJavaScript(JavaScriptBindable $component)
    {
        if ($component instanceof Triggerer) {
            $component = $this->addTriggererOnLoadCode($component);
        }
        return $this->bindOnloadCode($component);
    }

    /**
     * Bind the JavaScript onload-code.
     *
     * @param	JavaScriptBindable	$component
     * @return	string|null
     */
    private function bindOnloadCode(JavaScriptBindable $component)
    {
        $binder = $component->getOnLoadCode();
        if ($binder === null) {
            return null;
        }

        $id = $this->js_binding->createId();
        $on_load_code = $binder($id);
        if (!is_string($on_load_code)) {
            throw new \LogicException(
                "Expected JavaScript binder to return string" .
                " (used component: " . get_class($component) . ")"
            );
        }
        $this->js_binding->addOnLoadCode($on_load_code);
        return $id;
    }

    /**
     * Add onload-code for triggerer.
     *
     * @param	Triggerer	$triggerer
     * @return 	Triggerer
     */
    private function addTriggererOnLoadCode(Triggerer $triggerer)
    {
        $triggered_signals = $triggerer->getTriggeredSignals();
        if (count($triggered_signals) == 0) {
            return $triggerer;
        }
        return $triggerer->withAdditionalOnLoadCode(function ($id) use ($triggered_signals) {
            $code = "";
            foreach ($triggered_signals as $triggered_signal) {
                $signal = $triggered_signal->getSignal();
                $event = $triggered_signal->getEvent();
                $options = json_encode($signal->getOptions());
                //Note this switch is necessary since $(#...).on('load', ...) could be fired before the binding of the event.
                if ($event == 'load') {
                    $code .=
                            "$(this).trigger('{$signal}',
							{
								'id' : '{$signal}', 'event' : '{$event}',
								'triggerer' : $('#{$id}'),
								'options' : JSON.parse('{$options}')
							}
						);";
                } else {
                    $code .=
                    "$('#{$id}').on('{$event}', function(event) {
						$(this).trigger('{$signal}',
							{
								'id' : '{$signal}', 'event' : '{$event}',
								'triggerer' : $(this),
								'options' : JSON.parse('{$options}')
							}
						);
						return false;
					});";
                }
            }
            return $code;
        });
    }

    /**
     * Check if a given component fits this renderer and throw \LogicError if that is not
     * the case.
     *
     * @param	Component			$component
     * @throws	\LogicException		if component does not fit.
     * @return  null
     */
    final protected function checkComponent(Component $component)
    {
        $interfaces = $this->getComponentInterfaceName();
        if (!is_array($interfaces)) {
            throw new \LogicException(
                "Expected array, found '" . (string) (null) . "' when rendering."
            );
        }

        foreach ($interfaces as $interface) {
            if ($component instanceof $interface) {
                return;
            }
        }
        $ifs = implode(", ", $interfaces);
        throw new \LogicException(
            "Expected $ifs, found '" . get_class($component) . "' when rendering."
        );
    }

    /**
     * Get the name of the component-interface this renderer is supposed to render.
     *
     * ATTENTION: Fully qualified please!
     *
     * @return string[]
     */
    abstract protected function getComponentInterfaceName();


    private function getMyComponent()
    {
        $class = get_class($this);
        if (isset(self::$component_storage[$class])) {
            return self::$component_storage[$class];
        }
        $matches = array();
        // Extract component
        $re = "%ILIAS\\\\UI\\\\Implementation\\\\Component\\\\(\\w+)\\\\(\\w+)%";
        preg_match($re, $class, $matches);
        if (preg_match($re, $class, $matches) !== 1) {
            throw new \LogicException("The Renderer needs to be located in ILIAS\\UI\\Implementation\\Component\\*.");
        }
        self::$component_storage[$class] = $matches[1];

        return self::$component_storage[$class];
    }
}
