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

namespace ILIAS\Data\Privacy;

use ILIAS\Data\Privacy\Logger\PrivacyLogger;
use ILIAS\Data\Privacy\Purpose\Purposes;
use ILIAS\Data\Privacy\Source\Sources;

/**
 * Wired in the Data component bootstrap ({@see \ILIAS\Data::init()}).
 * The constructor must stay free of any I/O — it is executed at
 * bootstrap build time.
 */
class ServicesImpl implements Services
{
    private ?Factory $factory = null;

    public function __construct(
        private readonly PrivacyLogger $logger,
        private readonly Sources $sources,
        private readonly Purposes $purposes,
    ) {
    }

    public function factory(): Factory
    {
        return $this->factory ??= new Factory($this->logger);
    }

    public function sources(): Sources
    {
        return $this->sources;
    }

    public function purposes(): Purposes
    {
        return $this->purposes;
    }
}
