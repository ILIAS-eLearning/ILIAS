<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Symbol\Avatar;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Symbol\Icon\AbstractAvatar;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;

class Renderer extends AbstractComponentRenderer
{

    protected function getTemplateFilename()
    {
        return 'tpl.avatar.html';
    }

    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);
        /**
         * @var $component AbstractAvatar
         */

        $tpl_file = $this->getTemplateFilename();
        $tpl      = $this->getTemplate($tpl_file, true, true);

        $tpl->setVariable('ARIA_LABEL', $component->getUsername());

        if ($component instanceof Component\Symbol\Avatar\Letter) {
            $tpl->setVariable('MODE', 'letter');
            $tpl->setVariable('TEXT', $component->getAbbreviation());
            $tpl->setVariable('COLOR', (string)$component->getBackgroundColorVariant());
        } elseif ($component instanceof Component\Symbol\Avatar\Picture) {
            $tpl->setVariable('MODE', 'picture');
            $tpl->setVariable('CUSTOMIMAGE', $component->getPicturePath());
        }

        $str = $tpl->get();

        return $str;
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return array(
            Component\Symbol\Avatar\Letter::class,
            Component\Symbol\Avatar\Picture::class,
        );
    }
}
