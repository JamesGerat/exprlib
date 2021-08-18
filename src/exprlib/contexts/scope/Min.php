<?php

namespace exprlib\contexts\scope;

use exprlib\contexts\ScopeGroup;

class Min extends ScopeGroup
{
    public function evaluate()
    {
        $result = parent::evaluate();
        if (!is_array($result)) {
            return $result;
        }

        return min($result);
    }
}
