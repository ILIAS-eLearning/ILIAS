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

namespace ILIAS\MetaData\OERExposer\OAIPMH;

use ILIAS\MetaData\OERExposer\OAIPMH\HTTP\WrapperInterface as HTTPWrapperInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\ParserInterface as RequestParserInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Responses\RequestProcessorInterface;
use ILIAS\MetaData\Settings\SettingsInterface;

/**
 * The OAI PMH interface does not use the usual internal services of
 * the MetaData component, as it should be lightweight and decoupled
 * from everything else as much as possible.
 */
interface InitiatorInterface
{
    public function httpWrapper(): HTTPWrapperInterface;

    public function settings(): SettingsInterface;

    public function requestParser(): RequestParserInterface;

    public function requestProcessor(): RequestProcessorInterface;
}
