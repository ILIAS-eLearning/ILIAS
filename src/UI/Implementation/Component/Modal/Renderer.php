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

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Modal\LightboxDescriptionEnabledPage;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Component\Modal\InterruptiveItem\InterruptiveItem;
use ILIAS\UI\Implementation\Component\Input\Container\Form\FormWithoutSubmitButton;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        // If the modal is rendered async, we just create a fake container which will be
        // replaced by the modal upon successful ajax request
        /** @var Modal $component */
        if ($component->getAsyncRenderUrl()) {
            return $this->renderAsync($component);
        }

        if ($component instanceof Component\Modal\Interruptive) {
            return $this->renderInterruptive($component, $default_renderer);
        } elseif ($component instanceof Component\Modal\RoundTrip) {
            return $this->renderRoundTrip($component, $default_renderer);
        } elseif ($component instanceof Component\Modal\Lightbox) {
            return $this->renderLightbox($component, $default_renderer);
        }
        throw new \LogicException(self::class . " cannot render component '" . get_class($component) . "'.");
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Modal/modal.js');
    }

    protected function registerSignals(Component\Modal\Modal $modal): Component\JavaScriptBindable
    {
        $show = $modal->getShowSignal();
        $close = $modal->getCloseSignal();

        $replace = "";
        if ($modal instanceof Component\Modal\RoundTrip) {
            $replace = $modal->getReplaceSignal();
        }

        $options = array(
            'ajaxRenderUrl' => $modal->getAsyncRenderUrl(),
            'keyboard' => $modal->getCloseWithKeyboard()
        );
        // ATTENTION, ATTENTION:
        // with(Additional)OnLoadCode opens a wormhole into the future, where some unspecified
        // entity magically created an id for the component that can be used to refer to it
        // via javascript.
        // This replaced a pattern, where an id was created manually and the java script
        // code was manually inserted to the (now internal) js-binding of the
        // AbstractComponentRenderer. (see commit 192144fd1f0e040cadc0149c3dc15fbc4b67858e).
        // The wormhole solution is considered superior over the manual creation of ids because:
        // * withAdditionalOnLoadCode introduces no new principles to the UI framework but reuses
        //   an existing one
        // * withAdditionalOnLoadCode does not require it to expose internals (js-binding) from
        //   the AbstractComponentRenderer and thus does have less coupling
        // * withAdditionalOnLoadCode allows the framework to decide, when ids are actually
        //   created
        // * since withAdditionalOnLoadCode refers to some yet unknown future, it disencourages
        //   tempering with the id _here_.
        return $modal->withAdditionalOnLoadCode(function ($id) use ($show, $close, $options, $replace): string {
            $options["url"] = "#$id";
            $options = json_encode($options);
            $code =
                "$(document).on('$show', function(event, signalData) { il.UI.modal.showModal('$id', $options, signalData);});" .
                "$(document).on('$close', function() { il.UI.modal.closeModal('$id');});";
            if ($replace != "") {
                $code .= "$(document).on('$replace', function(event, signalData) { il.UI.modal.replaceFromSignal('$id', signalData);});";
            }
            return $code;
        });
    }

    protected function renderAsync(Component\Modal\Modal $modal): string
    {
        $modal = $this->registerSignals($modal);
        $id = $this->bindJavaScript($modal);
        return "<span id='$id'></span>";
    }

    protected function renderInterruptive(
        Component\Modal\Interruptive $modal,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate('tpl.interruptive.html', true, true);
        $modal = $this->registerSignals($modal);
        $id = $this->bindJavaScript($modal);
        $tpl->setVariable('ID', $id);
        $value = $modal->getFormAction();
        $tpl->setVariable('FORM_ACTION', $value);
        $tpl->setVariable('TITLE', $modal->getTitle());
        $tpl->setVariable('MESSAGE', $modal->getMessage());
        $tpl->setVariable('CLOSE_LABEL', $this->txt('close'));

        $standard_items = $this->renderInterruptiveItemsByClass(
            Component\Modal\InterruptiveItem\Standard::class,
            $modal->getAffectedItems(),
            $default_renderer
        );
        if ($standard_items) {
            $tpl->setCurrentBlock('with_standard_items');
            $tpl->setVariable('STANDARD_ITEMS', $standard_items);
        }

        $key_value_items = $this->renderInterruptiveItemsByClass(
            Component\Modal\InterruptiveItem\KeyValue::class,
            $modal->getAffectedItems(),
            $default_renderer
        );
        if ($key_value_items) {
            $tpl->setCurrentBlock('with_key_value_items');
            $tpl->setVariable('KEY_VALUE_ITEMS', $key_value_items);
        }

        $tpl->setVariable('ACTION_BUTTON_LABEL', $modal->getActionButtonLabel() ?? $this->txt('delete'));
        $tpl->setVariable('CANCEL_BUTTON_LABEL', $modal->getCancelButtonLabel() ?? $this->txt('cancel'));
        return $tpl->get();
    }

    /**
     * Filters items by provided class, and renders only those.
     * @param string            $class_name
     * @param InterruptiveItem  $items
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function renderInterruptiveItemsByClass(
        string $class_name,
        array $items,
        RendererInterface $default_renderer
    ): string {
        $items_of_class = array_filter(
            $items,
            fn ($i) => $i instanceof $class_name
        );
        $rendered_items = '';
        foreach ($items_of_class as $item) {
            $rendered_items .= $default_renderer->render($item);
        }
        return $rendered_items;
    }

    protected function renderRoundTrip(Component\Modal\RoundTrip $modal, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate('tpl.roundtrip.html', true, true);
        /** @var $modal RoundTrip */
        $modal = $this->registerSignals($modal);
        $id = $this->bindJavaScript($modal);
        $tpl->setVariable('ID', $id);
        $tpl->setVariable('TITLE', $modal->getTitle());
        $tpl->setVariable('CLOSE_LABEL', $this->txt('close'));

        foreach ($modal->getContent() as $content) {
            $tpl->setCurrentBlock('with_content');
            $tpl->setVariable('CONTENT', $default_renderer->render($content));
            $tpl->parseCurrentBlock();
        }
        foreach ($modal->getActionButtons() as $button) {
            $tpl->setCurrentBlock('with_buttons');
            $tpl->setVariable('BUTTON', $default_renderer->render($button));
            $tpl->parseCurrentBlock();
        }

        // only render form if it contains any inputs (for now).
        if (!empty($modal->getInputs())) {
            // render form in modal body.
            $tpl->setCurrentBlock('with_form');
            $tpl->setVariable('FORM', $default_renderer->render($modal->getForm()));
            $tpl->parseCurrentBlock();

            // render submit in modal footer.
            $submit = $this->getUIFactory()->button()->standard(
                $modal->getSubmitCaption() ?? $this->txt('save'),
                ''
            )->withOnClick($modal->getForm()->getSubmitSignal());
            $tpl->setCurrentBlock('with_submit');
            $tpl->setVariable('SUBMIT_BUTTON', $default_renderer->render($submit));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('CANCEL_BUTTON_LABEL', $modal->getCancelButtonLabel() ?? $this->txt('cancel'));
        return $tpl->get();
    }

    protected function renderLightbox(Component\Modal\Lightbox $modal, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate('tpl.lightbox.html', true, true);
        $modal = $this->registerSignals($modal);
        $id = $this->bindJavaScript($modal);
        $tpl->setVariable('ID', $id);
        $id_carousel = "{$id}_carousel";
        $pages = $modal->getPages();
        $tpl->setVariable('TITLE', $pages[0]->getTitle());
        $tpl->setVariable('ID_CAROUSEL', $id_carousel);
        $tpl->setVariable('CLOSE_LABEL', $this->txt('close'));
        $tpl->setVariable('COLOR_SCHEME', $modal->getScheme());

        if (count($pages) > 1) {
            $tpl->setCurrentBlock('has_indicators');
            foreach ($pages as $index => $page) {
                $tpl->setCurrentBlock('indicators');
                $tpl->setVariable('INDEX', $index);
                $tpl->setVariable('CLASS_ACTIVE', ($index == 0) ? 'active' : '');
                $tpl->setVariable('ID_CAROUSEL2', $id_carousel);
                $tpl->parseCurrentBlock();
            }
        }
        foreach ($pages as $i => $page) {
            if ($page instanceof LightboxTextPage) {
                $tpl->setCurrentBlock('pages');
                $tpl->touchBlock('page_type_text');
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock('pages');
            $tpl->setVariable('CLASS_ACTIVE', ($i == 0) ? ' active' : '');
            $tpl->setVariable('TITLE2', htmlentities($page->getTitle(), ENT_QUOTES, 'UTF-8'));
            $tpl->setVariable('CONTENT', $default_renderer->render($page->getComponent()));
            if ($page instanceof LightboxDescriptionEnabledPage) {
                $tpl->setVariable('DESCRIPTION', $page->getDescription());
            }
            $tpl->parseCurrentBlock();
        }
        if (count($pages) > 1) {
            $tpl->setCurrentBlock('controls');
            $tpl->setVariable('ID_CAROUSEL3', $id_carousel);
            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable('ID_CAROUSEL4', $id_carousel);
        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array(
            Component\Modal\Interruptive::class,
            Component\Modal\RoundTrip::class,
            Component\Modal\Lightbox::class,
        );
    }
}
