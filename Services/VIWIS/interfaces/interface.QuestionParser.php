<?php

interface QuestionParser {

	/**
	 *	Parse a xml, it will check the contents and read medatata.
	 *
	 *	@return	questionParser
	 *	@param	string	$xml_string
	 */
	public function parseXML($xml_string);

	/**
	 *	Get the title of question corresponding to last parsed xml.
	 *
	 *	@return	questionParser
	 */
	public function title();

	/**
	 *	Get the id of question corresponding to last parsed xml.
	 *
	 *	@return	string	$title
	 */
	public function id();

	/**
	 *	Get the question-text of question corresponding to last parsed xml.
	 *
	 *	@return	string 	$id
	 */
	public function question();

	/**
	 *	Get the answer options of question corresponding to last parsed xml.
	 *
	 *	@return	string[answer_id]	$answers
	 */
	public function answers();

	/**
	 *	Get the correct answer ids of question corresponding to last parsed xml.
	 *
	 *	@return	string[]	$correct_answers
	 */
	public function correctAnswerIds();

	/**
	 *	Get the type of question corresponding to last parsed xml (single, multi...).
	 *
	 *	@return	string	$type
	 */
	public function type();
}