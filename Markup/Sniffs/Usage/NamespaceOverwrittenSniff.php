<?php declare(strict_types=1);

namespace Markup\Sniffs\Usage;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\UseStatementHelper;

class NamespaceOverwrittenSniff implements Sniff
{
    private const BLACKLIST_NAMESPACES = [
        [
            'namespace' => 'Knp\\DoctrineBehaviors\\Model\\Timestampable\\Timestampable',
            'require_use' => 'Phoenix\\Common\\Database\\Doctrine\\Entity\\Timestampable',
        ],
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
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $openTagPointer
     */
    public function process(File $phpcsFile, $openTagPointer)
    {
        $useStatements = UseStatementHelper::getUseStatements($phpcsFile, $openTagPointer);

        foreach ($useStatements as $useStatement) {
            foreach (self::BLACKLIST_NAMESPACES as $namespace) {
                if (stripos($useStatement->getFullyQualifiedTypeName(), $namespace['namespace']) !== false) {
                    $phpcsFile->addError(
                        sprintf(
                            "Class %s is overwritten, use %s instead",
                            $useStatement->getFullyQualifiedTypeName(),
                            $namespace['require_use']
                        ),
                        $useStatement->getPointer(),
                        'BlacklistedNamespace'
                    );
                }
            }
        }
    }
}
