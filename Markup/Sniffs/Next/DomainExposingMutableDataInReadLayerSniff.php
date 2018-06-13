<?php
declare(strict_types=1);

namespace Markup\Sniffs\Next;

use SlevomatCodingStandard\Helpers\ClassHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\ReturnTypeHint;
use SlevomatCodingStandard\Helpers\UseStatement;
use SlevomatCodingStandard\Helpers\UseStatementHelper;

class DomainExposingMutableDataInReadLayerSniff implements \PHP_CodeSniffer\Sniffs\Sniff
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

        try {
            $class = ClassHelper::getFullyQualifiedName($phpcsFile, $openTagPointer);
        } catch (\Throwable $e) {
            return;
        }

        if (stripos($class, 'Repository\Read\\') === false) {
            return;
        }

        if (stripos($class, 'Next\Domain\\') === false) {
            return;
        }

        /** @var ReturnTypeHint $type */
        $type = FunctionHelper::findReturnTypeHint($phpcsFile, $openTagPointer);

        if ($type === null) {
            return;
        }

        /** @var UseStatement $useStatement */
        foreach ($this->useStatements[$filename] as $useStatement) {
            foreach (get_class_methods($useStatement->getFullyQualifiedTypeName()) as $method) {
                if (stripos($method, 'set') !== false) {
                    $phpcsFile->addError(
                        sprintf(
                            'Read repository should not return a model that can be mutated',
                            $class,
                            $useStatement->getFullyQualifiedTypeName()
                        ),
                        $openTagPointer,
                        'DomainExposingMutableDataInReadLayer'
                    );
                }
            }
        }
    }
}
