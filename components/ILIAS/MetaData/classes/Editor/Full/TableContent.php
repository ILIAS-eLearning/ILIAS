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
use ILIAS\UI\Component\Button\Button;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Editor\Full\Components\Actions\FlexibleModal;
use ILIAS\MetaData\Editor\Full\Components\Tables\Table;
use ILIAS\MetaData\Editor\Http\RequestForFormInterface;
use ILIAS\MetaData\Editor\Full\Components\Actions\Actions;
use ILIAS\MetaData\Editor\Full\Components\Tables\TableFactory;

class TableContent
{
    public function __construct(
        protected Actions $actions,
        protected TableFactory $table_factory
    ) {
    }

    /**
     * @return Generator<Table|FlexibleModal|Button>
     */
    public function content(
        PathInterface $base_path,
        ?RequestForFormInterface $request,
        ElementInterface ...$elements
    ): Generator {
        yield from $this->createModalAndButton(
            $base_path,
            $request,
            ...$elements
        );
        $builder = $this->table_factory->table();
        foreach ($elements as $element) {
            if ($element->isScaffold()) {
                continue;
            }
            $update_modal = $this->actions->getModal()->update(
                $base_path,
                $element,
                $request
            );
            $delete_modal = $this->actions->getModal()->delete(
                $base_path,
                $element,
                true
            );
            $builder = $builder->withAdditionalRow(
                $element,
                $update_modal->getFlexibleSignal(),
                $delete_modal?->getFlexibleSignal()
            );
            yield ContentType::MODAL => $update_modal;
            if (isset($delete_modal)) {
                yield ContentType::MODAL => $delete_modal;
            }
        }
        yield ContentType::MAIN => $builder->get();
    }

    /**
     * @return Generator<FlexibleModal|Button>
     */
    protected function createModalAndButton(
        PathInterface $base_path,
        ?RequestForFormInterface $request,
        ElementInterface ...$elements
    ): Generator {
        foreach ($elements as $element) {
            if (!$element->isScaffold()) {
                continue;
            }
            $modal = $this->actions->getModal()->create(
                $base_path,
                $element,
                $request
            );
            $button = $this->actions->getButton()->create(
                $modal->getFlexibleSignal(),
                $element
            );
            yield ContentType::MODAL => $modal;
            yield ContentType::TOOLBAR => $button;
        }
    }
}
