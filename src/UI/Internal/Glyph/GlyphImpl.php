<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Internal\Glyph;
use ILIAS\UI\Element as E;

class GlyphImpl implements \ILIAS\UI\Element\Glyph {
    private $type;

    private $status_counter = null;
    private $novelty_counter = null;

    public function __construct(E\GlyphType $type, E\Counter $status_counter = null, E\Counter $novelty_counter = null) {
        $this->type = $type;
        assert('is_null($status_counter) or $status_counter->type() instanceof \\ILIAS\\UI\\Element\\StatusCounterType');
        assert('is_null($novelty_counter) or $novelty_counter->type() instanceof \\ILIAS\\UI\\Element\\NoveltyCounterType');
        $this->status_counter = $status_counter;
        $this->novelty_counter = $novelty_counter;
    }

    public function addCounter(E\Counter $counter) {
        $sc = $this->status_counter;
        $nc = $this->novelty_counter;

        $t = $counter->type();
        if ($t instanceof E\StatusCounterType) {
            $sc = $counter;
        }
        else if ($t instanceof E\NoveltyCounterType) {
            $nc = $counter;
        }
        else {
            assert(false, "Type of counter unknown: ".get_class($t));
        }

        return new GlyphImpl($this->type(), $sc, $nc);
    }

    public function type() {
        return $this->type;
    }

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

    public function to_html_string() {
        throw new \Exception("Implement me!");
    }
}
