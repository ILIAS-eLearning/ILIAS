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

namespace ILIAS\ResourceStorage\Flavour\Definition;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface FlavourDefinition
{
    /**
     * @return string max. 64 characters, MUST be unique and NOT a class-related magic-constant.
     * E.g. you can generate a random one with
     *   $ php -r"echo hash('sha256', uniqid());" | pbcopy
     * in your shell and paste string in your getId() implementation.
     *
     * If you ever change the ID, existing - maybe persisted - flavours created based on this
     * definition will not be found anymore and have to be regenerated.
     */
    public function getId(): string;

    /**
     * Defines the ID of the machine that supports this definition. The machine MUST exist.
     */
    public function getFlavourMachineId(): string;

    /**
     * This defines the speaky internal name of the definition, as the consumer would like to use it, e.g. to be
     * able to distinguish between several flavors.
     */
    public function getInternalName(): string;

    /**
     * If a definition can be used in several variants (e.g. configurable size of a thumbnail),
     * such variants must be distinguishable. For example, a variant name may contain "{height}x{width}"
     * if these are configurable values.
     *
     * The Variant-Name MUST be less than 768 characters long!
     */
    public function getVariantName(): ?string;

    /**
     * Define whether the generated flavor and the respective streams should be persisted,
     * or whether they should only be generated and used in-memory.
     */
    public function persist(): bool;
}
