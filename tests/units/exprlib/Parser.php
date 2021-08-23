<?php

namespace tests\units\exprlib;

require_once __DIR__ . '/../../../vendor/autoload.php';

use atoum\atoum\test;
use exprlib\exceptions\DivisionByZeroException;
use exprlib\exceptions\OutOfScopeException;
use exprlib\exceptions\ParsingException;
use exprlib\exceptions\UnknownTokenException;
use exprlib\Parser as ParserModel;
use mageekguy\atoum;

/**
 * Parser
 *
 * @uses   \atoum\atoum\test
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class Parser extends test
{
    public function testUnknownTokenException()
    {
        $this->exception(
            static function () {
                ParserModel::build('ß2+1')->evaluate();
            }
        )
            ->isInstanceOf(UnknownTokenException::class)
            ->hasMessage('"ß" is not supported yet');

    }
    public function testOutOfScopeException()
    {
        $this->exception(
            static function () {
                ParserModel::build('2+1)')->evaluate();
            }
        )
            ->isInstanceOf(OutOfScopeException::class)
            ->hasMessage('It misses an open scope');

    }
    public function testDivisionByZeroException()
    {
        $this->exception(
            static function () {
                ParserModel::build('2/0')->evaluate();
            }
        )
            ->isInstanceOf(DivisionByZeroException::class);
    }
    public function testSinArgumentException()
    {
        $this->exception(
            static function () {
                ParserModel::build('sin(2,3)')->evaluate();
            }
        )
            ->isInstanceOf(ParsingException::class);
    }
    public function testOperationException()
    {
        $this->exception(
            static function () {
                ParserModel::build('5++-15')->evaluate();
            }
        )
            ->isInstanceOf(ParsingException::class);
    }
    public function testLnArgumentException()
    {
        $this->exception(
            static function () {
                ParserModel::build('ln(1,2)-sin(1,2)')->evaluate();
            }
        )
            ->isInstanceOf(ParsingException::class);
    }
    public function testArgumentsWithGroupException()
    {
        $this->exception(
            static function () {
                ParserModel::build('ln(1,2)-1')->evaluate();
            }
        )
            ->isInstanceOf(ParsingException::class);
    }

    public function testEmptyArgumentException()
    {
        $this->exception(
            static function () {
                ParserModel::build('if(>1,1,2)')->evaluate();
            }
        )
            ->isInstanceOf(ParsingException::class);
    }

    /**
     * @dataProvider operationsDataProvider
     */
    public function testOperations($operation, $result)
    {
        $this->string((string)ParserModel::build($operation, 5)->evaluate())
            ->isEqualTo((string)$result);
    }

    public function operationsDataProvider(): array
    {
        return [
            ['2+4/2+2', 6],
            ['2+4/2', 4],
            ['2+4/-2', 0],
            ['2+-4/-2', 4],
            ['2+-4*-2', 10],
            ['2+1', 3],
            ['2/1', 2],
            ['2/(3.6*8.5)', 0.06536],
            ['2+(6/2)+(8*3)', 29],
            ['2+3+6+6/2+3', 17],
            ['0.001 + 0.02', 0.021],
            ['10*-2', -20],
            ['-10*-2', 20],
            ['4^-2', 0.0625],
            ['4^0.5', 2],
            ['-0.1=-0.1', 1],
            ['1-1+1', 1],
            ['100 - 80 - 90 + 100', 30],
            ['5>1', 1],
            ['1+0+0+0+1*2-1+1', 3],
            ['1-1-if(1<5,10+10,100)', -20],
            // OPERATIONS
            // cos
            ['COS(0)', 1],
            ['cos(90)', 0],
            ['cos(180)', -1],
            ['cos(360)', 1],
            // sin
            ['sin(0)', 0],
            ['sin(90)', 1],
            ['sin(180)', 0],
            // sqrt
            ['sqrt(9)', 3],
            ['sqrt(4)', 2],
            ['sqrt(3)', 1.73205],
            // tangent
            [sprintf('tan(%s)', rad2deg(M_PI_4)), 1],
            ['tan(180)', 0],
            // log
            ['log(10)', '1'],
            ['log(10,10)', '1'],
            ['ln(10)', '2.30259'],
            ['log(0.7)', '-0.1549'],
            ['ln(0.7)', '-0.35667'],
            // pow
            ['pow(10, 2)', 100],
            ['pow(10, 3)', 1000],
            ['pow(10, 0)', 1],
            // exp
            ['exp(12)', 162754.79142],
            ['exp(5.7)', 298.8674],
            // sum
            ['sum(10, 20, 30)', 60],
            // avg
            ['avg(10, 20, 30)', 20],
            // max
            ['max(10, 20, 30)', 30],
            // min
            ['min(10, 20, 30)', 10],
            // special cases
            ['log(0)', -INF],
            ['log(0)*-1', INF],
            ['0.1=0.1', 1],
            ['sum(1,2,3) - min(1,2,3)', 5],
            [sprintf('acos(%s)', rad2deg(8)), NAN],
            ['1 != 1', 0],
            ['1 != 0', 1],
            ['0 | 0', 0],
            ['1 | 0', 1],
            ['0 | 1', 1],
            ['1 | 1', 1],
            ['0 & 0', 0],
            ['1 & 0', 0],
            ['0 & 1', 0],
            ['1 & 1', 1],
            ['1 & 1 | 1 & 0', 1],
        ];
    }
}
