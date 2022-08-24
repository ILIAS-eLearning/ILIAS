<?php

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

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssOrderingElementList implements Iterator
{
    public static $objectInstanceCounter = 0;
    public $objectInstanceId;

    public const SOLUTION_IDENTIFIER_BUILD_MAX_TRIES = 1000;
    public const SOLUTION_IDENTIFIER_VALUE_INTERVAL = 1;
    public const SOLUTION_IDENTIFIER_START_VALUE = 0;

    public const RANDOM_IDENTIFIER_BUILD_MAX_TRIES = 1000;
    public const RANDOM_IDENTIFIER_RANGE_LOWER_BOUND = 1;
    public const RANDOM_IDENTIFIER_RANGE_UPPER_BOUND = 100000;

    public const FALLBACK_DEFAULT_ELEMENT_RANDOM_IDENTIFIER = 0;
    public const JS_ADDED_ELEMENTS_RANDOM_IDENTIFIER_START_VALUE = -1;
    public const JS_ADDED_ELEMENTS_RANDOM_IDENTIFIER_VALUE_INTERVAL = -1;

    public const IDENTIFIER_TYPE_SOLUTION = 'SolutionIds';
    public const IDENTIFIER_TYPE_RANDOM = 'RandomIds';

    /**
     * @var array[integer]
     */
    protected static $identifierRegistry = array(
        self::IDENTIFIER_TYPE_SOLUTION => array(),
        self::IDENTIFIER_TYPE_RANDOM => array()
    );

    /**
     * @var integer
     */
    protected $question_id;

    /**
     * @var array
     */
    protected $elements = array();

    /**
     * ilAssOrderingElementList constructor.
     * @param ilAssOrderingElement[] $elements
     */
    public function __construct(
        int $question_id = null,
        array $elements = []
    ) {
        $this->objectInstanceId = ++self::$objectInstanceCounter;

        $this->question_id = $question_id;
        $this->elements = $elements;
    }

    /**
     * clone list by additionally cloning the element objects
     */
    public function __clone()
    {
        $this->objectInstanceId = ++self::$objectInstanceCounter;

        $elements = array();

        foreach ($this as $key => $element) {
            $elements[$key] = clone $element;
        }

        $this->elements = $elements;
    }

    /**
     * @return ilAssOrderingElementList
     */
    public function getClone(): ilAssOrderingElementList
    {
        $that = clone $this;
        return $that;
    }

    /**
     * @return integer
     */
    public function getQuestionId(): ?int
    {
        return $this->question_id;
    }

    /**
     * @param integer $question_id
     */
    public function setQuestionId($question_id): void
    {
        $this->question_id = $question_id;
    }

    public function countElements(): int
    {
        return count($this->elements);
    }

    public function hasElements(): bool
    {
        return (bool) $this->countElements();
    }

    public function isFirstElementPosition($position): bool
    {
        return $position == 0;
    }

    public function isLastElementPosition($position): bool
    {
        return $position == ($this->countElements() - 1);
    }

    public function moveElementByPositions($currentPosition, $targetPosition): void
    {
        $movingElement = $this->getElementByPosition($currentPosition);
        $dodgingElement = $this->getElementByPosition($targetPosition);

        $elementList = new self();
        $elementList->setQuestionId($this->getQuestionId());

        foreach ($this as $element) {
            if ($element->getPosition() == $currentPosition) {
                $elementList->addElement($dodgingElement);
                continue;
            }

            if ($element->getPosition() == $targetPosition) {
                $elementList->addElement($movingElement);
                continue;
            }

            $elementList->addElement($element);
        }

        $dodgingElement->setPosition($currentPosition);
        $movingElement->setPosition($targetPosition);

        $this->setElements($elementList->getElements());
    }

    public function removeElement(ilAssOrderingElement $removeElement): void
    {
        $elementList = new self();
        $elementList->setQuestionId($this->getQuestionId());

        $positionCounter = 0;

        foreach ($this as $element) {
            if ($element->isSameElement($removeElement)) {
                continue;
            }

            $element->setPosition($positionCounter++);
            $elementList->addElement($element);
        }
    }

    /**
     * resets elements
     */
    public function resetElements(): void
    {
        $this->elements = array();
    }

    /**
     * @param $elements
     */
    public function setElements($elements): void
    {
        $this->resetElements();

        foreach ($elements as $element) {
            $this->addElement($element);
        }
    }

    /**
     * @return ilAssOrderingElement[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @return array
     */
    public function getRandomIdentifierIndexedElements(): array
    {
        return $this->getIndexedElements(self::IDENTIFIER_TYPE_RANDOM);
    }

    /**
     * @return array
     */
    public function getRandomIdentifierIndex(): array
    {
        return array_keys($this->getRandomIdentifierIndexedElements());
    }

    /**
     * @return array
     */
    public function getSolutionIdentifierIndexedElements(): array
    {
        return $this->getIndexedElements(self::IDENTIFIER_TYPE_SOLUTION);
    }

    /**
     * @return array
     */
    public function getSolutionIdentifierIndex(): array
    {
        return array_keys($this->getSolutionIdentifierIndexedElements());
    }

    /**
     * @return array
     */
    protected function getIndexedElements($identifierType): array
    {
        $elements = array();

        foreach ($this as $element) {
            $elements[$this->fetchIdentifier($element, $identifierType)] = $element;
        }

        return $elements;
    }

    /**
     * @param ilAssOrderingElement $element
     */
    public function addElement(ilAssOrderingElement $element): void
    {
        if ($this->hasValidIdentifiers($element)) {
            $this->registerIdentifiers($element);
        }

        $this->elements[] = $element;
    }

    /**
     * @param $randomIdentifier
     * @return ilAssOrderingElement
     */
    public function getElementByPosition($position): ?ilAssOrderingElement
    {
        if (isset($this->elements[$position])) {
            return $this->elements[$position];
        }

        return null;
    }

    /**
     * @param $position
     * @return bool
     */
    public function elementExistByPosition($position): bool
    {
        return ($this->getElementByPosition($position) !== null);
    }

    /**
     * @param $randomIdentifier
     * @return ilAssOrderingElement
     */
    public function getElementByRandomIdentifier($randomIdentifier): ?ilAssOrderingElement
    {
        foreach ($this as $element) {
            if ($element->getRandomIdentifier() != $randomIdentifier) {
                continue;
            }

            return $element;
        }

        return null;
    }

    /**
     * @param $randomIdentifier
     * @return bool
     */
    public function elementExistByRandomIdentifier($randomIdentifier): bool
    {
        return ($this->getElementByRandomIdentifier($randomIdentifier) !== null);
    }

    /**
     * @param $randomIdentifier
     * @return ilAssOrderingElement
     */
    public function getElementBySolutionIdentifier($solutionIdentifier): ?ilAssOrderingElement
    {
        foreach ($this as $element) {
            if ($element->getSolutionIdentifier() != $solutionIdentifier) {
                continue;
            }

            return $element;
        }
        return null;
    }

    /**
     * @param $solutionIdentifier
     * @return bool
     */
    public function elementExistBySolutionIdentifier($solutionIdentifier): bool
    {
        return ($this->getElementBySolutionIdentifier($solutionIdentifier) !== null);
    }

    /**
     * @return array
     */
    protected function getRegisteredSolutionIdentifiers(): array
    {
        return $this->getRegisteredIdentifiers(self::IDENTIFIER_TYPE_SOLUTION);
    }

    /**
     * @return array
     */
    protected function getRegisteredRandomIdentifiers(): array
    {
        return $this->getRegisteredIdentifiers(self::IDENTIFIER_TYPE_RANDOM);
    }

    /**
     * @param string $identifierType
     * @return array
     */
    protected function getRegisteredIdentifiers($identifierType): array
    {
        if (!isset(self::$identifierRegistry[$identifierType][$this->getQuestionId()])) {
            return array();
        }

        return self::$identifierRegistry[$identifierType][$this->getQuestionId()];
    }

    /**
     * @param ilAssOrderingElement $element
     * @return bool
     */
    protected function hasValidIdentifiers(ilAssOrderingElement $element): bool
    {
        $identifier = $this->fetchIdentifier($element, self::IDENTIFIER_TYPE_SOLUTION);

        if (!$this->isValidIdentifier(self::IDENTIFIER_TYPE_SOLUTION, $identifier)) {
            return false;
        }

        $identifier = $this->fetchIdentifier($element, self::IDENTIFIER_TYPE_RANDOM);

        if (!$this->isValidIdentifier(self::IDENTIFIER_TYPE_RANDOM, $identifier)) {
            return false;
        }

        return true;
    }

    /**
     * @param ilAssOrderingElement $element
     */
    public function ensureValidIdentifiers(ilAssOrderingElement $element): ilAssOrderingElement
    {
        //TODO: remove!
        $this->ensureValidIdentifier($element, self::IDENTIFIER_TYPE_SOLUTION);
        $this->ensureValidIdentifier($element, self::IDENTIFIER_TYPE_RANDOM);
        return $element;
    }

    /**
     * @param ilAssOrderingElement $element
     * @param string $identifierType
     */
    protected function ensureValidIdentifier(ilAssOrderingElement $element, $identifierType): void
    {
        $identifier = $this->fetchIdentifier($element, $identifierType);

        if (!$this->isValidIdentifier($identifierType, $identifier)) {
            $identifier = $this->buildIdentifier($identifierType);
            $this->populateIdentifier($element, $identifierType, $identifier);
            $this->registerIdentifier($element, $identifierType);
        }
    }

    /**
     * @param ilAssOrderingElement $element
     */
    protected function registerIdentifiers(ilAssOrderingElement $element): void
    {
        $this->registerIdentifier($element, self::IDENTIFIER_TYPE_SOLUTION);
        $this->registerIdentifier($element, self::IDENTIFIER_TYPE_RANDOM);
    }

    /**
     * @param ilAssOrderingElement $element
     * @param string $identifierType
     * @throws ilTestQuestionPoolException
     */
    protected function registerIdentifier(ilAssOrderingElement $element, $identifierType): void
    {
        if (!isset(self::$identifierRegistry[$identifierType][$this->getQuestionId()])) {
            self::$identifierRegistry[$identifierType][$this->getQuestionId()] = array();
        }

        $identifier = $this->fetchIdentifier($element, $identifierType);

        if (!in_array($identifier, self::$identifierRegistry[$identifierType][$this->getQuestionId()])) {
            self::$identifierRegistry[$identifierType][$this->getQuestionId()][] = $identifier;
        }
    }

    /**
     * @param ilAssOrderingElement $element
     * @param string $identifierType
     * @return bool
     * @throws ilTestQuestionPoolException
     */
    protected function isIdentifierRegistered(ilAssOrderingElement $element, $identifierType): bool
    {
        if (!isset(self::$identifierRegistry[$identifierType][$this->getQuestionId()])) {
            return false;
        }

        $identifier = $this->fetchIdentifier($element, $identifierType);

        if (!in_array($identifier, self::$identifierRegistry[$identifierType][$this->getQuestionId()])) {
            return false;
        }

        return true;
    }

    /**
     * @throws ilTestQuestionPoolException
     */
    protected function fetchIdentifier(ilAssOrderingElement $element, string $identifierType): ?int
    {
        if ($identifierType == self::IDENTIFIER_TYPE_SOLUTION) {
            return $element->getSolutionIdentifier();
        } else {
            return $element->getRandomIdentifier();
        }

        $this->throwUnknownIdentifierTypeException($identifierType);
    }

    /**
     * @param ilAssOrderingElement $element
     * @param string $identifierType
     * @param $identifier
     * @throws ilTestQuestionPoolException
     */
    protected function populateIdentifier(ilAssOrderingElement $element, $identifierType, $identifier): void
    {
        switch ($identifierType) {
            case self::IDENTIFIER_TYPE_SOLUTION: $element->setSolutionIdentifier($identifier);
                break;
            case self::IDENTIFIER_TYPE_RANDOM: $element->setRandomIdentifier($identifier);
                break;
            default: $this->throwUnknownIdentifierTypeException($identifierType);
        }
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    protected function isValidIdentifier($identifierType, $identifier)
    {
        switch ($identifierType) {
            case self::IDENTIFIER_TYPE_SOLUTION:
                return self::isValidSolutionIdentifier($identifier);

            case self::IDENTIFIER_TYPE_RANDOM:
                return self::isValidRandomIdentifier($identifier);
        }

        $this->throwUnknownIdentifierTypeException($identifierType);
    }

    protected function buildIdentifier($identifierType): int
    {
        switch ($identifierType) {
            case self::IDENTIFIER_TYPE_SOLUTION:
                return $this->buildSolutionIdentifier();
            default:
            case self::IDENTIFIER_TYPE_RANDOM:
                return $this->buildRandomIdentifier();
        }

        $this->throwUnknownIdentifierTypeException($identifierType);
    }

    /**
     * @param string $identifierType
     * @throws ilTestQuestionPoolException
     */
    protected function throwUnknownIdentifierTypeException($identifierType): void
    {
        throw new ilTestQuestionPoolException(
            "unknown identifier type given (type: $identifierType)"
        );
    }

    /**
     * @param string $identifierType
     * @throws ilTestQuestionPoolException
     */
    protected function throwCouldNotBuildRandomIdentifierException($maxTries): void
    {
        throw new ilTestQuestionPoolException(
            "could not build random identifier (max tries: $maxTries)"
        );
    }

    /**
     * @param string $randomIdentifier
     * @throws ilTestQuestionPoolException
     */
    protected function throwMissingReorderPositionException($randomIdentifier): void
    {
        throw new ilTestQuestionPoolException(
            "cannot reorder element due to missing position (random identifier: $randomIdentifier)"
        );
    }

    /**
     * @param array $randomIdentifiers
     * @throws ilTestQuestionPoolException
     */
    protected function throwUnknownRandomIdentifiersException($randomIdentifiers): void
    {
        throw new ilTestQuestionPoolException(
            'cannot reorder element due to one or more unknown random identifiers ' .
            '(' . implode(', ', $randomIdentifiers) . ')'
        );
    }

    /**
     * @return integer|null $lastSolutionIdentifier
     */
    protected function getLastSolutionIdentifier(): ?int
    {
        $lastSolutionIdentifier = null;

        foreach ($this->getRegisteredSolutionIdentifiers() as $registeredIdentifier) {
            if ($lastSolutionIdentifier > $registeredIdentifier) {
                continue;
            }

            $lastSolutionIdentifier = $registeredIdentifier;
        }

        return $lastSolutionIdentifier;
    }

    /**
     * @return integer|null $nextSolutionIdentifier
     */
    protected function buildSolutionIdentifier(): ?int
    {
        $lastSolutionIdentifier = $this->getLastSolutionIdentifier();

        if ($lastSolutionIdentifier === null) {
            return 0;
        }

        $nextSolutionIdentifier = $lastSolutionIdentifier + self::SOLUTION_IDENTIFIER_VALUE_INTERVAL;

        return $nextSolutionIdentifier;
    }

    /**
     * @return int $randomIdentifier
     * @throws ilTestQuestionPoolException
     */
    protected function buildRandomIdentifier(): int
    {
        $usedTriesCounter = 0;

        do {
            if ($usedTriesCounter >= self::RANDOM_IDENTIFIER_BUILD_MAX_TRIES) {
                $this->throwCouldNotBuildRandomIdentifierException(self::RANDOM_IDENTIFIER_BUILD_MAX_TRIES);
            }

            $usedTriesCounter++;

            $lowerBound = self::RANDOM_IDENTIFIER_RANGE_LOWER_BOUND;
            $upperBound = self::RANDOM_IDENTIFIER_RANGE_UPPER_BOUND;
            $randomIdentifier = mt_rand($lowerBound, $upperBound);

            $testElement = new ilAssOrderingElement();
            $testElement->setRandomIdentifier($randomIdentifier);
        } while ($this->isIdentifierRegistered($testElement, self::IDENTIFIER_TYPE_RANDOM));

        return $randomIdentifier;
    }

    public static function isValidSolutionIdentifier($identifier): bool
    {
        return is_numeric($identifier)
            && $identifier == (int) $identifier
            && (int) $identifier >= 0;
    }

    public static function isValidRandomIdentifier($identifier): bool
    {
        return is_numeric($identifier)
            && $identifier == (int) $identifier
            && (int) $identifier >= self::RANDOM_IDENTIFIER_RANGE_LOWER_BOUND
            && (int) $identifier <= self::RANDOM_IDENTIFIER_RANGE_UPPER_BOUND;
    }

    public static function isValidPosition($position): bool
    {
        return self::isValidSolutionIdentifier($position); // this was the position earlier
    }

    public static function isValidIndentation($indentation): bool
    {
        return self::isValidPosition($indentation); // horizontal position ^^
    }

    /**
     *
     */
    public function distributeNewRandomIdentifiers(): void
    {
        foreach ($this as $element) {
            $element->setRandomIdentifier($this->buildRandomIdentifier());
        }
    }

    /**
     * @param ilAssOrderingElementList $otherList
     */
    public function hasSameElementSetByRandomIdentifiers(self $otherList): bool
    {
        $numIntersectingElements = count(array_intersect(
            $otherList->getRandomIdentifierIndex(),
            $this->getRandomIdentifierIndex()
        ));

        return $numIntersectingElements == $this->countElements()
            && $numIntersectingElements == $otherList->countElements();
    }

    public function getParityTrueElementList(self $otherList): ilAssOrderingElementList
    {
        if (!$this->hasSameElementSetByRandomIdentifiers($otherList)) {
            throw new ilTestQuestionPoolException('cannot compare lists having different element sets');
        }

        $parityTrueElementList = new self();
        $parityTrueElementList->setQuestionId($this->getQuestionId());

        foreach ($this as $thisElement) {
            $otherElement = $otherList->getElementByRandomIdentifier(
                $thisElement->getRandomIdentifier()
            );

            if ($otherElement->getPosition() != $thisElement->getPosition()) {
                continue;
            }

            if ($otherElement->getIndentation() != $thisElement->getIndentation()) {
                continue;
            }

            $parityTrueElementList->addElement($thisElement);
        }

        return $parityTrueElementList;
    }

    /**
     * @param $randomIdentifiers
     * @throws ilTestQuestionPoolException
     */
    public function reorderByRandomIdentifiers($randomIdentifiers): void
    {
        $positionsMap = array_flip(array_values($randomIdentifiers));

        $orderedElements = array();

        foreach ($this as $element) {
            if (!isset($positionsMap[$element->getRandomIdentifier()])) {
                $this->throwMissingReorderPositionException($element->getRandomIdentifier());
            }

            $position = $positionsMap[$element->getRandomIdentifier()];
            unset($positionsMap[$element->getRandomIdentifier()]);

            $element->setPosition($position);
            $orderedElements[$position] = $element;
        }

        if (count($positionsMap)) {
            $this->throwUnknownRandomIdentifiersException(array_keys($positionsMap));
        }

        ksort($orderedElements);

        $this->setElements(array_values($orderedElements));
    }

    /**
     * resets the indentation to level 0 for all elements in list
     */
    public function resetElementsIndentations(): void
    {
        foreach ($this as $element) {
            $element->setIndentation(0);
        }
    }

    /**
     * @param ilAssOrderingElementList $otherElementList
     * @return $differenceElementList ilAssOrderingElementList
     */
    public function getDifferenceElementList(self $otherElementList): ilAssOrderingElementList
    {
        $differenceRandomIdentifierIndex = $this->getDifferenceRandomIdentifierIndex($otherElementList);

        $differenceElementList = new self();
        $differenceElementList->setQuestionId($this->getQuestionId());

        foreach ($differenceRandomIdentifierIndex as $randomIdentifier) {
            $element = $this->getElementByRandomIdentifier($randomIdentifier);
            $differenceElementList->addElement($element);
        }

        return $differenceElementList;
    }

    /**
     * @param ilAssOrderingElementList $other
     * @return array
     */
    protected function getDifferenceRandomIdentifierIndex(self $otherElementList): array
    {
        $differenceRandomIdentifierIndex = array_diff(
            $this->getRandomIdentifierIndex(),
            $otherElementList->getRandomIdentifierIndex()
        );

        return $differenceRandomIdentifierIndex;
    }

    /**
     * @param ilAssOrderingElementList $otherList
     */
    public function completeContentsFromElementList(self $otherList): void
    {
        foreach ($this as $thisElement) {
            if (!$otherList->elementExistByRandomIdentifier($thisElement->getRandomIdentifier())) {
                continue;
            }

            $otherElement = $otherList->getElementByRandomIdentifier(
                $thisElement->getRandomIdentifier()
            );

            $thisElement->setContent($otherElement->getContent());
        }
    }

    /**
     * @return ilAssOrderingElement
     */
    public function current(): ilAssOrderingElement
    {
        return current($this->elements);
    }

    /**
     * @return ilAssOrderingElement|false
     */
    public function next()
    {
        return next($this->elements);
    }

    /**
     * @return integer|bool
     */
    public function key()
    {
        return key($this->elements);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return ($this->key() !== null);
    }

    /**
     * @return ilAssOrderingElement|false
     */
    public function rewind()
    {
        return reset($this->elements);
    }

    /**
     * @return ilAssOrderingElement
     */
    public static function getFallbackDefaultElement(): ilAssOrderingElement
    {
        $element = new ilAssOrderingElement();
        $element->setRandomIdentifier(self::FALLBACK_DEFAULT_ELEMENT_RANDOM_IDENTIFIER);

        return $element;
    }

    //TODO: remove this (it's __construct, actually...)
    public static function buildInstance(int $question_id, array $elements = []): ilAssOrderingElementList
    {
        $elementList = new self();

        $elementList->setQuestionId($question_id);
        $elementList->setElements($elements);

        return $elementList;
    }

    public function getHash(): string
    {
        $items = array();

        foreach ($this as $element) {
            $items[] = implode(':', array(
                $element->getSolutionIdentifier(),
                $element->getRandomIdentifier(),
                $element->getIndentation()
            ));
        }

        return md5(serialize($items));
    }

    /**
     * @param ilAssOrderingElement[];
     */
    public function withElements(array $elements): self
    {
        $clone = clone $this;
        $clone->elements = $elements;
        return $clone;
    }

    public function withQuestionId(int $question_id): self
    {
        $clone = clone $this;
        $clone->question_id = $question_id;
        return $clone;
    }
}
