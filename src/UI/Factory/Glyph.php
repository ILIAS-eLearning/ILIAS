<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Factory;

/**
 * This is how a factory for glyphs looks like.
 */
interface Glyph {
    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function up();

    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function down();

    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function add();
    
    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function remove();
    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function previous();

    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function next();

    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function calendar();

    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function close();

    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function attachment();

    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function caret();

    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function drag();

    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function search();

    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function filter();

    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function info();
}