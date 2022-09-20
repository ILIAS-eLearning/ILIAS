<?php

declare(strict_types=1);

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

require_once __DIR__ . '/../../exceptions/class.ilTemplateException.php';

/**
 * Integrated Template - IT
 * Well there's not much to say about it. I needed a template class that
 * supports a single template file with multiple (nested) blocks inside and
 * a simple block API.
 * The Isotemplate API is somewhat tricky for a beginner although it is the best
 * one you can build. template::parse() [phplib template = Isotemplate] requests
 * you to name a source and a target where the current block gets parsed into.
 * Source and target can be block names or even handler names. This API gives you
 * a maximum of fexibility but you always have to know what you do which is
 * quite unusual for php skripter like me.
 * I noticed that I do not any control on which block gets parsed into which one.
 * If all blocks are within one file, the script knows how they are nested and in
 * which way you have to parse them. IT knows that inner1 is a child of block2, there's
 * no need to tell him about this.
 * <table border>
 *   <tr>
 *     <td colspan=2>
 *       __global__
 *       <p>
 *       (hidden and automatically added)
 *     </td>
 *   </tr>
 *   <tr>
 *     <td>block1</td>
 *     <td>
 *       <table border>
 *         <tr>
 *           <td colspan=2>block2</td>
 *         </tr>
 *         <tr>
 *           <td>inner1</td>
 *           <td>inner2</td>
 *         </tr>
 *       </table>
 *     </td>
 *   </tr>
 * </table>
 * To add content to block1 you simply type:
 * <code>$tpl->setCurrentBlock("block1");</code>
 * and repeat this as often as needed:
 * <code>
 *   $tpl->setVariable(...);
 *   $tpl->parseCurrentBlock();
 * </code>
 * To add content to block2 you would type something like:
 * <code>
 * $tpl->setCurrentBlock("inner1");
 * $tpl->setVariable(...);
 * $tpl->parseCurrentBlock();
 * $tpl->setVariable(...);
 * $tpl->parseCurrentBlock();
 * $tpl->parse("block1");
 * </code>
 * This will result in one repition of block1 which contains two repitions
 * of inner1. inner2 will be removed if $removeEmptyBlock is set to true which is the default.
 * Usage:
 * <code>
 * $tpl = new HTML_Template_IT( [string filerootdir] );
 * // load a template or set it with setTemplate()
 * $tpl->loadTemplatefile( string filename [, boolean removeUnknownVariables, boolean removeEmptyBlocks] )
 * // set "global" Variables meaning variables not beeing within a (inner) block
 * $tpl->setVariable( string variablename, mixed value );
 * // like with the Isotemplates there's a second way to use setVariable()
 * $tpl->setVariable( array ( string varname => mixed value ) );
 * // Let's use any block, even a deeply nested one
 * $tpl->setCurrentBlock( string blockname );
 * // repeat this as often as you need it.
 * $tpl->setVariable( array ( string varname => mixed value ) );
 * $tpl->parseCurrentBlock();
 * // get the parsed template or print it: $tpl->show()
 * $tpl->get();
 * </code>
 * @author Ulf Wendel <uw@netuse.de>
 */
class HTML_Template_IT
{
    public const IT_OK = 1;
    public const IT_ERROR = -1;
    public const IT_TPL_NOT_FOUND = -2;
    public const IT_BLOCK_NOT_FOUND = -3;
    public const IT_BLOCK_DUPLICATE = -4;
    public const IT_UNKNOWN_OPTION = -6;
    public const IT_DEFAULT_BLOCK = '__global__';

    /**
     * Contains the error objects.
     */
    public array $err = [];

    /**
     * Clear cache on get()?
     */
    public bool $clearCache = false;

    /**
     * First character of a variable placeholder ( _{_VARIABLE} ).
     */
    public string $openingDelimiter = '{';

    /**
     * Last character of a variable placeholder ( {VARIABLE_}_ ).
     */
    public string $closingDelimiter = '}';

    /**
     * RegExp matching a block in the template.
     * Per default "sm" is used as the regexp modifier, "i" is missing.
     * That means a case sensitive search is done.
     */
    public string $blocknameRegExp = '[\.0-9A-Za-z_-]+';

    /**
     * RegExp matching a variable placeholder in the template.
     * Per default "sm" is used as the regexp modifier, "i" is missing.
     * That means a case sensitive search is done.
     */
    public string $variablenameRegExp = '[\.0-9A-Za-z_-]+';

    /**
     * RegExp used to find variable placeholder, filled by the constructor.
     * Looks somewhat like @(delimiter varname delimiter).
     */
    public string $variablesRegExp = '';

    /**
     * RegExp used to strip unused variable placeholder.
     */
    public string $removeVariablesRegExp = '';

    /**
     * Controls the handling of unknown variables, default is remove.
     */
    public bool $removeUnknownVariables = true;

    /**
     * Controls the handling of empty blocks, default is remove.
     */
    public bool $removeEmptyBlocks = true;

    /**
     * RegExp used to find blocks an their content, filled by the constructor.
     */
    public string $blockRegExp = '';

    /**
     * Name of the current block.
     */
    public string $currentBlock = self::IT_DEFAULT_BLOCK;

    /**
     * Content of the template.
     */
    public string $template = '';

    /**
     * Array of all blocks and their content.
     */
    public array $blocklist = [];

    /**
     * Array with the parsed content of a block.
     */
    public array $blockdata = [];

    /**
     * Array of variables in a block.
     */
    public array $blockvariables = [];

    /**
     * Array of block parents.
     */
    public array $blockparents = [];

    /**
     * Array of inner blocks of a block.
     */
    public array $blockinner = [];

    /**
     * List of blocks to preverse even if they are "empty".
     * This is something special. Sometimes you have blocks that
     * should be preserved although they are empty (no placeholder replaced).
     * Think of a shopping basket. If it's empty you have to drop a message to
     * the user. If it's filled you have to show the contents of
     * the shopping baseket. Now where do you place the message that the basket
     * is empty? It's no good idea to place it in you applications as customers
     * tend to like unecessary minor text changes. Having another template file
     * for an empty basket means that it's very likely that one fine day
     * the filled and empty basket templates have different layout. I decided
     * to introduce blocks that to not contain any placeholder but only
     * text such as the message "Your shopping basked is empty".
     * Now if there is no replacement done in such a block the block will
     * be recognized as "empty" and by default ($removeEmptyBlocks = true) be
     * stripped off. To avoid thisyou can now call touchBlock() to avoid this.
     * The array $touchedBlocks stores a list of touched block which must not
     * be removed even if they are empty.
     */
    public array $touchedBlocks = [];

    /**
     * Variable cache.
     * Variables get cached before any replacement is done.
     * Advantage: empty blocks can be removed automatically.
     * Disadvantage: might take some more memory
     */
    public array $variableCache = [];

    /**
     * Clear the variable cache on parse?
     * If you're not an expert just leave the default false.
     * True reduces memory consumption somewhat if you tend to
     * add lots of values for unknown placeholder.
     */
    public bool $clearCacheOnParse = false;

    /**
     * Root directory for all file operations.
     * The string gets prefixed to all filenames given.
     */
    public string $fileRoot = '';

    /**
     * Internal flag indicating that a blockname was used multiple times.
     */
    public bool $flagBlocktrouble = false;

    /**
     * Flag indicating that the global block was parsed.
     */
    public bool $flagGlobalParsed = false;

    /**
     * EXPERIMENTAL! FIXME!
     * Flag indication that a template gets cached.
     * Complex templates require some times to be preparsed
     * before the replacement can take place. Often I use
     * one template file over and over again but I don't know
     * before that I will use the same template file again.
     * Now IT could notice this and skip the preparse.
     */
    public bool $flagCacheTemplatefile = true;

    /**
     * EXPERIMENTAL! FIXME!
     */
    public string $lastTemplatefile = '';

    /**
     * $_options['preserve_data'] Whether to substitute variables and remove
     * empty placeholders in data passed through setVariable
     * (see also bugs #20199, #21951).
     * $_options['use_preg'] Whether to use preg_replace instead of
     * str_replace in parse()
     * (this is a backwards compatibility feature, see also bugs #21951, #20392)
     */
    public array $_options = [
        'preserve_data' => false,
        'use_preg' => true
    ];

    /**
     * Holds the real template file name.
     */
    protected string $real_filename = '';

    /**
     * Builds some complex regular expressions and optinally sets the
     * file root directory.
     * Make sure that you call this constructor if you derive your template
     * class from this one.
     * @param string   $root    File root directory, prefix for all filenames given to the object.
     * @param string[] $options array of options.
     * @throws ilTemplateException
     */
    public function __construct(string $root = '', array $options = null)
    {
        if (!is_null($options)) {
            $this->setOptions($options);
        }
        $this->variablesRegExp = '@' . $this->openingDelimiter .
            '(' . $this->variablenameRegExp . ')' .
            $this->closingDelimiter . '@sm';
        $this->removeVariablesRegExp = '@' . $this->openingDelimiter .
            "\s*(" . $this->variablenameRegExp .
            ")\s*" . $this->closingDelimiter . '@sm';

        $this->blockRegExp = '@<!--\s+BEGIN\s+(' . $this->blocknameRegExp .
            ')\s+-->(.*)<!--\s+END\s+\1\s+-->@sm';

        $this->setRoot($root);
    }

    /**
     * Sets the option for the template class
     * @param mixed $value
     * @throws ilTemplateException
     */
    public function setOption(string $option, $value): int
    {
        if (array_key_exists($option, $this->_options)) {
            $this->_options[$option] = $value;
            return self::IT_OK;
        }

        throw new ilTemplateException($this->errorMessage(self::IT_UNKNOWN_OPTION) . ": '$option'");
    }

    /**
     * Sets the options for the template class
     * @param string[] $options
     * @throws ilTemplateException
     */
    public function setOptions(array $options): int
    {
        foreach ($options as $option => $value) {
            $this->setOption($option, $value);
        }

        return self::IT_OK;
    }

    /**
     * Print a certain block with all replacements done.
     * @throws ilTemplateException
     */
    public function show(string $block = self::IT_DEFAULT_BLOCK): void
    {
        print $this->get($block);
    }

    /**
     * Returns a block with all replacements done.
     * @throws ilTemplateException
     */
    public function get(string $block = self::IT_DEFAULT_BLOCK): string
    {
        if ($block === self::IT_DEFAULT_BLOCK && !$this->flagGlobalParsed) {
            $this->parse();
        }

        if (!isset($this->blocklist[$block])) {
            throw new ilTemplateException($this->errorMessage(self::IT_BLOCK_NOT_FOUND) . '"' . $block . "'");
        }

        if (isset($this->blockdata[$block])) {
            $ret = $this->blockdata[$block];
            if ($this->clearCache) {
                unset($this->blockdata[$block]);
            }
            if ($this->_options['preserve_data']) {
                $ret = str_replace(
                    $this->openingDelimiter .
                    '%preserved%' . $this->closingDelimiter,
                    $this->openingDelimiter,
                    $ret
                );
            }
            return $ret;
        }

        return '';
    }

    /**
     * Parses the given block.
     * @param string    name of the block to be parsed
     * @access   public
     * @throws   ilTemplateException
     * @see      parseCurrentBlock()
     */
    public function parse(string $block = self::IT_DEFAULT_BLOCK, bool $flag_recursion = false): bool
    {
        static $regs, $values;

        if (!isset($this->blocklist[$block])) {
            throw new ilTemplateException($this->errorMessage(self::IT_BLOCK_NOT_FOUND) . '"' . $block . "'");
        }

        if (self::IT_DEFAULT_BLOCK === $block) {
            $this->flagGlobalParsed = true;
        }

        if (!$flag_recursion) {
            $regs = [];
            $values = [];
        }
        $outer = $this->blocklist[$block];
        $empty = true;

        if ($this->clearCacheOnParse) {
            foreach ($this->variableCache as $name => $value) {
                $regs[] = $this->openingDelimiter .
                    $name . $this->closingDelimiter;
                $values[] = $value;
                $empty = false;
            }
            $this->variableCache = [];
        } else {
            foreach ($this->blockvariables[$block] as $allowedvar => $v) {
                if (isset($this->variableCache[$allowedvar])) {
                    $regs[] = $this->openingDelimiter .
                        $allowedvar . $this->closingDelimiter;
                    $values[] = $this->variableCache[$allowedvar];
                    unset($this->variableCache[$allowedvar]);
                    $empty = false;
                }
            }
        }

        if (isset($this->blockinner[$block])) {
            foreach ($this->blockinner[$block] as $k => $innerblock) {
                $this->parse($innerblock, true);
                if ($this->blockdata[$innerblock] !== '') {
                    $empty = false;
                }

                $placeholder = $this->openingDelimiter . "__" .
                    $innerblock . "__" . $this->closingDelimiter;
                $outer = str_replace(
                    $placeholder,
                    $this->blockdata[$innerblock],
                    $outer
                );
                $this->blockdata[$innerblock] = "";
            }
        }

        if (!$flag_recursion && 0 !== count($values)) {
            if ($this->_options['use_preg']) {
                $regs = array_map(
                    [
                        &$this,
                        '_addPregDelimiters'
                    ],
                    $regs
                );
                $funcReplace = 'preg_replace';
            } else {
                $funcReplace = 'str_replace';
            }

            if ($this->_options['preserve_data']) {
                $values = array_map(
                    [&$this, '_preserveOpeningDelimiter'],
                    $values
                );
            }

            $outer = $funcReplace($regs, $values, $outer);

            if ($this->removeUnknownVariables) {
                $outer = preg_replace($this->removeVariablesRegExp, "", $outer);
            }
        }

        if ($empty) {
            if (!$this->removeEmptyBlocks) {
                $this->blockdata[$block] .= $outer;
            } elseif (isset($this->touchedBlocks[$block])) {
                $this->blockdata[$block] .= $outer;
                unset($this->touchedBlocks[$block]);
            }
        } elseif (empty($this->blockdata[$block])) {
            $this->blockdata[$block] = $outer;
        } else {
            $this->blockdata[$block] .= $outer;
        }

        return $empty;
    }

    /**
     * Parses the current block
     * @throws ilTemplateException
     */
    public function parseCurrentBlock(): bool
    {
        return $this->parse($this->currentBlock);
    }

    /**
     * Sets a variable value.
     * The function can be used eighter like setVariable( "varname", "value")
     * or with one array $variables["varname"] = "value"
     * given setVariable($variables) quite like phplib templates set_var().
     * @param string|array $variable string with the variable name or an array
     *                               %variables["varname"] = "value"
     * @param mixed        $value    value of the variable or empty if $variable
     *                               is an array.
     */
    public function setVariable($variable, $value = ''): void
    {
        if (is_array($variable)) {
            $this->variableCache = array_merge(
                $this->variableCache,
                $variable
            );
        } else {
            $this->variableCache[$variable] = $value;
        }
    }

    /**
     * Sets the name of the current block that is the block where variables
     * are added.
     * @throws ilTemplateException
     */
    public function setCurrentBlock(string $block = self::IT_DEFAULT_BLOCK): bool
    {
        if (!isset($this->blocklist[$block])) {
            throw new ilTemplateException($this->errorMessage(self::IT_BLOCK_NOT_FOUND) . '"' . $block . "'");
        }

        $this->currentBlock = $block;

        return true;
    }

    /**
     * Preserves an empty block even if removeEmptyBlocks is true.
     * @throws ilTemplateException
     */
    public function touchBlock(string $block): bool
    {
        if (!isset($this->blocklist[$block])) {
            throw new ilTemplateException($this->errorMessage(self::IT_BLOCK_NOT_FOUND) . '"' . $block . "'");
        }

        $this->touchedBlocks[$block] = true;

        return true;
    }

    /**
     * Clears all datafields of the object and rebuild the internal blocklist
     * LoadTemplatefile() and setTemplate() automatically call this function
     * when a new template is given. Don't use this function
     * unless you know what you're doing.
     * @throws ilTemplateException
     */
    protected function init(): void
    {
        $this->free();
        $blocks = ilGlobalCache::getInstance(ilGlobalCache::COMP_TPL_BLOCKS);

        if ($blockdata = $blocks->get($this->real_filename)) {
            $this->blockdata = $blockdata['blockdata'];
            $this->blocklist = $blockdata['blocklist'];
        } else {
            ilGlobalCache::log('have to build blocks...', ilGlobalCacheSettings::LOG_LEVEL_FORCED);
            $this->findBlocks($this->template);
            $blockdata['blockdata'] = $this->blockdata;
            $blockdata['blocklist'] = $this->blocklist;
            $blocks->set($this->real_filename, $blockdata, 60);
        }

        // we don't need it any more
        $this->template = '';

        $variables = ilGlobalCache::getInstance(ilGlobalCache::COMP_TPL_VARIABLES);
        if ($blockvariables = $variables->get($this->real_filename)) {
            $this->blockvariables = $blockvariables;
        } else {
            $this->buildBlockvariablelist();
            $variables->set($this->real_filename, $this->blockvariables, 60);
        }
    }

    /**
     * Clears all datafields of the object.
     * Don't use this function unless you know what you're doing.
     */
    public function free(): void
    {
        $this->err = [];

        $this->currentBlock = self::IT_DEFAULT_BLOCK;

        $this->variableCache = [];
        $this->blocklist = [];
        $this->touchedBlocks = [];

        $this->flagBlocktrouble = false;
        $this->flagGlobalParsed = false;
    }

    /**
     * Sets the template.
     * You can eighter load a template file from disk with
     * LoadTemplatefile() or set the template manually using this function.
     * @throws ilTemplateException
     */
    public function setTemplate(
        string $template,
        bool $removeUnknownVariables = true,
        bool $removeEmptyBlocks = true
    ): bool {
        $this->removeUnknownVariables = $removeUnknownVariables;
        $this->removeEmptyBlocks = $removeEmptyBlocks;

        if ($template === '' && $this->flagCacheTemplatefile) {
            $this->variableCache = [];
            $this->blockdata = [];
            $this->touchedBlocks = [];
            $this->currentBlock = self::IT_DEFAULT_BLOCK;
        } else {
            $this->template =
                '<!-- BEGIN ' . self::IT_DEFAULT_BLOCK . ' -->' .
                $template .
                '<!-- END ' . self::IT_DEFAULT_BLOCK . ' -->';
            $this->init();
        }

        if ($this->flagBlocktrouble) {
            return false;
        }

        return true;
    }

    /**
     * Reads a template file from the disk.
     * @throws ilTemplateException
     */
    public function loadTemplatefile(
        string $filename,
        bool $removeUnknownVariables = true,
        bool $removeEmptyBlocks = true
    ): bool {
        $template = '';
        if (!$this->flagCacheTemplatefile ||
            $this->lastTemplatefile !== $filename
        ) {
            $template = $this->getFile($filename);
        }
        $this->lastTemplatefile = $filename;

        return $template !== '' && $this->setTemplate(
            $template,
            $removeUnknownVariables,
            $removeEmptyBlocks
        );
    }

    /**
     * Sets the file root. The file root gets prefixed to all filenames passed
     * to the object.
     * Make sure that you override this function when using the class
     * on windows.
     */
    public function setRoot(string $root): void
    {
        if ($root !== '' && substr($root, -1) !== '/') {
            $root .= '/';
        }

        $this->fileRoot = $root;
    }

    /**
     * Build a list of all variables within of a block
     */
    public function buildBlockvariablelist(): void
    {
        foreach ($this->blocklist as $name => $content) {
            preg_match_all($this->variablesRegExp, $content, $regs);

            if (count($regs[1]) !== 0) {
                foreach ($regs[1] as $var) {
                    $this->blockvariables[$name][$var] = true;
                }
            } else {
                $this->blockvariables[$name] = [];
            }
        }
    }

    /**
     * Recusively builds a list of all blocks within the template.
     * @throws ilTemplateException
     */
    public function findBlocks(string $string): array
    {
        $blocklist = [];
        if (preg_match_all($this->blockRegExp, $string, $regs, PREG_SET_ORDER)) {
            foreach ($regs as $match) {
                $blockname = $match[1];
                $blockcontent = $match[2];

                if (isset($this->blocklist[$blockname])) {
                    throw new ilTemplateException($this->errorMessage(self::IT_BLOCK_DUPLICATE, $blockname));
                }

                $this->blocklist[$blockname] = $blockcontent;
                $this->blockdata[$blockname] = "";

                $blocklist[] = $blockname;

                $inner = $this->findBlocks($blockcontent);
                foreach ($inner as $name) {
                    $pattern = sprintf(
                        '@<!--\s+BEGIN\s+%s\s+-->(.*)<!--\s+END\s+%s\s+-->@sm',
                        $name,
                        $name
                    );

                    $this->blocklist[$blockname] = preg_replace(
                        $pattern,
                        $this->openingDelimiter .
                        '__' . $name . '__' .
                        $this->closingDelimiter,
                        $this->blocklist[$blockname]
                    );
                    $this->blockinner[$blockname][] = $name;
                    $this->blockparents[$name] = $blockname;
                }
            }
        }

        return $blocklist;
    }

    /**
     * Reads a file from disk and returns its content.
     * @throws ilTemplateException
     */
    public function getFile(string $filename): string
    {
        if ($filename[0] === '/' && substr($this->fileRoot, -1) === '/') {
            $filename = substr($filename, 1);
        }

        $filename = $this->fileRoot . $filename;

        $this->real_filename = $filename;
        $ilGlobalCache = ilGlobalCache::getInstance(ilGlobalCache::COMP_TEMPLATE);
        if (!$content = $ilGlobalCache->get($filename)) {
            if (!($fh = @fopen($filename, 'rb'))) {
                throw new ilTemplateException($this->errorMessage(self::IT_TPL_NOT_FOUND) . ': "' . $filename . '"');
            }

            $fsize = filesize($filename);
            if ($fsize < 1) {
                fclose($fh);
                return '';
            }

            $content = fread($fh, $fsize);
            $ilGlobalCache->set($filename, $content, 60);
            fclose($fh);
        }

        return $content;
    }

    /**
     * Adds delimiters to a string, so it can be used as a pattern
     * in preg_* functions
     */
    public function _addPregDelimiters(string $str): string
    {
        return '@' . $str . '@';
    }

    /**
     * Replaces an opening delimiter by a special string.
     */
    public function _preserveOpeningDelimiter(string $str): string
    {
        return (false === strpos($str, $this->openingDelimiter)) ?
            $str :
            str_replace(
                $this->openingDelimiter,
                $this->openingDelimiter .
                '%preserved%' . $this->closingDelimiter,
                $str
            );
    }

    /**
     * Return a textual error message for a IT error code
     */
    public function errorMessage(int $value, string $blockname = ''): string
    {
        static $errorMessages;
        if (!isset($errorMessages)) {
            $errorMessages = [
                self::IT_OK => '',
                self::IT_ERROR => 'unknown error',
                self::IT_TPL_NOT_FOUND => 'Cannot read the template file',
                self::IT_BLOCK_NOT_FOUND => 'Cannot find this block',
                self::IT_BLOCK_DUPLICATE => 'The name of a block must be' .
                    ' uniquewithin a template.' .
                    ' Found "' . $blockname . '" twice.' .
                    'Unpredictable results ' .
                    'may appear.',
                self::IT_UNKNOWN_OPTION => 'Unknown option'
            ];
        }

        return $errorMessages[$value] ?? $errorMessages[self::IT_ERROR];
    }
}
