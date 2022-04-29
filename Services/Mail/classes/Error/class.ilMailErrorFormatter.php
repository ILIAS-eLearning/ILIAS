<?php declare(strict_types=1);

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

/**
 * Class ilMailErrorFormatter
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailErrorFormatter
{
    protected ilLanguage $lng;

    public function __construct(ilLanguage $lng)
    {
        $this->lng = $lng;
    }

    /**
     * Formats an error string based on the passed list of errors. If the list contains > 1 elements, the 1st error
     * will be used as a headline for the list of errors.
     * @param ilMailError[] $errors
     */
    public function format(array $errors) : string
    {
        if (0 === count($errors)) {
            return '';
        }

        $errorsToDisplay = [];
        foreach ($errors as $error) {
            $translation = $this->lng->txt($error->getLanguageVariable());
            if ($translation === '-' . $error->getLanguageVariable() . '-') {
                $translation = $error->getLanguageVariable();
            }

            if (
                $translation === $error->getLanguageVariable() ||
                0 === count($error->getPlaceHolderValues())
            ) {
                $errorsToDisplay[] = $translation;
            } else {
                $escapedPlaceholderValues = array_map(static function (string $address) : string {
                    return ilLegacyFormElementsUtil::prepareFormOutput($address);
                }, $error->getPlaceHolderValues());

                array_unshift($escapedPlaceholderValues, $translation);
                $errorsToDisplay[] = sprintf(...$escapedPlaceholderValues);
            }
        }

        $tpl = new ilTemplate(
            'tpl.mail_new_submission_errors.html',
            true,
            true,
            'Services/Mail'
        );
        if (1 === count($errorsToDisplay)) {
            $tpl->setCurrentBlock('single_error');
            $tpl->setVariable('SINGLE_ERROR', current($errorsToDisplay));
        } else {
            $firstError = array_shift($errorsToDisplay);

            foreach ($errorsToDisplay as $error) {
                $tpl->setCurrentBlock('error_loop');
                $tpl->setVariable('ERROR', $error);
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock('multiple_errors');
            $tpl->setVariable('FIRST_ERROR', $firstError);
        }
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }
}
