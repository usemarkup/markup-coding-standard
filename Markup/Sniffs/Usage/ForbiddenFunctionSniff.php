<?php declare(strict_types=1);

namespace Markup\Sniffs\Usage;

use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff as GenericForbiddenFunctionsSniff;

class ForbiddenFunctionSniff extends GenericForbiddenFunctionsSniff
{
    protected $patternMatch = true;

    public $forbiddenFunctions = [
        '^json_encode$' => 'Markup\Json\Encoder::encode',
        '^json_decode$' => 'Markup\Json\Encoder::decode',
    ];
}
