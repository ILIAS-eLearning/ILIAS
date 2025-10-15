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

namespace ILIAS\User\Search;

use ILIAS\User\Profile\DataRepository;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Field\Tag;

class Search
{
    public function __construct(
        private readonly UIFactory $ui_factory
    ) {
    }

    public function getInput(
        string $label,
        ?Endpoint $endpoint = null,
        ?string $byline = null
    ): Tag {
        $endpoint ??= new DefaultEndpointGUI();
        return $this->ui_factory->input()->field()->tag($label, [], $byline)
            ->withSuggestionsStartAfter($endpoint->getSuggestionsStartAfter())
            ->withAsyncAutocomplete(...$endpoint->acquireBuilderAndToken());
    }
}
