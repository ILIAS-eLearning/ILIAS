<?php
/**
 * PEAR_REST_14
 *
 * PHP versions 4 and 5
 *
 * @category   pear
 * @package    PEAR
 * @author     Helgi Þormar Þorbjörnsson <helgi@php.net>
 * @copyright  1997-2009 The Authors
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id: $
 * @link       http://pear.php.net/package/PEAR
 * @since      File available since Release 1.9
 */

/**
 * For downloading REST xml/txt files
 */
require_once 'PEAR/REST.php';
require_once 'PEAR/REST/13.php';

/**
 * Implement REST 1.4
 *
 * @category   pear
 * @package    PEAR
 * @author     Helgi Þormar Þorbjörnsson <helgi@php.net>
 * @copyright  1997-2009 The Authors
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PEAR
 * @since      Class available since Release 1.9
 */
class PEAR_REST_14  extends PEAR_REST_13
{
    function listLatestUpgrades($base, $pref_state, $installed, $channel, &$reg)
    {
        $packagelist = $this->_rest->retrieveData($base . 'p/latestpackages.xml', false, false, $channel);
        if (PEAR::isError($packagelist)) {
            return $packagelist;
        }

        $ret = array();
        if (!is_array($packagelist) || !isset($packagelist['p'])) {
            return $ret;
        }

        if (isset($packagelist['p']['n'])) {
            $packagelist['p'] = array($packagelist['p']);
        }

        foreach ($packagelist['p'] as $package) {
            if (!isset($installed[strtolower($package['n'])])) {
                continue;
            }

            $inst_version = $reg->packageInfo($package['n'], 'version',       $channel);
            $inst_state   = $reg->packageInfo($package['n'], 'release_state', $channel);


            $release = $found = false;
            $data = array();
            if (isset($package['alpha'])) {
                $data['alpha'] = $package['alpha'];
            }

            if (isset($package['beta'])) {
                $data['beta'] = $package['beta'];
            }

            if (isset($package['stable'])) {
                $data['stable'] = $package['stable'];
            }

            foreach ($data as $state => $release) {
                if ($inst_version && version_compare($release['v'], $inst_version, '<=')) {
                    // not newer than the one installed
                    break;
                }

                // new version > installed version
                if (!$pref_state) {
                    // every state is a good state
                    $found = true;
                    $release['state'] = $state;
                    break;
                } else {
                    $new_state = $state;
                    // if new state >= installed state: go
                    if (in_array($new_state, $this->betterStates($inst_state, true))) {
                        $found = true;
                        $release['state'] = $state;
                        break;
                    } else {
                        // only allow to lower the state of package,
                        // if new state >= preferred state: go
                        if (in_array($new_state, $this->betterStates($pref_state, true))) {
                            $found = true;
                            $release['state'] = $state;
                            break;
                        }
                    }
                }
            }

            if (!$found) {
                continue;
            }

            $ret[$package] = array(
                'version'  => $release['v'],
                'state'    => $release['s'],
                'filesize' => $release['f'],
            );
        }

        return $ret;
    }
}