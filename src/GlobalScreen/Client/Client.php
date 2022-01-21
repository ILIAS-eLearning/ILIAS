<?php

namespace ILIAS\GlobalScreen\Client;

use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class Client
 * @package ILIAS\GlobalScreen\Client
 */
class Client
{
    private ClientSettings $settings;
    
    /**
     * Client constructor.
     * @param ClientSettings $settings
     */
    public function __construct(ClientSettings $settings)
    {
        $this->settings = $settings;
    }
    
    public function init(MetaContent $content) : void
    {
        $content->addJs("./src/GlobalScreen/Client/dist/GS.js", true, 1);
        $init_script = "il.GS.Client.init('" . json_encode($this->settings) . "');";
        $content->addOnloadCode($init_script, 1);
    }
}
