<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once("./Modules/LearningModule/classes/class.ilBibItem.php");

/**
* Class ilBibItemGUI
*
* GUI class for BibItems
*
* @author Jens Conze <jc@databay.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilBibItemGUI
{
	var $ilias;
	var $tpl;
	var $lng;
	var $obj;
	var $bib_obj;


	/**
	* Constructor
	* @access	public
	*/
	function ilBibItemGUI()
	{
		global $ilias, $tpl, $lng;
		$lng->loadLanguageModule("bibitem");

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
	}

	function setObject($a_obj)
	{
		$this->obj = $a_obj;
		$this->obj->initBibItemObject();
		$this->bib_obj = $this->obj->bib_obj;
#		echo $this->bib_obj->getXML();
	}

	/**
	* use this method to initialize form fields
	*/
	function curValue($a_val_name)
	{
		if(is_object($this->meta_obj))
		{
			$method = "get".$a_val_name;
			return $this->meta_obj->$method();
		}
		else
		{
			return "";
		}
	}

	/**
	* shows language select box
	*/
	function showLangSel($a_name, $a_value = "")
	{
		$tpl = new ilTemplate("tpl.lang_selection.html", true, true,
			"Services/MetaData");
		$languages = ilMDLanguageItem::_getLanguages();
		foreach($languages as $code => $text)
		{
			$tpl->setCurrentBlock("lg_option");
			$tpl->setVariable("VAL_LG", $code);
			$tpl->setVariable("TXT_LG", $text);
			if ($a_value != "" &&
				$a_value == $code)
			{
				$tpl->setVariable("SELECTED", "selected");
			}
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
		$tpl->setVariable("SEL_NAME", $a_name);
		$return = $tpl->get();
		unset($tpl);

		return $return;
	}

	function fill($a_formaction, $a_index = 0, $a_language = "")
	{
			$this->tpl->setVariable("TXT_BIBITEM", $this->lng->txt("bibitem_bibitem"));
			$this->tpl->setVariable("BIBITEM_TXT_DELETE", $this->lng->txt("bibitem_delete"));
			$this->tpl->setVariable("BIBITEM_ACTION_DELETE", $a_formaction . "&cmd=deleteBibItem&bibItemIndex=" . $a_index . "&bibItemName=BibItem");
			$this->tpl->setVariable("BIBITEM_TXT_ADD", $this->lng->txt("bibitem_add"));
			$this->tpl->setVariable("BIBITEM_ACTION_ADD", $a_formaction . "&cmd=addBibItem&bibItemName=BibItem");
			$this->tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("bibitem_new_element"));
			$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("bibitem_language"));
			$this->tpl->setVariable("TXT_AUTHOR", $this->lng->txt("bibitem_author"));
			$this->tpl->setVariable("TXT_CROSSREF", $this->lng->txt("bibitem_cross_reference"));
			$this->tpl->setVariable("TXT_EDITOR", $this->lng->txt("bibitem_editor"));
			$this->tpl->setVariable("TXT_WHERE_PUBLISHED", $this->lng->txt("bibitem_where_published"));
			$this->tpl->setVariable("TXT_INSTITUTION", $this->lng->txt("bibitem_institution"));
			$this->tpl->setVariable("TXT_KEYWORD", $this->lng->txt("bibitem_keyword"));
			$this->tpl->setVariable("TXT_SCHOOL", $this->lng->txt("bibitem_school"));
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("bibitem_add"));

			$bibitem = $this->bib_obj->getElement("BibItem");
			$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("bibitem_type"));
			$this->tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("bibitem_please_select"));
			$this->tpl->setVariable("TXT_BOOK", $this->lng->txt("bibitem_book"));
			$this->tpl->setVariable("TXT_INBOOK", $this->lng->txt("bibitem_inbook"));
			$this->tpl->setVariable("TXT_JOURNALARTICLE", $this->lng->txt("bibitem_journal_article"));
			$this->tpl->setVariable("TXT_PROCEEDINGS", $this->lng->txt("bibitem_proceedings"));
			$this->tpl->setVariable("TXT_INPROCEEDINGS", $this->lng->txt("bibitem_inproceedings"));
			$this->tpl->setVariable("TXT_DISSERTATION", $this->lng->txt("bibitem_dissertation"));
			$this->tpl->setVariable("TXT_PHDTHESIS", $this->lng->txt("bibitem_phd_thesis"));
			$this->tpl->setVariable("TXT_MASTERSTHESIS", $this->lng->txt("bibitem_master_thesis"));
			$this->tpl->setVariable("TXT_TECHREPORT", $this->lng->txt("bibitem_technical_report"));
			$this->tpl->setVariable("TXT_MANUAL", $this->lng->txt("bibitem_manual"));
			$this->tpl->setVariable("TXT_NEWSPAPERARTICLE", $this->lng->txt("bibitem_newspaper_article"));
			$this->tpl->setVariable("TXT_AV", $this->lng->txt("bibitem_av"));
			$this->tpl->setVariable("TXT_INTERNET", $this->lng->txt("bibitem_internet"));
			$this->tpl->setVariable("TXT_UNPUBLISHED", $this->lng->txt("bibitem_unpublished"));
			$this->tpl->setVariable("TYPE_VAL_" . strtoupper(ilUtil::prepareFormOutput($bibitem[$a_index]["Type"])), " selected");

			$this->tpl->setVariable("TXT_LABEL", $this->lng->txt("bibitem_label"));
			$this->tpl->setVariable("VAL_LABEL", ilUtil::prepareFormOutput($bibitem[$a_index]["Label"]));

			$identifier = $this->bib_obj->getElement("Identifier", "BibItem[" . ($a_index+1) . "]");
			$this->tpl->setVariable("TXT_IDENTIFIER", $this->lng->txt("bibitem_identifier"));
			$this->tpl->setVariable("TXT_CATALOG", $this->lng->txt("bibitem_catalog"));
			$this->tpl->setVariable("VAL_IDENTIFIER_CATALOG", ilUtil::prepareFormOutput($identifier[0]["Catalog"]));
			$this->tpl->setVariable("TXT_ENTRY", $this->lng->txt("bibitem_entry"));
			$this->tpl->setVariable("VAL_IDENTIFIER_ENTRY", ilUtil::prepareFormOutput($identifier[0]["Entry"]));

			/* Language Loop */
			if (is_array($language = $this->bib_obj->getElement("Language", "BibItem[" . ($a_index+1) . "]")) &&
				count($language) > 0)
			{
				for ($i = 0; $i < count($language); $i++)
				{
					if (count($language) > 1)
					{
						$this->tpl->setCurrentBlock("language_delete");
						$this->tpl->setVariable("LANGUAGE_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteBibItem&bibItemIndex=" . $a_index . "," . $i . "&bibItemPath=BibItem&bibItemName=Language");
						$this->tpl->setVariable("LANGUAGE_LOOP_TXT_DELETE", $this->lng->txt("bibitem_delete"));
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("language_loop");
					$this->tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("bibitem_language"));
					$this->tpl->setVariable("LANGUAGE_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[Language][" . $i . "][Language]", $language[$i]["Language"]));
					$this->tpl->setVariable("LANGUAGE_LOOP_ACTION_ADD", $a_formaction . "&cmd=addBibItem&bibItemName=Language&bibItemPath=BibItem&bibItemIndex=" . $a_index);
					$this->tpl->setVariable("LANGUAGE_LOOP_TXT_ADD", $this->lng->txt("bibitem_add"));
					$this->tpl->parseCurrentBlock();
				}
			}

			/* Author Loop */
			if (is_array($author = $this->bib_obj->getElement("Author", "BibItem[" . ($a_index+1) . "]")) &&
				count($author) > 0)
			{
				for ($i = 0; $i < count($author); $i++)
				{
					$this->tpl->setCurrentBlock("author_loop");
					$this->tpl->setVariable("AUTHOR_LOOP_TXT_AUTHOR", $this->lng->txt("bibitem_author"));
					$this->tpl->setVariable("AUTHOR_LOOP_TXT_DELETE", $this->lng->txt("bibitem_delete"));
					$this->tpl->setVariable("AUTHOR_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteBibItem&bibItemIndex=" . $a_index . "," . $i . "&bibItemPath=BibItem&bibItemName=Author");
					$this->tpl->setVariable("AUTHOR_LOOP_TXT_ADD", $this->lng->txt("bibitem_add"));
					$this->tpl->setVariable("AUTHOR_LOOP_ACTION_ADD", $a_formaction . "&cmd=addBibItem&bibItemName=Author&bibItemPath=BibItem&bibItemIndex=" . $a_index);
					$this->tpl->setVariable("AUTHOR_LOOP_NO", $i);
					$this->tpl->setVariable("AUTHOR_LOOP_TXT_FIRSTNAME", $this->lng->txt("bibitem_first_name"));
					if (is_array($firstname = $this->bib_obj->getElement("FirstName", "BibItem[" . ($a_index+1) . "]/Author[" . ($i+1) . "]")))
					{
						$this->tpl->setVariable("AUTHOR_LOOP_VAL_FIRSTNAME", ilUtil::prepareFormOutput($firstname[0]["value"]));
					}
					$this->tpl->setVariable("AUTHOR_LOOP_TXT_MIDDLENAME", $this->lng->txt("bibitem_middle_name"));
					if (is_array($middlename = $this->bib_obj->getElement("MiddleName", "BibItem[" . ($a_index+1) . "]/Author[" . ($i+1) . "]")))
					{
						$this->tpl->setVariable("AUTHOR_LOOP_VAL_MIDDLENAME", ilUtil::prepareFormOutput($middlename[0]["value"]));
					}
					$this->tpl->setVariable("AUTHOR_LOOP_TXT_LASTNAME", $this->lng->txt("bibitem_last_name"));
					if (is_array($lastname = $this->bib_obj->getElement("LastName", "BibItem[" . ($a_index+1) . "]/Author[" . ($i+1) . "]")))
					{
						$this->tpl->setVariable("AUTHOR_LOOP_VAL_LASTNAME", ilUtil::prepareFormOutput($lastname[0]["value"]));
					}
					$this->tpl->parseCurrentBlock("author_loop");
				}
			}

			$booktitle = $this->bib_obj->getElement("Booktitle", "BibItem[" . ($a_index+1) . "]");
			$this->tpl->setVariable("TXT_BOOKTITLE", $this->lng->txt("bibitem_booktitle"));
			$this->tpl->setVariable("BOOKTITLE_VAL", ilUtil::prepareFormOutput($booktitle[0]["value"]));
			$this->tpl->setVariable("BOOKTITLE_VAL_LANGUAGE", $this->showLangSel("meta[Booktitle][Language]", $booktitle[0]["Language"]));

			/* CrossRef Loop */
			if (is_array($crossref = $this->bib_obj->getElement("CrossRef", "BibItem[" . ($a_index+1) . "]")) &&
				count($crossref) > 0)
			{
				for ($i = 0; $i < count($crossref); $i++)
				{
					$this->tpl->setCurrentBlock("crossref_loop");
					$this->tpl->setVariable("CROSSREF_LOOP_TXT_CROSSREF", $this->lng->txt("bibitem_cross_reference"));
					$this->tpl->setVariable("CROSSREF_LOOP_TXT_DELETE", $this->lng->txt("bibitem_delete"));
					$this->tpl->setVariable("CROSSREF_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteBibItem&bibItemIndex=" . $a_index . "," . $i . "&bibItemPath=BibItem&bibItemName=CrossRef");
					$this->tpl->setVariable("CROSSREF_LOOP_TXT_ADD", $this->lng->txt("bibitem_add"));
					$this->tpl->setVariable("CROSSREF_LOOP_ACTION_ADD", $a_formaction . "&cmd=addBibItem&bibItemName=CrossRef&bibItemPath=BibItem&bibItemIndex=" . $a_index);
					$this->tpl->setVariable("CROSSREF_LOOP_NO", $i);
					$this->tpl->setVariable("CROSSREF_LOOP_VAL", ilUtil::prepareFormOutput($crossref[$i]["value"]));
					$this->tpl->parseCurrentBlock("crossref_loop");
				}
			}

			$edition = $this->bib_obj->getElement("Edition", "BibItem[" . ($a_index+1) . "]");
			$this->tpl->setVariable("TXT_EDITION", $this->lng->txt("bibitem_edition"));
			$this->tpl->setVariable("VAL_EDITION", ilUtil::prepareFormOutput($edition[0]["value"]));

			/* Editor Loop */
			if (is_array($editor = $this->bib_obj->getElement("Editor", "BibItem[" . ($a_index+1) . "]")) &&
				count($editor) > 0)
			{
				for ($i = 0; $i < count($editor); $i++)
				{
					$this->tpl->setCurrentBlock("editor_loop");
					$this->tpl->setVariable("EDITOR_LOOP_TXT_EDITOR", $this->lng->txt("bibitem_editor"));
					$this->tpl->setVariable("EDITOR_LOOP_TXT_DELETE", $this->lng->txt("bibitem_delete"));
					$this->tpl->setVariable("EDITOR_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteBibItem&bibItemIndex=" . $a_index . "," . $i . "&bibItemPath=BibItem&bibItemName=Editor");
					$this->tpl->setVariable("EDITOR_LOOP_TXT_ADD", $this->lng->txt("bibitem_add"));
					$this->tpl->setVariable("EDITOR_LOOP_ACTION_ADD", $a_formaction . "&cmd=addBibItem&bibItemName=Editor&bibItemPath=BibItem&bibItemIndex=" . $a_index);
					$this->tpl->setVariable("EDITOR_LOOP_NO", $i);
					$this->tpl->setVariable("EDITOR_LOOP_VAL", ilUtil::prepareFormOutput($editor[$i]["value"]));
					$this->tpl->parseCurrentBlock("editor_loop");
				}
			}

			$howPublished = $this->bib_obj->getElement("HowPublished", "BibItem[" . ($a_index+1) . "]");
			$this->tpl->setVariable("TXT_HOWPUBLISHED", $this->lng->txt("bibitem_how_published"));
			$this->tpl->setVariable("TXT_GREYLITERATURE", $this->lng->txt("bibitem_grey_literature"));
			$this->tpl->setVariable("TXT_PRINT", $this->lng->txt("bibitem_print"));
			$this->tpl->setVariable("HOWPUBLISHED_TYPE_VAL_" . strtoupper(ilUtil::prepareFormOutput($howPublished[0]["Type"])), " selected");

			/* WherePublished Loop */
			if (is_array($wherepublished = $this->bib_obj->getElement("WherePublished", "BibItem[" . ($a_index+1) . "]")) &&
				count($wherepublished) > 0)
			{
				for ($i = 0; $i < count($wherepublished); $i++)
				{
					$this->tpl->setCurrentBlock("wherepublished_loop");
					$this->tpl->setVariable("WHEREPUBLISHED_LOOP_TXT_WHEREPUBLISHED", $this->lng->txt("bibitem_where_published"));
					$this->tpl->setVariable("WHEREPUBLISHED_LOOP_TXT_DELETE", $this->lng->txt("bibitem_delete"));
					$this->tpl->setVariable("WHEREPUBLISHED_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteBibItem&bibItemIndex=" . $a_index . "," . $i . "&bibItemPath=BibItem&bibItemName=WherePublished");
					$this->tpl->setVariable("WHEREPUBLISHED_LOOP_TXT_ADD", $this->lng->txt("bibitem_add"));
					$this->tpl->setVariable("WHEREPUBLISHED_LOOP_ACTION_ADD", $a_formaction . "&cmd=addBibItem&bibItemName=WherePublished&bibItemPath=BibItem&bibItemIndex=" . $a_index);
					$this->tpl->setVariable("WHEREPUBLISHED_LOOP_NO", $i);
					$this->tpl->setVariable("WHEREPUBLISHED_LOOP_VAL", ilUtil::prepareFormOutput($wherepublished[$i]["value"]));
					$this->tpl->parseCurrentBlock("wherepublished_loop");
				}
			}

			/* Institution Loop */
			if (is_array($institution = $this->bib_obj->getElement("Institution", "BibItem[" . ($a_index+1) . "]")) &&
				count($institution) > 0)
			{
				for ($i = 0; $i < count($institution); $i++)
				{
					$this->tpl->setCurrentBlock("institution_loop");
					$this->tpl->setVariable("INSTITUTION_LOOP_TXT_INSTITUTION", $this->lng->txt("bibitem_institution"));
					$this->tpl->setVariable("INSTITUTION_LOOP_TXT_DELETE", $this->lng->txt("bibitem_delete"));
					$this->tpl->setVariable("INSTITUTION_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteBibItem&bibItemIndex=" . $a_index . "," . $i . "&bibItemPath=BibItem&bibItemName=Institution");
					$this->tpl->setVariable("INSTITUTION_LOOP_TXT_ADD", $this->lng->txt("bibitem_add"));
					$this->tpl->setVariable("INSTITUTION_LOOP_ACTION_ADD", $a_formaction . "&cmd=addBibItem&bibItemName=Institution&bibItemPath=BibItem&bibItemIndex=" . $a_index);
					$this->tpl->setVariable("INSTITUTION_LOOP_NO", $i);
					$this->tpl->setVariable("INSTITUTION_LOOP_VAL", ilUtil::prepareFormOutput($institution[$i]["value"]));
					$this->tpl->parseCurrentBlock("institution_loop");
				}
			}

			/* Journal */
			if (is_array($journal = $this->bib_obj->getElement("Journal", "BibItem[" . ($a_index+1) . "]")) &&
				count($journal) > 0)
			{
				$this->tpl->setCurrentBlock("journal");
				$this->tpl->setVariable("JOURNAL_TXT_JOURNAL", $this->lng->txt("bibitem_journal"));
				$this->tpl->setVariable("JOURNAL_TXT_DELETE", $this->lng->txt("bibitem_delete"));
				$this->tpl->setVariable("JOURNAL_ACTION_DELETE", $a_formaction . "&cmd=deleteBibItem&bibItemIndex=" . $a_index . "&bibItemPath=BibItem&bibItemName=Journal");
				$this->tpl->setVariable("VAL_JOURNAL", ilUtil::prepareFormOutput($journal[0]["value"]));
				$this->tpl->setVariable("VAL_JOURNAL_NOTE", ilUtil::prepareFormOutput($journal[0]["Note"]));
				$this->tpl->setVariable("VAL_JOURNAL_NUMBER", ilUtil::prepareFormOutput($journal[0]["Number"]));
				$this->tpl->setVariable("VAL_JOURNAL_ORGANIZATION", ilUtil::prepareFormOutput($journal[0]["Organization"]));
				$this->tpl->parseCurrentBlock("journal");

			}
			else
			{
				$this->tpl->setVariable("TXT_JOURNAL", $this->lng->txt("bibitem_journal"));
			}

			/* Keyword Loop */
			if (is_array($keyword = $this->bib_obj->getElement("Keyword", "BibItem[" . ($a_index+1) . "]")) &&
				count($keyword) > 0)
			{
				for ($i = 0; $i < count($keyword); $i++)
				{
					$this->tpl->setCurrentBlock("keyword_loop");
					$this->tpl->setVariable("KEYWORD_LOOP_TXT_KEYWORD", $this->lng->txt("bibitem_keyword"));
					$this->tpl->setVariable("KEYWORD_LOOP_TXT_DELETE", $this->lng->txt("bibitem_delete"));
					$this->tpl->setVariable("KEYWORD_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteBibItem&bibItemIndex=" . $a_index . "," . $i . "&bibItemPath=BibItem&bibItemName=Keyword");
					$this->tpl->setVariable("KEYWORD_LOOP_TXT_ADD", $this->lng->txt("bibitem_add"));
					$this->tpl->setVariable("KEYWORD_LOOP_ACTION_ADD", $a_formaction . "&cmd=addBibItem&bibItemName=Keyword&bibItemPath=BibItem&bibItemIndex=" . $a_index);
					$this->tpl->setVariable("KEYWORD_LOOP_NO", $i);
					$this->tpl->setVariable("KEYWORD_LOOP_VAL", ilUtil::prepareFormOutput($keyword[$i]["value"]));
					$this->tpl->setVariable("KEYWORD_LOOP_TXT_LANGUAGE", $this->lng->txt("bibitem_language"));
					$this->tpl->setVariable("KEYWORD_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[Language][" . $i . "][Language]", $keyword[$i]["Language"]));
					$this->tpl->parseCurrentBlock("keyword_loop");
				}
			}
					
			/* Month */
			if (is_array($month = $this->bib_obj->getElement("Month", "BibItem[" . ($a_index+1) . "]")) &&
				count($month) > 0)
			{
				$this->tpl->setCurrentBlock("month");
				$this->tpl->setVariable("MONTH_TXT_MONTH", $this->lng->txt("bibitem_month"));
				$this->tpl->setVariable("MONTH_TXT_DELETE", $this->lng->txt("bibitem_delete"));
				$this->tpl->setVariable("MONTH_ACTION_DELETE", $a_formaction . "&cmd=deleteBibItem&bibItemIndex=" . $a_index . "&bibItemPath=BibItem&bibItemName=Month");
				$this->tpl->setVariable("VAL_MONTH", ilUtil::prepareFormOutput($month[0]["value"]));
				$this->tpl->parseCurrentBlock("journal");
			}
			else
			{
				$this->tpl->setVariable("TXT_MONTH", $this->lng->txt("bibitem_month"));
			}

			/* Pages */
			if (is_array($pages = $this->bib_obj->getElement("Pages", "BibItem[" . ($a_index+1) . "]")) &&
				count($pages) > 0)
			{
				$this->tpl->setCurrentBlock("pages");
				$this->tpl->setVariable("PAGES_TXT_PAGES", $this->lng->txt("bibitem_pages"));
				$this->tpl->setVariable("PAGES_TXT_DELETE", $this->lng->txt("bibitem_delete"));
				$this->tpl->setVariable("PAGES_ACTION_DELETE", $a_formaction . "&cmd=deleteBibItem&bibItemIndex=" . $a_index . "&bibItemPath=BibItem&bibItemName=Pages");
				$this->tpl->setVariable("VAL_PAGES", ilUtil::prepareFormOutput($pages[0]["value"]));
				$this->tpl->parseCurrentBlock("pages");
			}
			else
			{
				$this->tpl->setVariable("TXT_PAGES", $this->lng->txt("bibitem_pages"));
			}

			$publisher = $this->bib_obj->getElement("Publisher", "BibItem[" . ($a_index+1) . "]");
			$this->tpl->setVariable("TXT_PUBLISHER", $this->lng->txt("bibitem_publisher"));
			$this->tpl->setVariable("VAL_PUBLISHER", ilUtil::prepareFormOutput($publisher[0]["value"]));

			/* School Loop */
			if (is_array($school = $this->bib_obj->getElement("School", "BibItem[" . ($a_index+1) . "]")) &&
				count($school) > 0)
			{
				for ($i = 0; $i < count($school); $i++)
				{
					$this->tpl->setCurrentBlock("school_loop");
					$this->tpl->setVariable("SCHOOL_LOOP_TXT_SCHOOL", $this->lng->txt("bibitem_school"));
					$this->tpl->setVariable("SCHOOL_LOOP_TXT_DELETE", $this->lng->txt("bibitem_delete"));
					$this->tpl->setVariable("SCHOOL_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteBibItem&bibItemIndex=" . $a_index . "," . $i . "&bibItemPath=BibItem&bibItemName=School");
					$this->tpl->setVariable("SCHOOL_LOOP_TXT_ADD", $this->lng->txt("bibitem_add"));
					$this->tpl->setVariable("SCHOOL_LOOP_ACTION_ADD", $a_formaction . "&cmd=addBibItem&bibItemName=School&bibItemPath=BibItem&bibItemIndex=" . $a_index);
					$this->tpl->setVariable("SCHOOL_LOOP_NO", $i);
					$this->tpl->setVariable("SCHOOL_LOOP_VAL", ilUtil::prepareFormOutput($school[$i]["value"]));
					$this->tpl->parseCurrentBlock("school_loop");
				}
			}

			/* Series */
			if (is_array($series = $this->bib_obj->getElement("Series", "BibItem[" . ($a_index+1) . "]")) &&
				count($series) > 0)
			{
				$this->tpl->setCurrentBlock("series");
				$this->tpl->setVariable("SERIES_TXT_SERIES", $this->lng->txt("bibitem_series"));
				$this->tpl->setVariable("SERIES_TXT_DELETE", $this->lng->txt("bibitem_delete"));
				$this->tpl->setVariable("SERIES_ACTION_DELETE", $a_formaction . "&cmd=deleteBibItem&&bibItemPath=BibItem[" . ($a_index+1) ."]&bibItemName=Series");
				$this->tpl->setVariable("SERIES_TXT_SERIESTITLE", $this->lng->txt("bibitem_series_title"));
				if (is_array($title = $this->bib_obj->getElement("SeriesTitle", "BibItem[" . ($a_index+1) . "]/Series")))
				{
					$this->tpl->setVariable("SERIES_VAL_SERIESTITLE", ilUtil::prepareFormOutput($title[0]["value"]));
				}
				$this->tpl->setVariable("SERIES_TXT_SERIESEDITOR", $this->lng->txt("bibitem_series_editor"));
				if (is_array($editor = $this->bib_obj->getElement("SeriesEditor", "BibItem[" . ($a_index+1) . "]/Series")))
				{
					$this->tpl->setVariable("SERIES_VAL_SERIESEDITOR", ilUtil::prepareFormOutput($editor[0]["value"]));
				}
				$this->tpl->setVariable("SERIES_TXT_SERIESVOLUME", $this->lng->txt("bibitem_series_volume"));
				if (is_array($volume = $this->bib_obj->getElement("SeriesVolume", "BibItem[" . ($a_index+1) . "]/Series")))
				{
					$this->tpl->setVariable("SERIES_VAL_SERIESVOLUME", ilUtil::prepareFormOutput($volume[0]["value"]));
				}
				$this->tpl->parseCurrentBlock("series");
			}
			else
			{
				$this->tpl->setVariable("TXT_SERIES", $this->lng->txt("bibitem_series"));
			}

			$year = $this->bib_obj->getElement("Year", "BibItem[" . ($a_index+1) . "]");
			$this->tpl->setVariable("TXT_YEAR", $this->lng->txt("bibitem_year"));
			$this->tpl->setVariable("VAL_YEAR", ilUtil::prepareFormOutput($year[0]["value"]));

			/* URL || ISBN || ISSN */
			if (is_array($url = $this->bib_obj->getElement("URL", "BibItem[" . ($a_index+1) . "]")) &&
				count($url) > 0)
			{
				$url_isbn_issn["type"] = "URL";
				$url_isbn_issn["value"] = $url[0]["value"];
			}
			else if (is_array($isbn = $this->bib_obj->getElement("ISBN", "BibItem[" . ($a_index+1) . "]")) &&
				count($isbn) > 0)
			{
				$url_isbn_issn["type"] = "ISBN";
				$url_isbn_issn["value"] = $isbn[0]["value"];
			}
			else if (is_array($issn = $this->bib_obj->getElement("ISSN", "BibItem[" . ($a_index+1) . "]")) &&
				count($issn) > 0)
			{
				$url_isbn_issn["type"] = "ISSN";
				$url_isbn_issn["value"] = $issn[0]["value"];
			}
			$this->tpl->setVariable($url_isbn_issn["type"], " selected");
			$this->tpl->setVariable("VAL_URL_ISBN_ISSN", ilUtil::prepareFormOutput($url_isbn_issn["value"]));

			$this->tpl->setVariable("EDIT_ACTION", $a_formaction . "&cmd=post");
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$this->tpl->setVariable("VAL_INDEX", $a_index);
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("bibitem_save"));

		return true;
	}

	function edit($a_temp_var, $a_temp_block, $a_formaction, $a_index = 0, $a_language = "")
	{
		if ($a_language == "")
		{
			$a_language = $this->ilias->account->getLanguage();
		}
		$this->tpl->addBlockFile($a_temp_var, $a_temp_block, "tpl.bib_data_editor.html", "Modules/LearningModule");

#echo "Pr�fen, ob BibItems vorhanden sind:<br>\n";
		if (!is_array($data = $this->bib_obj->getElement("BibItem")))
		{
#echo "Nein!<br>\n";
			$this->tpl->setCurrentBlock("not_found");
			$this->tpl->setVariable("NOT_FOUND_ACTION", $a_formaction . "&cmd=addBibItem&bibItemName=BibItem");
			$this->tpl->setVariable("NOT_FOUND_TXT" , $this->lng->txt("bibitem_not_found"));
			$this->tpl->setVariable("NOT_FOUND_TXT_ADD" , $this->lng->txt("bibitem_add"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
#echo "OK!<br>\n";
#vd($data);va
			if(count($data) > 1)
			{
				for($i = 0; $i < count($data); $i++)
				{
					$edition = $this->bib_obj->getElement("Edition", "BibItem", $i);
					$year    = $this->bib_obj->getElement("Year", "BibItem", $i);
					$this->tpl->setCurrentBlock("index_loop");
					$this->tpl->setVariable("INDEX_LOOP_VALUE", $i);
					if($a_index == $i)
					{
						$this->tpl->setVariable("INDEX_LOOP_SELECTED", " selected");
					}
					$this->tpl->setVariable("INDEX_LOOP_TEXT", $edition[0]["value"] . ", " . $year[0]["value"]);
					$this->tpl->parseCurrentBlock("index_loop");
				}

				$this->tpl->setVariable("FOUND_ACTION", $a_formaction . "&cmd=editBibItem");
				$this->tpl->setVariable("FOUND_TXT" , $this->lng->txt("bibitem_choose_index"));
			}
			$this->fill($a_formaction, $a_index, $a_language);
#echo "Einlesen des BibItems:<br>\n";
			
		}
	}

	function save($a_index)
	{
		$p = "//Bibliography";
		$this->bib_obj->nested_obj->updateDomNode($p, $_POST["meta"], $a_index);
		$this->bib_obj->nested_obj->updateFromDom();
		$data = $this->bib_obj->getElement("BibItem");
		return (count($data) - 1);
	}


	/**
	* get target frame for command (command is method name without "Object", e.g. "perm")
	* @param	string		$a_cmd			command
	* @param	string		$a_target_frame	default target frame (is returned, if no special
	*										target frame was set)
	* @access	public 
	*/
	function getTargetFrame($a_cmd, $a_target_frame = "")
	{
		if ($this->target_frame[$a_cmd] != "")
		{
			return $this->target_frame[$a_cmd];
		}
		elseif (!empty($a_target_frame))
		{
			return $a_target_frame;
		}
		else
		{
			return;
		}
	}

	/**
	* set specific target frame for command
	* @param	string		$a_cmd			command
	* @param	string		$a_target_frame	default target frame (is returned, if no special
	*										target frame was set)
	* @access	public 
	*/
	function setTargetFrame($a_cmd, $a_target_frame)
	{
		$this->target_frame[$a_cmd] = $a_target_frame;
	}
}
?>
