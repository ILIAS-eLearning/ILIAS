<?php

namespace SAML2;

use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Utilities\Temporal;
use SimpleSAML_Configuration;
use SimpleSAML_Metadata_MetaDataStorageHandler;
use SimpleSAML_Store;
use SimpleSAML_Utilities;

/**
 * Class which implements the HTTP-Artifact binding.
 *
 * @author  Danny Bollaert, UGent AS. <danny.bollaert@ugent.be>
 * @package SimpleSAMLphp
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HTTPArtifact extends Binding
{
    /**
     * @var \SimpleSAML_Configuration
     */
    private $spMetadata;

    /**
     * Create the redirect URL for a message.
     *
     * @param  \SAML2\Message $message The message.
     * @return string        The URL the user should be redirected to in order to send a message.
     * @throws \Exception
     */
    public function getRedirectURL(Message $message)
    {
        $store = SimpleSAML_Store::getInstance();
        if ($store === false) {
            throw new \Exception('Unable to send artifact without a datastore configured.');
        }

        $generatedId = pack('H*', ((string) SimpleSAML_Utilities::stringToHex(SimpleSAML_Utilities::generateRandomBytes(20))));
        $artifact = base64_encode("\x00\x04\x00\x00" . sha1($message->getIssuer(), true) . $generatedId) ;
        $artifactData = $message->toUnsignedXML();
        $artifactDataString = $artifactData->ownerDocument->saveXML($artifactData);

        $store->set('artifact', $artifact, $artifactDataString, Temporal::getTime() + 15*60);

        $params = array(
            'SAMLart' => $artifact,
        );
        $relayState = $message->getRelayState();
        if ($relayState !== null) {
            $params['RelayState'] = $relayState;
        }

        return SimpleSAML_Utilities::addURLparameter($message->getDestination(), $params);
    }

    /**
     * Send a SAML 2 message using the HTTP-Redirect binding.
     *
     * Note: This function never returns.
     *
     * @param \SAML2\Message $message The message we should send.
     */
    public function send(Message $message)
    {
        $destination = $this->getRedirectURL($message);
        Utils::getContainer()->redirect($destination);
    }

    /**
     * Receive a SAML 2 message sent using the HTTP-Artifact binding.
     *
     * Throws an exception if it is unable receive the message.
     *
     * @return \SAML2\Message The received message.
     * @throws \Exception
     */
    public function receive()
    {
        if (array_key_exists('SAMLart', $_REQUEST)) {
            $artifact = base64_decode($_REQUEST['SAMLart']);
            $endpointIndex =  bin2hex(substr($artifact, 2, 2));
            $sourceId = bin2hex(substr($artifact, 4, 20));
        } else {
            throw new \Exception('Missing SAMLart parameter.');
        }

        $metadataHandler = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();

        $idpMetadata = $metadataHandler->getMetaDataConfigForSha1($sourceId, 'saml20-idp-remote');

        if ($idpMetadata === null) {
            throw new \Exception('No metadata found for remote provider with SHA1 ID: ' . var_export($sourceId, true));
        }

        $endpoint = null;
        foreach ($idpMetadata->getEndpoints('ArtifactResolutionService') as $ep) {
            if ($ep['index'] ===  hexdec($endpointIndex)) {
                $endpoint = $ep;
                break;
            }
        }

        if ($endpoint === null) {
            throw new \Exception('No ArtifactResolutionService with the correct index.');
        }

        Utils::getContainer()->getLogger()->debug("ArtifactResolutionService endpoint being used is := " . $endpoint['Location']);

        //Construct the ArtifactResolve Request
        $ar = new ArtifactResolve();

        /* Set the request attributes */

        $ar->setIssuer($this->spMetadata->getString('entityid'));
        $ar->setArtifact($_REQUEST['SAMLart']);
        $ar->setDestination($endpoint['Location']);

        /* Sign the request */
        \sspmod_saml_Message::addSign($this->spMetadata, $idpMetadata, $ar); // Shoaib - moved from the SOAPClient.

        $soap = new SOAPClient();

        // Send message through SoapClient
        /** @var \SAML2\ArtifactResponse $artifactResponse */
        $artifactResponse = $soap->send($ar, $this->spMetadata);

        if (!$artifactResponse->isSuccess()) {
            throw new \Exception('Received error from ArtifactResolutionService.');
        }

        $xml = $artifactResponse->getAny();
        if ($xml === null) {
            /* Empty ArtifactResponse - possibly because of Artifact replay? */

            return null;
        }

        $samlResponse = Message::fromXML($xml);
        $samlResponse->addValidator(array(get_class($this), 'validateSignature'), $artifactResponse);

        if (isset($_REQUEST['RelayState'])) {
            $samlResponse->setRelayState($_REQUEST['RelayState']);
        }

        return $samlResponse;
    }

    /**
     * @param \SimpleSAML_Configuration $sp
     */
    public function setSPMetadata(SimpleSAML_Configuration $sp)
    {
        $this->spMetadata = $sp;
    }

    /**
     * A validator which returns true if the ArtifactResponse was signed with the given key
     *
     * @param \SAML2\ArtifactResponse $message
     * @param XMLSecurityKey $key
     * @return bool
     */
    public static function validateSignature(ArtifactResponse $message, XMLSecurityKey $key)
    {
        return $message->validate($key);
    }
}
