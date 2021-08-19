<?php

namespace exprlib\contexts\scope;

use exprlib\contexts\ScopeGroup;

class Sum extends ScopeGroup
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
