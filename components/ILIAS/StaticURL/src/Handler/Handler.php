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

namespace ILIAS\StaticURL\Handler;

use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Response\Response;
use ILIAS\StaticURL\Response\Factory;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface Handler
{
    public function __construct();

    public function getNamespace(): string;

    public function canHandle(Request $request): bool;

    public function handle(Request $request, Context $context, Factory $response_factory): Response;

}
