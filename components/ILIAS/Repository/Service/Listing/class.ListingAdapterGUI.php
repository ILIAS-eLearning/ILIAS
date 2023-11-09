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

declare(strict_types=1);

namespace ILIAS\Repository\Listing;

use ILIAS\UI\Component\Listing\Listing;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ListingAdapterGUI
{
    protected \ILIAS\DI\UIServices $ui;
    protected array $nodes = [];
    protected array $childs = [];
    protected array $nr = [];
    protected bool $auto_numbers = false;

    public function __construct(
    ) {
        global $DIC;
        $this->ui = $DIC->ui();
    }

    public function node(
        \ILIAS\UI\Component\Component $component,
        string $a_id,
        string $a_parent = "0"
    ): self {
        $this->nodes[$a_id] = $component;
        $this->childs[$a_parent][] = $a_id;
        return $this;
    }

    public function autoNumbers(bool $auto): self
    {
        $this->auto_numbers = $auto;
        return $this;
    }

    public function render(): string
    {
        $depth = 1;
        $nr = [];
        if (isset($this->childs[0]) && count($this->childs[0]) > 0) {
            $items = [];
            foreach ($this->childs[0] as $child) {
                $items[] = $this->renderNode($child, $depth, $nr);
            }
            $listing = $this->ui->factory()->listing()->unordered($items);
            return $this->ui->renderer()->render($listing);
        }
        return "";
    }

    public function getNumbers(): array
    {
        return $this->nr;
    }

    public function renderNode(
        string $a_id,
        int $depth,
        array &$nr
    ): string {
        if (!isset($nr[$depth])) {
            $nr[$depth] = 1;
        } else {
            $nr[$depth]++;
        }

        $nr_str = $sep = "";
        if ($this->auto_numbers) {
            for ($i = 1; $i <= $depth; $i++) {
                $nr_str .= $sep . $nr[$i];
                $sep = ".";
            }
        }
        $html = $nr_str . " " . $this->ui->renderer()->render($this->nodes[$a_id]);
        $this->nr[$a_id] = $nr_str;

        if (isset($this->childs[$a_id]) && count($this->childs[$a_id]) > 0) {
            $childs = [];
            foreach ($this->childs[$a_id] as $child) {
                $childs[] = $this->renderNode($child, $depth + 1, $nr);
            }
            $html .= $this->ui->renderer()->render(
                $this->ui->factory()->listing()->unordered($childs)
            );
        }
        unset($nr[$depth + 1]);

        return $html;
    }
}
