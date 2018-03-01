<?php

namespace SAML2;

/**
 * The \SAML2\ArtifactResponse, is the response to the \SAML2\ArtifactResolve.
 *
 * @author Danny Bollaert, UGent AS. <danny.bollaert@ugent.be>
 * @package SimpleSAMLphp
 */
class ArtifactResponse extends StatusResponse
{
    /**
     * The \DOMElement with the message the artifact refers
     * to, or null if we don't refer to any artifact.
     *
     * @var \DOMElement|null
     */
    private $any;


    public function __construct(\DOMElement $xml = null)
    {
        parent::__construct('ArtifactResponse', $xml);

        if (!is_null($xml)) {
            $status = Utils::xpQuery($xml, './saml_protocol:Status');
            assert('!empty($status)'); /* Will have failed during StatusResponse parsing. */

            $status = $status[0];

            for ($any = $status->nextSibling; $any !== null; $any = $any->nextSibling) {
                if ($any instanceof \DOMElement) {
                    $this->any = $any;
                    break;
                }
                /* Ignore comments and text nodes. */
            }
        }
    }

    public function setAny(\DOMElement $any = null)
    {
        $this->any = $any;
    }

    public function getAny()
    {
        return $this->any;
    }

    /**
     * Convert the response message to an XML element.
     *
     * @return \DOMElement This response.
     */
    public function toUnsignedXML()
    {
        $root = parent::toUnsignedXML();
        if (isset($this->any)) {
            $node = $root->ownerDocument->importNode($this->any, true);
            $root->appendChild($node);
        }

        return $root;
    }
}
