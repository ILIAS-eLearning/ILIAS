<?php

declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;

class ilUtilsPreventTest extends TestCase
{
    private function extractMethodNames(array $reflection_methods): array
    {
        return array_map(function (ReflectionMethod $m): string {
            return $m->getName();
        }, $reflection_methods);
    }

    public function testAmountOfMethodsInUtil(): void
    {
        $r = new ReflectionClass(ilUtil::class);
        $methods = $this->extractMethodNames($r->getMethods());
        $amount_of_methods = count($methods);
        $this->assertEquals(47, $amount_of_methods);
        $this->assertEquals([
            0 => 'getImageTagByType',
            1 => 'getImagePath',
            2 => 'getHtmlPath',
            3 => 'getStyleSheetLocation',
            4 => 'getNewContentStyleSheetLocation',
            5 => 'switchColor',
            6 => 'makeClickable',
            7 => 'is_email',
            8 => 'isLogin',
            9 => 'img',
            10 => 'deliverData',
            11 => 'appendUrlParameterString',
            12 => 'stripSlashes',
            13 => 'stripOnlySlashes',
            14 => 'secureString',
            15 => 'getSecureTags',
            16 => 'maskSecureTags',
            17 => 'unmaskSecureTags',
            18 => 'securePlainString',
            19 => 'htmlencodePlainString',
            20 => 'maskAttributeTag',
            21 => 'unmaskAttributeTag',
            22 => 'maskTag',
            23 => 'unmaskTag',
            24 => 'secureLink',
            25 => 'stripScriptHTML',
            26 => 'secureUrl',
            27 => 'extractParameterString',
            28 => 'yn2tf',
            29 => 'tf2yn',
            30 => 'deducibleSize',
            31 => 'redirect',
            32 => 'insertInstIntoID',
            33 => 'groupNameExists',
            34 => 'isWindows',
            35 => 'now',
            36 => '_getObjectsByOperations',
            37 => 'isHTML',
            38 => '__extractRefId',
            39 => '__extractId',
            40 => '_sortIds',
            41 => 'getSystemMessageHTML',
            42 => 'setCookie',
            43 => '_getHttpPath',
            44 => 'parseImportId',
            45 => 'fmtFloat',
            46 => 'formatSize',
        ], $methods);
    }

    public function testAmountOfMethodsInArrayUtil(): void
    {
        $r = new ReflectionClass(ilArrayUtil::class);
        $methods = $this->extractMethodNames($r->getMethods());
        $amount_of_methods = count($methods);
        $this->assertEquals(8, $amount_of_methods);
        $this->assertEquals([
            0 => 'quoteArray',
            1 => 'stripSlashesRecursive',
            2 => 'stripSlashesArray',
            3 => 'sortArray',
            4 => 'sort_func',
            5 => 'sort_func_numeric',
            6 => 'mergesort',
            7 => 'stableSortArray',
        ], $methods);
    }

    public function testAmountOfMethodsInShellUtil(): void
    {
        $r = new ReflectionClass(ilShellUtil::class);
        $methods = $this->extractMethodNames($r->getMethods());
        $amount_of_methods = count($methods);
        $this->assertEquals(9, $amount_of_methods);
        $this->assertEquals([
            0 => 'resizeImage',
            1 => 'escapeShellArg',
            2 => 'processConvertVersion',
            3 => 'escapeShellCmd',
            4 => 'execQuoted',
            5 => 'isConvertVersionAtLeast',
            6 => 'getConvertCmd',
            7 => 'convertImage',
            8 => 'execConvert',
        ], $methods);
    }

    public function testAmountOfMethodsInLegacyFormUtil(): void
    {
        $r = new ReflectionClass(ilLegacyFormElementsUtil::class);
        $methods = $this->extractMethodNames($r->getMethods());
        $amount_of_methods = count($methods);
        $this->assertEquals(7, $amount_of_methods);
        $this->assertEquals([
            0 => 'prepareFormOutput',
            1 => 'period2String',
            2 => 'prepareTextareaOutput',
            3 => 'makeTimeSelect',
            4 => 'formCheckbox',
            5 => 'formSelect',
            6 => 'formRadioButton',
        ], $methods);
    }

    public function testAmountOfMethodsInStrUtil(): void
    {
        $r = new ReflectionClass(ilStr::class);
        $methods = $this->extractMethodNames($r->getMethods());
        $amount_of_methods = count($methods);
        $this->assertEquals(12, $amount_of_methods);
        $this->assertEquals([
            0 => 'subStr',
            1 => 'strPos',
            2 => 'strIPos',
            3 => 'strLen',
            4 => 'strToLower',
            5 => 'strToUpper',
            6 => 'strCmp',
            7 => 'shortenText',
            8 => 'isUtf8',
            9 => 'convertUpperCamelCaseToUnderscoreCase',
            10 => 'shortenTextExtended',
            11 => 'shortenWords',
        ], $methods);
    }
}
