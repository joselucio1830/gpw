<?php
namespace Admin\Controller;

use Admin\Entity\Usuario;
use Core\Controller\AbstractActionController;
use Core\View\Model\JMSModel;
use Zend\View\Model\ViewModel;


class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $model = new ViewModel();
        $model->setTerminal(true);
        return $model;
    }

    public function getTokenAction(){

        if($this->getUsuarioAutenticado() instanceof Usuario){
            return new JMSModel(['success'=>true,'token'=>$this->getUsuarioAutenticado()->getToken()]) ;
        }else{
            return new JMSModel(['success'=>false]) ;
        }

    }






}

?>
