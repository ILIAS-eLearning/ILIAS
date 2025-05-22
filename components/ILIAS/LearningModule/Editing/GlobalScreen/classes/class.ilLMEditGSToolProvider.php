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

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Legacy as LegacySlate;

/**
 * Learning module editing GS tool provider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilLMEditGSToolProvider extends AbstractDynamicToolProvider
{
    use Hasher;
    public const SHOW_TREE = 'show_tree';

    /**
     * @inheritDoc
     */
    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main()->repository();
    }


    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts): array
    {
        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_TREE, true)) {
            $title = $this->dic->language()->txt('objs_st');
            $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("standard/icon_chp.svg"), $title);

            $iff = function ($id) {
                return $this->identification_provider->contextAwareIdentifier($id);
            };
            $l = function (string $content) {
                return $this->dic->ui()->factory()->legacy()->content($content);
            };
            $identification = $iff("tree");
            $hashed = $this->hash($identification->serialize());
            $tools[] = $this->factory->tool($identification)
                ->addComponentDecorator(static function (ILIAS\UI\Component\Component $c) use ($hashed): ILIAS\UI\Component\Component {
                    if ($c instanceof LegacySlate) {
                        $signal_id = $c->getToggleSignal()->getId();
                        return $c->withAdditionalOnLoadCode(static function ($id) use ($hashed) {
                            return "document.addEventListener('il-lm-editor-tree', () => {
                                    il.UI.maincontrols.mainbar.engageTool('$hashed');
                                 });";
                        });
                    }
                    return $c;
                })
                ->withTitle($title)
                ->withSymbol($icon)
                ->withContentWrapper(function () use ($l) {
                    return $l($this->getContent());
                });
        }

        return $tools;
    }


    /**
     *
     * @return string
     */
    private function getContent(): string
    {
        global $DIC;
        $request = $DIC->learningModule()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        $lm = new ilObjLearningModule($request->getRefId());

        $exp = new ilLMEditorExplorerGUI("illmeditorgui", "showTree", $lm);

        return $exp->getHTML(true);
    }
}
