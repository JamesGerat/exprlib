<?php

namespace exprlib\contexts\scope;

use exprlib\contexts\Scope;

class Sum extends Scope
{
    public function evaluate()
    {
        $result = parent::evaluate();
        if (!is_array($result)) {
            return $result;
        }

        return array_sum($result);
    }
}
