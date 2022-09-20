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
    public function render(Component $component, RenderInterface $default_renderer): string
    {
        $this->checkComponent($component);

        if ($component instanceof \ILIAS\UI\Component\Dropzone\File\Wrapper) {
            return $this->renderWrapper($component, $default_renderer);
        }
        if ($component instanceof \ILIAS\UI\Component\Dropzone\File\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }
        throw new LogicException("Cannot render '" . get_class($component) . "'");
    }

    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register("./src/UI/templates/js/Dropzone/File/dropzone.js");
    }

    protected function renderWrapper(Wrapper $dropzone, RenderInterface $default_renderer): string
    {
        $modal = $this->getUIFactory()->modal()->roundtrip(
            $dropzone->getTitle(),
            [$dropzone->getForm()]
        );

        $template = $this->getTemplate("tpl.dropzone.html", true, true);
        $template->setVariable('MODAL', $default_renderer->render($modal));
        $template->setVariable('CONTENT', $default_renderer->render($dropzone->getContent()));
        $template->setVariable('WRAPPER_CLASS', 'ui-dropzone-wrapper');

        $dropzone = $this->initClientsideDropzone($dropzone);
        $dropzone = $dropzone->withAdditionalDrop($modal->getShowSignal());

        $this->bindAndApplyJavaScript($dropzone, $template);

        return $template->get();
    }

    protected function renderStandard(Standard $dropzone, RenderInterface $default_renderer): string
    {
        $modal = $this->getUIFactory()->modal()->roundtrip(
            $dropzone->getTitle(),
            [$dropzone->getForm()]
        );

        $template = $this->getTemplate("tpl.dropzone.html", true, true);
        $template->setVariable('MODAL', $default_renderer->render($modal));
        $template->setVariable('MESSAGE', $dropzone->getMessage());

        $upload_button = $dropzone->getUploadButton();
        if (null !== $upload_button) {
            // override default onClick behaviour with modal signal
            // to prevent the action from triggering.
            $upload_button = $upload_button->withOnClick(
                $modal->getShowSignal()
            );

            $template->setVariable('BUTTON', $default_renderer->render($upload_button));
        }

        $dropzone = $this->initClientsideDropzone($dropzone);
        $dropzone = $dropzone->withAdditionalDrop($modal->getShowSignal());

        $this->bindAndApplyJavaScript($dropzone, $template);

        return $template->get();
    }

    protected function initClientsideDropzone(FileInterface $dropzone): FileInterface
    {
        return $dropzone->withAdditionalOnLoadCode(static function ($id) {
            // the file-input JS-ID would be nice here too, but I don't see
            // how it could be retrieved without being hacky.
            return "
                $(document).ready(function() {
                    il.UI.Dropzone.init('$id');
                });
            ";
        });
    }

    protected function bindAndApplyJavaScript(FileInterface $dropzone, Template $template): void
    {
        $template->setVariable('ID', $this->bindJavaScript($dropzone));
    }

    protected function getComponentInterfaceName(): array
    {
        return array(
            \ILIAS\UI\Component\Dropzone\File\Standard::class,
            \ILIAS\UI\Component\Dropzone\File\Wrapper::class,
        );
    }
}
