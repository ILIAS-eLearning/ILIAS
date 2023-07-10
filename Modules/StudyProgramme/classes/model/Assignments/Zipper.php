<?php

declare(strict_types=1);

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

namespace ILIAS\StudyProgramme\Assignment;

class Zipper
{
    /**
     * @var Node[]
     */
    protected array $path = [];


    public function __construct(Node $focus)
    {
        $this->path[] = $focus;
    }

    public function toChild(string $id): Zipper
    {
        $clone = clone $this;
        $clone->path[] = end($this->path)->getSubnode($id);
        return $clone;
    }

    public function isTop(): bool
    {
        return count($this->path) == 1;
    }

    public function toParent(): Zipper
    {
        $clone = clone $this;
        $last_node = array_pop($clone->path);
        $parent = array_pop($clone->path);
        $clone->path[] = $parent->withSubnode($last_node);

        return $clone;
    }

    public function toPath(array $hops): Zipper
    {
        $zipper = $this;
        foreach ($hops as $hop) {
            if ($hop != end($this->path)->getId()) {
                $zipper = $zipper->toChild($hop);
            }
        }
        return $zipper;
    }

    public function getRoot(): Node
    {
        if (count($this->path) == 1) {
            return array_pop($this->path);
        } else {
            return $this->toParent()->getRoot();
        }
    }

    public function modifyFocus(callable $f): Zipper
    {
        $clone = clone $this;
        $focus = array_pop($clone->path);
        $new_focus = $f($focus);
        $clone->path[] = $new_focus;
        return $clone;
    }

    public function modifyAll(callable $f, Zipper $zipper = null): Zipper
    {
        $zipper = $zipper ?? $this;
        $zipper = $zipper->modifyFocus($f);
        foreach (end($zipper->path)->getSubnodes() as $subnode) {
            $zipper = $zipper
                ->modifyAll($f, $zipper->toChild($subnode->getId()))
                ->toParent();
        }
        return $zipper;
    }
}
