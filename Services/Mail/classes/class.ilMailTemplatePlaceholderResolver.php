<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplaceProcessor
 */
class ilMailTemplatePlaceholderResolver
{
    /**
     * @var ilMailTemplateContext
     */
    protected $context;

    /**
     * @var string
     */
    protected $message;

    /**
     * ilMailTemplateProcessor constructor.
     * @param ilMailTemplateContext $context
     * @param string                $a_message
     */
    public function __construct(ilMailTemplateContext $context, $a_message)
    {
        $this->context = $context;
        $this->message = $a_message;
    }

    /**
     * @param ilObjUser|null $user
     * @param array          $a_context_params
     * @param $replace_empty boolean
     * @return string
     *
     */
    public function resolve(ilObjUser $user = null, $a_context_params = array(), $replace_empty = true)
    {
        $message = $this->message;

        foreach ($this->context->getPlaceholders() as $key => $ph_definition) {
            $result  = $this->context->resolvePlaceholder($key, $a_context_params, $user);
            if (!$replace_empty && strlen($result) == 0) {
                continue;
            }

            $startTag = '\[IF_' . strtoupper($key) . '\]';
            $endTag   = '\[\/IF_' . strtoupper($key) . '\]';

            if (strlen($result) > 0) {
                $message = str_replace('[' . $ph_definition['placeholder'] . ']', $result, $message);

                if (array_key_exists('supportsCondition', $ph_definition) && $ph_definition['supportsCondition']) {
                    $message = preg_replace('/' . $startTag . '(.*?)' . $endTag . '/imsU', '$1', $message);
                }
            } else {
                $message = preg_replace('/[[:space:]]{1,1}\[' . $ph_definition['placeholder'] . '\][[:space:]]{1,1}/ims', ' ', $message);
                $message = preg_replace('/\[' . $ph_definition['placeholder'] . '\]/ims', '', $message);

                if (array_key_exists('supportsCondition', $ph_definition) && $ph_definition['supportsCondition']) {
                    $message = preg_replace('/' . $startTag . '.*?' . $endTag . '/imsU', '', $message);
                }
            }
        }

        return $message;
    }
}
