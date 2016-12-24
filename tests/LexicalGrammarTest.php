<?php
/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Microsoft Corporation. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/


require_once(__DIR__ . "/../lexer.php");
require_once(__DIR__ . "/../parser.php");
require_once(__DIR__ . "/../Token.php");

use PhpParser\Token;
use PHPUnit\Framework\TestCase;


class LexicalGrammarTest extends TestCase {
    public function run(PHPUnit_Framework_TestResult $result = null) : PHPUnit_Framework_TestResult {
        if (!isset($GLOBALS["GIT_CHECKOUT"])) {
            $GLOBALS["GIT_CHECKOUT"] = true;
            exec("git checkout " . __DIR__ . "/cases/lexical/*.php.tokens");
        }

        $result->addListener(new class() extends PHPUnit_Framework_BaseTestListener  {
            function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time) {
                if (isset($test->expectedTokensFile) && isset($test->tokens)) {
                    file_put_contents($test->expectedTokensFile, str_replace("\r\n", "\n", $test->tokens));
                }
                parent::addFailure($test, $e, $time);
            }
        });

        $result = parent::run($result);
        return $result;
    }


    /**
     * @dataProvider lexicalProvider
     */
    public function testOutputTokenClassificationAndLength($testCaseFile, $expectedTokensFile) {
        $expectedTokens = str_replace("\r\n", "\n", file_get_contents($expectedTokensFile));
        $lexer = new \PhpParser\Lexer($testCaseFile);
        $GLOBALS["SHORT_TOKEN_SERIALIZE"] = true;
        $tokens = str_replace("\r\n", "\n", json_encode($lexer->getTokensArray(), JSON_PRETTY_PRINT));
        $GLOBALS["SHORT_TOKEN_SERIALIZE"] = false;
        $this->expectedTokensFile = $expectedTokensFile;
        $this->tokens = $tokens;
        $this->assertEquals($expectedTokens, $tokens, "input: $testCaseFile\r\nexpected: $expectedTokensFile");
    }

    public function lexicalProvider() {
        $testCases = glob(__dir__ . "/cases/lexical/*.php");
        $tokensExpected = glob(__dir__ . "/cases/lexical/*.php.tokens");

        $skipped = json_decode(file_get_contents(__DIR__ . "/skipped.json"));

        $testProviderArray = array();
        foreach ($testCases as $index=>$testCase) {
            if (in_array(basename($testCase), $skipped)) {
                continue;
            }
            $testProviderArray[basename($testCase)] = [$testCase, $tokensExpected[$index]];
        }

        return $testProviderArray;
    }

    /**
     * @dataProvider lexicalSpecProvider
     */
    public function testSpecTokenClassificationAndLength($testCaseFile, $expectedTokensFile) {
        $lexer = new \PhpParser\Lexer($testCaseFile);
        $tokensArray = $lexer->getTokensArray();
        $tokens = str_replace("\r\n", "\n", json_encode($tokensArray, JSON_PRETTY_PRINT));
        file_put_contents($expectedTokensFile, $tokens);
        foreach ($tokensArray as $child) {
            if ($child instanceof Token) {
                $this->assertNotEquals(\PhpParser\TokenKind::Unknown, $child->kind, "input: $testCaseFile\r\nexpected: $expectedTokensFile");
                $this->assertNotEquals(\PhpParser\TokenKind::SkippedToken, $child->kind, "input: $testCaseFile\r\nexpected: $expectedTokensFile");
                $this->assertNotEquals(\PhpParser\TokenKind::MissingToken, $child->kind, "input: $testCaseFile\r\nexpected: $expectedTokensFile");
            }
        }
//        $tokens = str_replace("\r\n", "\n", json_encode($tokens, JSON_PRETTY_PRINT));
//        $this->assertEquals($expectedTokens, $tokens, "input: $testCaseFile\r\nexpected: $expectedTokensFile");

    }

    public function lexicalSpecProvider() {
        $testCases = glob(__dir__ . "/cases/php-langspec/**/*.php");
        $tokensExpected = glob(__dir__ . "/cases/php-langspec/**/*.php.tree");

        $testProviderArray = array();
        foreach ($testCases as $index=>$testCase) {
            $testProviderArray[basename($testCase)] = [$testCase, $tokensExpected[$index]];
        }

        return $testProviderArray;
    }

}