<?php

namespace exprlib\contexts\scope;

use exprlib\contexts\Scope;
use exprlib\exceptions\ParsingException;

class Cosin extends Scope
{
    public function evaluate()
    {
        if (is_array($result = parent::evaluate())) {
            throw new ParsingException('exp accept only one argument');
        }
        return cos(deg2rad($result));
    }
}
