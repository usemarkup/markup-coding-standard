<?php
declare(strict_types=1);

namespace Markup\Sniffs\Next;

use SlevomatCodingStandard\Helpers\ClassHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\ReturnTypeHint;
use SlevomatCodingStandard\Helpers\UseStatement;
use SlevomatCodingStandard\Helpers\UseStatementHelper;

class PreventExposingDomainSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    private $useStatements = [];

    /**
     * @return mixed[]
     */
    public function register()
    {
        return [
            T_OPEN_TAG,
            T_FUNCTION,
        ];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $openTagPointer
     */
    public function process(\PHP_CodeSniffer\Files\File $phpcsFile, $openTagPointer)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$openTagPointer];
        $filename = $phpcsFile->getFilename();

        if ($token['type'] == 'T_OPEN_TAG') {
            $this->useStatements[$filename] = UseStatementHelper::getUseStatements(
                $phpcsFile,
                $openTagPointer
            );

            return;
        }


        /** @var ReturnTypeHint $type */
        $type = FunctionHelper::findReturnTypeHint($phpcsFile, $openTagPointer);

        if ($type === null) {
            return;
        }

        try {
            $class = ClassHelper::getFullyQualifiedName($phpcsFile, $openTagPointer);
        } catch (\Throwable $e) {
            return;
        }

        /** @var UseStatement $useStatement */
        foreach ($this->useStatements[$filename] as $useStatement) {
            if (!class_exists($type->getTypeHint())) {
                continue;
            }

            if (stripos($useStatement->getFullyQualifiedTypeName(), $type->getTypeHint()) !== false) {
                if (stripos($useStatement->getFullyQualifiedTypeName(), 'Next\Domain\\') !== false) {
                    if (stripos($class, 'Next\Application\\') == true) {
                        $phpcsFile->addError(
                            sprintf(
                                'Application code %s should not be exposing domain layer code %s',
                                $class,
                                $useStatement->getFullyQualifiedTypeName()
                            ),
                            $openTagPointer,
                            'DomainBeingExposed'
                        );
                    }
                }
            }
        }
    }
}
