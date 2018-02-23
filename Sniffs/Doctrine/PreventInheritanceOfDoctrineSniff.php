<?php declare(strict_types=1);

namespace Markup\Sniffs\Doctrine;

use Doctrine\ORM\EntityRepository;
use SlevomatCodingStandard\Helpers\ClassHelper;

class PreventInheritanceOfDoctrineSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    /**
     * @return mixed[]
     */
    public function register()
    {
        return [
            T_CLASS,
        ];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $openTagPointer
     */
    public function process(\PHP_CodeSniffer\Files\File $phpcsFile, $openTagPointer)
    {
        try {
            $parents = class_parents(ClassHelper::getFullyQualifiedName($phpcsFile, $openTagPointer));
        } catch (\Throwable $t) {
            return;
        }

        if (!empty($parents) && in_array(EntityRepository::class, $parents)) {
            $phpcsFile->addError(
                sprintf(
                    'extending EntityRepository exposes Doctrine, this isn\'t ideal or recommended, seek advice.'
                ),
                $openTagPointer,
                __CLASS__
            );
        }
    }

}
