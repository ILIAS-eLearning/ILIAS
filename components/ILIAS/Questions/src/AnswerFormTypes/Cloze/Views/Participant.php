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

use ILIAS\Questions\Attempt\Attempt;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\AnswerForm\Views\Participant as ParticipantViewInterface;
use ILIAS\Questions\Response\Response;
use ILIAS\UICore\GlobalTemplate;
use Mustache\Engine as MustacheEngine;

class Participant implements ParticipantViewInterface
{
    public function __construct(
        private readonly GlobalTemplate $global_tpl,
        private readonly MustacheEngine $mustache_engine
    ) {
    }

    #[\Override]
    public function show(
        Properties $properties,
        ?Attempt $attempt_data
    ): string {
        $this->global_tpl->addJavaScript('assets/js/ParticipantViewLongMenu.js');
        return $this->mustache_engine->render(
            $properties->getClozeTextForPresentation(),
            $properties->getGaps()->getPlaceholderArrayForParticipantView($attempt_data)
        );
    }

    #[\Override]
    public function retrieveResponse(): Response
    {

    }
}
