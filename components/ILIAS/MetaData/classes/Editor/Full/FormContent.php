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
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Editor\Full\Services\Services as FullEditorServices;
use ILIAS\MetaData\Editor\Full\Services\Actions\FlexibleModal;
use ILIAS\MetaData\Editor\Http\RequestForFormInterface;

class FormContent
{
    protected FullEditorServices $services;

    public function __construct(
        FullEditorServices $services
    ) {
        $this->services = $services;
    }

    /**
     * @return StandardForm[]|FlexibleModal[]|Button[]
     */
    public function content(
        PathInterface $base_path,
        ElementInterface $element,
        ?RequestForFormInterface $request
    ): \Generator {
        $delete_modal = $this->services->actions()->getModal()->delete(
            $base_path,
            $element,
            true
        );
        if ($delete_modal) {
            $button = $this->services->actions()->getButton()->delete(
                $delete_modal->getFlexibleSignal(),
                false,
                true
            );
            yield ContentType::MODAL => $delete_modal;
            yield ContentType::TOOLBAR => $button;
        }
        $form = $this->services->formFactory()->getUpdateForm(
            $base_path,
            $element
        );
        if ($request) {
            $form = $request->applyRequestToForm($form);
        }
        yield ContentType::MAIN => $form;
    }
}
