<?php

namespace Admin\Controller;

use Core\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;


class ErrorController extends AbstractActionController
{

    public function forbiddenAction()
    {
        $data = $this->params()->fromQuery();
        $data['msg'] = "Você não tem permissão para acessar esta pagina!";
        $data['code'] = 403;
        return new JsonModel($data);

    }

    public function noResourceAction()
    {
        $data = $this->params()->fromQuery();
        $data['msg'] = "Resource não cadastrado!";
        $data['code'] = 403;
        return new JsonModel($data);
    }

    public function noRoleAction()
    {
        return new JsonModel($this->params()->fromQuery());

    }

}