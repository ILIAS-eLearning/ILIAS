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

namespace ILIAS\UI\Implementation\Crawler\Entry;

use JsonSerializable;

/**
 * Container to hold rules of UI Components
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class ComponentEntryRules extends AbstractEntryPart implements JsonSerializable
{
    protected array $rules = array(
        "usage" => array(),
        "composition" => array(),
        "interaction" => array(),
        "wording" => array(),
        "ordering" => array(),
        "style" => array(),
        "responsiveness" => array(),
        "accessibility" => array()
    );

    public function __construct(array $rules = array())
    {
        parent::__construct();
        $this->setRules($rules);
    }

    public function withRules(array $rules = array()): ComponentEntryRules
    {
        $clone = clone $this;
        $clone->setRules($rules);
        return $clone;
    }

    protected function setRules(array $rules): void
    {
        if (!$rules) {
            return;
        }

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

    public function getRules(): array
    {
        return $this->rules;
    }

    public function hasRules(): bool
    {
        foreach ($this->rules as $category_rules) {
            if (sizeof($category_rules)) {
                return true;
            }
        }
        return false;
    }

    public function jsonSerialize(): array
    {
        return $this->getRules();
    }
}
