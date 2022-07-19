<?php

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
 * abstract parent class that manages/holds the data for a question set configuration
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/Test
 */
abstract class ilTestQuestionSetConfig
{
    protected ilTree $tree;
    protected ilDBInterface $db;
    protected ilComponentRepository $component_repository;
    protected ilObjTest $testOBJ;

    public function __construct(
        ilTree $tree,
        ilDBInterface $db,
        ilComponentRepository $component_repository,
        ilObjTest $testOBJ
    ) {
        $this->tree = $tree;
        $this->db = $db;
        $this->component_repository = $component_repository;
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

    public function areDepenciesInVulnerableState() : bool
    {
        return false;
    }
    
    public function getDepenciesInVulnerableStateMessage(ilLanguage $lng) : string
    {
        return '';
    }
    
    public function areDepenciesBroken() : bool
    {
        return false;
    }
    
    public function getDepenciesBrokenMessage(ilLanguage $lng) : string
    {
        return '';
    }
    
    public function isValidRequestOnBrokenQuestionSetDepencies($nextClass, $cmd) : bool
    {
        return true;
    }
    
    public function getHiddenTabsOnBrokenDepencies() : array
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
    abstract public function removeQuestionSetRelatedData() : void;

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
    public function getQuestionPoolPathString($poolId) : string
    {
        $ref_id = current(ilObject::_getAllReferences($poolId));

        $path = new ilPathGUI();
        $path->enableTextOnly(true);
        return $path->getPath(ROOT_FOLDER_ID, (int) $ref_id);
    }
    
    public function getFirstQuestionPoolRefIdByObjId(int $pool_obj_id) : int
    {
        $refs_ids = ilObject::_getAllReferences($pool_obj_id);
        $refs_id = current($refs_ids);

        return (int) $refs_id;
    }

    abstract public function isResultTaxonomyFilterSupported();
}
