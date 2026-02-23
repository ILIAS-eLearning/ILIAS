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
        if ($this->isVimeo($component)) {
            return $this->renderVimeo(
                $component,
                $default_renderer
            );
        } elseif ($this->isYoutube($component)) {
            return $this->renderYoutube(
                $component,
                $default_renderer
            );
        }
        return $this->renderNative(
            $component,
            $default_renderer
        );
    }

    public function renderVimeo(
        Component\Component $component,
        RendererInterface $default_renderer
    ): string {

        $tpl = $this->getTemplate("tpl.video_vimeo.html", true, true);

        $id = $this->bindJavaScript($component);

        $tpl->setVariable("ID", $id);
        $tpl->setVariable("SOURCE", $component->getSource());

        return $tpl->get();
    }

    public function renderYoutube(
        Component\Component $component,
        RendererInterface $default_renderer
    ): string {

        $tpl = $this->getTemplate("tpl.video_youtube.html", true, true);

        $id = $this->bindJavaScript($component);

        $tpl->setVariable("ID", $id);
        $tpl->setVariable("SOURCE", $component->getSource());

        return $tpl->get();
    }

    public function renderNative(
        Component\Component $component,
        RendererInterface $default_renderer
    ): string {

        $tpl = $this->getTemplate("tpl.video.html", true, true);

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

    protected function isVimeo(
        Component\Component $component
    ): bool {
        if (is_int(strpos($component->getSource(), 'vimeo.com'))) {
            return true;
        }
        return false;
    }

    protected function isYoutube(
        Component\Component $component
    ): bool {
        if (is_int(strpos($component->getSource(), 'youtube.com'))) {
            return true;
        }
        return false;
    }
}
