<?php
/**
 * Created by PhpStorm.
 * User: José Lucio
 * Date: 24/10/16
 * Time: 10:48
 */

namespace Admin\Controller;


use Admin\Entity\Repository\SessionApiRepository;
use Admin\Entity\SessaoApi;
use Admin\Forms\Usuario;
use Admin\Service\AuthUser;
use Admin\Storage\Session;
use Core\Controller\AbstractActionController;
use Core\View\Model\JMSModel;
use Zend\Authentication\AuthenticationService;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Log\Logger;
use Zend\View\Model\JsonModel;


class AuthController extends AbstractActionController
{
    protected $form;
    protected $storage;
    protected $authservice;


    /**
     * @return AuthenticationService
     */
    public function getAuthService()
    {

        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()
                ->get(AuthenticationService::class);
        }

        return $this->authservice;
    }

    /**
     * @return Session
     */

    public function getSessionStorage()
    {
        if (!$this->storage) {
            $this->storage = $this->getServiceLocator()
                ->get(Session::class);
        }

        return $this->storage;
    }

    public function getForm()
    {
        if (!$this->form) {
            $user = new Usuario();
            $builder = new AnnotationBuilder();
            $this->form = $builder->createForm($user);
        }

        return $this->form;
    }

    public function authAction()
    {
        //if already auth, redirect to success page
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('admin');
        }

        $form = $this->getForm();
        if ($this->flashMessenger()->hasMessages()) {

            echo '<div class="alert alert-info">';

            $messages = $this->flashMessenger()->getMessages();
            foreach ($messages as $message) {
                echo $message;
            }

            echo '</div>';
        }
        return array(
            'form' => $form,
            'messages' => $this->flashMessenger()->getMessages()
        );
    }

    public function authenticateJsonAction()
    {
        $retorno = ['success' => true,'id'=>$this->getAuthService()->hasIdentity()];

        $request = $this->getRequest();
        $data = $this->getDataPost();


        if ($request->isPost() && !(empty($data['login']) || empty($data['senha']))) {


            //check authentication...
            $this->getAuthService()->getAdapter()
                ->setIdentity($data['login'])
                ->setCredential(md5($data['senha']));

            $result = $this->getAuthService()->authenticate();


            if ($result->isValid()) {
                /** @var \Admin\Entity\Usuario $user */
                $user = $result->getIdentity();

                if ($user->isAtivo()) {

                    $this->getAuthService()->getStorage()->write($user->getLogin());
                    $retorno['user'] = $user;
                }else{
                    $retorno = ['success' => false, 'msg' => "usuario ou senha inválidos "];
                }

            } else {
                $retorno = ['success' => false, 'msg' => "usuario ou senha inválidos "];
            }

        } else {

            $retorno = ['success' => false, 'msg' => "Erro no envio de dados "];
        }

        //$this->sm->get('log');
        /** @var Logger $log */
        $log = $this->getServiceLocator('log');
        $log->info("[AUTENTICATION] " . json_encode($retorno));

        return new JMSModel($retorno);
    }

    public function authenticateAction()
    {
        $form = $this->getForm();
        $redirect = 'auth';

        $request = $this->getRequest();
        if ($request->isPost()) {

            $data = $this->getDataPost();


            $form->setData($data);
            if ($form->isValid()) {
                //check authentication...
                $this->getAuthService()->getAdapter()
                    ->setIdentity($data['username'])
                    ->setCredential(md5($data['password']));

                $result = $this->getAuthService()->authenticate();
                foreach ($result->getMessages() as $message) {
                    //save message temporary into flashmessenger
                    $this->flashmessenger()->addMessage($message);
                }

                if ($result->isValid()) {
                    $redirect = 'admin';
                    //check if it has rememberMe :
                    if ($request->getPost('rememberme') == 1) {
                        $this->getSessionStorage()
                            ->setRememberMe(1);
                        //set storage again
                        $this->getAuthService()->setStorage($this->getSessionStorage());
                    }
                    $this->getAuthService()->getStorage()->write($request->getPost('username'));
                }
            } else {
                $this->flashmessenger()->addMessage(print_r($form->getInputFilter()->getMessages(), true));
            }
        } else {

            $this->flashmessenger()->addMessage("Error no envio dos dados");
        }

        return $this->redirect()->toRoute($redirect);
    }


    public function renewTokenAction()
    {
        $retorno = ['success' => true];

        try {
            $data = $this->getDataPost();
            /** @var SessionApiRepository $repoToken */
            $repoToken = $this->getEm()->getRepository(SessaoApi::class);

            // procura a session
            $token = $repoToken->findByToken($data['token']);

            // verifica se a sessao existe
            if ($token instanceof SessaoApi) {
                /** @var \Admin\Service\Session $srv */
                $srv = $this->getServiceLocator()->get(\Admin\Service\Session::class);
                // gera um novo toke
                $token->setToken();

                // salva o toke
                $srv->save($token);

                $retorno['token'] = $token->getToken();
            } else {
                throw  new \Exception("sessao não encontrada");
            }
        } catch (\Exception $e) {
            $retorno['success'] = false;
            $retorno['msg'] = $e->getMessage();
        }


        return new JsonModel($retorno);

    }

    public function logoutAction()
    {
        $this->getSessionStorage()->forgetMe();
        $this->getAuthService()->clearIdentity();

        return $this->redirect()->toUrl('/');
    }

    public function changePasswordAction()
    {
        $retorno = ['success' => true];
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getDataPost();

                /** @var AuthUser $userSrv */
                $userSrv = $this->getServiceLocator()->get(AuthUser::class);

                $userSrv->changePassword($data);
                $retorno['msg'] = 'Senha auterado com sucesso!';
            } catch (\Exception $e) {
                $retorno['success'] = false;
                $retorno['msg'] = $e->getMessage();
            }

        }

        return new JsonModel($retorno);

    }
}
