<?php declare(strict_types=1);

namespace Markup\Sniffs\Symfony;

use SlevomatCodingStandard\Helpers\FunctionHelper;

class ControllerPublicMethodNamesSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    private const CONTROLLER_NAMESPACE = '/Controller/';
    private const CONTROLLER_FILE_SUFFIX = 'Controller.php';
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
        if (stripos($phpcsFile->getFilename(), self::CONTROLLER_NAMESPACE) === false) {
            return;
        }

        if (stripos($phpcsFile->getFilename(), self::CONTROLLER_FILE_SUFFIX) === false) {
            return;
        }

        if (!FunctionHelper::isMethod($phpcsFile, $openTagPointer)) {
            return;
        }

        $name = FunctionHelper::getName($phpcsFile, $openTagPointer);

        try {
            $properties = $phpcsFile->getMethodProperties($openTagPointer);

            if (!isset($properties['scope']) || $properties['scope'] !== 'public') {
                return;
            }
        } catch (\Throwable $e) {
            return;
        }

        if (in_array($name, ['__construct', '__invoke'])) {
            return;
        }
    }

}
