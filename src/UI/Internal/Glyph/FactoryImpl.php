<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Internal\Glyph;
use ILIAS\UI\Element as E;

class FactoryImpl implements \ILIAS\UI\Factory\Glyph {
    /**
     * @inheritdoc
     */
    public function up() {
        return new GlyphImpl(new E\UpGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function down() {
        return new GlyphImpl(new E\DownGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function add() {
        return new GlyphImpl(new E\AddGlyphType());
    }
    
    /**
     * @inheritdoc
     */
    public function remove() {
        return new GlyphImpl(new E\RemoveGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function previous() {
        return new GlyphImpl(new E\PreviousGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function next() {
        return new GlyphImpl(new E\NextGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function calendar() {
        return new GlyphImpl(new E\CalendarGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function close() {
        return new GlyphImpl(new E\CloseGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function attachment() {
        return new GlyphImpl(new E\AttachmentGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function caret() {
        return new GlyphImpl(new E\CaretGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function drag() {
        return new GlyphImpl(new E\DragGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function search() {
        return new GlyphImpl(new E\SearchGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function filter() {
        return new GlyphImpl(new E\FilterGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function info() {
        return new GlyphImpl(new E\InfoGlyphType());
    }

    /**
     * @inheritdoc
     */
    public function envelope() {
        return new GlyphImpl(new E\EnvelopeGlyphType());
    }
}

//Force autoloading of Counter.php for counter types.
interface Force_Glyph extends \ILIAS\UI\Element\Glyph {}
