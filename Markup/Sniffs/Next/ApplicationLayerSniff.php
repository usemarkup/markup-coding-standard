<?php declare(strict_types=1);

namespace Markup\Sniffs\Next;

use SlevomatCodingStandard\Helpers\UseStatement;
use SlevomatCodingStandard\Helpers\UseStatementHelper;

class ApplicationLayerSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    private const APPLICATION_LAYER_PATH = 'src/Next/Application/';

    private const PROHIBITED_NAMESPACES = [
        'Doctrine\\Orm',
        'Doctrine\Common\Persistence',
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
        if (stripos($phpcsFile->getFilename(), self::APPLICATION_LAYER_PATH) === false) {
            return;
        }

        $useStatements = UseStatementHelper::getUseStatements($phpcsFile, $openTagPointer);

        /** @var UseStatement $useStatement */
        foreach ($useStatements as $useStatement) {
            foreach (self::PROHIBITED_NAMESPACES as $namespace) {
                if (stripos($useStatement->getFullyQualifiedTypeName(), $namespace) !== false) {
                    $phpcsFile->addError(
                        sprintf(
                            'Application code cannot reference %s namespace',
                            $useStatement->getFullyQualifiedTypeName()
                        ),
                        $openTagPointer,
                        'ApplicationReference'
                    );
                }
            }
        }
    }

}
