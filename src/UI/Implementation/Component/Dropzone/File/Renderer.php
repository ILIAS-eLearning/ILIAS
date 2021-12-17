<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Dropzone\File\File as FileInterface;
use ILIAS\UI\Renderer as RenderInterface;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\Template;
use LogicException;

/**
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Renderer extends AbstractComponentRenderer
{
    protected RenderInterface $default_renderer;

    public function render(Component $component, RenderInterface $default_renderer) : string
    {
        $this->checkComponent($component);
        $this->default_renderer = $default_renderer;

        switch (true) {
            case ($component instanceof \ILIAS\UI\Component\Dropzone\File\Wrapper):
                return $this->renderWrapper($component);

            case ($component instanceof \ILIAS\UI\Component\Dropzone\File\Standard):
                return $this->renderStandard($component);

            default:
                throw new LogicException("Cannot render '" . get_class($component) . "'");
        }
    }

    public function registerResources(ResourceRegistry $registry) : void
    {
        parent::registerResources($registry);
        $registry->register("./libs/bower/bower_components/jquery-dragster/jquery.dragster.js");
        $registry->register("./src/UI/templates/js/Dropzone/File/dropzone.js");
    }

    protected function renderWrapper(Wrapper $dropzone) : string
    {
        $modal = $this->getUIFactory()->modal()->roundtrip('', [$dropzone->getForm()]);

        $template = $this->getTemplate("tpl.dropzone.html", true, true);
        $template->setVariable('MODAL', $this->default_renderer->render($modal));
        $template->setVariable('CONTENT', $this->default_renderer->render($dropzone->getContent()));

        $dropzone = $this->initClientsideDropzone($dropzone);
        $dropzone = $dropzone->withAdditionalDrop($modal->getShowSignal());

        $this->bindAndApplyJavaScript($dropzone, $template);

        return $template->get();
    }

    protected function renderStandard(Standard $dropzone) : string
    {
        $modal = $this->getUIFactory()->modal()->roundtrip('', [$dropzone->getForm()]);

        $template = $this->getTemplate("tpl.dropzone.html", true, true);
        $template->setVariable('MODAL', $this->default_renderer->render($modal));

        $dropzone = $this->initClientsideDropzone($dropzone);
        $dropzone = $dropzone->withAdditionalDrop($modal->getShowSignal());

        $this->bindAndApplyJavaScript($dropzone, $template);

        return $template->get();
    }

    protected function initClientsideDropzone(FileInterface $dropzone) : FileInterface
    {
        return $dropzone->withAdditionalOnLoadCode(static function ($id) {
            return "
                $(document).ready(function() {
                    il.UI.Dropzone.init('$id');
                });
            ";
        });
    }

    protected function bindAndApplyJavaScript(FileInterface $dropzone, Template $template) : void
    {
        $template->setVariable('ID', $this->bindJavaScript($dropzone));
    }

    protected function getComponentInterfaceName() : array
    {
        return array(
            \ILIAS\UI\Component\Dropzone\File\Standard::class,
            \ILIAS\UI\Component\Dropzone\File\Wrapper::class,
        );
    }
}
