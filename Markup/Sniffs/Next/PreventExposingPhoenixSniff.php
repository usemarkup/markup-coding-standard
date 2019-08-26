<?php
declare(strict_types=1);

namespace Markup\Sniffs\Next;

use SlevomatCodingStandard\Helpers\ClassHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\ReturnTypeHint;
use SlevomatCodingStandard\Helpers\UseStatement;
use SlevomatCodingStandard\Helpers\UseStatementHelper;

class PreventExposingPhoenixSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    private const NEXT_CODE_PATH = 'src/Next/';

    private const BLACKLIST_NAMESPACES = [
        'Phoenix\\',
    ];

    /**
     * @return mixed[]
     */
    public function register()
    {
        return [
            T_OPEN_TAG,
        ];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $openTagPointer
     */
    public function process(\PHP_CodeSniffer\Files\File $phpcsFile, $openTagPointer)
    {
        if (stripos($phpcsFile->getFilename(), self::NEXT_CODE_PATH) === false) {
            return;
        }

        $useStatements = UseStatementHelper::getUseStatements($phpcsFile, $openTagPointer);

        $blacklist = self::BLACKLIST_NAMESPACES;

        /** @var UseStatement $useStatement */
        foreach (array_shift($useStatements) ?? [] as $useStatement) {
            $found = false;

            foreach ($blacklist as $namespace) {
                if (stripos($useStatement->getFullyQualifiedTypeName(), $namespace) !== false) {
                    $statement = $useStatement->getFullyQualifiedTypeName();

                    $phpcsFile->addError(
                        sprintf(
                            'Next cannot be consuming Phoenix code',
                            $useStatement->getFullyQualifiedTypeName()
                        ),
                        $openTagPointer,
                        'PhoenixUsed'
                    );
                }
            }
        }
    }
}
