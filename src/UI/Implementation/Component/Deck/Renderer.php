<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Deck;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        $tpl_card = $this->getTemplate("tpl.deck_card.html", true, true);
        $tpl_row = $this->getTemplate("tpl.deck_row.html", true, true);

        foreach ($component->getCards() as $card) {
            $tpl_card->setCurrentBlock("card");
            $tpl_card->setVariable("CARD", $default_renderer->render($card));
            $tpl_card->setVariable("SIZE_MD", $component->getCardsSizeForDisplaySize(Component\Deck\Deck::SIZE_M));
            $tpl_card->setVariable("SIZE_SM", $component->getCardsSizeForDisplaySize(Component\Deck\Deck::SIZE_S));
            $tpl_card->setVariable("SIZE_XS", $component->getCardsSizeForDisplaySize(Component\Deck\Deck::SIZE_XS));
            $tpl_card->setVariable("SIZE_LG", $component->getCardsSizeForDisplaySize(Component\Deck\Deck::SIZE_L));
            $tpl_card->parseCurrentBlock();
        }

        $this->parseRow($tpl_row, $tpl_card->get());

        return $tpl_row->get();
    }

    protected function parseRow(Template $tpl_row, string $content) : void
    {
        $tpl_row->setCurrentBlock("row");
        $tpl_row->setVariable("CARDS", $content);
        $tpl_row->parseCurrentBlock();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName() : array
    {
        return array(Component\Deck\Deck::class);
    }
}
