<?php
/**
 * Created by PhpStorm.
 * User: José Lucio
 * Date: 14/06/17
 * Time: 14:38
 */

namespace Core\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Lexer;
/**
 * Adds MySQL MD5 function to DQL Query or QueryBuilder
 *
 * MD5Function ::= "MD5" "(" ArithmeticPrimary ")"
 *
 */
class MD5 extends FunctionNode {
    public $value = null;
    public function parse(Parser $parser) {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->value = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
    public function getSql(SqlWalker $walker) {
        return "MD5(" . $this->value->dispatch($walker) . ")";
    }
}