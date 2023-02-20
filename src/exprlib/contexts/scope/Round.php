<?php

namespace exprlib\contexts\scope;

use exprlib\contexts\ScopeGroup;
use exprlib\exceptions\ParsingException;

class Round extends ScopeGroup
{
    public function evaluate()
    {
        $result = parent::evaluate();
        if (!is_array($result) || count($result) !== 2) {
            throw new ParsingException('Power must have 2 arguments, ex: Round(10,2)');
        }

        return round($result[0], $result[1]);
    }
}
