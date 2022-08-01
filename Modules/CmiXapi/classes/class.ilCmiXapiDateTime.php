<?php declare(strict_types=1);

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
 * Class ilCmiXapiDateTime
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiDateTime extends ilDateTime
{
    // DateTime::RFC3339_EXTENDED resolves to Y-m-d\TH:i:s.vP
    // note the v at the end -> this works with PHP 7.3
    // but not with PHP 7.2, 7.1 and probably not with versions below

    const RFC3336_EXTENDED_FIXED_USING_u_INSTEAD_OF_v = 'Y-m-d\TH:i:s.uP';

    public function toXapiTimestamp() : string
    {
        $phpDateTime = new DateTime();
        $phpDateTime->setTimestamp($this->get(IL_CAL_UNIX));
        
        return $phpDateTime->format(self::RFC3336_EXTENDED_FIXED_USING_u_INSTEAD_OF_v);
    }

    /**
     * @throws ilDateTimeException
     */
    public static function fromXapiTimestamp(string $xapiTimestamp) : \ilCmiXapiDateTime
    {
        $phpDateTime = DateTime::createFromFormat(
            self::RFC3336_EXTENDED_FIXED_USING_u_INSTEAD_OF_v,
            $xapiTimestamp
        );
        
        $unixTimestamp = $phpDateTime->getTimestamp();

        return new self($unixTimestamp, IL_CAL_UNIX);
    }

    /**
     * @throws ilDateTimeException
     */
    public static function fromIliasDateTime(ilDateTime $dateTime) : \ilCmiXapiDateTime
    {
        return new self($dateTime->get(IL_CAL_UNIX), IL_CAL_UNIX);
    }

    public static function dateIntervalToISO860Duration(\DateInterval $d) : string
    {
        $duration = 'P';
        if (!empty($d->y)) {
            $duration .= "{$d->y}Y";
        }
        if (!empty($d->m)) {
            $duration .= "{$d->m}M";
        }
        if (!empty($d->d)) {
            $duration .= "{$d->d}D";
        }
        if (!empty($d->h) || !empty($d->i) || !empty($d->s)) {
            $duration .= 'T';
            if (!empty($d->h)) {
                $duration .= "{$d->h}H";
            }
            if (!empty($d->i)) {
                $duration .= "{$d->i}M";
            }
            if (!empty($d->s)) {
                $duration .= "{$d->s}S";
            }
            // ToDo: nervt!
            /*
            if (!empty($d->f)) {
                if (!empty($d->s)) {
                    $s = $d->s + $d->f;
                }
                else {
                    $s = $d->f;
                }
                $duration .= "{$s}S";
            }
            else
            {
                if (!empty($d->s)) {
                    $duration .= "S";
                }
            }
            */
        }
        if ($duration === 'P') {
            $duration = 'PT0S'; // Empty duration (zero seconds)
        }
        return $duration;
    }
}
