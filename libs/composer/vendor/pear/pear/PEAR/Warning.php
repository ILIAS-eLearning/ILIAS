<?php
// +----------------------------------------------------------------------+
// | PEAR_Warning                                                         |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The PEAR Group                                    |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Greg Beaver <cellog@php.net>                                |
// +----------------------------------------------------------------------+
//
require_once 'PEAR/Exception.php';

/**
 * Exception class for internal PEAR_Warning exceptions
 * @package PEAR
 */
class PEAR_WarningException extends PEAR_Exception {}

interface PEAR_WarningInterface
{
    /**
     * Get the severity of this warning ('warning', 'notice')
     * @return string
     */
    public function getLevel();
}

/**
 * Warning mechanism for PEAR PHP5-only packages.
 *
 * For users:
 *
 * Unlike PEAR_ErrorStack, PEAR_Warning is designed to be used on a transactional basis.
 *
 * <code>
 * <?php
 * require_once 'PEAR/Warning.php';
 * require_once 'PEAR/Exception.php';
 * class Mypackage_Exception extends PEAR_Exception {}
 * PEAR_Warning::begin();
 * $c = new Somepackage;
 * $c->doSomethingComplex();
 * if (PEAR_Warning::hasWarnings()) {
 *     $warnings = PEAR_Warning::end();
 *     throw new Mypackage_Exception('unclean doSomethingComplex', $warnings);
 * } else {
 *     $c->doSomethingElse();
 * }
 * ?>
 * </code>
 *
 * Only warnings that occur between ::begin() and ::end() will be processed.  Remember,
 * a warning is a non-fatal error, exceptions will be used for unrecoverable errors in
 * all PEAR packages, and you should follow this model to be safe!
 * 
 * For developers:
 *
 * This class can be used globally or locally.  For global use, a
 * series of static methods have been provided.  The class is designed
 * for lazy loading, and so the following code will work, and increase
 * efficiency on production servers:
 *
 * <code>
 * <?php
 * if (class_exists('PEAR_Warning')) {
 *     PEAR_Warning::add(1, 'mypackage', 'possible mis-spelling of something');
 * }
 * ?>
 * </code>
 *
 * This means that PEAR_Warning can literally be used without the need for
 * require_once 'PEAR/Warning.php';!
 *
 * You can also pass in an exception class as a warning
 *
 * <code>
 * <?php
 * class MyPackage_Warning extends PEAR_Exception {}
 * PEAR_Warning::add(new MyPackage_Warning('some info'));
 * ?>
 * </code>
 *
 * An interface is provided to allow for severity differentiation
 *
 * <code>
 * <?php
 * class MyPackage_Warning extends PEAR_Exception implements PEAR_WarningInterface
 * {
 *     private $_level = 'warning';
 *     function __construct($message, $level = 'warning', $p1 = null, $p2 = null)
 *     {
 *         $this->_level = $level;
 *         parent::__construct($message, $p1, $p2);
 *     }
 *
 *     public function getLevel()
 *     {
 *         return $this->_level;
 *     }
 * }
 * PEAR_Warning::add(new MyPackage_Warning('some info', 'notice'));
 * ?>
 * </code>
 *
 * This can be used with {@link setErrorHandling()} to ignore warnings of different severities
 * for complex error situations.
 *
 * For local situations like an internal warning system for a parser that may become the cause
 * of a single PEAR_Exception, PEAR_Warning can also be instantiated and used without any connection
 * to the global warning stack.
 * @package PEAR
 */
class PEAR_Warning
{
    /**
     * properties used for global warning stacks
     */
    protected static $_hasWarnings = false;

    protected static $warnings = array();
    protected static $go = false;
    protected static $levels = array('warning', 'notice');

    private static $_observers = array();
    private static $_uniqueid = 0;
    /**
     * properties used for instantiation of private warning stack
     */
    private $_warnings = array();
    private $_go = false;
    private $_context;

    /**
     * Begin tracking all global warnings
     */
    static public function begin()
    {
        if (class_exists('PEAR_ErrorStack')) {
            PEAR_ErrorStack::setPEARWarningCallback(array('PEAR_Warning', '_catch'));
        }
        self::$go = true;
        self::$_hasWarnings = false;
    }

    /**
     * @return bool
     */
    static public function hasWarnings()
    {
        return self::$_hasWarnings;
    }

    /**
     * Stop tracking global warnings
     * @return array an array of all warnings in array and PEAR_Exception format
     *               suitable for use as a PEAR_Exception cause
     */
    static public function end()
    {
        if (class_exists('PEAR_ErrorStack')) {
            PEAR_ErrorStack::setPEARWarningCallback(false);
        }
        self::$go = false;
        self::$_hasWarnings = false;
        $a = self::$warnings;
        self::$warnings = array();
        return $a;
    }

    /**
     * @param mixed A valid callback that accepts either a
     *              PEAR_Exception or PEAR_ErrorStack-style array
     * @param string The name of the observer. Use this if you want
     *               to remove it later with removeObserver().
     *               {@link getUniqueId()} can be used to generate a label
     */
    public static function addObserver($callback, $label = 'default')
    {
        self::$_observers[$label] = $callback;
    }

    /**
     * @param mixed observer ID
     */
    public static function removeObserver($label = 'default')
    {
        unset(self::$_observers[$label]);
    }

    /**
     * @return int unique identifier for an observer
     */
    public static function getUniqueId()
    {
        return self::$_uniqueid++;
    }

    /**
     * Set the warning levels that should be captured by the warning mechanism
     *
     * WARNING: no error checking or spell checking.
     * @param array
     */
    public static function setErrorHandling($levels)
    {
        self::$_levels = $levels;
    }

    /**
     * Add a warning to the global warning stack.
     *
     * Note: if you want file/line context, use an exception object
     * @param PEAR_Exception|string|int Either pass in an exception to use as the warning, or an
     *                                  error code or some other error class differentiation technique
     * @param string Package is required if $codeOrException is not a PEAR_Exception object
     * @param string Error message, use %param% to do automatic parameter replacement from $params
     * @param array  Error parameters
     * @param string Error level, use the English name

     * @throws PEAR_WarningException if $codeOrException is not a PEAR_Exception and $package is not set
     */
    static public function add($codeOrException, $package = '', $msg = '', $params = array(),
                               $level = 'warning')
    {
        if ($codeOrException instanceof PEAR_Exception) {
            if ($codeOrException instanceof PEAR_WarningInterface) {
                if (in_array($codeOrException->getLevel(), self::$levels)) {
                    self::_signal($codeOrException);
                }
            } else {
                self::_signal($codeOrException);
            }
        } else {
            if (empty($package)) {
                throw new PEAR_WarningException('Package must be set for a non-exception warning');
            }
            if (in_array($level, self::$levels)) {
                $warning = self::_formatWarning($codeOrException, $package, $level, $msg, $params);
                self::_signal($warning);
            }
        }
        if (self::$go) {
            self::$_hasWarnings = true;
            self::$warnings[] = $warning;
        }
    }

    /**
     * @param string the package name, or other context information that can be used
     *               to differentiate this warning from warnings thrown by other packages
     * @throws PEAR_WarningException if $context is not a string
     */
    public function __construct($context)
    {
        if (!is_string($context)) {
            throw new PEAR_WarningException('$context constructor argument must be a string');
        }
        $this->_context = $context;
    }

    /**
     * Local stack function for adding a warning - note that package is not needed, as it is
     * defined in the constructor.
     *
     * Note: if you want file/line context, use an exception object
     * @param PEAR_Exception|string|int Either pass in an exception to use as the warning, or an
     *                                  error code or some other error class differentiation technique
     * @param string Error message, use %param% to do automatic parameter replacement from $params
     * @param array  Error parameters
     * @param string Error level, use the English name
     */
    public function localAdd($code, $msg = '', $params = array(), $level = 'warning')
    {
        if ($codeOrException instanceof PEAR_Exception) {
            $this->_warnings[] = $codeOrException;
        } else {
            $warning = self::_formatWarning($codeOrException, $this->_context, $level, $msg);
            $this->_warnings[] = $warning;
        }
    }

    /**
     * Begin a local warning stack session
     */
    public function localBegin()
    {
        $this->_warnings = array();
        $this->_go = true;
    }

    /**
     * End a local warning stack session
     * @return array
     */
    public function localEnd()
    {
        $a = $this->_warnings;
        $this->_warnings = array();
        $this->_go = false;
        return $a;
    }

    /**
     * Do not use this function directly - it should only be used by PEAR_ErrorStack
     * @access private
     */
    static public function _catch($err)
    {
        self::_signal($err);
    }

    private static function _signal($warning)
    {
        foreach (self::$_observers as $func) {
            if (is_callable($func)) {
                call_user_func($func, $this);
                continue;
            }
            settype($func, 'array');
            switch ($func[0]) {
                case PEAR_EXCEPTION_PRINT :
                    $f = (isset($func[1])) ? $func[1] : '%s';
                    printf($f, $this->getMessage());
                    break;
                case PEAR_EXCEPTION_TRIGGER :
                    $f = (isset($func[1])) ? $func[1] : E_USER_NOTICE;
                    trigger_error($this->getMessage(), $f);
                    break;
                case PEAR_EXCEPTION_DIE :
                    $f = (isset($func[1])) ? $func[1] : '%s';
                    die(printf($f, $this->getMessage()));
                    break;
                default:
                    trigger_error('invalid observer type', E_USER_WARNING);
            }
        }
    }

    static private function _formatWarning($code, $package, $level, $msg, $params, $backtrace)
    {
        return array('package' => $package,
                     'code' => $code, 
                     'level' => $level,
                     'message' => self::_formatMessage($msg, $params),
                     'params' => $params);
    }
    
    static private function _formatMessage($msg, $params)
    {
        if (count($params)) {
            foreach ($params as $name => $val) {
                if (strpos($msg, '%' . $name . '%') !== false) {
                    if (is_array($val)) {
                        // don't pass in an array that you expect to display unless it is 1-dimensional!
                        $val = implode(', ', $val);
                    }
                    if (is_object($val)) {
                        if (method_exists($val, '__toString')) {
                            $val = $val->__toString();
                        } else {
                            $val = 'Object';
                        }
                    }
                    $msg = str_replace('%' . $name . '%', $val, $msg);
                }
            }
        }
        return $msg;
    }
}
?>