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

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Implementation\Component\Input;
use ILIAS\UI\Implementation\Component\Input\NameSource;

/**
 * This implements a standard form.
 */
class Standard extends Form implements C\Input\Container\Form\Standard
{
    use HasPostURL;

    protected ?string $submit_caption = null;

    public function __construct(
        FieldFactory $field_factory,
        NameSource $name_source,
        string $post_url,
        array $inputs
    ) {
        parent::__construct($field_factory, $name_source, $inputs);
        $this->setPostURL($post_url);
    }

    /**
     * @inheritDoc
     */
    public function withSubmitCaption(string $caption): C\Input\Container\Form\Standard
    {
        $clone = clone $this;
        $clone->submit_caption = $caption;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getSubmitCaption(): ?string
    {
        return $this->submit_caption;
    }
}
