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

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Component\Dropzone\File\File as FileInterface;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Renderer as RenderInterface;
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
        $registry->register("./src/UI/templates/js/Dropzone/File/dropzone.js");

        parent::registerResources($registry);
    }

    protected function renderWrapper(Wrapper $dropzone, RenderInterface $default_renderer): string
    {
        $modal = $dropzone->getModal();

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
        $modal = $dropzone->getModal();

        $template = $this->getTemplate("tpl.dropzone.html", true, true);
        $template->setVariable('MODAL', $default_renderer->render($modal));
        $template->setCurrentBlock('with_message');
        $template->setVariable('MESSAGE', $dropzone->getMessage());
        $template->parseCurrentBlock();

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
        return $dropzone->withAdditionalOnLoadCode(static function ($id) use ($dropzone) {
            return "
                il.UI.Dropzone.init('$id');
                
                // @TODO: we need to refactor the signal-management to prevent using jQuery here.
                $(document).on('{$dropzone->getClearSignal()}', function () {
                    il.UI.Dropzone.removeAllFilesFromQueue('$id');
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
        return [
            \ILIAS\UI\Component\Dropzone\File\Standard::class,
            \ILIAS\UI\Component\Dropzone\File\Wrapper::class,
        ];
    }
}
