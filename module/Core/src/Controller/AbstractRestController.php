<?php


namespace Core\Controller;

use Admin\Entity\Usuario;
use Core\Entity\AbstractEntity;
use Core\Entity\Repository\AbstractRepository;
use Core\Entity\Validators\ValidateException;
use Core\Exceptions\UniqueExcecption;
use Core\Service\AbstractEnityService;
use Core\View\Model\JMSModel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerBuilder;
use Zend\Authentication\AuthenticationService;
use Zend\Log\Logger;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

abstract class AbstractRestController extends AbstractRestfulController
{
    protected $entity = null;

    /**
     * @var AbstractEnityService
     */
    protected $service;

    protected $retorno = ['success' => true];

    protected $start = null;
    protected $limit = null;
    protected $filter = null;
    protected $search = null;
    protected $sort = null;

    public function initInifilters()
    {
        $this->start = $this->params()->fromQuery('start', null);
        $this->limit = 200 ; // $this->params()->fromQuery('limit', null);
        $this->filter = $this->params()->fromQuery('filter', null);
        $this->search = $this->params()->fromQuery('query', null);;
        $this->sort = $this->params()->fromQuery('sort', null);

        // coloca a entidade
        $this->setEntity();

        $this->setService();
    }

    public function onDispatch(MvcEvent $e)
    {
        $this->initInifilters();
        return parent::onDispatch($e); // TODO: Change the autogenerated stub
    }


    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface | ObjectBasics
     */
    public function getServiceLocator($name = null)
    {
        if (is_null($name))
            return $this->getEvent()->getApplication()->getServiceManager();
        else
            return $this->getEvent()->getApplication()->getServiceManager()->get($name);
    }

    public function getUsuarioAutenticado()
    {
        $authService = $this->getServiceLocator()->get(AuthenticationService::class);

        if ($authService->hasIdentity()) {
            /** @var Usuario $usuario */
            $usuario = $this->getServiceLocator()->get(EntityManager::class)
                ->find(Usuario::class, $authService->getIdentity());

            return $usuario;
        }

        return false;
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->getServiceLocator()->get(EntityManager::class);
    }


    public function getJsonInputParams()
    {

        $dataByJson = json_decode(file_get_contents("php://input"), true);
        if (is_array($dataByJson)) {
            return $dataByJson;
        } else {
            return false;
        }
    }

    public function getDataPost()
    {
        if ($this->getRequest()->isPost()) {
            if (!$data = $this->getJsonInputParams()) {
                $data = $this->getRequest()->getPost()->toArray();
            }
            return $data;
        }
        return [];
    }

    public function getResponseText()
    {
        header("Content-type: text/plain; charset=utf-8");
        return $this->getResponse();
    }

    /***
     * @return Logger|\Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getLogger()
    {
        return $this->getServiceLocator("log");
    }

    /**
     * Monta a lista de usuarios
     * @return JMSModel
     */
    public function getList()
    {
        try {
            /** @var AbstractRepository $repo */
            $repo = $this->getEm()->getRepository($this->entity);

            // monta o retorno
            $retorno = $repo->findForPagination($this->start, $this->limit, $this->sort, $this->filter, $this->search, $this->entity);

            if ($retorno == null) {
                $this->retorno['rows'] = [];
                $this->retorno['total'] = 0;

            }

            // monta o retorno com resultado
            $this->retorno += $retorno;

        } catch (\Exception $e) {
            // seta a mensagem de error
            $this->retorno['success'] = false;
            $this->retorno['msg'] = $e->getMessage();
        }

        return new JMSModel($this->retorno);
    }

    /**
     * Faz a criação automático de entidades
     */
    public function create($data)
    {
        $data +=$this->getDataPost();

        try {
            /** @var AbstractEntity $row */
            $row = $this->service->create($data, new $this->entity);

            if ($row != null) {

                // dis ao sistema que não conseguiu atualizar
                $this->retorno['data'] = $row;
                $this->retorno['msg'] = "Item Criado com sucesso!";
            } else {
                $this->retorno['success'] = false;

                // seta a mensagem de error
                $this->retorno['msg'] = "Não consegui fazer o cadastro favor entrar em contato com adiminstrador ";
            }

            // log de sucesso
            $this->getLogger()->info($this->getStringLog($this->retorno['msg'],__FUNCTION__, $row));
        }  catch (UniqueConstraintViolationException $exception) {
            // erros de base de dadoe e demais
            $this->retorno['success'] = false;
            $this->retorno['msg'] = "Item duplicado!";
            $this->retorno['detailErr'] = $exception->getMessage();

            // log de error
            $this->getLogger()->err($this->getStringLog($this->retorno['msg'],__FUNCTION__, $this->retorno['detailErr']));
        } catch (UniqueExcecption $exception) {
            // erros de base de dadoe e demais
            $this->retorno['success'] = false;
            $this->retorno['msg'] = $exception->getMessage();
            $this->retorno['detailErr'] = $exception->getMessage();

            // log de error
            $this->getLogger()->err($this->getStringLog($this->retorno['msg'],__FUNCTION__, $this->retorno['detailErr']));
        }
        catch (ValidateException $exception) {
            // erros de validação
            $this->retorno['success'] = false;
            $this->retorno['data'] = $data;
            $this->retorno['msg'] = $exception->getMessage();
            $this->retorno['erros'] = $exception->getErros();

            // log de warning
            $this->getLogger()->warn($this->getStringLog($this->retorno['msg'],__FUNCTION__, $this->retorno['erros']));
        }catch (\Exception $exception) {
            // erros de base de dadoe e demais
            $this->retorno['success'] = false;
            $this->retorno['class'] = get_class($exception);
            $this->retorno['msg'] = $exception->getMessage();

            // log de error
            $this->getLogger()->crit($this->getStringLog($this->retorno['msg'],__FUNCTION__));
        }

        return new JMSModel($this->retorno);
    }

    public function patch($id, $data)
    {

        return $this->update($id, $data);
    }

    public function get($id)
    {
        try {
            $row = $this->getEm()->find($this->entity, $id);

            if ($row == null) {
                $this->retorno['success'] = false;
                $this->retorno['msg'] = "Nenhum item encontrado!";
            } else {
                $this->retorno['data'] = $row;

            }
        } catch (\Exception $exception) {
            // erros de base de dadoe e demais
            $this->retorno['success'] = false;
            $this->retorno['msg'] = $exception->getMessage();
        }

        return new JMSModel($this->retorno);
    }

    public function update($id, $data)
    {
        try {
            $row = $this->service->updade($data, $id, $this->entity);

            // verifica se os dados foram atualizados
            if ($row != null) {
                $this->retorno['data'] = $row;
                $this->retorno['msg'] = "Item Atualizado com sucesso";

                // log de sucesso
                $this->getLogger()->info($this->getStringLog($this->retorno['msg'],__FUNCTION__, $row));
            } else {
                $this->retorno['msg'] = "Item Não pode ser atualizado, pois nao foi encontrado ";

                $this->retorno['success'] = false;
                // log de sucesso
                $this->getLogger()->warn($this->getStringLog($this->retorno['msg'],__FUNCTION__, $row));
            }

        } catch (ValidateException $exception) {
            // erros de validação
            $this->retorno['success'] = false;
            $this->retorno['msg'] = $exception->getMessage();
            $this->retorno['erros'] = $exception->getErros();

            // log de warning
            $this->getLogger()->warn($this->getStringLog($this->retorno['msg'],__FUNCTION__, $this->retorno['erros']));
        } catch (UniqueConstraintViolationException $exception) {
            // erros de base de dadoe e demais
            $this->retorno['success'] = false;
            $this->retorno['msg'] = "Item duplicado!";
            $this->retorno['detailErr'] = $exception->getMessage();

            // log de error
            $this->getLogger()->err($this->getStringLog($this->retorno['msg'],__FUNCTION__, $this->retorno['detailErr']));
        }
        catch (\Exception $exception) {
            // erros de base de dadoe e demais
            $this->retorno['success'] = false;
            $this->retorno['class'] = get_class($exception);
            $this->retorno['msg'] = $exception->getMessage();

            // log de error
            $this->getLogger()->crit($this->getStringLog($this->retorno['msg'],__FUNCTION__));
        }

        // monta os dados
        return new JMSModel($this->retorno);
    }


    public function delete($id)
    {

        try {

            // remove o item da vase de dados
            $item = $this->service->delete($id, $this->entity);
            $this->retorno['msg'] = "Item removido com sucesso!";

            // log de sucesso
            $this->getLogger()->info($this->getStringLog($this->retorno['msg'],__FUNCTION__,$item));
        } catch (\Exception $exception) {
            // erros de base de dadoe e demais
            $this->retorno['success'] = false;
            $this->retorno['msg'] = $exception->getMessage();

            // log de error
            $this->getLogger()->crit($this->getStringLog($this->retorno['msg'],__FUNCTION__));
        }

        // monta os dados do model
        return new JMSModel($this->retorno);


    }

    /**
     * @see  coloca a entidade default que o sismtema vai trabalhar com ele
     */
    protected abstract function setEntity();

    /**
     *Coloca o serviço que o sistema vai trabalhar com ele para as operações de update e delete
     */
    protected abstract function setService();

    private function getStringLog($msg, $action, $data = null)
    {
        $dataJson = "";

        if (!$this->getUsuarioAutenticado()) {
            $user = "anonimo";
        } else {
            $user = $this->getUsuarioAutenticado()->getLogin();
        }


        if (!empty($data)) {
            $serializer = SerializerBuilder::create()
                ->setPropertyNamingStrategy(new \JMS\Serializer\Naming\IdenticalPropertyNamingStrategy())
                ->build();
            $dataJson = "data:" . $serializer->serialize($data, 'json');
        }

        return "[{$user}]  {$msg} (" . get_class($this) . ":{$action}) {$dataJson}";


    }


}