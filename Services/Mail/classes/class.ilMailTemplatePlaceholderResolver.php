<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplatePlaceholderResolver
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailTemplatePlaceholderResolver
{
    protected ilMailTemplateContext $context;
    protected string $message = '';

    public function __construct(ilMailTemplateContext $context, string $a_message)
    {
        $this->context = $context;
        $this->message = $a_message;
    }

    public function resolve(
        ilObjUser $user = null,
        array $contextParameters = [],
        bool $replaceEmptyPlaceholders = true
    ) : string {
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
                    '/[[:space:]]{1,1}\[' . $ph_definition['placeholder'] . '\][[:space:]]{1,1}/ims',
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
