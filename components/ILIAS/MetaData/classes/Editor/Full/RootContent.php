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

use Generator;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Editor\Full\Components\Actions\FlexibleModal;
use ILIAS\UI\Component\Panel\Panel;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\UI\Component\Dropdown\Standard as StandardDropdown;
use ILIAS\MetaData\Editor\Http\RequestForFormInterface;
use ILIAS\MetaData\Editor\Full\Components\Actions\Actions;

class RootContent
{
    public function __construct(
        protected Actions $actions,
        protected UIFactory $ui_factory,
        protected PresenterInterface $presenter,
        protected PanelContent $panel_content
    ) {
    }

    /**
     * @return Generator<Panel|FlexibleModal>
     */
    public function content(
        PathInterface $base_path,
        ElementInterface $element,
        ?RequestForFormInterface $request
    ): Generator {
        yield from $this->createModalsAndDropdown(
            $base_path,
            $element,
            $request
        );

        $content = [];
        $content[] = $this->ui_factory->messageBox()->info(
            $this->presenter->utilities()->txt('meta_full_editor_navigation_info')
        );
        foreach ($element->getSubElements() as $sub) {
            if ($sub->isScaffold()) {
                continue;
            }
            $sub_content = $this->panel_content->content(
                $base_path,
                $sub,
                true,
                $request
            );
            foreach ($sub_content as $type => $entity) {
                if ($type === ContentType::MAIN) {
                    $content[] = $entity;
                    continue;
                }
                yield $type => $entity;
            }
        }

        $panel = $this->ui_factory->panel()->standard(
            $this->presenter->elements()->name($element),
            $content
        );
        yield ContentType::MAIN => $panel;
    }

    /**
     * @return Generator<FlexibleModal|StandardDropdown>
     */
    protected function createModalsAndDropdown(
        PathInterface $base_path,
        ElementInterface $element,
        ?RequestForFormInterface $request
    ): Generator {
        $buttons = [];
        foreach ($element->getSubElements() as $sub) {
            if (!$sub->isScaffold()) {
                continue;
            }
            $create_modal = $this->actions->getModal()->create(
                $base_path,
                $sub,
                $request
            );
            $buttons[] = $this->actions->getButton()->create(
                $create_modal->getFlexibleSignal(),
                $sub,
                true
            );
            yield ContentType::MODAL => $create_modal;
        }
        $dropdown = $this->ui_factory->dropdown()
                                     ->standard($buttons)
                                     ->withLabel($this->presenter->utilities()->txt('add'));
        yield ContentType::TOOLBAR => $dropdown;
    }
}
