<?php declare(strict_types=1);

namespace Markup\Sniffs\Symfony;

use SlevomatCodingStandard\Helpers\FunctionHelper;

class PreventRequestStackInConstructorSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    /**
     * @return mixed[]
     */
    public function register()
    {
        return [
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
        if (!FunctionHelper::isMethod($phpcsFile, $openTagPointer)) {
            return;
        }

        $name = FunctionHelper::getName($phpcsFile, $openTagPointer);

        if ($name !== '__construct') {
            return;
        }

        foreach ($phpcsFile->getMethodParameters($openTagPointer) as $parameter) {
            if (isset($parameter['type_hint']) && $parameter['type_hint'] === 'RequestStack') {
                $phpcsFile->addError(
                    sprintf(
                        'Using RequestStack as a constructor argument ties the implementation to Reqest only which is not useful, see alteratives or context pass the Request from the HTTP Transport Layer',
                        $name
                    ),
                    $openTagPointer,
                    'PreventRequestStackInConstructor'
                );
            }
        }
    }

}
