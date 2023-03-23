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

/**
 * Class ilMailTemplatePlaceholderResolver
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailTemplatePlaceholderResolver
{
    public function __construct(protected ilMailTemplateContext $context, protected string $message)
    {
    }

    public function resolve(
        ilObjUser $user = null,
        array $contextParameters = [],
        bool $replaceEmptyPlaceholders = true
    ): string {
        $message = $this->message;

        foreach ($this->context->getPlaceholders() as $key => $ph_definition) {
            $result = $this->context->resolvePlaceholder($key, $contextParameters, $user);
            if (!$replaceEmptyPlaceholders && $result === '') {
                continue;
            }

            $startTag = '\[IF_' . strtoupper($key) . '\]';
            $endTag = '\[\/IF_' . strtoupper($key) . '\]';

            if ($result !== '') {
                $message = str_replace('[' . $ph_definition['placeholder'] . ']', $result, $message);

                if (array_key_exists('supportsCondition', $ph_definition) &&
                    $ph_definition['supportsCondition']
                ) {
                    $message = preg_replace(
                        '/' . $startTag . '(.*?)' . $endTag . '/imsU',
                        '$1',
                        $message
                    );
                }
            } else {
                $message = preg_replace(
                    '/[[:space:]]\[' . $ph_definition['placeholder'] . '\][[:space:]]/ims',
                    ' ',
                    $message
                );
                $message = preg_replace(
                    '/\[' . $ph_definition['placeholder'] . '\]/ims',
                    '',
                    $message
                );

                if (array_key_exists('supportsCondition', $ph_definition) &&
                    $ph_definition['supportsCondition']
                ) {
                    $message = preg_replace(
                        '/' . $startTag . '.*?' . $endTag . '/imsU',
                        '',
                        $message
                    );
                }
            }
        }

        return $message;
    }
}
