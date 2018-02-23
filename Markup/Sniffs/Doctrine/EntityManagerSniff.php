<?php declare(strict_types=1);

namespace Markup\Sniffs\Doctrine;

use SlevomatCodingStandard\Helpers\FunctionHelper;

class EntityManagerSniff implements \PHP_CodeSniffer\Sniffs\Sniff
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
        if (FunctionHelper::getName($phpcsFile, $openTagPointer) === '__construct') {
            $constructorArgumentTypes = FunctionHelper::getParametersTypeHints($phpcsFile, $openTagPointer);

            foreach ($constructorArgumentTypes as $argumentType) {
                if ($argumentType === null) {
                    continue;
                }

                if (stripos($argumentType->getTypeHint(), 'EntityManager') !== false) {
                    $phpcsFile->addError(
                        sprintf(
                            'Using the EntityManager via the constructor can be problematic, using the ManagerRegistry is advised'
                        ),
                        $openTagPointer,
                        'EntityManagerFound'
                    );
                }
            }

        }
    }

}
