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
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Editor\Full\Components\Actions\FlexibleModal;
use ILIAS\MetaData\Editor\Http\RequestForFormInterface;
use ILIAS\MetaData\Editor\Full\Components\Actions\Actions;
use ILIAS\MetaData\Editor\Full\Components\FormFactory;

class FormContent
{
    public function __construct(
        protected Actions $actions,
        protected FormFactory $form_factory
    ) {
    }

    /**
     * @return Generator<StandardForm|FlexibleModal|Button>
     */
    public function content(
        PathInterface $base_path,
        ElementInterface $element,
        ?RequestForFormInterface $request
    ): Generator {
        $delete_modal = $this->actions->getModal()->delete(
            $base_path,
            $element,
            true
        );
        if ($delete_modal) {
            $button = $this->actions->getButton()->delete(
                $delete_modal->getFlexibleSignal(),
                false,
                true
            );
            yield ContentType::MODAL => $delete_modal;
            yield ContentType::TOOLBAR => $button;
        }
        $form = $this->form_factory->getUpdateForm(
            $base_path,
            $element
        );
        if ($request) {
            $form = $request->applyRequestToForm($form);
        }
        yield ContentType::MAIN => $form;
    }
}
