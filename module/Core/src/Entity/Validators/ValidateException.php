<?php
    /**
     * Created by PhpStorm.
     * User: Jose Lucio
     * Date: 07/12/18
     * Time: 11:29
     */

    namespace Core\Entity\Validators;


    use Throwable;

    class ValidateException extends  \Exception
    {
        private  $erros =[] ;

        public function __construct($message = "", $code = 0, Throwable $previous = null,array $erros=[])
        {
            parent::__construct($message, $code, $previous);
            $this->erros = $erros ;
        }

        /**
         * @return array
         */
        public function getErros()
        {
            return $this->erros;
        }

        /**
         * @return array
         */
        public function getJsonErros()
        {
            return json_encode($this->erros);
        }

        /**
         * @param array $erros
         */
        public function setErros($erros)
        {
            $this->erros = $erros;
        }



    }