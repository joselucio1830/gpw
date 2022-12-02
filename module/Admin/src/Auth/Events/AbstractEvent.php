<?php
    /**
     * Created by PhpStorm.
     * User: José Lúcio
     * Date: 29/11/18
     * Time: 17:56
     */

    namespace Admin\Auth\Events;


    use Admin\Entity\Usuario;
    use Zend\ServiceManager\ServiceManager;

    abstract class AbstractEvent
    {
        /** @var ServiceManager */
        protected  $sm =null ;

        public function __construct( ServiceManager $serviceManager)
        {
            $this->sm = $serviceManager ;
        }

        public abstract  static function getInstance( ServiceManager $sm) ;


        public abstract function onAutenticate(Usuario $usuario);
    }
