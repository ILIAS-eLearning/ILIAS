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
 * Container to hold description of UI Components
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class ComponentEntryDescription extends AbstractEntryPart implements JsonSerializable
{
    protected array $description = array(
        "purpose" => "",
        "composition" => "",
        "effect" => "",
        "rivals" => array()
    );

    public function __construct(array $description = array())
    {
        parent::__construct();
        $this->setDescription($description);
    }

    public function withDescription(array $description = array()): ComponentEntryDescription
    {
        $clone = clone $this;
        $clone->setDescription($description);
        return $clone;
    }

    protected function setDescription(array $descriptionElements): void
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
     * @param mixed $key
     * @return mixed
     */
    public function getProperty($key)
    {
        $this->assert()->isIndex($key, $this->description);

        return $this->description[$key];
    }

    public function getDescription(): array
    {
        return $this->description;
    }

    public function jsonSerialize(): array
    {
        return $this->getDescription();
    }
}
