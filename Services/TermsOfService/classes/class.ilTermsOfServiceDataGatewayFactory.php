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

/**
 * Class ilTermsOfServiceDataGatewayFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDataGatewayFactory
{
    protected ?ilDBInterface $db = null;

    public function setDatabaseAdapter(?ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function getDatabaseAdapter(): ?ilDBInterface
    {
        return $this->db;
    }

    /**
     * @return ilTermsOfServiceAcceptanceDatabaseGateway
     * @throws InvalidArgumentException
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function getByName(string $name): ilTermsOfServiceAcceptanceDataGateway
    {
        if (!$this->db instanceof \ilDBInterface) {
            throw new ilTermsOfServiceMissingDatabaseAdapterException('Incomplete factory configuration. Please inject a database adapter.');
        }

        return match (strtolower($name)) {
            'iltermsofserviceacceptancedatabasegateway' => new ilTermsOfServiceAcceptanceDatabaseGateway($this->db),
            default => throw new InvalidArgumentException('Data gateway not supported'),
        };
    }
}
