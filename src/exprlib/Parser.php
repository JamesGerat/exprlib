<?php

namespace exprlib;

use exprlib\contexts\Scope;

/**
 * this model handles the tokenizing, the context stack functions, and
 * the parsing (token list to tree trans).
 * as well as an evaluate method which delegates to the global scopes evaluate.
 */
class Parser
{
    public $precision = 15;
    public $precisionType;

    protected $content;
    protected $contextStack = [];
    /** @var Scope */
    protected $tree;
    protected $tokens = [];
    protected $vars = [];

    /**
     * @param string|null $content content
     */
    public function __construct(string $content = null)
    {
        if (null !== $content) {
            $this->setContent($content);
        }
    }

    /**
     * Allow user to simplify evaluation
     * Parser::build('2+1')->evaluate();
     *
     * @param string  $content       content
     * @param int  $precision     precision
     * @param int $precisionType precisionType
     *
     * @return Parser
     */
    public static function build(string $content, int $precision = 15, int $precisionType = PHP_ROUND_HALF_UP): Parser
    {
        $instance = new static($content);
        $instance->precision = $precision;
        $instance->precisionType = $precisionType;

        return $instance;
    }

    /**
     * this function does some simple syntax cleaning:
     * - removes all spaces
     * - replaces '**' by '^'
     * then it runs a regex to split the contents into tokens. the set
     * of possible tokens in this case is predefined to numbers (ints of floats)
     * math operators (*, -, +, /, **, ^) and parentheses.
     */
    protected function tokenize(): Parser
    {
        $this->content = str_replace(["\n", "\r", "\t", " "], '', $this->content);
        $this->content = str_replace('**', '^', $this->content);
        $this->content = str_replace('PI', (string)PI(), $this->content);
        $this->tokens = preg_split(
            '@
              ([\d.]+)
              |(
                sin\(
                |log\(
                |ln\(
                |pow\(
                |round\(
                |exp\(
                |acos\(
                |cos\(
                |sum\(
                |avg\(
                |max\(
                |min\(
                |tan\(
                |sqrt\(
                |if\(
                |\+
                |-
                |\*
                |/
                |\^
                |&
                |\|
                |\(
                |\)
              )
            @ix',
            $this->content,
            null,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        return $this;
    }

    /**
     * this is the loop that transforms the tokens array into
     * a tree structure.
     */
    /**
     * @return $this
     * @throws exceptions\OutOfScopeException
     * @throws exceptions\UnknownTokenException
     */
    public function parse(): self
    {
        # this is the global scope which will contain the entire tree
        $this->pushContext(new Scope());
        foreach ($this->tokens as $token) {
            # get the last context model from the context stack,
            # and have it handle the next token
            $this->getContext()->handleToken($token);
        }
        $this->tree = $this->popContext();

        return $this;
    }

    /**
     * @param array $vars
     * @return $this
     * @noinspection PhpUnused Method for call from outside.
     */
    public function setVars(array $vars): Parser
    {
        if (count($vars)) {
            $this->vars = array_merge($this->vars, $vars);
        }

        return $this;
    }

    /**
     * @return float|array
     * @throws exceptions\Exception
     * @throws exceptions\OutOfScopeException
     * @throws exceptions\UnknownTokenException
     */
    public function evaluate(): float
    {
        if (count($this->vars)) {
            $this->content = str_replace(
                array_map(
                    static function ($varName) {
                        return sprintf('[%s]', $varName);
                    },
                    array_keys($this->vars)
                ),
                array_values($this->vars),
                $this->content
            );
        }

        if (!$this->tokens) {
            $this->tokenize();
        }

        if (!$this->tree) {
            $this->parse();
        }

        return round($this->tree->evaluate(), $this->precision, $this->precisionType);
    }

    public function setContent($content): Parser
    {
        $this->content = $content;
        // clear tokens
        $this->tokens = [];
        // clear tree
        $this->tree = null;

        return $this;
    }

    /**
     * @return mixed
     * @noinspection PhpUnused Method may be used for debug.
     */
    public function getContent()
    {
        return $this->content;
    }

    /*******************************************************
     * the context stack functions. for the stack im using
     * an array with the functions array_push, array_pop,
     * and end to push, pop, and get the current element
     * from the stack.
     *******************************************************/
    /**
     * @param Scope $context
     */
    public function pushContext(Scope $context): void
    {
        $this->contextStack[] = $context;
        $this->getContext()->setBuilder($this);
    }

    public function popContext(): ?Scope
    {
        return array_pop($this->contextStack);
    }

    /**
     * @return false|Scope
     */
    public function getContext()
    {
        return end($this->contextStack);
    }
}
