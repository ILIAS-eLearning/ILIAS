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
    protected object $json_obj;
    protected string $title = '';
    protected string $description = '';
    protected int $id = 0;
    
    protected array $participants = array();
    protected int $position = 0;
    
    /**
     * Constructor
     *
     * @param object json object
     */
    public function __construct(object $json_obj)
    {
        $this->json_obj = $json_obj;
        $this->read();
    }
    
    /**
     * get title
     */
    public function getTitle() : string
    {
        return $this->title;
    }
    
    /**
     * getDescription
     */
    public function getDescription() : string
    {
        return $this->description;
    }
    
    /**
     * get participants
     *
     * @return ilECSParticipant[]
     */
    public function getParticipants() : array
    {
        return $this->participants ?: [];
    }

    /**
     * Get array of mids of all participants
     */
    public function getMids() : array
    {
        $mids = [];
        foreach ($this->getParticipants() as $part) {
            $mids[] = $part->getMID();
        }
        return $mids;
    }

    /**
     * Get own mid of community
     */
    public function getOwnId() : int
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
     */
    public function getId() : int
    {
        return $this->id;
    }
    
    
    /**
     * Read community entries and participants
     */
    private function read() : void
    {
        $this->title = $this->json_obj->community->name;
        $this->description = $this->json_obj->community->description;
        $this->id = $this->json_obj->community->cid;
        
        foreach ($this->json_obj->participants as $participant) {
            $this->participants[] = new ilECSParticipant($participant, $this->getId());
        }
    }
}
