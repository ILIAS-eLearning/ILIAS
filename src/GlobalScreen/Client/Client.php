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
namespace ILIAS\GlobalScreen\Client;

use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;

/**
 * Class Client
 * @package ILIAS\GlobalScreen\Client
 */
class Client
{
    /**
     * @var \ILIAS\GlobalScreen\Client\ClientSettings
     */
    private $settings;

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
