<?php
/**
 * <tasks:windowseol> - read/write version
 *
 * PHP versions 4 and 5
 *
 * @category  pear
 * @package   PEAR
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 1997-2009 The Authors
 * @license   http://opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://pear.php.net/package/PEAR
 * @since     File available since Release 1.4.0a10
 */
/**
 * Base class
 */
require_once 'PEAR/Task/Windowseol.php';
/**
 * Abstracts the windowseol task xml.
 *
 * @category  pear
 * @package   PEAR
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 1997-2009 The Authors
 * @license   http://opensource.org/licenses/bsd-license.php New BSD License
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PEAR
 * @since     Class available since Release 1.4.0a10
 */
class PEAR_Task_Windowseol_rw extends PEAR_Task_Windowseol
{
    function __construct(&$pkg, &$config, &$logger, $fileXml)
    {
        parent::__construct($config, $logger, PEAR_TASK_PACKAGE);
        $this->_contents = $fileXml;
        $this->_pkg = &$pkg;
        $this->_params = array();
    }

    public function validate()
    {
        return true;
    }

    public function getName()
    {
        return 'windowseol';
    }

    public function getXml()
    {
        return '';
    }
}
?>
