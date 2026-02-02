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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Field\Tag;

class Search
{
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly EndpointFactory $endpoint_factory
    ) {
    }

    /**
     * @param $endpoint_configurator You can get a DefaultEndpointConfigurator from
     * \ILIAS\User\Search\Search::getDefaultEndpointConfigurator().
     */
    public function getInput(
        string $label,
        EndpointConfigurator $endpoint_configurator,
        ?string $byline = null
    ): Tag {
        $endpoint = $this->getEndpointGUI($endpoint_configurator);

        return $this->ui_factory->input()->field()->tag($label, [], $byline)
            ->withSuggestionsStartAfter($endpoint->getSuggestionsStartAfter())
            ->withAsyncAutocomplete(...$endpoint->acquireBuilderAndToken())
            ->withoutStripTags();
    }

    /**
     * @param $endpoint_configurator You can get a DefaultEndpointConfigurator from
     * \ILIAS\User\Search\Search::getDefaultEndpointConfigurator().
     */
    public function getEndpointGUI(
        EndpointConfigurator $endpoint_configurator
    ): EndpointGUI {
        return $this->endpoint_factory->getEndpointGUI($endpoint_configurator);
    }

    /**
     *
     * @param list<string> $parent_class_path Please Provide the full class_path
     * to reach this class and make sure that the path contains a check, if the
     * current user is actually allowed to search here. The only check that can
     * and will happen once the Endpoint is called is that a user is actually
     * logged in (no anonymous user).
     * @return \ILIAS\User\Search\Endpoint This endpoints returns
     * information from the user table by searching the fields for login,
     * lastname, firstname, email, and second_email depending on the searchability
     * of these fields set in Administration and the visibility of the fields set
     * by the user.
     */
    public function getDefaultEndpointConfigurator(
        array $parent_class_path
    ): EndpointConfigurator {
        return new DefaultEndpointConfigurator(
            $parent_class_path
        );
    }
}
