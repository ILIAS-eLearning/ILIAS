<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Mapping\Loader;

use Symfony\Component\Serializer\Exception\MappingException;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

/**
 * YAML File Loader.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class YamlFileLoader extends FileLoader
{
    private $yamlParser;

    /**
     * An array of YAML class descriptions.
     *
     * @var array
     */
    private $classes;

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(ClassMetadataInterface $classMetadata)
    {
        if (null === $this->classes) {
            $this->classes = $this->getClassesFromYaml();
        }

        if (!$this->classes) {
            return false;
        }

        if (!isset($this->classes[$classMetadata->getName()])) {
            return false;
        }

        $yaml = $this->classes[$classMetadata->getName()];

        if (isset($yaml['attributes']) && \is_array($yaml['attributes'])) {
            $attributesMetadata = $classMetadata->getAttributesMetadata();

            foreach ($yaml['attributes'] as $attribute => $data) {
                if (isset($attributesMetadata[$attribute])) {
                    $attributeMetadata = $attributesMetadata[$attribute];
                } else {
                    $attributeMetadata = new AttributeMetadata($attribute);
                    $classMetadata->addAttributeMetadata($attributeMetadata);
                }

                if (isset($data['groups'])) {
                    if (!\is_array($data['groups'])) {
                        throw new MappingException(sprintf('The "groups" key must be an array of strings in "%s" for the attribute "%s" of the class "%s".', $this->file, $attribute, $classMetadata->getName()));
                    }

                    foreach ($data['groups'] as $group) {
                        if (!\is_string($group)) {
                            throw new MappingException(sprintf('Group names must be strings in "%s" for the attribute "%s" of the class "%s".', $this->file, $attribute, $classMetadata->getName()));
                        }

                        $attributeMetadata->addGroup($group);
                    }
                }

                if (isset($data['max_depth'])) {
                    if (!\is_int($data['max_depth'])) {
                        throw new MappingException(sprintf('The "max_depth" value must be an integer in "%s" for the attribute "%s" of the class "%s".', $this->file, $attribute, $classMetadata->getName()));
                    }

                    $attributeMetadata->setMaxDepth($data['max_depth']);
                }

                if (isset($data['serialized_name'])) {
                    if (!\is_string($data['serialized_name']) || empty($data['serialized_name'])) {
                        throw new MappingException(sprintf('The "serialized_name" value must be a non-empty string in "%s" for the attribute "%s" of the class "%s".', $this->file, $attribute, $classMetadata->getName()));
                    }

                    $attributeMetadata->setSerializedName($data['serialized_name']);
                }
            }
        }

        if (isset($yaml['discriminator_map'])) {
            if (!isset($yaml['discriminator_map']['type_property'])) {
                throw new MappingException(sprintf('The "type_property" key must be set for the discriminator map of the class "%s" in "%s".', $classMetadata->getName(), $this->file));
            }

            if (!isset($yaml['discriminator_map']['mapping'])) {
                throw new MappingException(sprintf('The "mapping" key must be set for the discriminator map of the class "%s" in "%s".', $classMetadata->getName(), $this->file));
            }

            $classMetadata->setClassDiscriminatorMapping(new ClassDiscriminatorMapping(
                $yaml['discriminator_map']['type_property'],
                $yaml['discriminator_map']['mapping']
            ));
        }

        return true;
    }

    /**
     * Return the names of the classes mapped in this file.
     *
     * @return string[] The classes names
     */
    public function getMappedClasses()
    {
        if (null === $this->classes) {
            $this->classes = $this->getClassesFromYaml();
        }

        return array_keys($this->classes);
    }

    private function getClassesFromYaml()
    {
        if (!stream_is_local($this->file)) {
            throw new MappingException(sprintf('This is not a local file "%s".', $this->file));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new Parser();
        }

        $classes = $this->yamlParser->parseFile($this->file, Yaml::PARSE_CONSTANT);

        if (empty($classes)) {
            return [];
        }

        if (!\is_array($classes)) {
            throw new MappingException(sprintf('The file "%s" must contain a YAML array.', $this->file));
        }

        return $classes;
    }
}
