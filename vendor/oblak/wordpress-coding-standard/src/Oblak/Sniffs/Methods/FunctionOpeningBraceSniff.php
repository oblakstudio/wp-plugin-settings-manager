<?php

/**
 * Checks that there are no blank spaces after the opening brace of a function or closure.
 *
 * @package Oblak\WordPress
 */

namespace Oblak\WordPress\Sniffs\Methods;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class FunctionOpeningBraceSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_FUNCTION,
            T_CLOSURE,
        ];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in
     *                                               the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $openBrace = $tokens[$stackPtr]['scope_opener'];
        $nextContent = $phpcsFile->findNext(T_WHITESPACE, ($openBrace + 1), null, true);
        $found = ($tokens[$nextContent]['line'] - $tokens[$openBrace]['line'] - 1);

        if ($found === 0) {
            return;
        }

        $error = 'There should be no blank lines after the opening brace; found %s blank lines';
        $data = [$found];
        $fix = $phpcsFile->addFixableError($error, $openBrace, 'SpacingAfterOpen', $data);

        if ($fix) {
            $phpcsFile->fixer->beginChangeset();
            for ($i = ($openBrace + 1); $i < $nextContent; $i++) {
                if ($tokens[$i]['line'] > $tokens[$openBrace]['line'] + 1) {
                    break;
                }
                if ($tokens[$i]['line'] === $tokens[$openBrace]['line'] + 1) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
            }
            $phpcsFile->fixer->endChangeset();
        }
    }
}
