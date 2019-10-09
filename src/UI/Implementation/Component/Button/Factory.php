<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component\Button as B;
use ILIAS\UI\Component\Signal;

class Factory implements B\Factory
{
    /**
     * @inheritdoc
     */
    public function standard($label, $action)
    {
        return new Standard($label, $action);
    }

    /**
     * @inheritdoc
     */
    public function primary($label, $action)
    {
        return new Primary($label, $action);
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        return new Close();
    }

    /**
     * @inheritdoc
     */
    public function tag($label, $action)
    {
        return new Tag($label, $action);
    }

    /**
     * @inheritdoc
     */
    public function shy($label, $action)
    {
        return new Shy($label, $action);
    }

    /**
     * @inheritdoc
     */
    public function month($default)
    {
        return new Month($default);
    }

    /**
     * @inheritdoc
     */
    public function bulky($icon_or_glyph, $label, $action)
    {
        return new Bulky($icon_or_glyph, $label, $action);
    }

    /**
     * @inheritdoc
     */
    public function toggle(string $label, $on_action, $off_action, bool $is_on = false, Signal $click_signal = null) : \ILIAS\UI\Component\Button\Toggle
    {
        return new Toggle($label, $on_action, $off_action, $is_on, $click_signal);
    }
}
