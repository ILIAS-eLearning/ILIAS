<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\UI\Implementation\Component\Video;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Video Renderer
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class Renderer extends AbstractComponentRenderer
{
    public function render(
        Component\Component $component,
        RendererInterface $default_renderer
    ) : string {
        /**
         * @var Component\Video\Video $component
         */
        $this->checkComponent($component);
        $tpl = $this->getTemplate("tpl.video.html", true, true);

        $component = $component->withAdditionalOnLoadCode(function ($id) {
            return "$('#$id').mediaelementplayer();";
        });
        $id = $this->bindJavaScript($component);

        foreach ($component->getSubtitleFiles() as $lang_key => $file) {
            $tpl->setCurrentBlock("track");
            $tpl->setVariable("TRACK_SOURCE", $file);
            $tpl->setVariable("TRACK_LANG", $lang_key);
            $tpl->parseCurrentBlock();
        }

        if ($component->getPoster() !== "") {
            $tpl->setCurrentBlock("poster");
            $tpl->setVariable("POSTER_SOURCE", $component->getPoster());
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("ID", $id);
        $tpl->setVariable("SOURCE", $component->getSource());

        return $tpl->get();
    }

    public function registerResources(
        \ILIAS\UI\Implementation\Render\ResourceRegistry $registry
    ) : void {
        parent::registerResources($registry);
        $registry->register('./node_modules/mediaelement/build/mediaelement-and-player.min.js');
        $registry->register('./node_modules/mediaelement/build/renderers/vimeo.min.js');
        $registry->register('./node_modules/mediaelement/build/mediaelementplayer.min.css');
    }

    protected function getComponentInterfaceName() : array
    {
        return [Component\Video\Video::class];
    }
}
