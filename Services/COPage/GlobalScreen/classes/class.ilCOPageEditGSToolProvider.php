<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Page  editing GS tool provider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilCOPageEditGSToolProvider extends AbstractDynamicToolProvider
{
    const SHOW_EDITOR = 'copg_show_editor';


    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->repository();
    }


    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_EDITOR, true)) {
            $title = $this->dic->language()->txt('editor');
            $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_edtr.svg"), $title);

            $iff = function ($id) {
                return $this->identification_provider->contextAwareIdentifier($id);
            };
            $l = function (string $content) {
                return $this->dic->ui()->factory()->legacy($content);
            };
            $tools[] = $this->factory->tool($iff("copg_editor"))
                ->withSymbol($icon)
                ->withTitle($title)
                ->withContent($l($this->getContent()));
        }

        return $tools;
    }


    /**
     * @param int $ref_id
     *
     * @return string
     */
    private function getContent() : string
    {
        $lng = $this->dic->language();
        $lng->loadLanguageModule("content");
        $tpl = new ilTemplate("tpl.editor_slate.html", true, true, "Services/COPage");
        $tpl->setCurrentBlock("help");
        $tpl->setVariable("TXT_ADD_EL", $lng->txt("cont_add_elements"));
        $tpl->setVariable("PLUS", ilGlyphGUI::get(ilGlyphGUI::ADD));
        $tpl->setVariable("DRAG_ARROW", ilGlyphGUI::get(ilGlyphGUI::DRAG));
        $tpl->setVariable("TXT_DRAG", $lng->txt("cont_drag_and_drop_elements"));
        $tpl->setVariable("TXT_SEL", $lng->txt("cont_double_click_to_delete"));
        $tpl->parseCurrentBlock();
        return $tpl->get();
    }
}
