<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAutoCompleteSearch
 */
class ilMailAutoCompleteSearch
{
    /**
     * @var ilMailAutoCompleteRecipientResult
     */
    protected $result;

    /**
     * @var Iterator[]
     */
    protected $providers = array();

    /**
     * @param ilMailAutoCompleteRecipientResult $result
     */
    public function __construct(ilMailAutoCompleteRecipientResult $result)
    {
        $this->result = $result;
    }

    /**
     * @param Iterator $provider
     */
    public function addProvider(Iterator $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     *
     */
    public function search()
    {
        foreach ($this->providers as $provider) {
            foreach ($provider as $row) {
                if (!$this->result->isResultAddable()) {
                    $this->result->result['hasMoreResults'] = true;
                    break 2;
                }
                $this->result->addResult($row['login'], $row['firstname'], $row['lastname']);
            }
        }
    }
}
