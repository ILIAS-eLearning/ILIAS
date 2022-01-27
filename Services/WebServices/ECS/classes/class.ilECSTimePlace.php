<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 *  Representation of ECS EContent Time Place
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 *
 * @ingroup ServicesWebServicesECS
 */
class ilECSTimePlace
{
    private ilLogger $logger;
    private string $room = '';
    private string $begin = '';
    private string $end = '';
    private string $cycle = '';

    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->wsrv();
    }

    /**
     * load from json
     *
     * @access public
     * @param object json representation
     * @throws ilException
     */
    public function loadFromJson($a_json)
    {
        if (!is_object($a_json)) {
            $this->logger->error(__METHOD__ . ': Cannot load from JSON. No object given.');
            throw new ilException('Cannot parse ECSContent.');
        }
        
        $this->logger->debug(__METHOD__ . ': ' . print_r($a_json, true));

        $this->room = $a_json->room;
        $this->begin = $a_json->begin;
        $this->end = $a_json->end;
        $this->cycle = $a_json->cycle;
        
        $two = new ilDate('2000-01-02', IL_CAL_DATE);
        if (ilDate::_before(new ilDateTime($this->getUTBegin(), IL_CAL_UNIX), $two)) {
            $this->begin = '';
        }
        if (ilDate::_before(new ilDateTime($this->getUTEnd(), IL_CAL_UNIX), $two)) {
            $this->end = '';
        }
    }

    /**
     * set begin
     *
     * @access public
     *
     */
    public function setBegin($a_begin)
    {
        // is it unix time ?
        if (is_numeric($a_begin) and $a_begin) {
            $dt = new ilDateTime($a_begin, IL_CAL_UNIX, ilTimeZone::UTC);
            $this->end = $dt->get(IL_CAL_DATE);
        } else {
            $this->begin = $a_begin;
        }
    }

    /**
     * get begin
     *
     * @access public
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * get begin as unix time
     *
     * @access public
     *
     */
    public function getUTBegin()
    {
        $dt = new ilDateTime($this->begin, IL_CAL_DATE, ilTimeZone::UTC);
        return $dt->get(IL_CAL_UNIX);
    }

    /**
     * set end
     *
     * @access public
     * @param string end
     *
     */
    public function setEnd($a_end)
    {
        // is it unix time ?
        if (is_numeric($a_end) and $a_end) {
            $dt = new ilDateTime($a_end, IL_CAL_UNIX, ilTimeZone::UTC);
            $this->end = $dt->get(IL_CAL_DATE);
        } else {
            $this->end = $a_end;
        }
    }

    /**
     * get end
     *
     * @access public
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * get end as unix time
     *
     * @access public
     *
     */
    public function getUTEnd()
    {
        $dt = new ilDateTime($this->end, IL_CAL_DATE, ilTimeZone::UTC);
        return $dt->get(IL_CAL_UNIX);
    }

    /**
     * set room
     *
     * @access public
     * @param string room
     *
     */
    public function setRoom($a_room)
    {
        $this->room = $a_room;
    }

    /**
     * get room
     *
     * @access public
     *
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * set cycle
     *
     * @access public
     * @param
     *
     */
    public function setCycle($a_cycle)
    {
        $this->cycle = $a_cycle;
    }

    /**
     * get cycle
     *
     * @access public
     * @param
     *
     */
    public function getCycle()
    {
        return $this->cycle;
    }
}
