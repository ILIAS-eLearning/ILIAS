<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Glyph;

use ILIAS\UI\Element as E;

class Glyph implements \ILIAS\UI\Element\Glyph {

	/**
	 * @var E\GlyphType
	 */
	private $type;
	/**
	 * @var E\Counter|null
	 */
	private $status_counter = null;
	/**
	 * @var E\Counter|null
	 */
	private $novelty_counter = null;


	/**
	 * GlyphImpl constructor.
	 * @param E\GlyphType $type
	 * @param E\Counter|null $status_counter
	 * @param E\Counter|null $novelty_counter
	 */
	public function __construct(E\GlyphType $type, E\Counter $status_counter = null, E\Counter $novelty_counter = null) {
		$this->type = $type;
		assert('is_null($status_counter) or $status_counter->type() instanceof \\ILIAS\\UI\\Element\\StatusCounterType');
		assert('is_null($novelty_counter) or $novelty_counter->type() instanceof \\ILIAS\\UI\\Element\\NoveltyCounterType');
		$this->status_counter = $status_counter;
		$this->novelty_counter = $novelty_counter;
	}


	/**
	 * @param E\Counter $counter
	 * @return GlyphImpl
	 */
	public function addCounter(E\Counter $counter) {
		$sc = $this->status_counter;
		$nc = $this->novelty_counter;

		$t = $counter->type();
		if ($t instanceof E\StatusCounterType) {
			$sc = $counter;
		} else {
			if ($t instanceof E\NoveltyCounterType) {
				$nc = $counter;
			} else {
				assert(false, "Type of counter unknown: " . get_class($t));
			}
		}

		return new GlyphImpl($this->type(), $sc, $nc);
	}


	/**
	 * @return E\GlyphType
	 */
	public function type() {
		return $this->type;
	}


	/**
	 * @return E\Counter[]
	 */
	public function counters() {
		$arr = array();
		if ($this->status_counter !== null) {
			$arr[] = $this->status_counter;
		}
		if ($this->novelty_counter !== null) {
			$arr[] = $this->novelty_counter;
		}
		return $arr;
	}


	/**
	 * @return string
	 */
	public function to_html_string() {
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
	}
}
