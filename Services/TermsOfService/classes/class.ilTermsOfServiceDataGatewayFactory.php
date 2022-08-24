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
     * @param string $name
     * @return ilTermsOfServiceAcceptanceDatabaseGateway
     * @throws InvalidArgumentException
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function getByName(string $name): ilTermsOfServiceAcceptanceDataGateway
    {
        if (null === $this->db) {
            throw new ilTermsOfServiceMissingDatabaseAdapterException('Incomplete factory configuration. Please inject a database adapter.');
        }

        switch (strtolower($name)) {
            case 'iltermsofserviceacceptancedatabasegateway':
                return new ilTermsOfServiceAcceptanceDatabaseGateway($this->db);

            default:
                throw new InvalidArgumentException('Data gateway not supported');
        }
    }
}
