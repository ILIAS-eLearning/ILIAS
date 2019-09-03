<?php

namespace ILIAS\GlobalScreen\Client;

use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;

/**
 * Class Client
 *
 * @package ILIAS\GlobalScreen\Client
 */
class Client {

	/**
	 * @var ClientSettings
	 */
	private $settings;


	/**
	 * Client constructor.
	 *
	 * @param ClientSettings $settings
	 */
	public function __construct(ClientSettings $settings) {
		$this->settings = $settings;
	}


	/**
	 * @param MetaContent $content
	 */
	public function init(MetaContent $content) {
		$content->addJs("./src/GlobalScreen/Client/dist/GS.js", true, 1);
		$init_script = "il.GS.Client.init('" . json_encode($this->settings) . "');";
		$content->addOnloadCode($init_script, 1);
	}


    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return "LOREM";
    }
}