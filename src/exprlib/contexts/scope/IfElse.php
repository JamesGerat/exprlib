<?php

namespace exprlib\contexts\scope;

use exprlib\contexts\ScopeGroup;
use exprlib\exceptions\ParsingException;

class IfElse extends ScopeGroup
{
    public function evaluate()
    {
        if (!empty($this->operations)) {
            $this->addScopeGroup($this->operations);
        }

        if (!is_array($this->scopeGroups) || count($this->scopeGroups) !== 3) {
            throw new ParsingException('If must have 3 arguments, ex: if(a > 2, 1, 2)');
        }

        return $this->scopeGroups[0]->evaluate() ? $this->scopeGroups[1]->evaluate() : $this->scopeGroups[2]->evaluate();
    }
}
