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

use ILIAS\UI\Component\Button\Button;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Editor\Full\Services\Services as FullEditorServices;
use ILIAS\MetaData\Editor\Full\Services\Actions\FlexibleModal;
use ILIAS\MetaData\Editor\Full\Services\Tables\Table;
use ILIAS\MetaData\Editor\Http\RequestForFormInterface;

class TableContent
{
    protected FullEditorServices $services;

    public function __construct(
        FullEditorServices $services
    ) {
        $this->services = $services;
    }

    /**
     * @return Table[]|FlexibleModal[]|Button[]
     */
    public function content(
        PathInterface $base_path,
        ?RequestForFormInterface $request,
        ElementInterface ...$elements
    ): \Generator {
        yield from $this->createModalAndButton(
            $base_path,
            $request,
            ...$elements
        );
        $builder =  $this->services->tableFactory()->table();
        $delete_buttons = [];
        $update_buttons = [];
        foreach ($elements as $element) {
            if ($element->isScaffold()) {
                continue;
            }
            $update_modal = $this->services->actions()->getModal()->update(
                $base_path,
                $element,
                $request
            );
            $delete_modal = $this->services->actions()->getModal()->delete(
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
     * @return FlexibleModal[]|Button[]
     */
    protected function createModalAndButton(
        PathInterface $base_path,
        ?RequestForFormInterface $request,
        ElementInterface ...$elements
    ): \Generator {
        foreach ($elements as $element) {
            if (!$element->isScaffold()) {
                continue;
            }
            $modal = $this->services->actions()->getModal()->create(
                $base_path,
                $element,
                $request
            );
            $button = $this->services->actions()->getButton()->create(
                $modal->getFlexibleSignal(),
                $element
            );
            yield ContentType::MODAL => $modal;
            yield ContentType::TOOLBAR => $button;
        }
    }
}
