<?php

namespace exprlib\contexts;

/**
 * ScopeGroup
 *
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class ScopeGroup extends Scope
{
    protected $scopeGroups = [];
    protected $groups = [];

    public function evaluate()
    {
        if (!empty($this->operations)) {
            $this->addScopeGroup($this->operations);
        }

        foreach ($this->scopeGroups as $scopeGroup) {
            $this->groups[] = $scopeGroup->evaluate();
        }

        if (is_array($this->groups) && count($this->groups) === 1) {
            return end($this->groups);
        }
        return $this->groups;
    }

    public function addScopeGroup($group)
    {
        $scope = new Scope();
        $scope->setBuilder($this->builder);
        foreach ($group as $p) {
            $scope->addOperation($p);
        }

        $this->scopeGroups[] = $scope;
    }

    public function getGroups()
    {
        return $this->groups;
    }
}
