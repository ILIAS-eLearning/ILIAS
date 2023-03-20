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
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class HasAction extends Form
{
    protected string $post_url;

    public function __construct(
        FieldFactory $input_factory,
        NameSource $name_source,
        string $post_url,
        array $inputs
    ) {
        parent::__construct($input_factory, $name_source, $inputs);
        $this->post_url = $post_url;
    }

    public function getPostURL(): string
    {
        return $this->post_url;
    }
}
