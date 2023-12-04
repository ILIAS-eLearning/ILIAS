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

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Component\Input\Container\Form as F;
use ILIAS\UI\Implementation\Component\Input;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements F\Factory
{
    public function __construct(
        protected Input\Field\Factory $field_factory,
        protected SignalGeneratorInterface $signal_generator,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function standard(string $post_url, array $inputs): F\Standard
    {
        return new Standard($this->field_factory, new Input\FormInputNameSource(), $post_url, $inputs);
    }

    public function withoutButtons(string $post_url, array $inputs): F\Form
    {
        return new FormWithoutSubmitButton(
            $this->signal_generator,
            $this->field_factory,
            new Input\FormInputNameSource(),
            $post_url,
            $inputs
        );
    }
    public function dialog(string $post_url, array $inputs): F\Form
    {
        return new Dialog(
            $this->signal_generator,
            $this->field_factory,
            new Input\FormInputNameSource(),
            $post_url,
            $inputs
        );
    }

}
