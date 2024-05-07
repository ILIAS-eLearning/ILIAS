<?php

/**
 * This file is part of SebastianFeldmann\Git.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianFeldmann\Git;

use RuntimeException;

/**
 * Class CommitMessage
 *
 * @package SebastianFeldmann\Git
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/sebastianfeldmann/git
 * @since   Class available since Release 0.9.0
 */
class CommitMessage
{
    /**
     * Commit Message content
     *
     * This includes lines that are comments.
     *
     * @var string
     */
    private $rawContent;

    /**
     * Content split lines
     *
     * This includes lines that are comments.
     *
     * @var string[]
     */
    private $rawLines;

    /**
     * Amount of lines
     *
     * This includes lines that are comments
     *
     * @var int
     */
    private $rawLineCount;

    /**
     * The comment character
     *
     * @var string
     */
    private $commentCharacter;

    /**
     * All non comment lines
     *
     * @var string[]
     */
    private $contentLines;

    /**
     * Get the number of lines
     *
     * This excludes lines which are comments.
     *
     * @var int
     */
    private $contentLineCount;

    /**
     * Commit Message content
     *
     * This excludes lines that are comments.
     *
     * @var string
     */
    private $content;

    /**
     * CommitMessage constructor
     *
     * @param string $content
     * @param string $commentCharacter
     */
    public function __construct(string $content, string $commentCharacter = '#')
    {
        $this->rawContent       = $content;
        $this->rawLines         = empty($content) ? [] : preg_split("/\\r\\n|\\r|\\n/", $content);
        $this->rawLineCount     = count($this->rawLines);
        $this->commentCharacter = $commentCharacter;
        $this->contentLines     = $this->getContentLines($this->rawLines, $commentCharacter);
        $this->contentLineCount = count($this->contentLines);
        $this->content          = implode(PHP_EOL, $this->contentLines);
    }

    /**
     * Is message empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->content);
    }

    /**
     * Is this a fixup commit
     *
     * @return bool
     */
    public function isFixup(): bool
    {
        return strpos($this->rawContent, 'fixup!') === 0;
    }

    /**
     * Is this a squash commit
     *
     * @return bool
     */
    public function isSquash(): bool
    {
        return  strpos($this->rawContent, 'squash!') === 0;
    }

    /**
     * Get commit message content
     *
     * This excludes lines that are comments.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get complete commit message content
     *
     * This includes lines that are comments.
     *
     * @return string
     */
    public function getRawContent(): string
    {
        return $this->rawContent;
    }

    /**
     * Return all lines
     *
     * This includes lines that are comments.
     *
     * @return array<string>
     */
    public function getLines(): array
    {
        return $this->rawLines;
    }

    /**
     * Return line count
     *
     * This includes lines that are comments.
     *
     * @return int
     */
    public function getLineCount(): int
    {
        return $this->rawLineCount;
    }

    /**
     * Return content line count
     *
     * This doesn't includes lines that are comments.
     *
     * @return int
     */
    public function getContentLineCount(): int
    {
        return $this->contentLineCount;
    }

    /**
     * Get a specific line
     *
     * @param  int $index
     * @return string
     */
    public function getLine(int $index): string
    {
        return $this->rawLines[$index] ?? '';
    }

    /**
     * Get a specific content line
     *
     * @param  int $index
     * @return string
     */
    public function getContentLine(int $index): string
    {
        return $this->contentLines[$index] ?? '';
    }

    /**
     * Return first line
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->contentLines[0] ?? '';
    }

    /**
     * Return content from line nr. 3 to the last line
     *
     * @return string
     */
    public function getBody(): string
    {
        return implode(PHP_EOL, $this->getBodyLines());
    }

    /**
     * Return lines from line nr. 3 to the last line
     *
     * @return array<string>
     */
    public function getBodyLines(): array
    {
        return $this->contentLineCount < 3 ? [] : array_slice($this->contentLines, 2);
    }

    /**
     * Get the comment character
     *
     * Comment character defaults to '#'.
     *
     * @return string
     */
    public function getCommentCharacter(): string
    {
        return $this->commentCharacter;
    }

    /**
     * Get the lines that are not comments
     *
     * @param  array<string> $rawLines
     * @param  string        $commentCharacter
     * @return array<string>
     */
    private function getContentLines(array $rawLines, string $commentCharacter): array
    {
        $lines = [];

        foreach ($rawLines as $line) {
            // if we handle a comment line
            if (isset($line[0]) && $line[0] === $commentCharacter) {
                // check if we should ignore all following lines
                if (strpos($line, '------------------------ >8 ------------------------') !== false) {
                    break;
                }
                // or only the current one
                continue;
            }
            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * Create CommitMessage from file
     *
     * @param  string $path
     * @param  string $commentCharacter
     * @return \SebastianFeldmann\Git\CommitMessage
     */
    public static function createFromFile(string $path, $commentCharacter = '#'): CommitMessage
    {
        if (!file_exists($path)) {
            throw new RuntimeException('Commit message file not found');
        }

        return new CommitMessage(file_get_contents($path), $commentCharacter);
    }
}
