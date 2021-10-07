<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * abstract parent class that manages/holds the data for a question set configuration
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
abstract class ilTestQuestionSetConfig
{
    /**
     * global $tree object instance
     *
     * @var ilTree
     */
    protected $tree = null;
    
    /**
     * global $ilDB object instance
     *
     * @var ilDBInterface
     */
    protected $db = null;

    /**
     * global $pluginAdmin object instance
     *
     * @var ilPluginAdmin
     */
    protected $pluginAdmin = null;

    /**
     * object instance of current test
     *
     * @var ilObjTest
     */
    protected $testOBJ = null;

    /**
     * @param ilTree $tree
     * @param ilDBInterface $db
     * @param ilPluginAdmin $pluginAdmin
     * @param ilObjTest $testOBJ
     */
    public function __construct(ilTree $tree, ilDBInterface $db, ilPluginAdmin $pluginAdmin, ilObjTest $testOBJ)
    {
        $this->tree = $tree;
        $this->db = $db;
        $this->pluginAdmin = $pluginAdmin;
        $this->testOBJ = $testOBJ;
    }
    
    /**
     * loads the question set config for current test from the database
     */
    abstract public function loadFromDb();

    /**
     * saves the question set config for current test to the database
     */
    abstract public function saveToDb();

    /**
     * saves the question set config for test with given id to the database
     *
     * @param $testId
     */
    abstract public function cloneToDbForTestId($testId);

    /**
     * deletes the question set config for current test from the database
     */
    abstract public function deleteFromDb();

    public function areDepenciesInVulnerableState()
    {
        return false;
    }
    
    public function getDepenciesInVulnerableStateMessage(ilLanguage $lng)
    {
        return '';
    }
    
    public function areDepenciesBroken()
    {
        return false;
    }
    
    public function getDepenciesBrokenMessage(ilLanguage $lng)
    {
        return '';
    }
    
    public function isValidRequestOnBrokenQuestionSetDepencies($nextClass, $cmd)
    {
        return true;
    }
    
    public function getHiddenTabsOnBrokenDepencies()
    {
        return array();
    }
        
    abstract public function isQuestionSetConfigured();
    
    /**
     * checks wether question set config related data exists or not
     */
    abstract public function doesQuestionSetRelatedDataExist();
    
    /**
     * removes all question set config related data
     */
    abstract public function removeQuestionSetRelatedData();

    /**
     * resets all test settings that depends on a non changed question set config
     */
    abstract public function resetQuestionSetRelatedTestSettings();

    /**
     * removes all question set config related data for cloned/copied test
     *
     * @param ilObjTest $cloneTestOBJ
     */
    abstract public function cloneQuestionSetRelatedData(ilObjTest $cloneTestOBJ);
    
    /**
     * @param integer $poolId
     * @return string
     */
    public function getQuestionPoolPathString($poolId)
    {
        $ref_id = current(ilObject::_getAllReferences($poolId));

        $path = new ilPathGUI();
        $path->enableTextOnly(true);
        return $path->getPath(ROOT_FOLDER_ID, $ref_id);
    }
    
    public function getFirstQuestionPoolRefIdByObjId(int $pool_obj_id) : int
    {
        $refs_ids = ilObject::_getAllReferences($pool_obj_id);
        $refs_id = current($refs_ids);

        return (int) $refs_id;
    }

    abstract public function isResultTaxonomyFilterSupported();
}
