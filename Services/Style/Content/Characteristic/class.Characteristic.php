<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Style\Content;

/**
 * Characteristic (Class) of style
 * @author Alexander Killing <killing@leifos.de>
 */
class Characteristic
{
    protected int$style_id;
    protected string $type;
    protected string $characteristic;
    protected bool $hide;
    protected array $titles;        // key, is lang, value is title
    protected int $order_nr;
    protected bool $outdated;

    public function __construct(
        string $type,
        string $characteristic,
        bool $hide,
        array $titles,
        int $order_nr = 0,
        bool $outdated = false
    ) {
        $this->type = $type;
        $this->characteristic = $characteristic;
        $this->hide = $hide;
        $this->titles = $titles;
        $this->order_nr = $order_nr;
        $this->outdated = $outdated;
    }

    public function withStyleId(int $style_id) : Characteristic
    {
        $clone = clone $this;
        $clone->style_id = $style_id;
        return $clone;
    }

    public function getStyleId() : int
    {
        return $this->style_id;
    }

    public function getCharacteristic() : string
    {
        return $this->characteristic;
    }

    public function getType() : string
    {
        return $this->type;
    }

    // Is char hidden?
    public function isHidden() : bool
    {
        return $this->hide;
    }

    public function getTitles() : array
    {
        return $this->titles;
    }

    public function getOrderNr() : int
    {
        return $this->order_nr;
    }

    public function isOutdated() : bool
    {
        return $this->outdated;
    }
}
