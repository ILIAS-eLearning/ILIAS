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

namespace ILIAS\components\ResourceStorage\Container\View;

use ILIAS\UI\URLBuilderToken;
use ILIAS\components\ResourceStorage\Container\View\ActionBuilder\ExternalSingleAction;
use ILIAS\UI\Factory;
use ILIAS\Data\URI;
use ILIAS\components\ResourceStorage\URLSerializer;
use ILIAS\ResourceStorage\Services;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Implementation\Component\Table\Action\Action;
use ILIAS\components\ResourceStorage\Container\View\ActionBuilder\ActionProvider;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ActionBuilder
{
    use URLSerializer;

    /**
     * @var string
     */
    private const ACTION_UNZIP = 'unzip';
    /**
     * @var string
     */
    private const ACTION_DOWNLOAD = 'download';
    /**
     * @var string
     */
    private const ACTION_REMOVE = 'remove';
    /**
     * @var string
     */
    public const ACTION_NAMESPACE = 'rcgui';
    /**
     * @var Modal[]
     */
    private array $modals = [];
    private URLBuilder $url_builder;
    private URLBuilderToken $url_token;
    private array $single_actions;

    private array $appended_tokens = [];

    public function __construct(
        private Request $request,
        private \ilCtrlInterface $ctrl,
        private Factory $ui_factory,
        private \ilLanguage $language,
        private Services $irss,
        private ActionProvider $action_provider
    ) {
        $this->single_actions = $this->action_provider->getSingleActions($this->request);
        $this->initURIBuilder();
    }

    private function appendNamespaceToURIBuilder(string $namespace, string $param): URLBuilder
    {
        $key = $namespace . $param;
        if (isset($this->appended_tokens[$key])) {
            return $this->url_builder;
        }

        $parameters = $this->url_builder->acquireParameter(
            [$namespace],
            $param
        );

        $this->appended_tokens[$key] = $parameters[1];

        return $this->url_builder = $parameters[0];
    }

    private function initURIBuilder(): void
    {
        $uri_builder = new URLBuilder(
            new URI(
                ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                    \ilContainerResourceGUI::class,
                    \ilContainerResourceGUI::CMD_INDEX
                )
            )
        );

        $parameters = $uri_builder->acquireParameter(
            [self::ACTION_NAMESPACE],
            \ilContainerResourceGUI::P_PATH
        );

        $this->url_builder = $parameters[0];
        $this->url_token = $parameters[1];
    }

    public function getUrlBuilder(): URLBuilder
    {
        return $this->url_builder;
    }

    public function getUrlToken(): URLBuilderToken
    {
        return $this->url_token;
    }

    /**
     * @return Action[]
     */
    public function getActions(): array
    {
        $actions = [];

        foreach ($this->single_actions as $key => $single_action) {
            if ($single_action instanceof ExternalSingleAction) {
                $this->url_builder = $this->appendNamespaceToURIBuilder(
                    $single_action->getParameterNamespace(),
                    $single_action->getPathParameterName()
                );

                $token = $this->appended_tokens[$single_action->getParameterNamespace(
                ) . $single_action->getPathParameterName()] ?? $this->url_token;
            } else {
                $token = $this->url_token;
            }

            if ($single_action->isBulk()) {
                $action = $this->ui_factory->table()->action()->standard(
                    $single_action->getLabel(),
                    $this->url_builder->withURI($single_action->getAction()),
                    $token
                );
            } else {
                $action = $this->ui_factory->table()->action()->single(
                    $single_action->getLabel(),
                    $this->url_builder->withURI($single_action->getAction()),
                    $token
                );
            }
            $actions[$key] = $action->withAsync($single_action->isAsync());
        }

        return $actions;
    }

    public function getActionProvider(): ActionProvider
    {
        return $this->action_provider;
    }

}
