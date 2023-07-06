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

namespace ILIAS\UI\Implementation\Component\Table\Action;

use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Component\Table\Action as I;

class Factory implements I\Factory
{
    public function standard(
        string $label,
        URLBuilder $url_builder,
        URLBuilderToken $url_builder_token,
        bool $async = false
    ): I\Standard {
        return new Standard($label, $url_builder, $url_builder_token, $async);
    }

    public function single(
        string $label,
        URLBuilder $url_builder,
        URLBuilderToken $url_builder_token,
        bool $async = false
    ): I\Single {
        return new Single($label, $url_builder, $url_builder_token, $async);
    }

    public function multi(
        string $label,
        URLBuilder $url_builder,
        URLBuilderToken $url_builder_token,
        bool $async = false
    ): I\Multi {
        return new Multi($label, $url_builder, $url_builder_token, $async);
    }
}
