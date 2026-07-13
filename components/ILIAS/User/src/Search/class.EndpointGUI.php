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

use ILIAS\User\Profile\Fields\ConfigurationRepository as FieldConfigurationRepository;
use ILIAS\User\Profile\DataRepository as ProfileDataRepository;
use ILIAS\User\Settings\DataRepository as SettingsDataRepository;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Services as HttpService;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\URLBuilder;

class EndpointGUI
{
    private const array NAMESPACE = ['u', 's'];
    private const string SEARCH_TERM_TOKEN = 't';
    private const int SUGGESTIONS_START_AFTER = 3;

    public function __construct(
        private readonly FieldConfigurationRepository $field_configuration_repository,
        private readonly ProfileDataRepository $profile_data_repository,
        private readonly SettingsDataRepository $settings_data_repository,
        private readonly \ilObjUser $current_user,
        private readonly HttpService $http,
        private readonly Refinery $refinery,
        private readonly \ilCtrl $ctrl,
        private readonly DataFactory $data_factory,
        private readonly EndpointConfigurator $endpoint_configurator
    ) {
    }

    /**
     * @return array{0: \ILIAS\UI\URLBuilder, 1: \ILIAS\UI\URLBuilderToken}
     */
    public function acquireBuilderAndToken(): array
    {
        return (new URLBuilder(
            $this->data_factory->uri(
                ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                    array_merge(
                        $this->endpoint_configurator->getParentClassPath(),
                        [self::class]
                    )
                )
            )
        ))->acquireParameter(self::NAMESPACE, self::SEARCH_TERM_TOKEN);
    }

    public function getSuggestionsStartAfter(): int
    {
        return self::SUGGESTIONS_START_AFTER;
    }

    public function executeCommand(): void
    {
        $this->http->saveResponse(
            $this->http->response()->withBody(
                Streams::ofString($this->buildResponse())
            )
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    private function buildResponse(): string
    {
        if ($this->current_user->getId() === ANONYMOUS_USER_ID) {
            return '';
        }

        /** @var \ILIAS\UI\URLBuilderToken $token */
        [, $token] = $this->acquireBuilderAndToken();

        $autocomplete_query = $this->http->wrapper()->query()->retrieve(
            $token->getName(),
            $this->refinery->byTrying([
                $this->refinery->custom()->transformation(
                    $this->buildQueryStringTransformation()
                ),
                $this->refinery->always(null)
            ])
        );

        $response = array_values(
            array_reduce(
                array_merge(
                    $this->endpoint_configurator->getAdditionalAnswerElements(
                        $this->current_user,
                        $autocomplete_query
                    ),
                    $this->profile_data_repository->searchUsers(
                        $this->settings_data_repository,
                        $this->field_configuration_repository,
                        $autocomplete_query
                    )
                ),
                static function (array $c, AutocompleteItem $v): array {
                    $tag_array = $v->getTagArray();
                    $c[$tag_array['display']] = $tag_array;
                    return $c;
                },
                []
            )
        );

        usort(
            $response,
            fn(array $a, array $b): int => $a['display'] <=> $b['display']
        );

        return json_encode($response);
    }

    private function buildQueryStringTransformation(): \Closure
    {
        return function ($parameter): AutocompleteQuery {
            return new AutocompleteQuery(
                $this->getSuggestionsStartAfter(),
                $this->refinery->kindlyTo()->string()->transform($parameter)
            );
        };
    }
}
