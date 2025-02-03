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

namespace ILIAS\UI\Implementation\Component\Player;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * @author Alexander Killing <killing@leifos.de>
 * @package ILIAS\UI\Implementation\Component\Player
 */
class Renderer extends AbstractComponentRenderer
{
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Component\Player\Audio) {
            return $this->renderAudio($component, $default_renderer);
        }
        if ($component instanceof Component\Player\Video) {
            return $this->renderVideo($component, $default_renderer);
        }
        $this->cannotHandleComponent($component);
    }

    public function renderAudio(Component\Component $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.audio.html", true, true);

        $component = $component->withAdditionalOnLoadCode(function ($id) {
            return "$('#$id').mediaelementplayer({stretching: 'responsive'});";
        });
        $id = $this->bindJavaScript($component);

        if ($component->getTranscription() != "") {
            $factory = $this->getUIFactory();
            $page = $factory->modal()->lightboxTextPage(
                $component->getTranscription(),
                $this->txt("ui_transcription")
            );
            $modal = $factory->modal()->lightbox($page);
            $button = $factory->button()->standard($this->txt("ui_transcription"), '')
                              ->withOnClick($modal->getShowSignal());

            $tpl->setCurrentBlock("transcription");
            $tpl->setVariable("BUTTON_AND_MODAL", $default_renderer->render([$button, $modal]));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("ID", $id);
        $tpl->setVariable("SOURCE", $component->getSource());

        return $tpl->get();
    }

    public function renderVideo(
        Component\Component $component,
        RendererInterface $default_renderer
    ): string {
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

    public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./assets/js/mediaelement-and-player.min.js');
        $registry->register('./assets/css/mediaelementplayer.min.css');
        $registry->register('./assets/js/vimeo.min.js');
    }
}
