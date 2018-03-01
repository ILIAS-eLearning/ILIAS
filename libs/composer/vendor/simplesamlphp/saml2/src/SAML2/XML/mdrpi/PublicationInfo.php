<?php

namespace SAML2\XML\mdrpi;

use SAML2\Utils;

/**
 * Class for handling the mdrpi:PublicationInfo element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 * @package SimpleSAMLphp
 */
class PublicationInfo
{
    /**
     * The identifier of the metadata publisher.
     *
     * @var string
     */
    public $publisher;

    /**
     * The creation timestamp for the metadata, as a UNIX timestamp.
     *
     * @var int|null
     */
    public $creationInstant;

    /**
     * Identifier for this metadata publication.
     *
     * @var string|null
     */
    public $publicationId;

    /**
     * Link to usage policy for this metadata.
     *
     * This is an associative array with language=>URL.
     *
     * @var array
     */
    public $UsagePolicy = array();

    /**
     * Create/parse a mdrpi:PublicationInfo element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('publisher')) {
            throw new \Exception('Missing required attribute "publisher" in mdrpi:PublicationInfo element.');
        }
        $this->publisher = $xml->getAttribute('publisher');

        if ($xml->hasAttribute('creationInstant')) {
            $this->creationInstant = Utils::xsDateTimeToTimestamp($xml->getAttribute('creationInstant'));
        }

        if ($xml->hasAttribute('publicationId')) {
            $this->publicationId = $xml->getAttribute('publicationId');
        }

        $this->UsagePolicy = Utils::extractLocalizedStrings($xml, Common::NS_MDRPI, 'UsagePolicy');
    }

    /**
     * Convert this element to XML.
     *
     * @param \DOMElement $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_string($this->publisher)');
        assert('is_int($this->creationInstant) || is_null($this->creationInstant)');
        assert('is_string($this->publicationId) || is_null($this->publicationId)');
        assert('is_array($this->UsagePolicy)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Common::NS_MDRPI, 'mdrpi:PublicationInfo');
        $parent->appendChild($e);

        $e->setAttribute('publisher', $this->publisher);

        if ($this->creationInstant !== null) {
            $e->setAttribute('creationInstant', gmdate('Y-m-d\TH:i:s\Z', $this->creationInstant));
        }

        if ($this->publicationId !== null) {
            $e->setAttribute('publicationId', $this->publicationId);
        }

        Utils::addStrings($e, Common::NS_MDRPI, 'mdrpi:UsagePolicy', true, $this->UsagePolicy);

        return $e;
    }
}
