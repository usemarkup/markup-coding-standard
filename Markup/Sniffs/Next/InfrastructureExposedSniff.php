<?php declare(strict_types=1);

namespace Markup\Sniffs\Next;

use SlevomatCodingStandard\Helpers\UseStatement;
use SlevomatCodingStandard\Helpers\UseStatementHelper;

class InfrastructureExposedSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    private const NEXT_PATH = 'src/Next/';
    private const INFRASTRUCTURE_LAYER_PATH = 'src/Next/Infrastructure/';
    private const INFRASTRUCTURE_NS = 'Next\Infrastructure';

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
        if (stripos($phpcsFile->getFilename(), self::NEXT_PATH) === false) {
            return;
        }

        if (stripos($phpcsFile->getFilename(), self::INFRASTRUCTURE_LAYER_PATH) !== false) {
            return;
        }

        $useStatements = UseStatementHelper::getFileUseStatements($phpcsFile);

        /** @var UseStatement $useStatement */
        foreach (array_shift($useStatements) ?? [] as $useStatement) {
            if (stripos($useStatement->getFullyQualifiedTypeName(), self::INFRASTRUCTURE_NS) !== false) {
                $phpcsFile->addError(
                    sprintf(
                        'Infrastructure cannot be referenced here as the implementation is configurable'
                    ),
                    $openTagPointer,
                    'InfrastructureReference'
                );
            }
        }
    }

}
