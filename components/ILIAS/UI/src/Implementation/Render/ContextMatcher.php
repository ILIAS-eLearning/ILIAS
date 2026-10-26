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
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Render;

/**
 * Searches for patterns inside the given rendering context in order to
 * apply context rendering.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ContextMatcher
{
    /**
     * Returns true if any canonical name of $search_any is a direct ancestor of
     * the last canonical name inside the context.
     *
     * @param string[] $context_asc canonical names (first to last)
     * @param string[] $search_any  canonical names
     */
    public function isDirectDescendantOfAny(array $context_asc, array $search_any): bool
    {
        // ensure minimum context size allowing direct descendants
        $context_size = count($context_asc);
        if (2 > $context_size) {
            return false;
        }
        return in_array($context_asc[$context_size - 2], $search_any, true);
    }

    /**
     * Returns true the exact canonical names of $search_exactly are direct ancestors
     * of the last canonical name inside the context.
     *
     * @param string[] $context_asc    canonical names (first to last)
     * @param string[] $search_exactly canonical names (first to last)
     */
    public function isDirectDescendantOf(array $context_asc, array $search_exactly): bool
    {
        // ensure minimum context size allowing direct descendants
        $search_size = count($search_exactly);
        if (0 === $search_size || $search_size + 1 > count($context_asc)) {
            return false;
        }
        $ancestors = array_slice($context_asc, 0, -1);
        return $search_exactly === array_slice($ancestors, -$search_size);
    }

    /**
     * Returns true if any canonical name of $search_any is an indirect ancestor
     * of the last canonical name inside the context.
     *
     * @param string[] $context_asc canonical names (first to last)
     * @param string[] $search_any  canonical names
     */
    public function isIndirectDescendantOfAny(array $context_asc, array $search_any): bool
    {
        // ensure minimum context size allowing direct descendants
        if (3 > count($context_asc)) {
            return false;
        }
        $indirect_ancestors = array_slice($context_asc, 0, -2);
        return [] !== array_intersect($indirect_ancestors, $search_any);
    }

    /**
     * Returns true the exact canonical names of $search_exactly are indirect ancestors
     * of the last canonical name inside the context.
     *
     * @param string[] $context_asc    canonical names (first to last)
     * @param string[] $search_exactly canonical names (first to last)
     */
    public function isIndirectDescendantOf(array $context_asc, array $search_exactly): bool
    {
        // ensure minimum context size allowing direct descendants
        $search_size = count($search_exactly);
        if (0 === $search_size || $search_size + 2 > count($context_asc)) {
            return false;
        }
        $indirect_ancestors = array_slice($context_asc, 0, -2);
        $last_start = count($indirect_ancestors) - $search_size;
        for ($start = 0; $last_start >= $start; $start++) {
            if ($search_exactly === array_slice($indirect_ancestors, $start, $search_size)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns true if any canonical name of $search_any is an ancestor of any
     * kind of the last canonical name inside the context.
     *
     * @param string[] $context_asc canonical names (first to last)
     * @param string[] $search_any  canonical names
     */
    public function isDescendantOfAny(array $context_asc, array $search_any): bool
    {
        return $this->isDirectDescendantOfAny($context_asc, $search_any)
            || $this->isIndirectDescendantOfAny($context_asc, $search_any);
    }

    /**
     * Returns true the exact canonical names of $search_exactly are indirect ancestors
     * of any kind of the last canonical name inside the context.
     *
     * @param string[] $context_asc    canonical names (first to last)
     * @param string[] $search_exactly canonical names (first to last)
     */
    public function isDescendantOf(array $context_asc, array $search_exactly): bool
    {
        return $this->isDirectDescendantOf($context_asc, $search_exactly)
            || $this->isIndirectDescendantOf($context_asc, $search_exactly);
    }
}
