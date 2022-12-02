<?php


namespace Admin\Controller;


use Admin\Entity\Usuario;
use Admin\Service\UsuarioService;
use Core\Controller\AbstractRestController;

class UserController extends AbstractRestController
{


    /**
     * @see  coloca a entidade default que o sismtema vai trabalhar com ele
     */
    protected function setEntity()
    {
        $this->entity = Usuario::class;
    }

    /**
     *Coloca o serviço que o sistema vai trabalhar com ele para as operações de update e delete
     */
    protected function setService()
    {
        $this->service = $this->getServiceLocator(UsuarioService::class);
    }
}
