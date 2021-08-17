<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

/**
 * Class Renderer
 *
 * Renderer implementation for file dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(
            \ILIAS\UI\Component\Dropzone\File\Standard::class,
            \ILIAS\UI\Component\Dropzone\File\Wrapper::class,
        );
    }


    /**
     * @inheritdoc
     */
    public function render(Component $component, \ILIAS\UI\Renderer $default_renderer) : string
    {
        $this->checkComponent($component);

        switch (true) {
            case $component instanceof Wrapper:
                return $this->renderWrapper($component, $default_renderer);

            case $component instanceof Standard:
                return $this->renderStandard($component, $default_renderer);

            default:
                throw new \LogicException("Could not render component " . get_class($component));
        }
    }


    /**
     * @inheritDoc
     */
    public function registerResources(ResourceRegistry $registry)
    {
        parent::registerResources($registry);

        $registry->register("./libs/bower/bower_components/jquery-dragster/jquery.dragster.js");
        $registry->register("./src/UI/templates/js/Dropzone/File/dropzone.js");
    }

    /**
     * @param \ILIAS\UI\Component\Dropzone\File\Wrapper $dropzone
     * @param \ILIAS\UI\Renderer                        $default_renderer
     * @return string
     */
    private function renderWrapper(\ILIAS\UI\Component\Dropzone\File\Wrapper $dropzone, \ILIAS\UI\Renderer $default_renderer) : string
    {
        $dropzone_form = $dropzone->getForm();

        $dropzone_modal = $this->getUIFactory()->modal()->roundtrip(
            $this->txt('upload'),
            $dropzone_form
        );

        $tpl = $this->getTemplate('tpl.wrapper.html', true, true);

        $settings = new \stdClass();
        $settings->modal_show_signal = $dropzone_modal->getShowSignal()->getId();
        $settings->modal_close_signal = $dropzone_modal->getCloseSignal()->getId();

        $dropzone = $dropzone->withAdditionalDrop($dropzone_modal->getShowSignal());
        $dropzone = $dropzone->withAdditionalOnLoadCode(static function ($id) use ($settings) {
            $settings = json_encode($settings);
            return "
                $(document).ready(function () {
                    il.UI.Dropzone.wrapper.init('$id', '$settings');
                });
            ";
        });

        /**
         * @var $dropzone Wrapper
         */
        $js_id = $this->bindJavaScript($dropzone);
        
        $tpl->setVariable('ID', $js_id);
        $tpl->setVariable('MODAL', $default_renderer->render($dropzone_modal));
        $tpl->setVariable('CONTENT', $default_renderer->render($dropzone->getComponents()));

        return $tpl->get();
    }

    /**
     * @param \ILIAS\UI\Component\Dropzone\File\Standard $dropzone
     * @param \ILIAS\UI\Renderer                         $default_renderer
     * @return string
     */
    private function renderStandard(\ILIAS\UI\Component\Dropzone\File\Standard $dropzone, \ILIAS\UI\Renderer $default_renderer)
    {
        return '';
    }
}
