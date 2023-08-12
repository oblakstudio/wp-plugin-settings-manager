<?php

/**
 * Sniff to ensure hooks have doc comments.
 */

namespace WooCommerce\Sniffs\Commenting;

use PHP_CodeSniffer\Util\Tokens;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Comment tags sniff.
 */
class HookCommentSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [
        'PHP',
    ];

    /**
     * A list of specfic hooks to listen to.
     *
     * @var array
     */
    public $hooks = [
        'do_action',
        'apply_filters',
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register(): array
    {
        return [T_STRING];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (!in_array($tokens[$stackPtr]['content'], $this->hooks)) {
            return;
        }

        $previousComment = $phpcsFile->findPrevious(Tokens::$commentTokens, ($stackPtr - 1));

        if (false === $previousComment || $tokens[$previousComment]['line'] + 1 !== $tokens[$stackPtr]['line']) {
            $phpcsFile->addWarning(
                'A hook was found, but was not accompanied by a docblock comment on the line above to clarify the meaning of the hook.',
                $stackPtr,
                'CommentMissing'
            );
        }

        $isShortComment = $tokens[$previousComment]['code'] === T_COMMENT;
        $hasHookLink = strpos($tokens[$previousComment]['content'], 'Documented in ') !== false;

        if ($isShortComment && !$hasHookLink) {
            $phpcsFile->addWarning(
                'A hook shorthand comment was found, but does not explain where the hook is documented. It must begin with `Documented in`',
                $stackPtr,
                'LinkMissing'
            );
            return;
        }

        if ($tokens[$previousComment]['code'] === T_DOC_COMMENT_CLOSE_TAG) {
            $commentStart = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, ($previousComment - 1));
            $paramTags = [];
            $sinceFound = false;
            $returnFound = false;

            foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
                if ($tokens[$tag]['content'] === '@return') {
                    $returnFound = true;
                }

                if ($tokens[$tag]['content'] === '@since') {
                    $version_token = $tokens[$tag + 2];
                    $sinceFound = true;
                    if (!preg_match('/\d+\.\d+(\.\d+)?/', $version_token['content'])) {
                        $phpcsFile->addWarning(
                            'A "@since" tag was found but no valid version declared. Required format <x.x> or <x.x.x>',
                            $stackPtr,
                            'SinceCommentWrong'
                        );
                    }
                }

                if ($tokens[$tag]['content'] === '@param') {
                    $paramTags[] = $tag;
                }
            }

            if ($tokens[$stackPtr]['content'] === 'apply_filters' && !$returnFound) {
                $phpcsFile->addWarning(
                    'A "@return" tag was not found.',
                    $stackPtr,
                    'ReturnCommentMissing'
                );
            }

            if (!$sinceFound) {
                $phpcsFile->addWarning(
                    'A "@since" tag was not found.',
                    $stackPtr,
                    'SinceCommentMissing'
                );
            }

            $openPar = $tokens[$stackPtr + 1]['parenthesis_opener'] ?? false;
            $ClosePar = $tokens[$stackPtr + 1]['parenthesis_closer'] ?? false;
            $argumentCount = 0;

            for ($i = $openPar + 1; $i < $ClosePar; $i++) {
                if ($tokens[$i]['code'] === T_VARIABLE) {
                    $argumentCount++;
                }
            }

            if (count($paramTags) !== $argumentCount) {
                $phpcsFile->addWarning(
                    'The number of "@param" tags does not match the number of arguments in the function call.',
                    $stackPtr,
                    'ParamCommentMissing'
                );
            }
        }
    }
}
