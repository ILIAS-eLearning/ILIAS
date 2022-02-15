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

namespace ILIAS\UI\Implementation\Component\Audio;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Audio Renderer
 *
 * @author Alexander Killing <killing@leifos.de>
 * @package ILIAS\UI\Implementation\Component\Audio
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        /**
         * @var Component\Audio\Audio $component
         */
        $this->checkComponent($component);
        $tpl = $this->getTemplate("tpl.audio.html", true, true);


        $component = $component->withAdditionalOnLoadCode(function ($id) {
            return "$('#$id').mediaelementplayer();";
        });
        $id = $this->bindJavaScript($component);

        if ($component->getTranscription() != "") {
            $tpl->setCurrentBlock("transcription");
            $tpl->setVariable("TID", $id);
            $tpl->setVariable("TRANSCRIPTION_CONTENT", htmlspecialchars($component->getTranscription()));
            $tpl->setVariable("TRANSCRIPTION", $this->txt("transcription"));
            $tpl->setVariable("CLOSE", $this->txt("close"));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("ID", $id);
        $tpl->setVariable("SOURCE", $component->getSource());

        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry) : void
    {
        parent::registerResources($registry);
        $registry->register('./node_modules/mediaelement/build/mediaelement-and-player.min.js');
        $registry->register('./node_modules/mediaelement/build/mediaelementplayer.min.css');
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName() : array
    {
        return [Component\Audio\Audio::class];
    }
}
