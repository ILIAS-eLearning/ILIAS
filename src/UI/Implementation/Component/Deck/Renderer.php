<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Deck;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $tpl_card = $this->getTemplate("tpl.deck_card.html", true, true);
        $tpl_row = $this->getTemplate("tpl.deck_row.html", true, true);

        $size = $component->getCardsSize();
        $small_size = $component->getCardsSizeSmallDisplays();
        $cards_per_row = 12 / $size;

        $i = 1;

        foreach ($component->getCards() as $card) {
            $tpl_card->setCurrentBlock("card");
            $tpl_card->setVariable("CARD", $default_renderer->render($card, $default_renderer));
            $tpl_card->setVariable("SIZE", $size);
            $tpl_card->setVariable("SMALL_SIZE", $small_size);
            $tpl_card->parseCurrentBlock();

            if (($i % $cards_per_row) == 0) {
                $this->parseRow($tpl_row, $tpl_card->get());
                $tpl_card = $this->getTemplate("tpl.deck_card.html", true, true);
                $i = 0;
            }
            $i++;
        }

        $this->parseRow($tpl_row, $tpl_card->get());

        return $tpl_row->get();
    }

    protected function parseRow($tpl_row, $content)
    {
        $tpl_row->setCurrentBlock("row");
        $tpl_row->setVariable("CARDS", $content);
        $tpl_row->parseCurrentBlock();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return array(Component\Deck\Deck::class);
    }
}
