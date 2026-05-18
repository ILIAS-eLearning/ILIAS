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

namespace ILIAS\Questions\Legacy\Administration;

use ILIAS\Questions\AnswerForm\Capabilities\AsyncView\Capability as AsyncView;
use ILIAS\Questions\AnswerForm\Capabilities\DefaultView\Capability as DefaultView;
use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\Capability as TextFeedback;
use ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent\Capability as SuggestedLearningContent;
use ILIAS\Questions\AnswerForm\Capabilities\MarkingAllowingPartialPoints\Capability as MarkingAllowingPartialPoints;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Button\Standard as StandardButton;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class ViewConfiguration
{
    private const array PARAMETER_NAMESPACE = ['q', 'vc'];
    private const string CONFIGURATION_TOKEN_STRING = 'c';

    private readonly URLBuilder $url_builder;
    private readonly URLBuilderToken $configuration_token;

    private readonly array $current_configuration;

    public function __construct(
        private readonly Language $lng,
        private readonly Refinery $refinery,
        private readonly UIFactory $ui_factory,
        RequestWrapper $query_wrapper,
        URLBuilder $url_builder,
        private readonly \ilToolbarGUI $toolbar
    ) {
        [
            $this->url_builder,
            $this->configuration_token
        ] = $url_builder->acquireParameter(
            self::PARAMETER_NAMESPACE,
            self::CONFIGURATION_TOKEN_STRING
        );

        $this->current_configuration = $query_wrapper->retrieve(
            $this->configuration_token->getName(),
            $this->refinery->custom()->transformation(
                function (mixed $values): array {
                    $current_configuration = [
                        DefaultView::getIdentifier()
                    ];
                    if (!is_array($values)) {
                        return $current_configuration;
                    }

                    $available_capabilities = [
                        AsyncView::getIdentifier(),
                        DefaultView::getIdentifier(),
                        TextFeedback::getIdentifier(),
                        SuggestedLearningContent::getIdentifier(),
                        MarkingAllowingPartialPoints::getIdentifier()
                    ];

                    return array_reduce(
                        $values,
                        function (array $c, string $v) use ($available_capabilities): array {
                            if ($v === AsyncView::getIdentifier()
                                || $v === DefaultView::getIdentifier()) {
                                $c = array_filter(
                                    $c,
                                    fn(string $v): bool => $v !== DefaultView::getIdentifier()
                                        && $v !== AsyncView::getIdentifier()
                                );
                            }

                            if (in_array($v, $available_capabilities)
                                && !in_array($v, $c)) {
                                $c[] = $v;
                            }

                            return $c;
                        },
                        $current_configuration
                    );
                }
            )
        );
    }

    public function getCurrentConfiguration(): array
    {
        return $this->current_configuration;
    }

    public function getURLBuilderWithPreservedConfigurationParameter(
        ?URLBuilder $url_builder = null
    ): URLBuilder {
        if ($url_builder !== null) {
            [
                $url_builder,
                $configuration_token
            ] = $url_builder->acquireParameter(
                self::PARAMETER_NAMESPACE,
                self::CONFIGURATION_TOKEN_STRING
            );
        } else {
            $url_builder = $this->url_builder;
            $configuration_token = $this->configuration_token;
        }

        return $url_builder->withParameter(
            $configuration_token,
            $this->current_configuration
        );
    }

    public function initializeToolbar(): void
    {
        $configuration_without_view = array_filter(
            $this->current_configuration,
            fn(string $v): bool => $v !== DefaultView::getIdentifier() && $v !== AsyncView::getIdentifier()
        );
        $this->toolbar->addComponent(
            $this->ui_factory->viewControl()->mode(
                [
                    $this->lng->txt('default_view') => $this->url_builder->withParameter(
                        $this->configuration_token,
                        [...$configuration_without_view, DefaultView::getIdentifier()]
                    )->buildURI()->__toString(),
                    $this->lng->txt('async_view') => $this->url_builder->withParameter(
                        $this->configuration_token,
                        [...$configuration_without_view, AsyncView::getIdentifier()]
                    )->buildURI()->__toString()
                ],
                $this->lng->txt('select_view')
            )->withActive(
                in_array(DefaultView::getIdentifier(), $this->current_configuration)
                    ? $this->lng->txt('default_view')
                    : $this->lng->txt('async_view')
            ),
        );

        $this->toolbar->addComponent(
            $this->buildToolbarButton(
                MarkingAllowingPartialPoints::getIdentifier()
            )
        );

        $this->toolbar->addComponent(
            $this->buildToolbarButton(
                TextFeedback::getIdentifier()
            )
        );

        $this->toolbar->addComponent(
            $this->buildToolbarButton(
                SuggestedLearningContent::getIdentifier()
            )
        );
    }

    private function buildToolbarButton(
        string $identifier
    ): StandardButton {
        $capability_activated = in_array($identifier, $this->current_configuration);
        $filtered_configuration = array_filter(
            $this->current_configuration,
            fn(string $v): bool => $v !== $identifier
        );

        return $this->ui_factory->button()->standard(
            $this->buildButtonLabel($identifier, $capability_activated),
            $this->url_builder->withParameter(
                $this->configuration_token,
                $capability_activated
                    ? $filtered_configuration
                    : [...$filtered_configuration, $identifier]
            )->buildURI()->__toString()
        );
    }

    private function buildButtonLabel(
        string $identifier,
        bool $enabled
    ): string {
        $identifier_lng_string = strtolower($identifier);

        return $enabled
            ? $this->lng->txt("disable_{$identifier_lng_string}")
            : $this->lng->txt("enable_{$identifier_lng_string}");
    }
}
