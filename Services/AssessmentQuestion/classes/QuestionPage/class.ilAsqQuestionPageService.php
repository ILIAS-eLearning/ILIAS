<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAsqQuestionPageService
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAsqQuestionPageService
{
    /**
     * @param int $questionIntId
     * @return ilAsqQuestionPage
     */
    public function getPage(int $questionIntId) : ilAsqQuestionPage
    {
        return new ilAsqQuestionPage($questionIntId);
    }


    /**
     * @param int $parentObjId
     * @param int $questionIntId
     * @return ilAsqQuestionPage
     */
    public function createPage(int $parentObjId, int $questionIntId) : ilAsqQuestionPage
    {
        $page = new ilAsqQuestionPage(0);
        $page->setId($questionIntId);
        $page->setParentId($parentObjId);
        $page->setXMLContent($this->buildInitialPageXml($questionIntId));
        $page->create();

        return $page;
    }


    /**
     * @param int $questionIntId
     * @return string
     */
    protected function buildInitialPageXml(int $questionIntId)
    {
        $xml = "<PageObject>";
        $xml .= "<PageContent>";
        $xml .= "<Question QRef=\"il__qst_{$questionIntId}\"/>";
        $xml .= "</PageContent>";
        $xml .= "</PageObject>" ;

        return $xml;
    }
}
