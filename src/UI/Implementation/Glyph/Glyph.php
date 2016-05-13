<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Glyph;

use ILIAS\UI\Component as C;

class Glyph implements \ILIAS\UI\Component\Glyph {
	/**
	 * @var	string
	 */
	private $type;

	/**
	 * @var	C\Counter[]
	 */
	private $counters;

	/**
	 * @param string		$type
	 * @param C\Counter[]	$counters
	 */
	public function __construct($type, array $counters) {
		$this->type = $type;
		$this->counters = $counters;
	}

	/**
	 * @inheritdoc
	 */
	public function getCounters() {
		return array_values($this->counters);
	}

	/**
	 * @inheritdoc
	 */
	public function withCounter(C\Counter $counter) {
		$counters = array();
		return new Glyph($this->getType(), $counters);
	}

	/**
	 * @inheritdoc
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @inheritdoc
	 */
	public function withType($type) {
		return $this;
	}

	/**
	 * @return string
	 */
/*	public function to_html_string() {
		$type = '';
		switch (true) {
			case ($this->type instanceof E\DragGlyphType):
				$type = 'share-alt';
				break;
			case ($this->type instanceof E\EnvelopeGlyphType):
				$type = 'envelope';
				break;
		}
		$counter_html = '';
		if ($this->counters()) {
			foreach ($this->counters() as $counter) {
				$counter_html .= $counter->to_html_string();
			}
		}

		$tpl = new \ilTemplate('./src/UI/templates/default/Glyph/tpl.glyph.html', true, false);
		$tpl->setVariable('TYPE', $type);

		return $tpl->get() . $counter_html;
	}*/
}
