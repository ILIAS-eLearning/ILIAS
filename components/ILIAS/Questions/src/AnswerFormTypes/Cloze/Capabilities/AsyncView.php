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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Capabilities;

use ILIAS\Questions\AnswerForm\Capabilities\AsyncView\AsyncView as AsyncViewInterface;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\AnswerFormTypes\Cloze\Response\Factory as ResponseFactory;
use ILIAS\Questions\Attempt\AdditionalAttemptData;
use ILIAS\Questions\Presentation\Definitions\ViewConfiguration;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use Mustache\Engine as MustacheEngine;

class AsyncView extends AsyncViewInterface
{
    public function __construct(
        UIRenderer $ui_renderer,
        private readonly MustacheEngine $mustache_engine,
        private readonly ResponseFactory $response_factory
    ) {
        parent::__construct($ui_renderer);
    }

    #[\Override]
    public function getJavascriptFiles(): array
    {
        return [];
    }

    #[\Override]
    public function getAnswerFormPresentation(
        Language $lng,
        UIFactory $ui_factory,
        ViewConfiguration $view_configuration,
        Properties $properties,
        ?AdditionalAttemptData $additional_attempt_data,
        ?Response $response_data
    ): string {
        return $this->mustache_engine->render(
            $properties->getClozeTextForPresentation(),
            $properties->getGaps()->getPlaceholderArray(
                $lng,
                $view_configuration,
                $additional_attempt_data,
                $response_data
            )
        );
    }

    #[\Override]
    public function retrieveResponse(
        Uuid $response_id,
        Properties $properties,
        RequestWrapper $post_wrapper
    ): Response {
        return $this->response_factory->fromPost(
            $response_id,
            $properties,
            $post_wrapper
        );
    }
}
