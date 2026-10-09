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

class ilPCLegacyAnswerFormText extends ilPageContent
{
    private const string ELEMENT_TAG = 'LegacyAnswerFormText';
    private const string TEXT_ATTRIBUTE = 'Text';
    private const string TEXT_PLACEHOLDER = '\[\[\[LEGACY_ANSWER_FORM_TEXT_((?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?)\]\]\]';

    public function init(): void
    {
        $this->setType('laft');
    }

    #[\Override]
    public function modifyPageContentPostXsl(
        string $output,
        string $mode,
        bool $abstract_only = false
    ): string {
        if ($this->pg_obj::class !== QstsQuestionPage::class) {
            return $output;
        }

        return mb_ereg_replace_callback(
            self::TEXT_PLACEHOLDER,
            static fn(array $matches): string => \ilRTE::_replaceMediaObjectImageSrc(
                base64_decode($matches[1])
            ),
            $output
        );
    }

    public function create(
        string $legacy_answer_form_text
    ): void {
        $this->createInitialChildNode(
            $this->hier_id,
            '',
            self::ELEMENT_TAG,
            [self::TEXT_ATTRIBUTE => $legacy_answer_form_text]
        );
    }
}
