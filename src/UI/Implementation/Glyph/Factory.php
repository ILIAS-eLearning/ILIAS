<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Glyph;
use ILIAS\UI\Element as E;

class Factory implements \ILIAS\UI\Factory\Glyph {
    /**
     * @inheritdoc
     */
    public function up() {
        return new Glyph(new E\UpGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function down() {
        return new Glyph(new E\DownGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function add() {
        return new Glyph(new E\AddGlyphType());
    }
    
    /**
     * @inheritdoc
     */
    public function remove() {
        return new Glyph(new E\RemoveGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function previous() {
        return new Glyph(new E\PreviousGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function next() {
        return new Glyph(new E\NextGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function calendar() {
        return new Glyph(new E\CalendarGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function close() {
        return new Glyph(new E\CloseGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function attachment() {
        return new Glyph(new E\AttachmentGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function caret() {
        return new Glyph(new E\CaretGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function drag() {
        return new Glyph(new E\DragGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function search() {
        return new Glyph(new E\SearchGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function filter() {
        return new Glyph(new E\FilterGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function info() {
        return new Glyph(new E\InfoGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function envelope() {
        return new Glyph(new E\EnvelopeGlyphType());
    }
}

//Force autoloading of Counter.php for counter types.
interface Force_Glyph extends \ILIAS\UI\Element\Glyph {}
