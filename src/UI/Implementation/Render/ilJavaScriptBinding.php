<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

/**
 * Wraps global ilTemplate to provide JavaScriptBinding.
 */
class ilJavaScriptBinding implements JavaScriptBinding
{
    public const PREFIX = "il_ui_fw_";

    /**
     * @var \ilGlobalTemplate
     */
    private $global_tpl;

    /**
     * Cache for all registered JS code
     *
     * @var array
     */
    protected $code = array();

    public function __construct(\ilGlobalTemplateInterface $global_tpl)
    {
        $this->global_tpl = $global_tpl;
    }

    /**
     * @inheritdoc
     */
    public function createId()
    {
        return str_replace(".", "_", uniqid(self::PREFIX, true));
    }

    /**
     * @inheritdoc
     */
    public function addOnLoadCode($code)
    {
        $this->global_tpl->addOnLoadCode($code, 1);
        $this->code[] = $code;
    }

    /**
     * @inheritdoc
     */
    public function getOnLoadCodeAsync()
    {
        if (!count($this->code)) {
            return '';
        }
        $js_out = '<script data-replace-marker="script">' . implode("\n", $this->code) . '</script>';
        $this->code = [];
        return $js_out;
    }
}
