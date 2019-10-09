<?php

namespace ILIAS\UI\Implementation\Crawler\Entry;

/**
 * Container to hold description of UI Components
 *
 * @author			  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version			  $Id$
 *
 */
class ComponentEntryDescription extends AbstractEntryPart implements \JsonSerializable
{
    /**
     * @var array
     */
    protected $description = array(
        "purpose"=>"",
        "composition"=>"",
        "effect"=>"",
        "rivals"=>array()
    );

    /**
     * ComponentEntryDescription constructor.
     * @param array $description
     */
    public function __construct($description = array())
    {
        parent::__construct();
        $this->setDescription($description);
    }

    /**
     * @param array $description
     * @return ComponentEntryDescription
     */
    public function withDescription($description = array())
    {
        $clone = clone $this;
        $clone->setDescription($description);
        return $clone;
    }

    /**
     * @param $descriptionElements
     */
    protected function setDescription($descriptionElements)
    {
        if (!$descriptionElements) {
            return;
        }
        $this->assert()->isArray($descriptionElements);
        foreach ($descriptionElements as $category => $element) {
            $this->assert()->isIndex($category, $this->description);

            if (is_array($this->description[$category])) {
                if ($element && $element != "") {
                    $this->assert()->isArray($element);
                    foreach ($element as $key => $part) {
                        $this->assert()->isString($part);
                        $this->description[$category][$key] = $part;
                    }
                }
            } else {
                $this->assert()->isString($element);
                $this->description[$category] = $element;
            }
        }
    }

    /**
     * @param $key
     */
    public function getProperty($key)
    {
        $this->assert()->isIndex($key, $this->description);

        return $this->description[$key];
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getDescription();
    }
}
