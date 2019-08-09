<?php
namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Modal\LightboxDescriptionEnabledPage;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Renderer extends AbstractComponentRenderer
{

    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
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
        return '';
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Modal/modal.js');
    }


    /**
     * @param Component\Modal\Modal $modal
     * @param string $id
     */
    protected function registerSignals(Component\Modal\Modal $modal)
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
        return $modal->withAdditionalOnLoadCode(function ($id) use ($show, $close, $options, $replace) {
            $options["url"] = "#{$id}";
            $options = json_encode($options);
            $code =
                "$(document).on('{$show}', function(event, signalData) { il.UI.modal.showModal('{$id}', {$options}, signalData); return false; });" .
                "$(document).on('{$close}', function() { il.UI.modal.closeModal('{$id}'); return false; });";
            if ($replace != "") {
                $code.= "$(document).on('{$replace}', function(event, signalData) { il.UI.modal.replaceFromSignal('{$id}', signalData);});";
            }
            return $code;
        });
    }

    /**
     * @param Component\Modal\Modal $modal
     * @return string
     */
    protected function renderAsync(Component\Modal\Modal $modal)
    {
        $modal = $this->registerSignals($modal);
        $id = $this->bindJavaScript($modal);
        return "<span id='{$id}'></span>";
    }

    /**
     * @param Component\Modal\Interruptive $modal
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderInterruptive(Component\Modal\Interruptive $modal, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate('tpl.interruptive.html', true, true);
        $modal = $this->registerSignals($modal);
        $id = $this->bindJavaScript($modal);
        $tpl->setVariable('ID', $id);
        $tpl->setVariable('FORM_ACTION', $modal->getFormAction());
        $tpl->setVariable('TITLE', $modal->getTitle());
        $tpl->setVariable('MESSAGE', $modal->getMessage());
        if (count($modal->getAffectedItems())) {
            $tpl->setCurrentBlock('with_items');
            foreach ($modal->getAffectedItems() as $item) {
                $tpl->setCurrentBlock('item');
                $icon = ($item->getIcon()) ? $default_renderer->render($item->getIcon()) : '';
                $desc = ($item->getDescription()) ? '<br>' . $item->getDescription() : '';
                $tpl->setVariable('ITEM_ICON', $icon);
                $tpl->setVariable('ITEM_ID', $item->getId());
                $tpl->setVariable('ITEM_TITLE', $item->getTitle());
                $tpl->setVariable('ITEM_DESCRIPTION', $desc);
                $tpl->parseCurrentBlock();
            }
        }
        $tpl->setVariable('ACTION_BUTTON_LABEL', $this->txt($modal->getActionButtonLabel()));
        $tpl->setVariable('CANCEL_BUTTON_LABEL', $this->txt($modal->getCancelButtonLabel()));
        return $tpl->get();
    }


    /**
     * @param Component\Modal\RoundTrip $modal
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderRoundTrip(Component\Modal\RoundTrip $modal, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate('tpl.roundtrip.html', true, true);
        $modal = $this->registerSignals($modal);
        $id = $this->bindJavaScript($modal);
        $tpl->setVariable('ID', $id);
        $tpl->setVariable('TITLE', $modal->getTitle());
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
        $tpl->setVariable('CANCEL_BUTTON_LABEL', $this->txt($modal->getCancelButtonLabel()));
        return $tpl->get();
    }


    /**
     * @param Component\Modal\Lightbox $modal
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderLightbox(Component\Modal\Lightbox $modal, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate('tpl.lightbox.html', true, true);
        $modal = $this->registerSignals($modal);
        $id = $this->bindJavaScript($modal);
        $tpl->setVariable('ID', $id);
        $id_carousel = "{$id}_carousel";
        $pages = $modal->getPages();
        $tpl->setVariable('TITLE', $pages[0]->getTitle());
        $tpl->setVariable('ID_CAROUSEL', $id_carousel);
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
    protected function getComponentInterfaceName()
    {
        return array(
            Component\Modal\Interruptive::class,
            Component\Modal\RoundTrip::class,
            Component\Modal\Lightbox::class,
        );
    }
}
