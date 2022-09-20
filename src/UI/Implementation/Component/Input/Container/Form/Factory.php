<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Component\Input\Container\Form as F;
use ILIAS\UI\Implementation\Component\Input;

class Factory implements F\Factory
{
    protected Input\Field\Factory $field_factory;

    public function __construct(Input\Field\Factory $field_factory)
    {
        $this->field_factory = $field_factory;
    }

    /**
     * @inheritdoc
     */
    public function standard(string $post_url, array $inputs): F\Standard
    {
        return new Standard($this->field_factory, new Input\FormInputNameSource(), $post_url, $inputs);
    }
}
