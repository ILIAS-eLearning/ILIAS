<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

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
     * @param $errors ilMailError[]
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
                0 === count($error->getPlaceholderValues())
            ) {
                $errorsToDisplay[] = $translation;
            } else {
                $escapedPlaceholderValues = array_map(static function (string $address) : string {
                    return ilLegacyFormElementsUtil::prepareFormOutput($address);
                }, $error->getPlaceholderValues());

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
