<?php declare(strict_types=1);

namespace Markup\Sniffs\Next;

use SlevomatCodingStandard\Helpers\UseStatement;
use SlevomatCodingStandard\Helpers\UseStatementHelper;

class DomainLayerSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    private const DOMAIN_LAYER_PATH = 'src/Next/Domain/';

    private const WHITELIST_NAMESPACES = [
        'Next\\Domain\\Common',

        # Doctrine Common Collections is allowed in the namespace
        'Doctrine\\Common\\Collections',

        # To be amended at a later stage
        'SimpleBus\\',
        
        'Psr\\Log\\',
        
        # Allow Pagination Component
        'Next\\Component\\Pagination',
        
        # Allow Collection Component
        'Next\\Component\\Collection',

        # Allow Event Component
        'Next\\Component\\Event',
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
        if (stripos($phpcsFile->getFilename(), self::DOMAIN_LAYER_PATH) === false) {
            return;
        }

        $currentDomain = null;

        if (preg_match(
                sprintf('/%s(.*?)\/./i', addcslashes(self::DOMAIN_LAYER_PATH, '/')),
                $phpcsFile->getFilename(),
                $matches
            ) !== false) {
            if (!empty($matches)) {
                $currentDomain = end($matches);
            }
        }

        if ($currentDomain == null) {
            return;
        }

        $useStatements = UseStatementHelper::getUseStatements($phpcsFile, $openTagPointer);

        $whitelist = self::WHITELIST_NAMESPACES;
        $whitelist[] = sprintf('\\Domain\\%s', $currentDomain);

        /** @var UseStatement $useStatement */
        foreach ($useStatements as $useStatement) {
            $found = false;

            foreach ($whitelist as $namespace) {
                if (stripos($useStatement->getFullyQualifiedTypeName(), $namespace) !== false) {
                    $found = true;
                }
            }

            if (!$found) {
                $statement = $useStatement->getFullyQualifiedTypeName();

                // If its a NATIVE php class/interface its ok
                if (class_exists($statement, false) || interface_exists($statement, false)) {
                    return;
                }

                $phpcsFile->addError(
                    sprintf(
                        'Domain code cannot reference %s as its outside of the domain, seek advice on this matter.',
                        $useStatement->getFullyQualifiedTypeName()
                    ),
                    $openTagPointer,
                    'OutsideReference'
                );
            }
        }
    }

}
