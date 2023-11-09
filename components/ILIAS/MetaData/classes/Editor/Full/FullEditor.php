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

namespace ILIAS\MetaData\Editor\Full;

use ILIAS\UI\Component\Panel\Panel;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Dropdown\Standard as StandardDropdown;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\MetaData\Editor\Dictionary\DictionaryInterface as EditorDictionaryInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Editor\Full\Services\Services as FullEditorServices;
use ILIAS\MetaData\Editor\Full\Services\ManipulatorAdapter;
use ILIAS\MetaData\Editor\Full\Services\Tables\Table;
use ILIAS\MetaData\Editor\Full\Services\Actions\FlexibleModal;
use ILIAS\MetaData\Editor\Http\RequestForFormInterface;

class FullEditor
{
    public const TABLE = 'table';
    public const PANEL = 'panel';
    public const ROOT = 'root';
    public const FORM = 'form';

    protected EditorDictionaryInterface $editor_dictionary;
    protected NavigatorFactoryInterface $navigator_factory;
    protected FullEditorServices $services;
    protected FormContent $form_content;
    protected TableContent $table_content;
    protected PanelContent $panel_content;
    protected RootContent $root_content;

    public function __construct(
        EditorDictionaryInterface $editor_dictionary,
        NavigatorFactoryInterface $navigator_factory,
        FullEditorServices $services,
        FormContent $form_content,
        TableContent $table_content,
        PanelContent $panel_content,
        RootContent $root_content
    ) {
        $this->editor_dictionary = $editor_dictionary;
        $this->navigator_factory = $navigator_factory;
        $this->services = $services;
        $this->form_content = $form_content;
        $this->table_content = $table_content;
        $this->panel_content = $panel_content;
        $this->root_content = $root_content;
    }

    public function manipulateMD(): ManipulatorAdapter
    {
        return $this->services->manipulatorAdapter();
    }

    /**
     * @return Table[]|StandardForm[]|Panel[]|FlexibleModal[]|Button[]|StandardDropdown[]
     */
    public function getContent(
        SetInterface $set,
        PathInterface $base_path,
        ?RequestForFormInterface $request = null
    ): \Generator {
        $elements = $this->getElements($set, $base_path);
        switch ($this->decideContentType(...$elements)) {
            case self::FORM:
                yield from $this->form_content->content(
                    $base_path,
                    $elements[0],
                    $request
                );
                return;

            case self::TABLE:
                yield from $this->table_content->content(
                    $base_path,
                    $request,
                    ...$elements
                );
                return;

            case self::PANEL:
                yield from $this->panel_content->content(
                    $base_path,
                    $elements[0],
                    false,
                    $request,
                );
                return;

            case self::ROOT:
                yield from $this->root_content->content(
                    $base_path,
                    $elements[0],
                    $request
                );
                return;

            default:
                throw new \ilMDEditorException(
                    'Invalid content type.'
                );
        }
    }

    protected function decideContentType(
        ElementInterface ...$elements
    ): string {
        if ($elements[0]->isRoot()) {
            return self::ROOT;
        }
        $tag = $this->editor_dictionary->tagForElement($elements[0]);
        if (!$tag?->isLastInTree()) {
            return self::PANEL;
        }
        if ($tag?->isCollected()) {
            return self::TABLE;
        }
        return self::FORM;
    }

    /**
     * @return ElementInterface[]
     */
    protected function getElements(SetInterface $set, PathInterface $path): array
    {
        $res = $this->navigator_factory->navigator(
            $path,
            $set->getRoot()
        )->elementsAtFinalStep();
        return iterator_to_array($res);
    }
}
