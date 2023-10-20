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

namespace ILIAS\MetaData\Editor\Tree;

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;
use ILIAS\UI\Component\Tree\Tree;
use ILIAS\UI\Component\MainControls\Slate\Legacy as LegacySlate;
use ILIAS\UI\Component\Component;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\DI\Container;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Services\InternalServices;

class MDEditorToolProvider extends AbstractDynamicToolProvider
{
    use Hasher;

    protected InternalServices $services;

    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->services = new InternalServices($dic);
    }

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->repository();
    }

    /**
     * @param CalledContexts $called_contexts
     * @return Tool[]
     */
    public function getToolsForContextStack(
        CalledContexts $called_contexts
    ): array {
        $last_context = $called_contexts->getLast();

        if ($last_context) {
            $additional_data = $last_context->getAdditionalData();
            if (
                $additional_data->exists(\ilMDEditorGUI::SET_FOR_TREE) &&
                $additional_data->exists(\ilMDEditorGUI::PATH_FOR_TREE)
            ) {
                return [$this->buildTreeAsTool(
                    $additional_data->get(\ilMDEditorGUI::SET_FOR_TREE),
                    $additional_data->get(\ilMDEditorGUI::PATH_FOR_TREE)
                )];
            }
        }

        return [];
    }

    protected function buildTreeAsTool(SetInterface $set, PathInterface $path): Tool
    {
        $id_generator = function ($id) {
            return $this->identification_provider->contextAwareIdentifier($id);
        };
        $identification = $id_generator('system_styles_tree');
        $hashed = $this->hash($identification->serialize());

        $lng = $this->services->presentation()->utilities();

        return $this->factory
            ->tool($identification)
            ->withTitle(
                $lng->txt('meta_lom_short')
            )
            ->withSymbol(
                $this->services->dic()->ui()->factory()->symbol()->icon()->standard(
                    'mds',
                    $lng->txt('meta_lom_short')
                )
            )
            ->withContent($this->services->dic()->ui()->factory()->legacy(
                $this->services->dic()->ui()->renderer()->render($this->getUITree(
                    $set,
                    $path
                ))
            ))
            ->addComponentDecorator(static function (Component $c) use ($hashed): Component {
                if ($c instanceof LegacySlate) {
                    $signal_id = $c->getToggleSignal()->getId();
                    return $c->withAdditionalOnLoadCode(static function ($id) use ($hashed) {
                        return "il.UI.maincontrols.mainbar.engageTool('$hashed');";
                    });
                }
                return $c;
            });
    }

    protected function getUITree(SetInterface $set, PathInterface $path): Tree
    {
        $recursion = new Recursion(
            $this->services->paths()->pathFactory(),
            $this->services->editor()->presenter(),
            $this->services->editor()->dictionary(),
            $this->services->editor()->linkFactory(),
            ...$this->getElements($set, $path)
        );
        $f = $this->services->dic()->ui()->factory();

        return $f->tree()
                 ->expandable('MD Editor Tree', $recursion)
                 ->withData([$set->getRoot()])
                 ->withHighlightOnNodeClick(true);
    }

    /**
     * @return ElementInterface[]
     */
    protected function getElements(SetInterface $set, PathInterface $path): \Generator
    {
        yield from $this->services->paths()->navigatorFactory()->navigator(
            $path,
            $set->getRoot()
        )->elementsAtFinalStep();
    }
}
