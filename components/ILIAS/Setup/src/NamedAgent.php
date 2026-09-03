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

namespace ILIAS\Setup;

/**
 * An agent that declares its own name in the agent collection.
 *
 * By default the agent finder derives an agent's name from its class name
 * (legacy "ilXYZSetupAgent" => "xyz", or the full class name otherwise). A
 * namespaced agent that wants a stable, semantic name — which is in particular
 * the top-level key used for it in the setup config.json — implements this
 * interface to override that derivation.
 */
interface NamedAgent extends Agent
{
    /**
     * The name under which this agent is registered in the agent collection,
     * and therefore the top-level key used for its configuration in config.json.
     *
     * Should be a short, lowercase, snake_case identifier (e.g. "content_isolation").
     */
    public function getAgentName(): string;
}
