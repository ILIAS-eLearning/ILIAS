<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\Slate\Slate;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Implementation\Render\Template as UITemplateWrapper;

class Renderer extends AbstractComponentRenderer
{
    const BLOCK_MAINBAR_ENTRIES = 'trigger_item';
    const BLOCK_MAINBAR_TOOLS = 'tool_trigger_item';
    const BLOCK_MAINBAR_TOOLS_HIDDEN = 'tool_trigger_item_hidden';
    const BLOCK_METABAR_ENTRIES = 'meta_element';

    private $signals_for_tools = [];
    private $trigger_signals = [];

    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        if ($component instanceof MainBar) {
            return $this->renderMainbar($component, $default_renderer);
        }
        if ($component instanceof MetaBar) {
            return $this->renderMetabar($component, $default_renderer);
        }
        if ($component instanceof Footer) {
            return $this->renderFooter($component, $default_renderer);
        }
    }

    protected function enumerateNodes($parent_id, $id, $slate)
    {
         if (!$slate instanceof Slate && !$slate instanceof MainBar) {
            return $slate;
        }
        return $slate
            ->withMainBarId($parent_id, $id)
            ->withMappedSubNodes(
                function($num, $slate) use ($id) {
                    return $this->enumerateNodes("$id", "$id:$num", $slate);
                }
            );
    }

    protected function renderMainbarEntry(
        array $entries,
        string $block,
        MainBar $component,
        UITemplateWrapper $tpl,
        UIFactory $f,
        RendererInterface $default_renderer
    ) {

        $hidden = $component->getInitiallyHiddenToolIds();
        $close_buttons = $component->getCloseButtons();

        foreach ($entries as $k => $entry) {
            $use_block = $block;

            if ($entry instanceof Slate) {
                list($mb_parent, $mb_id) = $entry->getMainBarId();

                $trigger_signal = $component->getTriggerSignal($mb_id, $component::ENTRY_ACTION_TRIGGER);
                $this->trigger_signals[] = $trigger_signal;
                $button = $f->button()->bulky($entry->getSymbol(), $entry->getName(), '#')
                    ->withAdditionalOnLoadCode(
                        function ($id) use ($mb_id, $mb_parent, $k) {
                            return "
                            il.UI.maincontrols.mainbar.registerEntry(
                                '{$mb_id}', 'triggerer', '{$mb_parent}', '{$id}'
                            );
                            il.UI.maincontrols.mainbar.addMapping('{$k}','{$mb_id}');
                            ";
                        }
                    )
                    ->withOnClick($trigger_signal);

                //closeable
                if(array_key_exists($k, $close_buttons)) {
                    $trigger_signal = $component->getTriggerSignal($mb_id, $component::ENTRY_ACTION_REMOVE);
                    $this->trigger_signals[] = $trigger_signal;
                    $btn_removetool = $close_buttons[$k]
                       ->withAdditionalOnloadCode(
                            function ($id) use ($mb_id, $mb_parent) {
                                return "
                                il.UI.maincontrols.mainbar.registerEntry(
                                    '{$mb_id}', 'remover', '{$mb_parent}', '{$id}'
                                );";
                            }
                        )
                        ->withOnClick($trigger_signal);

                    $tpl->setCurrentBlock("tool_removal");
                    $tpl->setVariable("REMOVE_TOOL_ID", $button_id);
                    $tpl->setVariable("REMOVE_TOOL", $default_renderer->render($btn_removetool));
                    $tpl->parseCurrentBlock();
                }

                if(in_array($k, $hidden)) {
                    $use_block = static::BLOCK_MAINBAR_TOOLS_HIDDEN;
                    $button = $button->withAdditionalOnLoadCode(
                        function ($id) use ($mb_id, $mb_parent) {
                            return "il.UI.maincontrols.mainbar.entries.entries['{$mb_id}'].isHidden = true;";
                        }
                    );
                }

                $slate = $entry;

            } else {
                $button = $entry;
                $slate = null;
            }

            $tpl->setCurrentBlock($use_block);
            $tpl->setVariable("BUTTON", $default_renderer->render($button));
            $tpl->parseCurrentBlock();

            if ($slate) {
                $tpl->setCurrentBlock("slate_item");
                $tpl->setVariable("SLATE", $default_renderer->render($slate));
                $tpl->parseCurrentBlock();
            }
        }
    }

    protected function renderMainbar(MainBar $component, RendererInterface $default_renderer)
    {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.mainbar.html", true, true);

        //add "more"-slate
        $more_button = $component->getMoreButton();
        $more_slate = $f->maincontrols()->slate()->combined(
            $more_button->getLabel(),
            $more_button->getIconOrGlyph()
        );
        $component = $component->withAdditionalEntry(
            '_mb_more_entry',
             $more_slate
        );

        $component = $this->enumerateNodes("0", "0", $component);

        $mb_entries = [
            static::BLOCK_MAINBAR_ENTRIES => $component->getEntries(),
            static::BLOCK_MAINBAR_TOOLS => $component->getToolEntries()
        ];

        foreach ($mb_entries as $block => $entries) {
            $this->renderMainbarEntry(
            $entries, $block,
                $component, $tpl, $f, $default_renderer
            );
        }

        //tools-section trigger
        if(count($component->getToolEntries()) > 0) {
            $btn_tools = $component->getToolsButton()
                ->withOnClick($component->getToggleToolsSignal());

            $tpl->setCurrentBlock("tools_trigger");
            $tpl->setVariable("BUTTON", $default_renderer->render($btn_tools));
            $tpl->parseCurrentBlock();
        }

        //disengage all, close slates
        $btn_disengage = $f->button()->bulky($f->symbol()->glyph()->back("#"), "close", "#")
            ->withOnClick($component->getDisengageAllSignal());
        $tpl->setVariable("CLOSE_SLATES", $default_renderer->render($btn_disengage));


        $id = $this->bindMainbarJS($component);
        $tpl->setVariable('ID', $id);

        return $tpl->get();
    }

    protected function renderMetabar(MetaBar $component, RendererInterface $default_renderer)
    {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.metabar.html", true, true);
        $active = '';
        $signals = [
            'entry' => $component->getEntryClickSignal(),
            'close_slates' => $component->getDisengageAllSignal()
        ];
        $entries = $component->getEntries();

        //more-slate
        $more_button = $f->button()->bulky(
            $this->getUIFactory()->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/options-vertical.svg', ''),
            'more',
            '#'
        );
        $more_label = $more_button->getLabel();
        $more_symbol = $more_button->getIconOrGlyph();
        $more_slate = $f->maincontrols()->slate()->combined($more_label, $more_symbol, $f->legacy(''));
        $entries[] = $more_slate;

        $this->renderTriggerButtonsAndSlates(
            $tpl,
            $default_renderer,
            $signals['entry'],
            static::BLOCK_METABAR_ENTRIES,
            $entries,
            $active
        );

        $more_button = $this->getUIFactory()->button()->bulky(
            $this->getUIFactory()->symbol()->glyph()->more()
                 ->withCounter($this->getUIFactory()->counter()->novelty(0))
                 ->withCounter($this->getUIFactory()->counter()->status(0)),
            'more',
            '#'
        );

        $this->addMoreSlate($tpl, $default_renderer, static::BLOCK_METABAR_ENTRIES, $more_button, $signals, $active);

        $component = $component->withOnLoadCode(
            function ($id) use ($signals) {
                $entry_signal = $signals['entry'];
                $close_slates_signal = $signals['close_slates'];
                return "
					il.UI.maincontrols.metabar.registerSignals(
						'{$id}',
						'{$entry_signal}',
						'{$close_slates_signal}',
					);
					il.UI.maincontrols.metabar.init();
					$(window).resize(il.UI.maincontrols.metabar.init);
				";
            }
        );
        $id = $this->bindJavaScript($component);
        $tpl->setVariable('ID', $id);
        return $tpl->get();
    }


    protected function renderTriggerButtonsAndSlates(
        UITemplateWrapper $tpl,
        RendererInterface $default_renderer,
        Signal $entry_signal,
        string $block,
        array $entries,
        string $active = null,
        array $initially_hidden_ids = [],
        array $close_buttons = [],
        Signal $tool_removal_signal = null
    ) {
        foreach ($entries as $id=>$entry) {
            $use_block = $block;
            $engaged = (string) $id === $active;

            if ($entry instanceof Slate) {
                $f = $this->getUIFactory();
                $secondary_signal = $entry->getToggleSignal();
                $button = $f->button()->bulky($entry->getSymbol(), $entry->getName(), '#')
                    ->withOnClick($entry_signal)
                    ->appendOnClick($secondary_signal)
                    ->withEngagedState($engaged);

                $slate = $entry;
                $slate = $slate->withEngaged(false); //init disengaged, onLoadCode will "click" the button
            } else {
                $button = $entry;
                $slate = null;
            }

            $button_html = $default_renderer->render($button);

            $tpl->setCurrentBlock($use_block);
            $tpl->setVariable("BUTTON", $button_html);
            $tpl->parseCurrentBlock();

            if ($slate) {
                $tpl->setCurrentBlock("slate_item");
                $tpl->setVariable("SLATE", $default_renderer->render($slate));
                $tpl->parseCurrentBlock();
            }
        }
    }

    protected function bindMainbarJS(MainBar $component): string
    {
        $trigger_signals = $this->trigger_signals;
        $component = $component->withOnLoadCode(
            function ($id) use ($component, $trigger_signals)  {
                $disengage_all_signal = $component->getDisengageAllSignal();
                $tools_toggle_signal = $component->getToggleToolsSignal();
                $js = "
                    il.UI.maincontrols.mainbar.registerSignals(
                        '{$id}',
                        '{$disengage_all_signal}',
                        '{$tools_toggle_signal}'
                    );
                ";

                foreach ($trigger_signals as $signal) {
                    $js .= "il.UI.maincontrols.mainbar.addTriggerSignal('{$signal}');";
                }

                foreach ($component->getToolEntries() as $k => $tool) {
                    $signal = $component->getEngageToolSignal($k);
                    $js .= "il.UI.maincontrols.mainbar.addTriggerSignal('{$signal}');";
                }


                $js .= "
                    il.UI.maincontrols.mainbar.readAndRender();
                    il.UI.maincontrols.mainbar.initMore();
                    $(window).resize(il.UI.maincontrols.mainbar.initMore);
                ";
                return $js;
            }
        );
/*
        if ($active) {
            $component = $component->withAdditionalOnLoadCode(
                function ($id) {
                    return "il.UI.maincontrols.mainbar.initActive('{$id}');";
                }
            );
        }
*/
        $id = $this->bindJavaScript($component);
        return $id;
    }

    protected function renderFooter(Footer $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.footer.html", true, true);
        $links = $component->getLinks();
        if ($links) {
            $link_list = $this->getUIFactory()->listing()->unordered($links);
            $tpl->setVariable('LINKS', $default_renderer->render($link_list));
        }

        $tpl->setVariable('TEXT', $component->getText());

        $perm_url = $component->getPermanentURL();
        if ($perm_url) {
            $tpl->setVariable(
                'PERMANENT_URL',
                $perm_url->getBaseURI() . '?' . $perm_url->getQuery()
            );
        }
        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/MainControls/mainbar.js');
        $registry->register('./src/UI/templates/js/MainControls/metabar.js');
        $registry->register('./src/GlobalScreen/Client/dist/GS.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(
            MetaBar::class,
            MainBar::class,
            Footer::class
        );
    }
}
