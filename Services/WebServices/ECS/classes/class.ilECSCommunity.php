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
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSCommunity
{
    protected $json_obj = null;
    protected string $title = '';
    protected string $description = '';
    protected int $id = 0;
    
    protected array $participants = array();
    protected int $position = 0;
    
    /**
     * Constructor
     *
     * @access public
     * @param object json object
     *
     */
    public function __construct($json_obj)
    {
        $this->json_obj = $json_obj;
        $this->read();
    }
    
    /**
     * get title
     *
     * @access public
     *
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * getDescription
     *
     * @access public
     *
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * get participants
     *
     * @access public
     * @return \ilECSParticipant
     */
    public function getParticipants()
    {
        return $this->participants ? $this->participants : array();
    }

    /**
     * Get array of mids of all participants
     */
    public function getMids()
    {
        $mids = array();
        foreach ($this->getParticipants() as $part) {
            $mids[] = $part->getMID();
        }
        return $mids;
    }

    /**
     * Get own mid of community
     */
    public function getOwnId()
    {
        foreach ($this->getParticipants() as $part) {
            if ($part->isSelf()) {
                return $part->getMID();
            }
        }
        return 0;
    }

    
    /**
     * get id
     *
     * @access public
     *
     */
    public function getId()
    {
        return $this->id;
    }
    
    
    /**
     * Read community entries and participants
     *
     * @access private
     *
     */
    private function read()
    {
        $this->title = $this->json_obj->community->name;
        $this->description = $this->json_obj->community->description;
        $this->id = $this->json_obj->community->cid;
        
        foreach ($this->json_obj->participants as $participant) {
            $this->participants[] = new ilECSParticipant($participant, $this->getId());
        }
    }
}
