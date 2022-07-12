<?php declare(strict_types=1);

// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2005 Ulf Wendel, Pierre-Alain Joye                |
// +----------------------------------------------------------------------+
// | This source file is subject to the New BSD license, That is bundled  |
// | with this package in the file LICENSE, and is available through      |
// | the world-wide-web at                                                |
// | http://www.opensource.org/licenses/bsd-license.php                   |
// | If you did not receive a copy of the new BSD license and are unable  |
// | to obtain it through the world-wide-web, please send a note to       |
// | pajoye@php.net, so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Ulf Wendel <ulf.wendel@phpdoc.de>                            |
// |         Pierre-Alain Joye <pajoye@php.net>                           |
// +----------------------------------------------------------------------+

/**
 * Integrated Template Extension - ITX
 * With this class you get the full power of the phplib template class.
 * You may have one file with blocks in it but you have as well one main file
 * and multiple files one for each block. This is quite usefull when you have
 * user configurable websites. Using blocks not in the main template allows
 * you to modify some parts of your layout easily.
 * Note that you can replace an existing block and add new blocks at runtime.
 * Adding new blocks means changing a variable placeholder to a block.
 * @author Ulf Wendel <uw@netuse.de>
 */
class HTML_Template_ITX extends HTML_Template_IT
{
    /**
     * Array with all warnings.
     */
    public array $warn = [];

    /**
     * Print warnings?
     */
    public bool $printWarning = false;

    /**
     * Call die() on warning?
     */
    public bool $haltOnWarning = false;

    /**
     * RegExp used to test for a valid blockname.
     */
    public string $checkblocknameRegExp = '';

    /**
     * Functionnameprefix used when searching function calls in the template.
     */
    public string $functionPrefix = 'func_';

    /**
     * Functionname RegExp.
     */
    public string $functionnameRegExp = '[_a-zA-Z]+[A-Za-z_0-9]*';

    /**
     * RegExp used to grep function calls in the template.
     * The variable gets set by the constructor.
     */
    public string $functionRegExp = '';

    /**
     * List of functions found in the template.
     */
    public array $functions = [];

    /**
     * List of callback functions specified by the user.
     */
    public array $callback = [];

    /**
     * Builds some complex regexps and calls the constructor
     * of the parent class.
     * Make sure that you call this constructor if you derive your own
     * template class from this one.
     * @throws ilTemplateException
     */
    public function __construct(string $root = '')
    {
        $this->checkblocknameRegExp = '@' . $this->blocknameRegExp . '@';
        $this->functionRegExp = '@' . $this->functionPrefix . '(' .
            $this->functionnameRegExp . ')\s*\(@sm';

        parent::__construct($root);
    }

    protected function init() : void
    {
        $this->free();
        $this->buildFunctionlist();
        $this->findBlocks($this->template);
        // we don't need it any more
        $this->template = '';
        $this->buildBlockvariablelist();
    }

    /**
     * Replaces an existing block with new content.
     * This function will replace a block of the template and all blocks
     * contained in the replaced block and add a new block insted, means
     * you can dynamically change your template.
     * Note that changing the template structure violates one of the IT[X]
     * development goals. I've tried to write a simple to use template engine
     * supporting blocks. In contrast to other systems IT[X] analyses the way
     * you've nested blocks and knows which block belongs into another block.
     * The nesting information helps to make the API short and simple. Replacing
     * blocks does not only mean that IT[X] has to update the nesting
     * information (relatively time consumpting task) but you have to make sure
     * that you do not get confused due to the template change itself.
     * @param bool $keep_content true if the new block inherits the content of the old block
     * @throws ilTemplateException
     */
    public function replaceBlock(string $block, string $template, bool $keep_content = false) : bool
    {
        if (!isset($this->blocklist[$block])) {
            throw new ilTemplateException("The block " . "'$block'" . " does not exist in the template and thus it can't be replaced.");
        }

        if ($template === '') {
            throw new ilTemplateException('No block content given.');
        }

        if ($keep_content) {
            $blockdata = $this->blockdata[$block];
        }

        // remove all kinds of links to the block / data of the block
        $this->removeBlockData($block);

        $template = "<!-- BEGIN $block -->" . $template . "<!-- END $block -->";
        $parents = $this->blockparents[$block];
        $this->findBlocks($template);
        $this->blockparents[$block] = $parents;

        // KLUDGE: rebuild the list for all block - could be done faster
        $this->buildBlockvariablelist();

        if ($keep_content) {
            $this->blockdata[$block] = $blockdata;
        }

        return true;
    }

    /**
     * Adds a block to the template changing a variable placeholder
     * to a block placeholder.
     * Add means "replace a variable placeholder by a new block".
     * This is different to PHPLibs templates. The function loads a
     * block, creates a handle for it and assigns it to a certain
     * variable placeholder. To to the same with PHPLibs templates you would
     * call set_file() to create the handle and parse() to assign the
     * parsed block to a variable. By this PHPLibs templates assume
     * that you tend to assign a block to more than one one placeholder.
     * To assign a parsed block to more than only the placeholder you specify
     * in this function you have to use a combination of getBlock()
     * and setVariable().
     * As no updates to cached data is necessary addBlock() and addBlockfile()
     * are rather "cheap" meaning quick operations.
     * The block content must not start with <!-- BEGIN blockname -->
     * and end with <!-- END blockname --> this would cause overhead and
     * produce an error.
     * @throws ilTemplateException
     */
    public function addBlock(string $placeholder, string $blockname, string $template) : bool
    {
        // Don't trust any user even if it's a programmer or yourself...
        if ($placeholder === '') {
            throw new ilTemplateException('No variable placeholder given.');
        }

        if ($blockname === '' ||
            !preg_match($this->checkblocknameRegExp, $blockname)) {
            throw new ilTemplateException("No or invalid blockname '$blockname' given.");
        }

        if ($template === '') {
            throw new ilTemplateException('No block content given.');
        }

        if (isset($this->blocklist[$blockname])) {
            throw new ilTemplateException('The block ' . $blockname . ' already exists.');
        }

        // find out where to insert the new block
        $parents = $this->findPlaceholderBlocks($placeholder);
        if (count($parents) === 0) {
            throw (new ilTemplateException("The variable placeholder" .
                " '$placeholder' was not found in the template."));
        }

        if (count($parents) > 1) {
            $msg = '';
            foreach ($parents as $index => $parent) {
                $msg .= (isset($parents[$index + 1])) ?
                    "$parent, " : $parent;
            }

            throw new ilTemplateException("The variable placeholder " . "'$placeholder'" . " must be unique, found in multiple blocks '$msg'.");
        }

        $template = "<!-- BEGIN $blockname -->" . $template . "<!-- END $blockname -->";
        $this->findBlocks($template);
        if ($this->flagBlocktrouble) {
            return false;    // findBlocks() already throws an exception
        }
        $this->blockinner[$parents[0]][] = $blockname;
        $this->blocklist[$parents[0]] = preg_replace(
            '@' . $this->openingDelimiter . $placeholder .
            $this->closingDelimiter . '@',
            $this->openingDelimiter . '__' . $blockname . '__' .
            $this->closingDelimiter,
            $this->blocklist[$parents[0]]
        );

        $this->deleteFromBlockvariablelist($parents[0], $placeholder);
        $this->updateBlockvariablelist($blockname);

        return true;
    }

    /**
     * Adds a block taken from a file to the template changing a variable
     * placeholder to a block placeholder.
     * @throws ilTemplateException
     */
    public function addBlockfile(string $placeholder, string $blockname, string $filename) : bool
    {
        return $this->addBlock($placeholder, $blockname, $this->getFile($filename));
    }

    /**
     * Recursively removes all data assiciated with a block, including all inner blocks
     */
    public function removeBlockData(string $block) : void
    {
        if (isset($this->blockinner[$block])) {
            foreach ($this->blockinner[$block] as $inner) {
                $this->removeBlockData($inner);
            }

            unset($this->blockinner[$block]);
        }

        unset(
            $this->blocklist[$block],
            $this->blockdata[$block],
            $this->blockvariables[$block],
            $this->touchedBlocks[$block]
        );
    }

    /**
     * Checks wheter a block exists.
     */
    public function blockExists(string $blockname) : bool
    {
        return isset($this->blocklist[$blockname]);
    }

    /**
     * Builds a functionlist from the template.
     */
    public function buildFunctionlist() : void
    {
        $this->functions = [];

        $template = $this->template;
        $num = 0;

        while (preg_match($this->functionRegExp, $template, $regs)) {
            $pos = strpos($template, $regs[0]);
            $template = substr($template, $pos + strlen($regs[0]));

            $head = $this->getValue($template, ')');
            $args = [];

            $search = $regs[0] . $head . ')';

            $replace = $this->openingDelimiter .
                '__function' . $num . '__' .
                $this->closingDelimiter;

            $this->template = str_replace($search, $replace, $this->template);
            $template = str_replace($search, $replace, $template);

            while ($head !== '' && $args2 = $this->getValue($head, ',')) {
                $arg2 = trim($args2);
                $args[] = ('"' === $arg2[0] || "'" === $arg2[0]) ?
                    substr($arg2, 1, -1) : $arg2;
                if ($arg2 === $head) {
                    break;
                }
                $head = substr($head, strlen($arg2) + 1);
            }

            $this->functions[$num++] = [
                'name' => $regs[1],
                'args' => $args
            ];
        }
    }

    /**
     * Truncates the given code from the first occurence of
     * $delimiter but ignores $delimiter enclosed by " or '.
     * @param array|string $delimiter
     */
    public function getValue(string $code, $delimiter) : string
    {
        if ($code === '') {
            return '';
        }

        if (!is_array($delimiter)) {
            $delimiter = [$delimiter => true];
        }

        $len = strlen($code);
        $enclosed = false;
        $enclosed_by = '';

        if (isset($delimiter[$code[0]])) {
            $i = 1;
        } else {
            for ($i = 0; $i < $len; ++$i) {
                $char = $code[$i];

                if (
                    ($char === '"' || $char === "'") &&
                    ($char === $enclosed_by || '' === $enclosed_by) &&
                    (0 === $i || ($i > 0 && '\\' !== $code[$i - 1]))
                ) {
                    if (!$enclosed) {
                        $enclosed_by = $char;
                    } else {
                        $enclosed_by = "";
                    }
                    $enclosed = !$enclosed;
                }

                if (!$enclosed && isset($delimiter[$char])) {
                    break;
                }
            }
        }

        return substr($code, 0, $i);
    }

    /**
     * Deletes one or many variables from the block variable list.
     * @param array|string $variables Name of one variable or array of variables
     *                                ( array ( name => true ) ) to be stripped.
     */
    public function deleteFromBlockvariablelist(string $block, $variables) : void
    {
        if (!is_array($variables)) {
            $variables = [$variables => true];
        }

        reset($this->blockvariables[$block]);
        foreach ($this->blockvariables[$block] as $varname => $val) {
            if (isset($variables[$varname])) {
                unset($this->blockvariables[$block][$varname]);
            }
        }
    }

    /**
     * Updates the variable list of a block.
     */
    public function updateBlockvariablelist(string $block) : void
    {
        preg_match_all(
            $this->variablesRegExp,
            $this->blocklist[$block],
            $regs
        );

        if (count($regs[1]) !== 0) {
            foreach ($regs[1] as $var) {
                $this->blockvariables[$block][$var] = true;
            }
        } else {
            $this->blockvariables[$block] = [];
        }

        // check if any inner blocks were found
        if (isset($this->blockinner[$block]) &&
            is_array($this->blockinner[$block]) &&
            count($this->blockinner[$block]) > 0
        ) {
            /*
             * loop through inner blocks, registering the variable
             * placeholders in each
             */
            foreach ($this->blockinner[$block] as $childBlock) {
                $this->updateBlockvariablelist($childBlock);
            }
        }
    }

    /**
     * Returns an array of blocknames where the given variable
     * placeholder is used.
     */
    public function findPlaceholderBlocks(string $variable) : array
    {
        $parents = [];
        reset($this->blocklist);
        foreach ($this->blocklist as $blockname => $content) {
            reset($this->blockvariables[$blockname]);
            foreach ($this->blockvariables[$blockname] as $varname => $val) {
                if ($variable === $varname) {
                    $parents[] = $blockname;
                }
            }
        }

        return $parents;
    }

    /**
     * Handles warnings, saves them to $warn and prints them or
     * calls die() depending on the flags
     */
    public function warning(string $message, string $file = '', int $line = 0) : void
    {
        $message = sprintf(
            'HTML_Template_ITX Warning: %s [File: %s, Line: %d]',
            $message,
            $file,
            $line
        );

        $this->warn[] = $message;

        if ($this->printWarning) {
            print $message;
        }

        if ($this->haltOnWarning) {
            die($message);
        }
    }
}
