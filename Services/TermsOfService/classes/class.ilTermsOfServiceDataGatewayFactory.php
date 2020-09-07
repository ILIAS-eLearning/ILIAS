<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDataGatewayFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDataGatewayFactory
{
    /** @var \ilDBInterface */
    protected $db;

    /**
     * @param \ilDBInterface $db
     */
    public function setDatabaseAdapter(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return \ilDBInterface|null
     */
    public function getDatabaseAdapter()
    {
        return $this->db;
    }

    /**
     * @param string $name
     * @return \ilTermsOfServiceAcceptanceDatabaseGateway
     * @throws \InvalidArgumentException
     * @throws \ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function getByName(string $name) : \ilTermsOfServiceAcceptanceDataGateway
    {
        if (null == $this->db) {
            throw new \ilTermsOfServiceMissingDatabaseAdapterException('Incomplete factory configuration. Please inject a database adapter.');
        }

        switch (strtolower($name)) {
            case 'iltermsofserviceacceptancedatabasegateway':
                return new \ilTermsOfServiceAcceptanceDatabaseGateway($this->db);

            default:
                throw new \InvalidArgumentException('Data gateway not supported');
        }
    }
}
