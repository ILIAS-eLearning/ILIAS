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
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input\Field\Node;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Component as C;
use ILIAS\UI\Renderer as RendererInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Renderer extends AbstractComponentRenderer
{
    protected const string BRANCH_NODE_TYPE = 'branch';
    protected const string ASYNC_NODE_TYPE = 'async';

    public function render(C\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Async) {
            return $this->renderAsyncNode($component, $default_renderer);
        }
        if ($component instanceof Leaf) {
            return $this->renderLeafNode($component, $default_renderer);
        }
        if ($component instanceof Branch) {
            return $this->renderBranchNode($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    protected function renderAsyncNode(Async $component, RendererInterface $default_renderer): string
    {
        $template = $this->getTemplate('tpl.branch_node.html', true, true);

        $this->addNodeCharacteristics($component, $template, $default_renderer);
        $this->addNodeGlyphs($component, $template, $default_renderer);
        $this->addNodeLabels($component, $template, $default_renderer);

        $template->setVariable('RENDER_URL', (string) $component->getRenderUrl());
        $template->setVariable('NODE_TYPE', self::ASYNC_NODE_TYPE);

        return $template->get();
    }

    protected function renderLeafNode(Leaf $component, RendererInterface $default_renderer): string
    {
        $template = $this->getTemplate('tpl.leaf_node.html', true, true);

        $this->addNodeCharacteristics($component, $template, $default_renderer);
        $this->addNodeGlyphs($component, $template, $default_renderer);
        $this->addNodeLabels($component, $template, $default_renderer);

        return $template->get();
    }

    protected function renderBranchNode(Branch $component, RendererInterface $default_renderer): string
    {
        $template = $this->getTemplate('tpl.branch_node.html', true, true);

        $this->addNodeCharacteristics($component, $template, $default_renderer);
        $this->addNodeGlyphs($component, $template, $default_renderer);
        $this->addNodeLabels($component, $template, $default_renderer);

        $template->setVariable('NODE_CHILDREN', $default_renderer->render($component->getChildren()));
        $template->setVariable('NODE_TYPE', self::BRANCH_NODE_TYPE);

        return $template->get();
    }

    protected function addNodeCharacteristics(Node $component, Template $template, RendererInterface $default_renderer): void
    {
        $template->setVariable('NODE_ICON', $default_renderer->render($this->getNodeIcon($component)));
        $template->setVariable('NODE_NAME', $this->convertSpecialCharacters($component->getName()));
        $template->setVariable('NODE_ID', $this->convertSpecialCharacters((string) $component->getId()));
    }

    protected function addNodeGlyphs(Node $component, Template $template, RendererInterface $default_renderer): void
    {
        $template->setVariable('EXPAND_GLYPH', $default_renderer->render(
            $this->getUIFactory()->symbol()->glyph()->expand(),
        ));
        $template->setVariable('SELECT_GLYPH', $default_renderer->render(
            $this->getUIFactory()->symbol()->glyph()->add(),
        ));
        $template->setVariable('REMOVE_GLYPH', $default_renderer->render(
            $this->getUIFactory()->symbol()->glyph()->apply(),
        ));
    }

    protected function addNodeLabels(Node $component, Template $template, RendererInterface $default_renderer): void
    {
        $template->setVariable('SELECT_NODE_LABEL', sprintf($this->txt('select_node'), $component->getName()));
    }

    protected function getNodeIcon(Node $component): C\Symbol\Icon\Icon
    {
        return $component->getIcon() ??
            $this->getUIFactory()->symbol()->icon()->standard('', '')->withAbbreviation(
                $this->getFirstCharacter($component->getName())
            );
    }

    protected function getFirstCharacter(string $text): string
    {
        return mb_substr($text, 0, 1);
    }
}
