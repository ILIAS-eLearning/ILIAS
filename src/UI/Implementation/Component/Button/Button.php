<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Glyph\Glyph;

/**
 * This implements commonalities between standard and primary buttons. 
 */
abstract class Button implements C\Button\Button {
	use ComponentHelper;

	/**
	 * @var string|null
	 */
	protected $label;

	/**
	 * @var Glyph
	 */
	protected $glyph;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var bool
     */
    protected $active = true;


	public function __construct($label_or_glyph, $action) {
        $this->checkArg
            ( "label_or_glyph"
            , is_string($label_or_glyph) || $label_or_glyph instanceof Glyph
            , $this->wrongTypeMessage("string or Glyph", $label_or_glyph)
            );
        $this->checkStringArg("action", $action);
        if (is_string($label_or_glyph)) {
			$this->label = $label_or_glyph;
            $this->glyph = null;
		}
		else {
			$this->label = null;
            $this->glyph = $label_or_glyph;
		}
        $this->action = $action;
	} 

	/**
	 * @inheritdocs 
	 */
	public function getLabel() {
        return $this->label;
	}

	/**
	 * @inheritdocs 
	 */
	public function withLabel($label) {
        $this->checkStringArg("label", $label);
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
	}

	/**
	 * @inheritdocs 
	 */
	public function getGlyph() {
        return $this->glyph;
	}

	/**
	 * @inheritdocs 
	 */
	public function withGlyph(Glyph $glyph) {
        $clone = clone $this;
        $clone->glyph = $glyph;
        return $clone;
	}

	/**
	 */
	public function getAction() {
        return $this->action;
	}

	/**
	 * @inheritdocs 
	 */
	public function isActive() {
        return $this->active;
	}

	/**
	 * @inheritdocs 
	 */
	public function withUnavailableAction() {
        $clone = clone $this;
        $clone->active = false;
        return $clone;
	}
}
