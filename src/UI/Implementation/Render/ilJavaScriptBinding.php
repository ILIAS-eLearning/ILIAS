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
 
namespace ILIAS\UI\Implementation\Render;

use ilGlobalTemplateInterface;

/**
 * Wraps global ilTemplate to provide JavaScriptBinding.
 */
class ilJavaScriptBinding implements JavaScriptBinding
{
    public const PREFIX = "il_ui_fw_";

    private ilGlobalTemplateInterface $global_tpl;

    /**
     * Cache for all registered JS code
     */
    protected array $code = array();

    public function __construct(ilGlobalTemplateInterface $global_tpl)
    {
        $this->global_tpl = $global_tpl;
    }

    /**
     * @inheritdoc
     */
    public function createId() : string
    {
        return str_replace(".", "_", uniqid(self::PREFIX, true));
    }

    /**
     * @inheritdoc
     */
    public function addOnLoadCode(string $code) : void
    {
        $this->global_tpl->addOnLoadCode($code, 1);
        $this->code[] = $code;
    }

    /**
     * @inheritdoc
     */
    public function getOnLoadCodeAsync() : string
    {
        if (!count($this->code)) {
            return '';
        }
        $js_out = '<script data-replace-marker="script">' . implode("\n", $this->code) . '</script>';
        $this->code = [];
        return $js_out;
    }
}
