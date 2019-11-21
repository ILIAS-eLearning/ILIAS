<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Component;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\ModeInfo;
use ILIAS\UI\Component\MainControls\Slate\Slate;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\Template as UITemplateWrapper;
use ILIAS\UI\Renderer as RendererInterface;

class Renderer extends AbstractComponentRenderer
{

    const BLOCK_MAINBAR_ENTRIES = 'trigger_item';
    const BLOCK_MAINBAR_TOOLS = 'tool_trigger_item';
    const BLOCK_MAINBAR_TOOLS_HIDDEN = 'tool_trigger_item_hidden';
    const BLOCK_METABAR_ENTRIES = 'meta_element';
    private $signals_for_tools = [];


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
        if ($component instanceof ModeInfo) {
            return $this->renderModeInfo($component, $default_renderer);
        }
    }


    protected function renderMainbar(MainBar $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.mainbar.html", true, true);
        $active = $component->getActive();
        $tools = $component->getToolEntries();
        $signals = [
            'entry'         => $component->getEntryClickSignal(),
            'tools'         => $component->getToolsClickSignal(),
            'close_slates'  => $component->getDisengageAllSignal(),
            'tools_removal' => $component->getToolsRemovalSignal(),
        ];

        $this->renderTriggerButtonsAndSlates(
            $tpl,
            $default_renderer,
            $signals['entry'],
            static::BLOCK_MAINBAR_ENTRIES,
            $component->getEntries(),
            $active
        );

        if (count($tools) > 0) {
            $tools_button = $component->getToolsButton();
            $initially_hidden_ids = $component->getInitiallyHiddenToolIds();
            $close_buttons = $component->getCloseButtons();
            $this->addTools(
                $tpl,
                $default_renderer,
                $tools_button,
                $tools,
                $signals,
                $active,
                $initially_hidden_ids,
                $close_buttons
            );
        }

        $more_button = $component->getMoreButton();
        $this->addMoreSlate($tpl, $default_renderer, static::BLOCK_MAINBAR_ENTRIES, $more_button, $signals, $active);
        $this->addCloseSlateButton($tpl, $default_renderer, $signals);
        $this->addMainbarJS($tpl, $component, $signals, $active);

        return $tpl->get();
    }


    protected function renderMetabar(MetaBar $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.metabar.html", true, true);
        $active = '';
        $signals = [
            'entry'        => $component->getEntryClickSignal(),
            'close_slates' => $component->getDisengageAllSignal(),
        ];

        $this->renderTriggerButtonsAndSlates(
            $tpl,
            $default_renderer,
            $signals['entry'],
            static::BLOCK_METABAR_ENTRIES,
            $component->getEntries(),
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
        foreach ($entries as $id => $entry) {
            $use_block = $block;
            $engaged = (string) $id === $active;

            if ($entry instanceof Slate) {
                $f = $this->getUIFactory();
                $secondary_signal = $entry->getToggleSignal();
                if ($block === static::BLOCK_MAINBAR_TOOLS) {
                    $secondary_signal = $entry->getEngageSignal();
                }
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

            if ($block === static::BLOCK_MAINBAR_TOOLS) {
                $this->button_id = null;
                $button = $button->withAdditionalOnLoadCode(
                    function ($id) use ($button_id) {
                        $this->button_id = $id;
                    }
                );
                $button_html = $default_renderer->render($button);
                $button_id = $this->button_id;

                //closeable tool
                if (array_key_exists($id, $close_buttons)) {
                    $btn_removetool = $close_buttons[$id]
                        ->appendOnClick($tool_removal_signal);
                    $tpl->setCurrentBlock("tool_removal");
                    $tpl->setVariable("REMOVE_TOOL_ID", $button_id);
                    $tpl->setVariable("REMOVE_TOOL", $default_renderer->render($btn_removetool));
                    $tpl->parseCurrentBlock();
                }

                //initially hidden
                if (in_array($id, $initially_hidden_ids)) {
                    $this->signals_for_tools[$id] = $button_id;
                    $use_block = static::BLOCK_MAINBAR_TOOLS_HIDDEN;
                }
            } else {
                $button_html = $default_renderer->render($button);
            }

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


    protected function addCloseSlateButton(
        UITemplateWrapper $tpl,
        RendererInterface $default_renderer,
        array $signals
    ) {
        $f = $this->getUIFactory();
        $btn_disengage = $f->button()->bulky($f->symbol()->glyph()->back("#"), "close", "#")
            ->withOnClick($signals['close_slates']);
        $tpl->setVariable("CLOSE_SLATES", $default_renderer->render($btn_disengage));
    }


    protected function addTools(
        UITemplateWrapper $tpl,
        RendererInterface $default_renderer,
        Component\Button\Bulky $tools_button,
        array $tools,
        array $signals,
        string $active = null,
        array $initially_hidden_ids = [],
        array $close_buttons
    ) {
        $f = $this->getUIFactory();

        $btn_tools = $tools_button
            ->withOnClick($signals['tools'])
            ->withEngagedState(false); //if a tool-entry is active, onLoadCode will "click" the button

        $tpl->setCurrentBlock("tools_trigger");
        $tpl->setVariable("BUTTON", $default_renderer->render($btn_tools));
        $tpl->parseCurrentBlock();

        $this->renderTriggerButtonsAndSlates(
            $tpl,
            $default_renderer,
            $signals['entry'],
            static::BLOCK_MAINBAR_TOOLS,
            $tools,
            $active,
            $initially_hidden_ids,
            $close_buttons,
            $signals['tools_removal']
        );
    }


    protected function addMoreSlate(
        UITemplateWrapper $tpl,
        RendererInterface $default_renderer,
        string $block,
        Component\Button\Bulky $more_button,
        array $signals,
        string $active = null
    ) {
        $f = $this->getUIFactory();
        $more_label = $more_button->getLabel();
        $more_symbol = $more_button->getIconOrGlyph();
        $more_slate = $f->maincontrols()->slate()->combined($more_label, $more_symbol, $f->legacy(''));
        $this->renderTriggerButtonsAndSlates(
            $tpl,
            $default_renderer,
            $signals['entry'],
            $block,
            [$more_slate],
            $active
        );
    }


    protected function addMainbarJS(
        UITemplateWrapper $tpl,
        MainBar $component,
        array $signals,
        string $active = null
    ) {
        $ext_signals = $this->signals_for_tools;
        $component = $component->withOnLoadCode(
            function ($id) use ($signals, $ext_signals, $component) {
                $entry_signal = $signals['entry'];
                $tools_signal = $signals['tools'];
                $close_slates_signal = $signals['close_slates'];
                $tool_removal_signal = $signals['tools_removal'];
                $js = "
                    il.UI.maincontrols.mainbar.registerSignals(
                        '{$id}',
                        '{$entry_signal}',
                        '{$close_slates_signal}',
                        '{$tools_signal}',
                        '{$tool_removal_signal}'
                    );
                    il.UI.maincontrols.mainbar.initMore();
                    $(window).resize(il.UI.maincontrols.mainbar.initMore);
				";

                foreach ($ext_signals as $key => $btn_id) {
                    $ext_signal = $component->getEngageToolSignal($key);
                    $js .= "
                        il.UI.maincontrols.mainbar.addExternalSignals(
                            '{$id}',
                            '{$ext_signal}',
                            '{$btn_id}'
                        );
                    ";
                }

                return $js;
            }
        );

        if ($active) {
            $component = $component->withAdditionalOnLoadCode(
                function ($id) {
                    return "il.UI.maincontrols.mainbar.initActive('{$id}');";
                }
            );
        }

        $id = $this->bindJavaScript($component);
        $tpl->setVariable('ID', $id);
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


    protected function renderModeInfo(ModeInfo $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.mode_info.html", true, true);
        $tpl->setVariable('MODE_TITLE', $component->getModeTitle());
        $tpl->setVariable('MODE_LEAVE_URI', $component->getCloseAction()->getBaseURI());
        $close_button = $this->getUIFactory()->button()->close();
        $tpl->setVariable('CLOSE_BUTTON', $default_renderer->render($close_button));

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
    }


    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(
            MetaBar::class,
            MainBar::class,
            Footer::class,
            ModeInfo::class,
        );
    }
}
