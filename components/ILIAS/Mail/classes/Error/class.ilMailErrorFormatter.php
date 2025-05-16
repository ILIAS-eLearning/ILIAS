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

class ilMailErrorFormatter
{
    public function __construct(protected ilLanguage $lng)
    {
    }

    /**
     * Formats an error string based on the passed list of errors. If the list contains > 1 elements, the 1st error
     * will be used as a headline for the list of errors.
     * @param list<ilMailError> $errors
     */
    public function format(array $errors): string
    {
        if ([] === $errors) {
            return '';
        }

        $errors_to_display = [];
        foreach ($errors as $error) {
            $translation = $this->lng->txt($error->getLanguageVariable());
            if ($translation === '-' . $error->getLanguageVariable() . '-') {
                $translation = $error->getLanguageVariable();
            }

            if ($translation === $error->getLanguageVariable() || [] === $error->getPlaceHolderValues()) {
                $errors_to_display[] = $translation;
            } else {
                $escaped_placeholder_values = array_map(static function (string $value): string {
                    return ilLegacyFormElementsUtil::prepareFormOutput($value);
                }, $error->getPlaceHolderValues());

                array_unshift($escaped_placeholder_values, $translation);
                $errors_to_display[] = sprintf(...$escaped_placeholder_values);
            }
        }

        $tpl = new ilTemplate(
            'tpl.mail_new_submission_errors.html',
            true,
            true,
            'components/ILIAS/Mail'
        );
        if (count($errors_to_display) === 1) {
            $tpl->setCurrentBlock('single_error');
            $tpl->setVariable('SINGLE_ERROR', current($errors_to_display));
        } else {
            $first_error = array_shift($errors_to_display);
            foreach ($errors_to_display as $error) {
                $tpl->setCurrentBlock('error_loop');
                $tpl->setVariable('ERROR', $error);
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock('multiple_errors');
            $tpl->setVariable('FIRST_ERROR', $first_error);
        }
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }
}
