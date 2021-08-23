<?php

namespace exprlib\contexts\scope;

use exprlib\contexts\Scope;
use exprlib\exceptions\ParsingException;

class Log extends Scope
{
    public function evaluate()
    {
        $result = parent::evaluate();

        $content = (string) $this->content;
        if (is_array($result)) {
            if ($content === 'ln(') {
                throw new ParsingException('Ln accepts only 1 argument');
            }
            if (count($result) !== 2) {
                throw new ParsingException('Log accepts only 2 arguments');
            }

            return log($result[0], $result[1]);
        }


        if ($content === 'log(') {
            return log10(parent::evaluate());
        }
        // ln
        return log(parent::evaluate());
    }
}
