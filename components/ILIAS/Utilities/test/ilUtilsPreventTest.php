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
        $this->assertEquals(48, $amount_of_methods);
        $this->assertEquals([
            0 => 'getImageTagByType',
            1 => 'getImagePath',
            2 => 'getHtmlPath',
            3 => 'getStyleSheetLocation',
            4 => 'getNewContentStyleSheetLocation',
            5 => 'switchColor',
            6 => 'makeClickable',
            7 => 'replaceLinkProperties',
            8 => 'is_email',
            9 => 'isLogin',
            10 => 'img',
            11 => 'deliverData',
            12 => 'appendUrlParameterString',
            13 => 'stripSlashes',
            14 => 'stripOnlySlashes',
            15 => 'secureString',
            16 => 'getSecureTags',
            17 => 'maskSecureTags',
            18 => 'unmaskSecureTags',
            19 => 'securePlainString',
            20 => 'htmlencodePlainString',
            21 => 'maskAttributeTag',
            22 => 'unmaskAttributeTag',
            23 => 'maskTag',
            24 => 'unmaskTag',
            25 => 'secureLink',
            26 => 'stripScriptHTML',
            27 => 'secureUrl',
            28 => 'extractParameterString',
            29 => 'yn2tf',
            30 => 'tf2yn',
            31 => 'deducibleSize',
            32 => 'redirect',
            33 => 'insertInstIntoID',
            34 => 'groupNameExists',
            35 => 'isWindows',
            36 => 'now',
            37 => '_getObjectsByOperations',
            38 => 'isHTML',
            39 => '__extractRefId',
            40 => '__extractId',
            41 => '_sortIds',
            42 => 'getSystemMessageHTML',
            43 => 'setCookie',
            44 => '_getHttpPath',
            45 => 'parseImportId',
            46 => 'fmtFloat',
            47 => 'formatSize',
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
