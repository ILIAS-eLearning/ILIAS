<?php declare(strict_types=1);

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
