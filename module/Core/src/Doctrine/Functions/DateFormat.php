<?php


    namespace Core\Doctrine\Functions;


    use Doctrine\ORM\Query\AST\ParenthesisExpression;
    use Doctrine\ORM\Query\Lexer;
    use Doctrine\ORM\Query\AST\Functions\FunctionNode;

    class DateFormat extends FunctionNode
    {

        /**
         * @var  ParenthesisExpression
         */
        public $firstDateExpression = null;
        /**
         * @var  ParenthesisExpression
         */
        public $secondDateExpression = null;
        /**
         * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
         *
         * @return string
         */
        public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
        {
            return 'DATE_FORMAT(' .
                $this->firstDateExpression->dispatch($sqlWalker) . ', ' .
                $this->secondDateExpression->dispatch($sqlWalker) .
                ')'; // (7)
        }

        /**
         * @param \Doctrine\ORM\Query\Parser $parser
         *
         * @return void
         * @throws \Doctrine\ORM\Query\QueryException
         */
        public function parse(\Doctrine\ORM\Query\Parser $parser)
        {
            $parser->match(Lexer::T_IDENTIFIER); // (2)
            $parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)
            $this->firstDateExpression = $parser->ArithmeticPrimary(); // (4)
            $parser->match(Lexer::T_COMMA); // (5)
            $this->secondDateExpression = $parser->ArithmeticPrimary(); // (6)
            $parser->match(Lexer::T_CLOSE_PARENTHESIS);
        }
    }