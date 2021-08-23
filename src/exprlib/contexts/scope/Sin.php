<?php

namespace exprlib\contexts\scope;

use exprlib\contexts\Scope;
use exprlib\exceptions\ParsingException;

class Sin extends Scope
{
    public function evaluate()
    {
        if (is_array($result = parent::evaluate())) {
            throw new ParsingException('exp accept only one argument');
        }
        return sin(deg2rad($result));
    }
}
