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
use ILIAS\UI\Implementation\Component\Listing\CharacteristicValue\Text as Listing;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Editor\Http\RequestForFormInterface;
use ILIAS\MetaData\Editor\Full\Components\Actions\Actions;
use ILIAS\MetaData\Editor\Full\Components\PropertiesFetcher;

class PanelContent
{
    public function __construct(
        protected Actions $actions,
        protected PropertiesFetcher $properties_fetcher,
        protected UIFactory $ui_factory,
        protected PresenterInterface $presenter
    ) {
    }

    /**
     * @return Generator<Panel|FlexibleModal>
     */
    public function content(
        PathInterface $base_path,
        ElementInterface $element,
        bool $is_subpanel,
        ?RequestForFormInterface $request
    ): Generator {
        $buttons = [];
        $delete_modal = $this->actions->getModal()->delete(
            $base_path,
            $element,
            true
        );
        if ($delete_modal) {
            $buttons[] = $this->actions->getButton()->delete(
                $delete_modal->getFlexibleSignal(),
                true,
                true
            );
            yield ContentType::MODAL => $delete_modal;
        }
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
        $dropdown = $this->ui_factory->dropdown()->standard($buttons);

        if ($is_subpanel) {
            $panel = $this->ui_factory->panel()->sub(
                $this->presenter->elements()->nameWithRepresentation(false, $element),
                $this->listing($element) ?? []
            )->withActions($dropdown);
        } else {
            $panel = $this->ui_factory->panel()->standard(
                $this->presenter->elements()->nameWithParents($element),
                $this->listing($element) ?? []
            )->withActions($dropdown);
        }
        yield ContentType::MAIN => $panel;
    }

    protected function listing(
        ElementInterface $element
    ): ?Listing {
        $properties = $this->properties_fetcher->getPropertiesByPreview($element);
        $properties = iterator_to_array($properties);
        if (!empty($properties)) {
            return $this->ui_factory->listing()
                                    ->characteristicValue()
                                    ->text($properties);
        }
        return null;
    }
}
