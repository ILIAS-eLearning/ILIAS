<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Forum/classes/class.ilForumBbCodePurifier.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilForumBbCodePurifierTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var bool
	 */
	protected $backupGlobals = false;

	/**
	 *
	 */
	public function setUp()
	{
	}

	/**
	 *
	 */
	public function testOneBbCodeTagWithAnEvenNumberOfOpeningAndClosingTags()
	{
		$p = new ilForumBbCodePurifier('quote', 'blockquote');
		$input    = '[quote][/quote]';
		$expected = '[quote][/quote]';
		$purified = $p->purify($input);
	
		$this->assertEquals($expected, $purified);
	}

	/**
	 *
	 */
	public function testOneBbCodeTagWithAnOddNumberOfOpeningAndClosingTags()
	{
		$p = new ilForumBbCodePurifier('quote', 'blockquote');

		$input    = '[quote]';
		$expected = '[quote][/quote]';
		$purified = $p->purify($input);
		var_dump($expected);
		var_dump($purified);
		$this->assertEquals($expected, $purified);

		$input    = '[/quote]';
		$expected = '[quote][/quote]';
		$purified = $p->purify($input);
		var_dump($expected);
		var_dump($purified);
		
		$this->assertEquals($expected, $purified);
	}

	/**
	 *
	 */
	public function testNestedBbCodeTagsWithAnEvenNumberOfOpeningAndClosingTags()
	{
		$p = new ilForumBbCodePurifier('quote', 'blockquote');

		$input    = '[quote][quote][/quote][/quote]';
		$expected = '[quote][quote][/quote][/quote]';
		$purified = $p->purify($input);
		var_dump($expected);
		var_dump($purified);
		$this->assertEquals($expected, $purified);

		$input    = '[quote][quote][quote][/quote][/quote][/quote]';
		$expected = '[quote][quote][quote][/quote][/quote][/quote]';
		$purified = $p->purify($input);
		var_dump($expected);
		var_dump($purified);
		$this->assertEquals($expected, $purified);
	}

	/**
	 *
	 */
	public function testNestedBbCodeTagsWithAnOddNumberOfOpeningAndClosingTags()
	{
		$p = new ilForumBbCodePurifier('quote', 'blockquote');

		$input    = '[quote][quote][/quote][/quote][/quote]';
		$expected = '[quote][quote][quote][/quote][/quote][/quote]';
		$purified = $p->purify($input);
		var_dump($expected);
		var_dump($purified);
		$this->assertEquals($expected, $purified);

		$input    = '[quote][quote][quote][/quote][/quote]';
		$expected = '[quote][quote][quote][/quote][/quote][/quote]';
		$purified = $p->purify($input);
		var_dump($expected);
		var_dump($purified);
		$this->assertEquals($expected, $purified);
	}
	
	public function testNestedBbCodeTagsWIthHtmlEquivalent()
	{
		$p = new ilForumBbCodePurifier('quote', 'blockquote');
		
		$input    = '[quote][quote]<blockquote>test 1[/quote]<blockquote>[quote]test 2</blockquote></blockquote>[/quote]<blockquote>[quote]test 3</blockquote>';
		$expected = '[quote][quote]<blockquote>[quote]test 1[/quote]<blockquote>[quote]test 2[/quote]</blockquote></blockquote>[quote][/quote]<blockquote>[quote]test 3[/quote]</blockquote>[/quote][/quote]';
		$purified = $p->purify($input);
		var_dump($expected);
		var_dump($purified);
		$this->assertEquals($expected, $purified);

		$input    = '[quote][quote]<blockquote style="color: red">[quote]test</blockquote>[/quote][/quote]';
		$expected = '[quote][quote]<blockquote style="color: red">[quote]test[/quote]</blockquote>[/quote][/quote]';
		$purified = $p->purify($input);
		var_dump($expected);
		var_dump($purified);
		$this->assertEquals($expected, $purified);
				
		$input 		= "[quote][quote]<blockquote>[quote]</blockquote>blablablalba<blockquote>[quote][quote][/quote]</blockquote>[/quote][/quote]";
		$expected 	= "[quote][quote]<blockquote>[quote][/quote]</blockquote>blablablalba<blockquote>[quote][quote][/quote][/quote]</blockquote>[/quote][/quote]";
		$purified = $p->purify($input);
		var_dump($expected);
		var_dump($purified);
		$this->assertEquals($expected, $purified);

		$input    = '[quote][quote]<blockquote style="color: red">test[/quote]</blockquote>[/quote][/quote]';
		$expected = '[quote][quote]<blockquote style="color: red">[quote]test[/quote]</blockquote>[/quote][/quote]';
		$purified = $p->purify($input);
		var_dump($expected);
		var_dump($purified);
		$this->assertEquals($expected, $purified);
	}
}
