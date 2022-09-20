<?php

declare(strict_types=1);

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

final class ilSamlIdpXmlMetadataErrorFormatter
{
    /**
     * Format an error as a string.
     * This function formats the given LibXMLError object as a string.
     * @param LibXMLError $error The LibXMLError which should be formatted.
     * @return string A string representing the given LibXMLError.
     */
    private function formatError(LibXMLError $error): string
    {
        return implode(',', [
            'level=' . $error->level,
            'code=' . $error->code,
            'line=' . $error->line,
            'col=' . $error->column,
            'msg=' . trim($error->message)
        ]);
    }

    /**
     * Format a list of errors as a string.
     *
     * This function takes an argument list of LibXMLError objects and creates a string with all the errors.
     * Each error will be separated by a newline, and the string will end with a newline-character.
     *
     * @param LibXMLError ...$errors A list of error arguments.
     * @return string A string representing the errors. An empty string will be returned if there were no errors in the argument list.
     */
    public function formatErrors(LibXMLError ...$errors): string
    {
        $text = '';
        foreach ($errors as $error) {
            $text .= $this->formatError($error) . "\n";
        }

        return $text;
    }
}
