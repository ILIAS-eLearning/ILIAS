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

namespace ILIAS\Data\Privacy\Logger;

use ILIAS\Data\Privacy\PrivacyDataType;
use ILIAS\Data\Privacy\Purpose\Purpose;

/**
 * Fans a log call out to all contributed logger backends. With no
 * backends contributed this is a no-op.
 */
final class CompositeLogger implements PrivacyLogger
{
    /**
     * @var list<PrivacyLogger>
     */
    private array $loggers;

    /**
     * @param iterable<PrivacyLogger> $loggers
     */
    public function __construct(iterable $loggers)
    {
        $this->loggers = is_array($loggers)
            ? array_values($loggers)
            : iterator_to_array($loggers, false);
    }

    public function log(PrivacyDataType $data, Purpose $purpose): void
    {
        foreach ($this->loggers as $logger) {
            $logger->log($data, $purpose);
        }
    }
}
