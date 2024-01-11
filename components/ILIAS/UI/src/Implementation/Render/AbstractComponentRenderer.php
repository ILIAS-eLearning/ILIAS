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

namespace ILIAS\UI\Implementation\Render;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Triggerer;
use ILIAS\UI\Factory;
use ILIAS\UI\HelpTextRetriever;
use ILIAS\UI\Help;
use ilLanguage;
use InvalidArgumentException;
use LogicException;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;
use ILIAS\UI\Renderer;
use JetBrains\PhpStorm\Deprecated;

/**
 * Base class for all component renderers.
 *
 * Offers some convenience methods for renderes, users only needs to implement
 * ComponentRenderer::render. Assumes that there is no special resource the
 * component requires.
 */
abstract class AbstractComponentRenderer implements ComponentRenderer, HelpTextRetriever
{
    protected const IS_HYDRATED_ATTRIBUTE = 'data-is-hydrated';
    protected const HYDRATED_BY_ATTRIBUTE = 'data-hydrated-by';

    private static array $component_storage;
    private static array $component_tree;

    final public function __construct(
        private Factory $ui_factory,
        private TemplateFactory $tpl_factory,
        private ilLanguage $lng,
        private JavaScriptBinding $js_binding,
        private \ILIAS\Refinery\Factory $refinery,
        private ImagePathResolver $image_path_resolver,
        private DataFactory $data_factory,
        private HelpTextRetriever $help_text_retriever,
        private UploadLimitResolver $upload_limit_resolver,
    ) {
    }

    public function render(Component $component, Renderer $default_renderer): string
    {
        $component_html = $this->renderComponent($component, $default_renderer);
        if (null === $component_html) {
            throw new \LogicException(static::class . " could not render component: " . get_class($component));
        }

        return $component_html;
    }

    /**
     * The Render\Loader is responsible to load a components corresponding renderer, therefore
     * this method should normally be able to render the given component. If this is not the
     * case, return null instead to abort the process.
     *
     * Rendering steps may include providing additional JavaScript code via JavaScriptBindable
     * facility, which makes it impossible to fully centralize the dehydration process. Please
     * use $this->dehydrateComponent() to finally render a JavaScriptBindable component.
     */
    abstract protected function renderComponent(Component $component, Renderer $default_renderer): ?string;

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        $registry->register('./components/ILIAS/UI/src/templates/js/Core/dist/core.js');
    }

    /**
     * Get a UI factory.
     *
     * This could be used to create and render subcomponents like close buttons, etc.
     */
    final protected function getUIFactory(): Factory
    {
        return $this->ui_factory;
    }

    final protected function getDataFactory(): DataFactory
    {
        return $this->data_factory;
    }

    final protected function getRefinery(): \ILIAS\Refinery\Factory
    {
        return $this->refinery;
    }

    final protected function getUploadLimitResolver(): UploadLimitResolver
    {
        return $this->upload_limit_resolver;
    }

    /**
     * Get a text from the language file.
     */
    final public function txt(string $id): string
    {
        return $this->lng->txt($id);
    }

    /**
     * Add language var to client side (il.Language)
     * @param mixed $key
     */
    final public function toJS($key): void
    {
        $this->lng->toJS($key);
    }

    /**
     * Get current language key
     */
    public function getLangKey(): string
    {
        return $this->lng->getLangKey();
    }

    final protected function getJavascriptBinding(): JavaScriptBinding
    {
        return $this->js_binding;
    }

    /**
     * Get template of component this renderer is made for.
     *
     * Serves as a wrapper around instantiation of ilTemplate, exposes
     * a smaller interface.
     *
     * @throws    InvalidArgumentException    if there is no such template
     */
    final protected function getTemplate(string $name, bool $purge_unfilled_vars, bool $purge_unused_blocks): Template
    {
        $path = $this->getTemplatePath($name);
        return $this->getTemplateRaw($path, $purge_unfilled_vars, $purge_unused_blocks);
    }

    /**
     * Get the path to the template of this component.
     */
    protected function getTemplatePath(string $name): string
    {
        $component = $this->getMyComponent();
        return "components/ILIAS/UI/src/templates/default/$component/$name";
    }

    /**
     * Get a template from any path.
     */
    private function getTemplateRaw(string $path, bool $purge_unfilled_vars, bool $purge_unused_blocks): Template
    {
        return $this->tpl_factory->getTemplate($path, $purge_unfilled_vars, $purge_unused_blocks);
    }

    /**
     * Binds and renders a JavaScriptBindable components JavaScript code. To achieve this,
     * the actual code will by bound by a data attribute along with some metadata which
     * will be prepended to the HTML's starting tag. For this reason, every component
     * rendered by this function must only consist of exactly one top-level HTML element.
     *
     * For backwards compatibility you may additionally pass an $id_binder which will be
     * receive the template and generated JavaScript id (if there is one), so derived
     * renderers may apply this as HTML id attribute. The goal will ultimately be to drop
     * this argument, as HTML id and JavaScript id should not be related to one another.
     * Everywhere $id_binder is used means that the component depends on the HTML elements
     * id attribute, which must be decoupled.
     *
     * @param \Closure(Template, string|null): void|null $id_binder
     */
    final protected function dehydrateComponent(
        JavaScriptBindable $component,
        Template $template,
        \Closure $id_binder = null,
    ): string {
        if ($component instanceof Triggerer) {
            $component = $this->addTriggererOnLoadCode($component);
        }

        $id_binder = $id_binder ?? static function () {
        };

        $component_id = null;
        $js_binder = $component->getOnLoadCode();
        $js_code = '';

        if (null !== $js_binder) {
            // NOTE: the id generation has been moved into this if statement to
            // comply with the numbering of our unit tests and avoids generating
            // ids unnecessarily.
            $component_id = $this->createId();

            $js_code = $js_binder($component_id);
            if (!is_string($js_code)) {
                throw new LogicException('Expected JavaScript binder to return string, got: ' . gettype($js_code));
            }
        }

        // if there is no actual JavaScript we don't need to dehydrate this
        // component. run the id-binder for backwards compatibility and
        // generate the final html.
        if ('' === $js_code || null === $component_id) {
            $id_binder($template, $component_id);
            return $template->get();
        }

        $id_binder($template, $component_id);
        $component_html = $template->get();

        // matches the beginning of an HTML tag, '<' followed by any word.
        if (!preg_match('/<\w+/', $component_html, $matches) ||
            null === ($first_match = array_shift($matches))
        ) {
            throw new LogicException("Could not find a starting tag in rendered component.");
        }

        // inserts data-attributes to the starting tag of the component while preserving
        // all other attributes, like:
        // <div data-hydrated-by="$component_id" data-is-hydrated="false" ...>
        $component_html = $first_match . " " .
            self::HYDRATED_BY_ATTRIBUTE . "=\"$component_id\" " .
            self::IS_HYDRATED_ATTRIBUTE . '="false"' .
            " " .
            substr(
                $component_html,
                (strpos($component_html, $first_match) + strlen($first_match))
            );

        $js_template = $this->getTemplateRaw(
            'components/ILIAS/UI/src/templates/default/tpl.javascript_bindable.html',
            true,
            true
        );

        // wraps the components JavaScript code in a <script> tag beneath the
        // components html.
        $js_template->setVariable('COMPONENT_HTML', $component_html);
        $js_template->setVariable('HYDRATION_CODE', $js_code);
        $js_template->setVariable('HYDRATION_ID', $component_id);

        return $js_template->get();
    }

    /**
     * Applies the component dehydration process and intercepts the component id
     * while doing so. This has been introduced as workaround for components which
     * need an id to finish rendering the component, e.g. in case of 'for' labels
     * where surrounding HTML needs this information. This method can be dropped
     * once JavaScript code is no longer coupled to an HTML id.
     *
     * Note that this method uses a proxy function to intercept the component id
     * of the given $id_binder. Since binders may create an id, this mechanism
     * will only work if the binder accepts the $id parameter as a reference. If
     * you are using this method, make sure this is the case.
     *
     * Returns the component HTML and applied id (which may be null) as an array,
     * which can be destructured like so: [$html, $id] = dehydrateComp...Id();
     *
     * @return array{string, string|null}
     */
    protected function dehydrateComponentAndInterceptId(
        JavaScriptBindable $component,
        Template $tpl,
        \Closure $id_binder = null
    ): array {
        $component_id = null;

        $id_proxy = function (Template $tpl, ?string $id) use (&$component_id, $id_binder): void {
            $id_binder = $id_binder ?? static function () {
            };
            $id_binder($tpl, $id);
            $component_id = $id;
        };

        $component_html = $this->dehydrateComponent($component, $tpl, $id_proxy);

        return [$component_html, $component_id];
    }

    /**
     * Returns an id-binder for $this->dehydrateComponent() which should always
     * apply an ID, even if no JavaScript code is present. This marks use-cases
     * where components need an id but not primarily because of JavaScript
     * coupling, e.g. form labels.
     *
     * @return \Closure(Template, string|null): void
     */
    final protected function getMandatoryIdBinder(): \Closure
    {
        // allow $id to be passed by reference to allow interception of
        // $this->dehydrateComponentAndInterceptId().
        return function (Template $template, ?string &$id): void {
            $id = $id ?? $this->createId();
            $this->getOptionalIdBinder()($template, $id);
        };
    }

    /**
     * Returns an id-binder for $this->dehydrateComponent() which should apply
     * an ID only if JavaScript code is present. This marks use-cases where
     * components primarily use this attribute to couple JavaScript code.
     *
     * @return \Closure(Template, string|null): void
     */
    final protected function getOptionalIdBinder(): \Closure
    {
        return static function (Template $template, ?string $id): void {
            if (null !== $id) {
                $template->setVariable('ID', $id);
            }
        };
    }

    /**
     * Get a fresh unique id.
     *
     * ATTENTION: This does not take care about any usage scenario of the provided
     * id. If you want to use it to bind JS-code to a component, you most probably
     * would want to use bindJavaScript instead, which returns an id that is used
     * to bind js to a component.
     *
     * However, there are cases (e.g radio-input) where an id is required even if
     * there is no javascript involved (e.g. to connect a label with an option),
     * this is where this method could come in handy.
     */
    final protected function createId(): string
    {
        return $this->js_binding->createId();
    }

    /**
     * Add onload-code for triggerer.
     */
    private function addTriggererOnLoadCode(Triggerer $triggerer): JavaScriptBindable
    {
        $triggered_signals = $triggerer->getTriggeredSignals();
        if (count($triggered_signals) == 0) {
            return $triggerer;
        }
        return $triggerer->withAdditionalOnLoadCode(function ($id) use ($triggered_signals): string {
            $code = "";
            foreach ($triggered_signals as $triggered_signal) {
                $signal = $triggered_signal->getSignal();
                $event = $triggered_signal->getEvent();
                $options = json_encode($signal->getOptions());
                //Note this switch is necessary since $(#...).on('load', ...) could be fired before the binding of the event.
                //Same seems true fro ready, see: #27456
                if ($event == 'load' || $event == 'ready') {
                    $code .=
                        "$(document).trigger('$signal',
							{
								'id' : '$signal', 'event' : '$event',
								'triggerer' : $('#$id'),
								'options' : JSON.parse('$options')
							}
						);";
                } else {
                    $code .=
                        "$('#$id').on('$event', function(event) {
						$(this).trigger('$signal',
							{
								'id' : '$signal', 'event' : '$event',
								'triggerer' : $(this),
								'options' : JSON.parse('$options')
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
     * @return mixed
     */
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
            throw new LogicException("The Renderer needs to be located in ILIAS\\UI\\Implementation\\Component\\*.");
        }
        self::$component_storage[$class] = $matches[1];

        return self::$component_storage[$class];
    }

    public function getImagePathResolver(): ImagePathResolver
    {
        return $this->image_path_resolver;
    }

    public function getHelpText(Help\Purpose $purpose, Help\Topic ...$topics): array
    {
        return $this->help_text_retriever->getHelpText($purpose, ...$topics);
    }

    /*
     * This is supposed to unify rendering of tooltips over all components.
     */
    protected ?TooltipRenderer $tooltip_renderer = null;

    protected function getTooltipRenderer(): TooltipRenderer
    {
        if ($this->tooltip_renderer === null) {
            $this->tooltip_renderer = new TooltipRenderer(
                $this,
                fn($path, $f1, $f2) => $this->getTemplateRaw($path, $f1, $f2)
            );
        }
        return $this->tooltip_renderer;
    }
}
