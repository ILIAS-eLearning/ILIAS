<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Generic slider.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/UICore/classes/class.ilTemplate.php");

abstract class catSliderGUI {
	protected $tpl_file = "tpl.cat_slider.html";
	protected $tpl_module = "Services/CaTUIComponents";
	protected $slider_id = "idSlider";
	protected $slides_class = "Slides";
	protected $button_disabled_class = "Disabled";
	protected $button_down_class = "Down";
	protected $button_over_class = "Over";
	protected $easing = "swing";
	protected $item_selector = "";
	protected $item_sliding_class = "Sliding";
	protected $next_button_class = "Next";
	protected $prev_button_class = "Prev";
	protected $amount_to_scroll = 1;
	protected $speed = 300;

	public function __construct() {
		global $tpl;
		iljQueryUtil::initjQuery();
		$tpl->addJavaScript("./Services/CaTUIComponents/js/jquery.slider.js");
	}

	public function setTemplate($a_filename, $a_module) {
		$this->tpl_file = $a_filename;
		$this->tpl_module = $a_module;
		return $this;
	}

	// Set id of the slider object
	public function setSliderId($a_slider_id) {
		$this->slider_id = $a_slider_id;
		return $this;
	}
	
	// Set id of wrapper around single slides
	public function setSlidesClass($a_slides_class) {
		$this->slides_class = $a_slides_class;
		return $this;
	}
	
	// Class for buttons when scrolling is not possible.
	public function setButtonDisabledClass($a_button_disabled_class) {
		$this->button_disabled_class = $a_button_disabled_class;
		return $this;
	}
	
	public function setButtonDownClass($a_button_down_class) {
		$this->button_down_class = $a_button_down_class;
		return $this;
	}
	
	public function setButtonOverClass($a_button_over_class) {
		$this->button_over_class = $a_button_over_class;
		return $this;
	}
	
	// Set jquery easing function for scrolling.
	public function setEasing($a_easing) {
		$this->easing = $a_easing;
		return $this;
	}
	
	// How to select a single slide? Takes all children of wrapper around slides
	// if not set.
	public function setItemSelector($a_item_selector) {
		$this->item_selector = $a_item_selector;
		return $this;
	}
	
	// Class of items during sliding.
	public function setItemSlidingClass($a_item_sliding_class) {
		$this->item_sliding_class = $a_item_sliding_class;
		return $this;
	}
	
	public function setNextButtonClass($a_next_button_class) {
		$this->next_button_class = $a_next_button_class;
		return $this;
	}
	
	public function setPrevButtonClass($a_prev_button_class) {
		$this->prev_button_class = $a_prev_button_class;
		return $this;
	}
	
	// How many item should be scrolled per click?
	public function setAmountToScroll($a_amount_to_scroll) {
		$this->amount_to_scroll = $a_amount_to_scroll;
		return $this;
	}
	
	// How fast should the scrolling happen? (in ms!)
	public function setSpeed($a_speed) {
		$this->speed = $a_speed;
		return $this;
	}
	
	public function render() {
		// TODO: templates should in general not be instantiated in the render procedure
		// directly, since this makes it impossible to reuse the render code with another
		// template.
		$tpl = new ilTemplate($this->tpl_file, false, false, $this->tpl_module);
		
		// JS initializer
		$js_tpl = new ilTemplate("tpl.cat_slider_js.html", true, true, "Services/CaTUIComponents");
		$js_tpl->setVariable("SLIDER_ID", $this->slider_id);
		$js_tpl->setVariable("BTN_DISABLED_CLASS", $this->button_disabled_class);
        $js_tpl->setVariable("BTN_DOWN_CLASS", $this->button_down_class);
        $js_tpl->setVariable("BTN_OVER_CLASS", $this->button_over_class);
        $js_tpl->setVariable("EASING", $this->easing);
        $js_tpl->setVariable("ITEM_SELECTOR", $this->item_selector);
        $js_tpl->setVariable("ITEM_SLIDING_CLASS", $this->item_sliding_class);
        $js_tpl->setVariable("BTN_NEXT_CLASS", $this->next_button_class);
        $js_tpl->setVariable("BTN_PREV_CLASS", $this->prev_button_class);
        $js_tpl->setVariable("SCROLL", $this->amount_to_scroll);
        $js_tpl->setVariable("SPEED", $this->speed);
        $js_tpl->setVariable("SLIDES_CLASS", $this->slides_class);

		$tpl->setVariable("JS", $js_tpl->get());
		$tpl->setVariable("SLIDER_ID", $this->slider_id);
		$tpl->setVariable("SLIDER_CLASS", $this->slider_class);
		$tpl->setVariable("SLIDES_CLASS", $this->slides_class);
		$tpl->setVariable("SLIDES", $this->renderSlides());
		
		return $tpl->get();
	}
	
	// Render all slides that should be displayed.
	abstract public function renderSlides();
}

?>