<?php

namespace ILIAS\UI\Implementation\Crawler\Entry;

/**
 * Container to hold rules of UI Components
 *
 * @author			  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version			  $Id$
 *
 */
class ComponentEntryRules extends AbstractEntryPart implements \JsonSerializable
{
    /**
     * @var array
     */
    protected $rules = array(
        "usage" => array(),
        "composition" => array(),
        "interaction" => array(),
        "wording" => array(),
        "ordering" => array(),
        "style" => array(),
        "responsiveness" => array(),
        "accessibility" => array()
    );

    /**
     * ComponentEntryDescription constructor.
     * @param array $rules
     */
    public function __construct($rules = array())
    {
        parent::__construct();
        $this->setRules($rules);
    }

    /**
     * @param	array $rules
     * @return	ComponentEntryRules
     */
    public function withRules($rules = array())
    {
        $clone = clone $this;
        $clone->setRules($rules);
        return $clone;
    }

    /**
     * @param	$rules
     * @throws	\ILIAS\UI\Implementation\Crawler\Exception\CrawlerException
     */
    protected function setRules($rules)
    {
        if (!$rules) {
            return;
        }
        $this->assert()->isArray($rules);
        foreach ($rules as $rule_category => $category_rules) {
            $this->assert()->isIndex($rule_category, $this->rules);
            if ($category_rules && $category_rules != "") {
                $this->assert()->isArray($category_rules);
                foreach ($category_rules as $rule_id => $rule) {
                    $this->assert()->isString($rule);
                    $this->rules[$rule_category][$rule_id] = $rule;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return bool
     */
    public function hasRules()
    {
        foreach ($this->rules as $category_rules) {
            if (sizeof($category_rules)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getRules();
    }
}
