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

namespace ILIAS\ResourceStorage\Flavour;

use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Flavours
{
    protected FlavourBuilder $flavour_builder;
    protected ResourceBuilder $resource_builder;

    public function __construct(
        FlavourBuilder $flavour_builder,
        ResourceBuilder $resource_builder
    ) {
        $this->flavour_builder = $flavour_builder;
        $this->resource_builder = $resource_builder;
    }


    /**
     * @description Use get() to get a Flavour for the FlavourDefinition for a ResourceIdentification.
     * If the Flavour already exists you will get it, if not it will be created and returned directly.
     *
     * Take into account that a Flavour can come back without Tokens for accessing the streams of the Flavour,
     * namely if the Flavour does not contain any Streams. You will usually use the flavor with the Consumers of
     * the IRSS anyway, so you don't have to worry about that.
     */
    public function get(ResourceIdentification $rid, FlavourDefinition $flavour_definition): Flavour
    {
        return $this->flavour_builder->get($rid, $flavour_definition, false);
    }

    /**
     * @description Actually like get(), but without return and can be used to create Flavour before you want to get them.
     */
    public function ensure(ResourceIdentification $rid, FlavourDefinition $flavour_definition): void
    {
        if ($this->flavour_builder->has($rid, $flavour_definition)) {
            return;
        }
        $this->flavour_builder->get($rid, $flavour_definition, true);
    }

    /**
     * @description This can be used to ask whether a Flavor already exists for the FlavourDefinition for a
     * certain IRSS ResourceIdentification.
     */
    public function has(ResourceIdentification $rid, FlavourDefinition $flavour_definition): bool
    {
        return $this->flavour_builder->has($rid, $flavour_definition);
    }


    /**
     * @description You don't need a flavor anymore or you want to delete it because you explicitly want to regenerate it?
     * Use delete for this
     */
    public function remove(ResourceIdentification $rid, FlavourDefinition $flavour_definition): void
    {
        if ($this->has($rid, $flavour_definition)) {
            $this->flavour_builder->delete($rid, $flavour_definition);
        }
    }


    /**
     * @description Hereby you can check in advance, if there is a Machine and an Engine for your FlavourDefinition,
     * which can generate the Flavour you want.
     */
    public function possible(ResourceIdentification $rid, FlavourDefinition $flavour_definition): bool
    {
        return $this->flavour_builder->testDefinition($rid, $flavour_definition);
    }
}
