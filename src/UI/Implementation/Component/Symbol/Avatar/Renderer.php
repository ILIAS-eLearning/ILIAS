<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Symbol\Avatar;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Symbol\Avatar\Avatar;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;

class Renderer extends AbstractComponentRenderer
{
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);
        $tpl = null;
        /**
         * @var $component Avatar
         */
        if ($component instanceof Component\Symbol\Avatar\Letter) {
            $tpl = $this->getTemplate('tpl.avatar_letter.html', true, true);
            $tpl->setVariable('ARIA_LABEL', $component->getUsername());
            $tpl->setVariable('MODE', 'letter');
            $tpl->setVariable('TEXT', $component->getAbbreviation());
            $tpl->setVariable('COLOR', (string) $component->getBackgroundColorVariant());
        } elseif ($component instanceof Component\Symbol\Avatar\Picture) {
            $tpl = $this->getTemplate('tpl.avatar_picture.html', true, true);
            $tpl->setVariable('ARIA_LABEL', $component->getUsername());
            $tpl->setVariable('MODE', 'picture');
            $tpl->setVariable('CUSTOMIMAGE', $component->getPicturePath());
        }

        $str = $tpl->get();

        return $str;
    }

    protected function getComponentInterfaceName()
    {
        return array(
            Component\Symbol\Avatar\Letter::class,
            Component\Symbol\Avatar\Picture::class,
        );
    }
}
