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
 ********************************************************************
 */

declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

class ilTestPlayerToolProvider extends AbstractDynamicToolProvider
{
    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main();
    }

    /**
     * @return \ILIAS\GlobalScreen\Scope\Tool\Factory\Tool[]
     */
    public function getToolsForContextStack(CalledContexts $called_contexts): array
    {
        $additionalData = $called_contexts->current()->getAdditionalData();
        $has_question_list = $additionalData->exists(ilTestPlayerLayoutProvider::TEST_PLAYER_QUESTIONLIST);

        if (!$has_question_list) {
            return [];
        }

        $ui = $this->dic->ui();
        $lng = $this->dic->language();

        return [
            $this->factory->tool(
                $this->identification_provider->contextAwareIdentifier('tst_qst_list')
            )->withSymbol($ui->factory()->symbol()->icon()->standard('tst', $lng->txt('more')))
            ->withTitle($lng->txt('mainbar_button_label_questionlist'))
            ->withContent(
                $ui->factory()->legacy(
                    $ui->renderer()->render(
                        $called_contexts->current()->getAdditionalData()->get(ilTestPlayerLayoutProvider::TEST_PLAYER_QUESTIONLIST)
                    )
                )
            )
        ];
    }
}
