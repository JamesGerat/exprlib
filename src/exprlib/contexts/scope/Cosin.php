<?php

namespace exprlib\contexts\scope;

use exprlib\contexts\ScopeGroup;
use exprlib\exceptions\ParsingException;

class Cosin extends ScopeGroup
{
    public function evaluate()
    {
        if (is_array($result = parent::evaluate())) {
            throw new ParsingException('exp accept only one argument');
        }
        return cos(deg2rad($result));
    }
}
