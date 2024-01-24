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

namespace ILIAS\UI\Implementation\Component\Popover;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    protected function renderComponent(Component\Component $component, RendererInterface $default_renderer): ?string
    {
        if ($component instanceof Component\Popover\Standard) {
            return $this->renderStandardPopover($component, $default_renderer);
        }

        if ($component instanceof Component\Popover\Listing) {
            return $this->renderListingPopover($component, $default_renderer);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./node_modules/webui-popover/dist/jquery.webui-popover.js');
        $registry->register('./components/ILIAS/UI/src/templates/js/Popover/popover.js');
    }

    protected function initClientSidePopover(Component\Popover\Popover $popover): Component\Popover\Popover
    {
        $tpl = $this->getTemplate('tpl.popover.html', true, true);
        $tpl->setVariable('FORCE_RENDERING', '');

        $replacement = array(
            '"' => '\"',
            "\n" => "",
            "\t" => "",
            "\r" => "",
        );

        $options = array(
            'title' => $this->escape($popover->getTitle()),
            'placement' => $popover->getPosition(),
            'multi' => true,
            'template' => str_replace(array_keys($replacement), array_values($replacement), $tpl->get()),
        );

        if ($popover->isFixedPosition()) {
            $options['style'] = "fixed";
        }

        $is_async = $popover->getAsyncContentUrl();
        if ($is_async) {
            $options['type'] = 'async';
            $options['url'] = $popover->getAsyncContentUrl();
        }

        $show = $popover->getShowSignal();
        $replace = $popover->getReplaceContentSignal();

        $popover = $popover->withAdditionalOnLoadCode(function ($id) use ($options, $show, $replace, $is_async) {
            if (!$is_async) {
                $options["url"] = "#$id";
            }
            $options = json_encode($options);

            return
                "$(document).on('$show', function(event, signalData) {
					il.UI.popover.showFromSignal(signalData, JSON.parse('$options'));
				});" .
                "$(document).on('$replace', function(event, signalData) {
					il.UI.popover.replaceContentFromSignal('$show', signalData);
				});";
        });

        return $popover;
    }

    protected function renderStandardPopover(
        Component\Popover\Standard $popover,
        RendererInterface $default_renderer,
    ): string {
        $popover = $this->initClientSidePopover($popover);

        $tpl = $this->getTemplate('tpl.standard-popover-content.html', true, true);
        $tpl->setVariable('CONTENT', $default_renderer->render($popover->getContent()));

        return $this->dehydrateComponent($popover, $tpl, $this->getOptionalIdBinder());
    }

    protected function renderListingPopover(
        Component\Popover\Listing $popover,
        RendererInterface $default_renderer,
    ): string {
        $popover = $this->initClientSidePopover($popover);

        $tpl = $this->getTemplate('tpl.listing-popover-content.html', true, true);
        foreach ($popover->getItems() as $item) {
            $tpl->setCurrentBlock('item');
            $tpl->setVariable('ITEM', $default_renderer->render($item));
            $tpl->parseCurrentBlock();
        }

        return $this->dehydrateComponent($popover, $tpl, $this->getOptionalIdBinder());
    }

    protected function escape(string $str): string
    {
        return strip_tags(htmlentities($str, ENT_QUOTES, 'UTF-8'));
    }
}
