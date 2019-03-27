<?php
declare(strict_types=1);

namespace Markup\Sniffs\Next;

use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\ReturnTypeHint;
use SlevomatCodingStandard\Helpers\TypeHintHelper;
use SlevomatCodingStandard\Helpers\UseStatementHelper;

class PreventingEntitiesInReadlayerSniff implements \PHP_CodeSniffer\Sniffs\Sniff
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

        if (stripos($filename, 'Repository/Read/') === false) {
            return;
        }

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

        $type = TypeHintHelper::getFullyQualifiedTypeHint($phpcsFile, $openTagPointer, $type->getTypeHint());

        if (stripos($type, 'Entity\\') !== false) {
            $phpcsFile->addError(
                sprintf(
                    'Read layer methods cannot return mutable objects %s in %s',
                    $type,
                    $filename
                ),
                $openTagPointer,
                'ReadLayer'
            );
        }


    }
}

