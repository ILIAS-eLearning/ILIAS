<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Rating;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) {
        $this->checkComponent($component);
        $tpl = $this->getTemplate("Rating/tpl.rating.html", true, true);

        $id = $this->createId();


        $tpl->setVariable("TOPIC",$component->topic());
        $tpl->setVariable("ID",$id);
        $tpl->setVariable("POSTVAR",$id);

        $captions = $component->captions();
        for($i = 0; $i < 5; ++$i) {
            $tpl->setVariable("SCALECAPTION_" .(string)$i ,$captions[$i]);
        }

        $byline = $component->byline();
        if($byline !== '') {
            $tpl->setVariable("BYLINE", $byline);
        }

        return $tpl->get();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName() {
        return array(Component\Input\Rating\Rating::class);
    }
}