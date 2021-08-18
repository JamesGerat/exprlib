<?php

namespace exprlib\contexts;

use exprlib\exceptions\DivisionByZeroException;
use exprlib\exceptions\Exception;
use exprlib\exceptions\OutOfScopeException;
use exprlib\exceptions\ParsingException;
use exprlib\exceptions\UnknownTokenException;
use exprlib\Parser;

class Scope
{
    /** @var Parser */
    protected $builder;
    protected $content;
    protected $operations = [];
    private const SUPPORTED_OPERATIONS = ['^', '/', '*', '+', '-', '>', '<', '='];
    private const OPERATION_PRIORITY = [['^'], ['/', '*'], ['+', '-'], ['>', '<'], ['=']];

    public function __construct($content = null)
    {
        $this->content = $content;
    }

    public function setBuilder(Parser $builder): void
    {
        $this->builder = $builder;
    }

    public function addOperation($operation): void
    {
        $this->operations[] = $operation;
    }

    /**
     * handle the next token from the tokenized list. example actions
     * on a token would be to add it to the current context expression list,
     * to push a new context on the the context stack, or pop a context off the
     * stack.
     */
    /**
     * @param $token
     *
     * @throws OutOfScopeException
     * @throws UnknownTokenException
     */
    public function handleToken($token): void
    {
        $baseToken = $token;
        $token = strtolower($token);

        if (in_array($token, self::SUPPORTED_OPERATIONS, true)) {
            $this->addOperation($token);
        } elseif ($token === ',') {
            $context = $this->builder->getContext();

            if (!$context instanceof ScopeGroup) {
                $this->builder->pushContext(new ScopeGroup());
            }

            $this->builder->getContext()->addScopeGroup($this->operations);
            $this->operations = [];
        } elseif ($token === '(') {
            $this->builder->pushContext(new Scope($token));
        } elseif ($token === ')') {
            $scopeOperation = $this->builder->popContext();
            $newContext = $this->builder->getContext();
            if ($scopeOperation === null || (!$newContext)) {
                throw new OutOfScopeException('It misses an open scope');
            }
            $newContext->addOperation($scopeOperation);
        } elseif ($token === 'sin(') {
            $this->builder->pushContext(new scope\Sin($token));
        } elseif ($token === 'acos(') {
            $this->builder->pushContext(new scope\Acos($token));
        } elseif ($token === 'cos(') {
            $this->builder->pushContext(new scope\Cosin($token));
        } elseif ($token === 'sum(') {
            $this->builder->pushContext(new scope\Sum($token));
        } elseif ($token === 'avg(') {
            $this->builder->pushContext(new scope\Avg($token));
        } elseif ($token === 'max(') {
            $this->builder->pushContext(new scope\Max($token));
        } elseif ($token === 'min(') {
            $this->builder->pushContext(new scope\Min($token));
        } elseif ($token === 'tan(') {
            $this->builder->pushContext(new scope\Tangent($token));
        } elseif ($token === 'sqrt(') {
            $this->builder->pushContext(new scope\Sqrt($token));
        } elseif ($token === 'log(' || $token === 'ln(') {
            $this->builder->pushContext(new scope\Log($token));
        } elseif ($token === 'pow(') {
            $this->builder->pushContext(new scope\Pow($token));
        } elseif ($token === 'if(') {
            $this->builder->pushContext(new scope\IfElse($token));
        } elseif ($token === 'exp(') {
            $this->builder->pushContext(new scope\Exp($token));
        } elseif (is_numeric($token)) {
            $this->addOperation((float)$token);
        } else {
            throw new UnknownTokenException(sprintf('"%s" is not supported yet', $baseToken));
        }
    }

    /**
     * @return float|array
     * @throws Exception
     */
    public function evaluate()
    {
        foreach ($this->operations as $i => $operation) {
            if ($operation instanceof self) {
                $this->operations[$i] = $operation->evaluate();
            }
        }
        return  $this->expressionLoop();
    }

    # order of operations:
    # - sub scopes first
    # - multiplication, division
    # - addition, subtraction
    # evaluating all the sub scopes (recursively):

    /**
     * order of operations:
     * - parentheses, these should all ready be executed before this method is called
     * - exponents, first order
     * - mult/divi, second order
     * - addi/subt, third order
     */
    /**
     * @return float|array
     * @throws Exception
     */
    protected function expressionLoop()
    {
        array_walk($this->operations, [$this, 'fixNegativeOperators']);
        $this->operations = array_values($this->operations);
        while (true) {
            // fetch main operator + position of it
            [$mainOperator, $pos] = $this->getMainOperator();
            if ($mainOperator === null) {
                break;
            }

            $after = array_slice($this->operations, $pos + 2);

            $left = (float)($this->operations[$pos - 1] ?? 0);
            $right = (float)($this->operations[$pos + 1] ?? 0);

            $this->operations = array_slice($this->operations, 0, $pos - 1);
            $this->operations[] = $this->calcOperator($mainOperator, $left, $right);
            $this->operations = array_values(array_merge($this->operations, $after));
        }

        if (count($this->operations) !== 1) {
            throw new ParsingException('String have wrong character');
        }

        return end($this->operations);
    }

    protected function fixNegativeOperators($v, $k): void
    {
        if ($v !== '-') {
            return;
        }
        $isPrevNotNumeric = $k === 0 || !is_numeric($this->operations[$k - 1]);
        if (isset($this->operations[$k + 1]) && $isPrevNotNumeric) {
            unset($this->operations[$k]);
            $this->operations[$k + 1] = (float)('-' . $this->operations[$k + 1]);
        }
    }

    protected function getMainOperator(): array
    {
        $operators = array_filter(
            $this->operations,
            static function ($v) {
                return !is_numeric($v);
            },
            ARRAY_FILTER_USE_BOTH
        );
        $pos = $mainOperator = null;
        foreach (self::OPERATION_PRIORITY as $sOperation) {
            foreach ($operators as $operator) {
                if (in_array($operator, $sOperation, true)) {
                    $mainOperator = $operator;
                    $pos = array_search($operator, $operators, true);
                    break 2;
                }
            }
        }
        return [$mainOperator, $pos];
    }

    /**
     * @param string $mainOperator
     * @param float  $left
     * @param float  $right
     *
     * @return float
     * @throws Exception
     */
    protected function calcOperator(string $mainOperator, float $left, float $right): float
    {
        switch ($mainOperator) {
            case '^':
                return $left ** $right;
                break;
            case '*':
                return ($left * $right);
                break;
            case '/':
                if ($right === (float)0) {
                    throw new DivisionByZeroException('Division by zero');
                }
                return (float)($left / $right);
                break;
            case '-':
                return ($left - $right);
                break;
            case '+':
                return $left + $right;
                break;
            case '>':
                return $left > $right;
                break;
            case '<':
                return $left < $right;
                break;
            case '=':
                return $left === $right;
                break;
        }
        throw new ParsingException('Unknown operator:' . $mainOperator);
    }
}
