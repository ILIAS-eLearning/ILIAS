<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Element;

/**
 * This describes how a glyph could be modified during construction of UI.
 */
interface Glyph extends \ILIAS\UI\Element {
    /**
     * Add a counter to the glyph.
     *
     * If there already is a counter of the added counter type, replace that
     * counter by the new one.
     *
     * @param   Counter $counter
     * @return  Glyph
     */
    public function addCounter(Counter $counter);

    /**
     * Get the type of the glyph.
     *
     * @return  GlyphType
     */
    public function type();

    /**
     * Get all counters attached to this glyph.
     *
     * @return  Counter[]
     */
    public function counters();
}

// Tags for the different types of counters.
class GlyphType {};
final class UpGlyphType extends GlyphType {};
final class DownGlyphType extends GlyphType {};
final class AddGlyphType extends GlyphType {};
final class RemoveGlyphType extends GlyphType {};
final class PreviousGlyphType extends GlyphType {};
final class NextGlyphType extends GlyphType {};
final class CalendarGlyphType extends GlyphType {};
final class CloseGlyphType extends GlyphType {};
final class AttachmentGlyphType extends GlyphType {};
final class CaretGlyphType extends GlyphType {};
final class DragGlyphType extends GlyphType {};
final class SearchGlyphType extends GlyphType {};
final class FilterGlyphType extends GlyphType {};
final class InfoGlyphType extends GlyphType {};
final class EnvelopeGlyphType extends GlyphType {};
