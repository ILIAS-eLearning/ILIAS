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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Views;

use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\AnswerForm\Views\Participant as ParticipantViewInterface;
use ILIAS\Questions\AnswerFormTypes\Cloze\Response\Factory as ResponseFactory;
use ILIAS\Questions\Attempt\AdditionalAttemptData;
use ILIAS\Questions\Presentation\Definitions\ViewMode;
use ILIAS\UICore\GlobalTemplate;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use Mustache\Engine as MustacheEngine;

class Participant implements ParticipantViewInterface
{
    public function __construct(
        private readonly GlobalTemplate $global_tpl,
        private readonly MustacheEngine $mustache_engine,
        private readonly ResponseFactory $response_factory
    ) {
    }

    #[\Override]
    public function show(
        Language $lng,
        Properties $properties,
        ?AdditionalAttemptData $additional_attempt_data,
        ?Response $response_data,
        ViewMode $view_mode
    ): string {
        $this->global_tpl->addJavaScript('assets/js/ParticipantViewLongMenu.js');
        return $this->mustache_engine->render(
            $properties->getClozeTextForPresentation(),
            $properties->getGaps()->getPlaceholderArray(
                $lng,
                $view_mode,
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
