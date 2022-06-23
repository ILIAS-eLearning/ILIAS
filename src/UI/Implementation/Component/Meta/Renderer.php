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
 */

namespace ILIAS\UI\Implementation\Component\Meta;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Component\Meta\Standard;
use ILIAS\UI\Component\Meta\Complex;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Renderer as ComponentRenderer;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Renderer extends AbstractComponentRenderer
{
    public function render(Component $component, ComponentRenderer $default_renderer) : string
    {
        $this->checkComponent($component);

        if ($component instanceof Complex) {
            $this->renderComplexMeta($component, $default_renderer);
        }

        if ($component instanceof Standard) {
            $this->renderStandardMeta($component, $default_renderer);
        }

        return '';
    }

    protected function getComponentInterfaceName() : array
    {
        return [
            \ILIAS\UI\Component\Meta\Standard::class,
            \ILIAS\UI\Component\Meta\Complex::class,
        ];
    }

    protected function renderComplexMeta(Complex $meta, ComponentRenderer $default_renderer) : string
    {
        $template = $this->getTemplate('tpl.complex.html', true, true);

        $template->setVariable('KEY', $meta->getKey());
        $template->setVariable('VALUE', $meta->getValue());
        $template->setVariable('CONTENT', $meta->getContent());

        return $template->get();
    }

    protected function renderStandardMeta(Standard $meta, ComponentRenderer $default_renderer) : string
    {
        $template = $this->getTemplate('tpl.standard.html', true, true);

        $template->setVariable('KEY', $meta->getKey());
        $template->setVariable('VALUE', $meta->getValue());

        return $template->get();
    }
}
