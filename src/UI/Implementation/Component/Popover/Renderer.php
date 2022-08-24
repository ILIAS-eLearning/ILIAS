<?php

declare(strict_types=1);

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
    public function render(Component\Component $popover, RendererInterface $default_renderer): string
    {
        $this->checkComponent($popover);
        $tpl = $this->getTemplate('tpl.popover.html', true, true);
        $tpl->setVariable('FORCE_RENDERING', '');
        /** @var Component\Popover\Popover $popover */

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

        $id = $this->bindJavaScript($popover);

        if ($popover->getAsyncContentUrl()) {
            return '';
        }

        if ($popover instanceof Component\Popover\Standard) {
            return $this->renderStandardPopover($popover, $default_renderer, $id);
        } elseif ($popover instanceof Component\Popover\Listing) {
            return $this->renderListingPopover($popover, $default_renderer, $id);
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./libs/bower/bower_components/webui-popover/dist/jquery.webui-popover.js');
        $registry->register('./src/UI/templates/js/Popover/popover.js');
    }

    protected function renderStandardPopover(
        Component\Popover\Standard $popover,
        RendererInterface $default_renderer,
        string $id
    ): string {
        $tpl = $this->getTemplate('tpl.standard-popover-content.html', true, true);
        $tpl->setVariable('ID', $id);
        $tpl->setVariable('CONTENT', $default_renderer->render($popover->getContent()));

        return $tpl->get();
    }

    protected function renderListingPopover(
        Component\Popover\Listing $popover,
        RendererInterface $default_renderer,
        string $id
    ): string {
        $tpl = $this->getTemplate('tpl.listing-popover-content.html', true, true);
        $tpl->setVariable('ID', $id);
        foreach ($popover->getItems() as $item) {
            $tpl->setCurrentBlock('item');
            $tpl->setVariable('ITEM', $default_renderer->render($item));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    protected function escape(string $str): string
    {
        return strip_tags(htmlentities($str, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array( Component\Popover\Standard::class, Component\Popover\Listing::class );
    }
}
