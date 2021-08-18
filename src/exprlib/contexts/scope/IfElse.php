<?php

namespace exprlib\contexts\scope;

use exprlib\contexts\ScopeGroup;
use exprlib\exceptions\ParsingException;

class IfElse extends ScopeGroup
{
    public function evaluate()
    {
        $result = parent::evaluate();
        if (!is_array($result) || count($result) !== 3) {
            throw new ParsingException('If must have 3 arguments, ex: if(a > 2, 1, 2)');
        }

        return $result[0] ? $result[1] : $result[2];
    }
}
