<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

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
        $registry->register('./libs/bower/bower_components/mediaelement/build/mediaelement-and-player.min.js');
        $registry->register('./libs/bower/bower_components/mediaelement/build/mediaelementplayer.min.css');
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName() : array
    {
        return [Component\Audio\Audio::class];
    }
}
