<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Encoder;

use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Encodes XML data.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author John Wards <jwards@whiteoctober.co.uk>
 * @author Fabian Vogler <fabian@equivalence.ch>
 * @author Kévin Dunglas <dunglas@gmail.com>
 * @author Dany Maillard <danymaillard93b@gmail.com>
 */
class XmlEncoder implements EncoderInterface, DecoderInterface, NormalizationAwareInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    const FORMAT = 'xml';

    const AS_COLLECTION = 'as_collection';

    /**
     * An array of ignored XML node types while decoding, each one of the DOM Predefined XML_* constants.
     */
    const DECODER_IGNORED_NODE_TYPES = 'decoder_ignored_node_types';

    /**
     * An array of ignored XML node types while encoding, each one of the DOM Predefined XML_* constants.
     */
    const ENCODER_IGNORED_NODE_TYPES = 'encoder_ignored_node_types';
    const ENCODING = 'xml_encoding';
    const FORMAT_OUTPUT = 'xml_format_output';

    /**
     * A bit field of LIBXML_* constants.
     */
    const LOAD_OPTIONS = 'load_options';
    const REMOVE_EMPTY_TAGS = 'remove_empty_tags';
    const ROOT_NODE_NAME = 'xml_root_node_name';
    const STANDALONE = 'xml_standalone';
    const TYPE_CASE_ATTRIBUTES = 'xml_type_cast_attributes';
    const VERSION = 'xml_version';

    private $defaultContext = [
        self::AS_COLLECTION => false,
        self::DECODER_IGNORED_NODE_TYPES => [XML_PI_NODE, XML_COMMENT_NODE],
        self::ENCODER_IGNORED_NODE_TYPES => [],
        self::LOAD_OPTIONS => LIBXML_NONET | LIBXML_NOBLANKS,
        self::REMOVE_EMPTY_TAGS => false,
        self::ROOT_NODE_NAME => 'response',
        self::TYPE_CASE_ATTRIBUTES => true,
    ];

    /**
     * @var \DOMDocument
     */
    private $dom;
    private $format;
    private $context;

    /**
     * @param array $defaultContext
     */
    public function __construct($defaultContext = [], int $loadOptions = null, array $decoderIgnoredNodeTypes = [XML_PI_NODE, XML_COMMENT_NODE], array $encoderIgnoredNodeTypes = [])
    {
        if (!\is_array($defaultContext)) {
            @trigger_error('Passing configuration options directly to the constructor is deprecated since Symfony 4.2, use the default context instead.', E_USER_DEPRECATED);

            $defaultContext = [
                self::DECODER_IGNORED_NODE_TYPES => $decoderIgnoredNodeTypes,
                self::ENCODER_IGNORED_NODE_TYPES => $encoderIgnoredNodeTypes,
                self::LOAD_OPTIONS => $loadOptions ?? LIBXML_NONET | LIBXML_NOBLANKS,
                self::ROOT_NODE_NAME => (string) $defaultContext,
            ];
        }

        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = [])
    {
        $encoderIgnoredNodeTypes = $context[self::ENCODER_IGNORED_NODE_TYPES] ?? $this->defaultContext[self::ENCODER_IGNORED_NODE_TYPES];
        $ignorePiNode = \in_array(XML_PI_NODE, $encoderIgnoredNodeTypes, true);
        if ($data instanceof \DOMDocument) {
            return $data->saveXML($ignorePiNode ? $data->documentElement : null);
        }

        $xmlRootNodeName = $context[self::ROOT_NODE_NAME] ?? $this->defaultContext[self::ROOT_NODE_NAME];

        $this->dom = $this->createDomDocument($context);
        $this->format = $format;
        $this->context = $context;

        if (null !== $data && !is_scalar($data)) {
            $root = $this->dom->createElement($xmlRootNodeName);
            $this->dom->appendChild($root);
            $this->buildXml($root, $data, $xmlRootNodeName);
        } else {
            $this->appendNode($this->dom, $data, $xmlRootNodeName);
        }

        return $this->dom->saveXML($ignorePiNode ? $this->dom->documentElement : null);
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = [])
    {
        if ('' === trim($data)) {
            throw new NotEncodableValueException('Invalid XML data, it can not be empty.');
        }

        $internalErrors = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);
        libxml_clear_errors();

        $dom = new \DOMDocument();
        $dom->loadXML($data, $context[self::LOAD_OPTIONS] ?? $this->defaultContext[self::LOAD_OPTIONS]);

        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($disableEntities);

        if ($error = libxml_get_last_error()) {
            libxml_clear_errors();

            throw new NotEncodableValueException($error->message);
        }

        $rootNode = null;
        $decoderIgnoredNodeTypes = $context[self::DECODER_IGNORED_NODE_TYPES] ?? $this->defaultContext[self::DECODER_IGNORED_NODE_TYPES];
        foreach ($dom->childNodes as $child) {
            if (XML_DOCUMENT_TYPE_NODE === $child->nodeType) {
                throw new NotEncodableValueException('Document types are not allowed.');
            }
            if (!$rootNode && !\in_array($child->nodeType, $decoderIgnoredNodeTypes, true)) {
                $rootNode = $child;
            }
        }

        // todo: throw an exception if the root node name is not correctly configured (bc)

        if ($rootNode->hasChildNodes()) {
            $xpath = new \DOMXPath($dom);
            $data = [];
            foreach ($xpath->query('namespace::*', $dom->documentElement) as $nsNode) {
                $data['@'.$nsNode->nodeName] = $nsNode->nodeValue;
            }

            unset($data['@xmlns:xml']);

            if (empty($data)) {
                return $this->parseXml($rootNode, $context);
            }

            return array_merge($data, (array) $this->parseXml($rootNode, $context));
        }

        if (!$rootNode->hasAttributes()) {
            return $rootNode->nodeValue;
        }

        $data = [];

        foreach ($rootNode->attributes as $attrKey => $attr) {
            $data['@'.$attrKey] = $attr->nodeValue;
        }

        $data['#'] = $rootNode->nodeValue;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return self::FORMAT === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return self::FORMAT === $format;
    }

    /**
     * Sets the root node name.
     *
     * @deprecated since Symfony 4.2
     *
     * @param string $name Root node name
     */
    public function setRootNodeName($name)
    {
        @trigger_error(sprintf('The "%s()" method is deprecated since Symfony 4.2, use the context instead.', __METHOD__), E_USER_DEPRECATED);

        $this->defaultContext[self::ROOT_NODE_NAME] = $name;
    }

    /**
     * Returns the root node name.
     *
     * @deprecated since Symfony 4.2
     *
     * @return string
     */
    public function getRootNodeName()
    {
        @trigger_error(sprintf('The "%s()" method is deprecated since Symfony 4.2, use the context instead.', __METHOD__), E_USER_DEPRECATED);

        return $this->defaultContext[self::ROOT_NODE_NAME];
    }

    final protected function appendXMLString(\DOMNode $node, string $val): bool
    {
        if ('' !== $val) {
            $frag = $this->dom->createDocumentFragment();
            $frag->appendXML($val);
            $node->appendChild($frag);

            return true;
        }

        return false;
    }

    final protected function appendText(\DOMNode $node, string $val): bool
    {
        $nodeText = $this->dom->createTextNode($val);
        $node->appendChild($nodeText);

        return true;
    }

    final protected function appendCData(\DOMNode $node, string $val): bool
    {
        $nodeText = $this->dom->createCDATASection($val);
        $node->appendChild($nodeText);

        return true;
    }

    /**
     * @param \DOMNode             $node
     * @param \DOMDocumentFragment $fragment
     */
    final protected function appendDocumentFragment(\DOMNode $node, $fragment): bool
    {
        if ($fragment instanceof \DOMDocumentFragment) {
            $node->appendChild($fragment);

            return true;
        }

        return false;
    }

    final protected function appendComment(\DOMNode $node, string $data): bool
    {
        $node->appendChild($this->dom->createComment($data));

        return true;
    }

    /**
     * Checks the name is a valid xml element name.
     */
    final protected function isElementNameValid(string $name): bool
    {
        return $name &&
            false === strpos($name, ' ') &&
            preg_match('#^[\pL_][\pL0-9._:-]*$#ui', $name);
    }

    /**
     * Parse the input DOMNode into an array or a string.
     *
     * @return array|string
     */
    private function parseXml(\DOMNode $node, array $context = [])
    {
        $data = $this->parseXmlAttributes($node, $context);

        $value = $this->parseXmlValue($node, $context);

        if (!\count($data)) {
            return $value;
        }

        if (!\is_array($value)) {
            $data['#'] = $value;

            return $data;
        }

        if (1 === \count($value) && key($value)) {
            $data[key($value)] = current($value);

            return $data;
        }

        foreach ($value as $key => $val) {
            $data[$key] = $val;
        }

        return $data;
    }

    /**
     * Parse the input DOMNode attributes into an array.
     */
    private function parseXmlAttributes(\DOMNode $node, array $context = []): array
    {
        if (!$node->hasAttributes()) {
            return [];
        }

        $data = [];
        $typeCastAttributes = (bool) ($context[self::TYPE_CASE_ATTRIBUTES] ?? $this->defaultContext[self::TYPE_CASE_ATTRIBUTES]);

        foreach ($node->attributes as $attr) {
            if (!is_numeric($attr->nodeValue) || !$typeCastAttributes) {
                $data['@'.$attr->nodeName] = $attr->nodeValue;

                continue;
            }

            if (false !== $val = filter_var($attr->nodeValue, FILTER_VALIDATE_INT)) {
                $data['@'.$attr->nodeName] = $val;

                continue;
            }

            $data['@'.$attr->nodeName] = (float) $attr->nodeValue;
        }

        return $data;
    }

    /**
     * Parse the input DOMNode value (content and children) into an array or a string.
     *
     * @return array|string
     */
    private function parseXmlValue(\DOMNode $node, array $context = [])
    {
        if (!$node->hasChildNodes()) {
            return $node->nodeValue;
        }

        if (1 === $node->childNodes->length && \in_array($node->firstChild->nodeType, [XML_TEXT_NODE, XML_CDATA_SECTION_NODE])) {
            return $node->firstChild->nodeValue;
        }

        $value = [];
        $decoderIgnoredNodeTypes = $context[self::DECODER_IGNORED_NODE_TYPES] ?? $this->defaultContext[self::DECODER_IGNORED_NODE_TYPES];
        foreach ($node->childNodes as $subnode) {
            if (\in_array($subnode->nodeType, $decoderIgnoredNodeTypes, true)) {
                continue;
            }

            $val = $this->parseXml($subnode, $context);

            if ('item' === $subnode->nodeName && isset($val['@key'])) {
                $value[$val['@key']] = $val['#'] ?? $val;
            } else {
                $value[$subnode->nodeName][] = $val;
            }
        }

        $asCollection = $context[self::AS_COLLECTION] ?? $this->defaultContext[self::AS_COLLECTION];
        foreach ($value as $key => $val) {
            if (!$asCollection && \is_array($val) && 1 === \count($val)) {
                $value[$key] = current($val);
            }
        }

        return $value;
    }

    /**
     * Parse the data and convert it to DOMElements.
     *
     * @param array|object $data
     *
     * @throws NotEncodableValueException
     */
    private function buildXml(\DOMNode $parentNode, $data, string $xmlRootNodeName = null): bool
    {
        $append = true;
        $removeEmptyTags = $this->context[self::REMOVE_EMPTY_TAGS] ?? $this->defaultContext[self::REMOVE_EMPTY_TAGS] ?? false;
        $encoderIgnoredNodeTypes = $this->context[self::ENCODER_IGNORED_NODE_TYPES] ?? $this->defaultContext[self::ENCODER_IGNORED_NODE_TYPES];

        if (\is_array($data) || ($data instanceof \Traversable && !$this->serializer->supportsNormalization($data, $this->format))) {
            foreach ($data as $key => $data) {
                //Ah this is the magic @ attribute types.
                if (0 === strpos($key, '@') && $this->isElementNameValid($attributeName = substr($key, 1))) {
                    if (!is_scalar($data)) {
                        $data = $this->serializer->normalize($data, $this->format, $this->context);
                    }
                    $parentNode->setAttribute($attributeName, $data);
                } elseif ('#' === $key) {
                    $append = $this->selectNodeType($parentNode, $data);
                } elseif ('#comment' === $key) {
                    if (!\in_array(XML_COMMENT_NODE, $encoderIgnoredNodeTypes, true)) {
                        $append = $this->appendComment($parentNode, $data);
                    }
                } elseif (\is_array($data) && false === is_numeric($key)) {
                    // Is this array fully numeric keys?
                    if (ctype_digit(implode('', array_keys($data)))) {
                        /*
                         * Create nodes to append to $parentNode based on the $key of this array
                         * Produces <xml><item>0</item><item>1</item></xml>
                         * From ["item" => [0,1]];.
                         */
                        foreach ($data as $subData) {
                            $append = $this->appendNode($parentNode, $subData, $key);
                        }
                    } else {
                        $append = $this->appendNode($parentNode, $data, $key);
                    }
                } elseif (is_numeric($key) || !$this->isElementNameValid($key)) {
                    $append = $this->appendNode($parentNode, $data, 'item', $key);
                } elseif (null !== $data || !$removeEmptyTags) {
                    $append = $this->appendNode($parentNode, $data, $key);
                }
            }

            return $append;
        }

        if (\is_object($data)) {
            $data = $this->serializer->normalize($data, $this->format, $this->context);
            if (null !== $data && !is_scalar($data)) {
                return $this->buildXml($parentNode, $data, $xmlRootNodeName);
            }

            // top level data object was normalized into a scalar
            if (!$parentNode->parentNode->parentNode) {
                $root = $parentNode->parentNode;
                $root->removeChild($parentNode);

                return $this->appendNode($root, $data, $xmlRootNodeName);
            }

            return $this->appendNode($parentNode, $data, 'data');
        }

        throw new NotEncodableValueException(sprintf('An unexpected value could not be serialized: %s', var_export($data, true)));
    }

    /**
     * Selects the type of node to create and appends it to the parent.
     *
     * @param array|object $data
     */
    private function appendNode(\DOMNode $parentNode, $data, string $nodeName, string $key = null): bool
    {
        $node = $this->dom->createElement($nodeName);
        if (null !== $key) {
            $node->setAttribute('key', $key);
        }
        $appendNode = $this->selectNodeType($node, $data);
        // we may have decided not to append this node, either in error or if its $nodeName is not valid
        if ($appendNode) {
            $parentNode->appendChild($node);
        }

        return $appendNode;
    }

    /**
     * Checks if a value contains any characters which would require CDATA wrapping.
     */
    private function needsCdataWrapping(string $val): bool
    {
        return 0 < preg_match('/[<>&]/', $val);
    }

    /**
     * Tests the value being passed and decide what sort of element to create.
     *
     * @param mixed $val
     *
     * @throws NotEncodableValueException
     */
    private function selectNodeType(\DOMNode $node, $val): bool
    {
        if (\is_array($val)) {
            return $this->buildXml($node, $val);
        } elseif ($val instanceof \SimpleXMLElement) {
            $child = $this->dom->importNode(dom_import_simplexml($val), true);
            $node->appendChild($child);
        } elseif ($val instanceof \Traversable) {
            $this->buildXml($node, $val);
        } elseif (\is_object($val)) {
            return $this->selectNodeType($node, $this->serializer->normalize($val, $this->format, $this->context));
        } elseif (is_numeric($val)) {
            return $this->appendText($node, (string) $val);
        } elseif (\is_string($val) && $this->needsCdataWrapping($val)) {
            return $this->appendCData($node, $val);
        } elseif (\is_string($val)) {
            return $this->appendText($node, $val);
        } elseif (\is_bool($val)) {
            return $this->appendText($node, (int) $val);
        } elseif ($val instanceof \DOMNode) {
            $child = $this->dom->importNode($val, true);
            $node->appendChild($child);
        }

        return true;
    }

    /**
     * Create a DOM document, taking serializer options into account.
     */
    private function createDomDocument(array $context): \DOMDocument
    {
        $document = new \DOMDocument();

        // Set an attribute on the DOM document specifying, as part of the XML declaration,
        $xmlOptions = [
            // nicely formats output with indentation and extra space
            self::FORMAT_OUTPUT => 'formatOutput',
            // the version number of the document
            self::VERSION => 'xmlVersion',
            // the encoding of the document
            self::ENCODING => 'encoding',
            // whether the document is standalone
            self::STANDALONE => 'xmlStandalone',
        ];
        foreach ($xmlOptions as $xmlOption => $documentProperty) {
            if ($contextOption = $context[$xmlOption] ?? $this->defaultContext[$xmlOption] ?? false) {
                $document->$documentProperty = $contextOption;
            }
        }

        return $document;
    }
}
