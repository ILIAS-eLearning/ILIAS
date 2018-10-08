<?php
/**
 * PEAR_Command_Auth (build command)
 *
 * PHP versions 4 and 5
 *
 * @category   pear
 * @package    PEAR
 * @author     Stig Bakken <ssb@php.net>
 * @author     Tomas V.V.Cox <cox@idecnet.com>
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  1997-2009 The Authors
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://pear.php.net/package/PEAR
 * @since      File available since Release 0.1
 */

/**
 * base class
 */
require_once 'PEAR/Command/Common.php';

/**
 * PEAR commands for building extensions.
 *
 * @category   pear
 * @package    PEAR
 * @author     Stig Bakken <ssb@php.net>
 * @author     Tomas V.V.Cox <cox@idecnet.com>
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  1997-2009 The Authors
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PEAR
 * @since      Class available since Release 0.1
 */
class PEAR_Command_Build extends PEAR_Command_Common
{
    var $commands = array(
        'build' => array(
            'summary' => 'Build an Extension From C Source',
            'function' => 'doBuild',
            'shortcut' => 'b',
            'options' => array(),
            'doc' => '[package.xml]
Builds one or more extensions contained in a package.'
            ),
        );

    /**
     * PEAR_Command_Build constructor.
     *
     * @access public
     */
    function __construct(&$ui, &$config)
    {
        parent::__construct($ui, $config);
    }

    function doBuild($command, $options, $params)
    {
        require_once 'PEAR/Builder.php';
        if (sizeof($params) < 1) {
            $params[0] = 'package.xml';
        }

        $builder = new PEAR_Builder($this->ui);
        $this->debug = $this->config->get('verbose');
        $err = $builder->build($params[0], array(&$this, 'buildCallback'));
        if (PEAR::isError($err)) {
            return $err;
        }

        return true;
    }

    function buildCallback($what, $data)
    {
        if (($what == 'cmdoutput' && $this->debug > 1) ||
            ($what == 'output' && $this->debug > 0)) {
            $this->ui->outputData(rtrim($data), 'build');
        }
    }
}
