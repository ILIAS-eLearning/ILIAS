<?php

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

/**
 * Action that can be performed on a user
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserAction
{
    protected string $text = "";
    protected string $href = "";
    /**
     * @var array<string,string>
     */
    protected array $data = [];
    protected string $type = "";

    public function setText(string $a_val): void
    {
        $this->text = $a_val;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setHref(string $a_val): void
    {
        $this->href = $a_val;
    }

    public function getHref(): string
    {
        return $this->href;
    }

    public function setType(string $a_val): void
    {
        $this->type = $a_val;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param array<string,string> $a_val array of key => value pairs which will be transformed to data-<key>="value" attributes of link)
     */
    public function setData(array $a_val): void
    {
        $this->data = $a_val;
    }

    /**
     * @return array array of key => value pairs which will be transformed to data-<key>="value" attributes of link
     */
    public function getData(): array
    {
        return $this->data;
    }
}
