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

/**
 * Class ilMailAutoCompleteSearch
 */
class ilMailAutoCompleteSearch
{
    /** @var Iterator[] */
    protected array $providers = [];

    public function __construct(protected ilMailAutoCompleteRecipientResult $result)
    {
    }

    public function addProvider(Iterator $provider): void
    {
        $this->providers[] = $provider;
    }

    public function search(): void
    {
        foreach ($this->providers as $provider) {
            foreach ($provider as $row) {
                if (!$this->result->isResultAddable()) {
                    $this->result->result['hasMoreResults'] = true;
                    break 2;
                }
                $this->result->addResult($row['login'], $row['firstname'], $row['lastname']);
            }
        }
    }
}
