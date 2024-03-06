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

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Symbol\Glyph\Glyph;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ResourceFactory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      A resource is an abstract representation for various kinds of resources the
     *      resource selector might be working with. Resources are visually represented
     *      by a leading icon and a title, and identified by an ID.
     *   rivals:
     *       ResourceContainer: >
     *          use a container resource if the resource can feature nested resources. This is
     *          also true if the container is empty.
     *   rules:
     *      usage:
     *          1: >
     *              Icons SHOULD represent the kind of resource it represents. If there is only one
     *              kind of resource overall, no icon should be provided.
     * ---
     * @param string                                      $id
     * @param string                                      $title
     * @param \ILIAS\UI\Component\Symbol\Glyph\Glyph|null $icon
     * @return \ILIAS\UI\Component\Input\Field\Resource
     */
    public function resource(string $id, string $title, Glyph $icon = null): Resource;

    /**
     *  ---
     *  description:
     *    purpose: >
     *      A resource container is an abstract representation of an object which contains
     *      multiple nested resources, while also representing a resource on its own. Otherwise
     *      a container has the same characteristics of a normal resource.
     *    rivals:
     *      Resource: use a simple resource if the resource cannot feature nested resources.
     *    rules:
     *       usage:
     *           1: >
     *               Icons SHOULD represent the kind of resource it represents. If there is only one
     *               kind of resource overall, no icon should be provided.
     *  ---
     * @param string                                      $id
     * @param string                                      $title
     * @param \ILIAS\UI\Component\Input\Field\Resource    $resources
     * @param \ILIAS\UI\Component\Symbol\Glyph\Glyph|null $icon
     * @return \ILIAS\UI\Component\Input\Field\ResourceContainer
     */
    public function container(
        string $id,
        string $title,
        Glyph $icon = null,
        Resource ...$resources
    ): ResourceContainer;
}
