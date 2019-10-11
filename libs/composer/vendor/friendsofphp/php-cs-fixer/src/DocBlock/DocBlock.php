<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\DocBlock;

use PhpCsFixer\Preg;

/**
 * This class represents a docblock.
 *
 * It internally splits it up into "lines" that we can manipulate.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class DocBlock
{
    /**
     * The array of lines.
     *
     * @var Line[]
     */
    private $lines = [];

    /**
     * The array of annotations.
     *
     * @var null|Annotation[]
     */
    private $annotations;

    /**
     * Create a new docblock instance.
     *
     * @param string $content
     */
    public function __construct($content)
    {
        foreach (Preg::split('/([^\n\r]+\R*)/', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $line) {
            $this->lines[] = new Line($line);
        }
    }

    /**
     * Get the string representation of object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getContent();
    }

    /**
     * Get this docblock's lines.
     *
     * @return Line[]
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * Get a single line.
     *
     * @param int $pos
     *
     * @return null|Line
     */
    public function getLine($pos)
    {
        if (isset($this->lines[$pos])) {
            return $this->lines[$pos];
        }
    }

    /**
     * Get this docblock's annotations.
     *
     * @return Annotation[]
     */
    public function getAnnotations()
    {
        if (null === $this->annotations) {
            $this->annotations = [];
            $total = \count($this->lines);

            for ($index = 0; $index < $total; ++$index) {
                if ($this->lines[$index]->containsATag()) {
                    // get all the lines that make up the annotation
                    $lines = \array_slice($this->lines, $index, $this->findAnnotationLength($index), true);
                    $annotation = new Annotation($lines);
                    // move the index to the end of the annotation to avoid
                    // checking it again because we know the lines inside the
                    // current annotation cannot be part of another annotation
                    $index = $annotation->getEnd();
                    // add the current annotation to the list of annotations
                    $this->annotations[] = $annotation;
                }
            }
        }

        return $this->annotations;
    }

    /**
     * @param int $pos
     *
     * @return null|Annotation
     */
    public function getAnnotation($pos)
    {
        $annotations = $this->getAnnotations();

        if (isset($annotations[$pos])) {
            return $annotations[$pos];
        }
    }

    /**
     * Get specific types of annotations only.
     *
     * If none exist, we're returning an empty array.
     *
     * @param string|string[] $types
     *
     * @return Annotation[]
     */
    public function getAnnotationsOfType($types)
    {
        $annotations = [];
        $types = (array) $types;

        foreach ($this->getAnnotations() as $annotation) {
            $tag = $annotation->getTag()->getName();
            foreach ($types as $type) {
                if ($type === $tag) {
                    $annotations[] = $annotation;
                }
            }
        }

        return $annotations;
    }

    /**
     * Get the actual content of this docblock.
     *
     * @return string
     */
    public function getContent()
    {
        return implode('', $this->lines);
    }

    private function findAnnotationLength($start)
    {
        $index = $start;

        while ($line = $this->getLine(++$index)) {
            if ($line->containsATag()) {
                // we've 100% reached the end of the description if we get here
                break;
            }

            if (!$line->containsUsefulContent()) {
                // if next line is also non-useful, or contains a tag, then we're done here
                $next = $this->getLine($index + 1);
                if (null === $next || !$next->containsUsefulContent() || $next->containsATag()) {
                    break;
                }
                // otherwise, continue, the annotation must have contained a blank line in its description
            }
        }

        return $index - $start;
    }
}
