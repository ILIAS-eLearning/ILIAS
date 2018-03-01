<?php

namespace SAML2\Compat\Ssp;

use SAML2\Compat\AbstractContainer;
use SimpleSAML_Utilities;

class Container extends AbstractContainer
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Create a new SimpleSAMLphp compatible container.
     */
    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * {@inheritdoc}
     */
    public function generateId()
    {
        return SimpleSAML_Utilities::generateID();
    }

    /**
     * {@inheritdoc}
     */
    public function debugMessage($message, $type)
    {
        SimpleSAML_Utilities::debugMessage($message, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function redirect($url, $data = array())
    {
        SimpleSAML_Utilities::redirectTrustedURL($url, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function postRedirect($url, $data = array())
    {
        SimpleSAML_Utilities::postRedirect($url, $data);
    }
}
