<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Admin\Entity\Usuario;
use Application\Entity\ContraCheque;
use Application\Files\Info\GetInformationFile;
use Application\Files\WMFUpload;
use Application\Service\ContraChequesService;
use CirclicalAutoWire\Annotations\Route;
use Core\Controller\AbstractActionController;
use Core\Entity\Repository\AbstractRepository;
use Core\Exceptions\FileException;
use Core\Utils\Json;
use Core\View\Model\JMSModel;
use mysql_xdevapi\Exception;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{


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

    }

    public function indexAction()
    {


        $view =  new ViewModel();
        $view->setTerminal(true);
        return $view ;
    }

    /**
     * @Route("/api/contra-check/list")
     * @return JMSModel
     */
    public function listContrachecksAction()
    {

        if ($this->getUsuarioAutenticado()->getPerfil() !== 'admin') {
            $this->filter = "usuario=\"{$this->getUsuarioAutenticado()->getLogin()}\"";
        }

        try {
            /** @var AbstractRepository $repo */
            $repo = $this->getEm()->getRepository(ContraCheque::class);

            // monta o retorno
            $retorno = $repo->findForPagination($this->start, $this->limit, $this->sort, $this->filter, $this->search, ContraCheque::class);

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
        }catch (\Throwable $e){
            var_dump($e->getMessage());
        }

        return new JMSModel($this->retorno);


    }

    /**
     * @Route("/api/contra-check/remove/:id")
     * @return JsonModel
     */
    public function removeAction()
    {
        /** @var ContraChequesService $srvContraCheck */
        $srvContraCheck = $this->getServiceLocator(ContraChequesService::class);

        $id = $this->params()->fromRoute('id');

        $retorno = ['success' => true];
        try {


            /** @var ContraCheque $item */
            $item = $this->getEm()->find(ContraCheque::class, $id);

            if(!$item)
                throw  new \Exception("Nenhum item encontrado ou ja foi removido ") ;

            $srvContraCheck->removeEntity($item);
        } catch (\Exception $e) {

            $retorno = ['success' => false,
                'msg' => $e->getMessage()];
        }

        return new JMSModel($retorno);

    }
    /**
     * @Route("/api/contra-check/view/:name")
     * @return JsonModel
     */
    public function displayAction()
    {
        $nome = $this->params()->fromRoute('name') ;
        $imageContent =__DIR__."/../../data/{$nome}";
        // get image content
        $response = $this->getResponse();

        header ('Content-Type: image/png');
        readfile($imageContent);
        $response
            ->getHeaders()
            ->addHeaderLine('Content-Type', 'image/png');

        return $response;
    }

    /**
     * @Route("/api/contra-check/upload")
     * @return JsonModel
     */
    public function uploadAction()
    {
        /** @var ContraChequesService $srvContraCheck */
        $srvContraCheck = $this->getServiceLocator(ContraChequesService::class);

        $ret = $_FILES['file'];
        $uploaFile = new WMFUpload();

        $retorno = ['success' => true];

        try {
            // pega a referencia do mes
            $referencia = new \DateTime($this->getDataPost()['referencia']);
            foreach ($ret['name'] as $i => $item) {


                // monta os arquivos
                try {
                    $newFile = [
                        'size' => $ret['size'][$i],
                        'tmp_name' => $ret['tmp_name'][$i],
                        'type' => $ret['type'][$i],
                        'name' => $ret['name'][$i]
                    ];

                    if (!$cpf = GetInformationFile::getCpf($newFile['tmp_name'])) {
                        throw  new FileException("O sistema não encontrou um cpf válido nesse arquivo ");
                    }

                    /** @var Usuario $usuario */
                    $usuario = $this->getEm()->find(Usuario::class, $cpf);
                    if (!$usuario) {
                        throw  new FileException("CPF:{$cpf} não cadastrado!");
                    }

                    $name = $uploaFile->upload($newFile);

                    $contracheck = $this->getEm()->getRepository(ContraCheque::class)->findOneBy(
                        [
                            'usuario' => $usuario,
                            'referencia' => $referencia->format('m-Y')
                        ]
                    );
                    // verifica se ja foi cadastrado
                    if ($contracheck)
                        throw new FileException("O arquivo ja foi cadastrado para o usuario {$usuario->getNome()}!");

                    $contracheck = new ContraCheque();
                    $contracheck->setUsuario($usuario);
                    $contracheck->setReferencia($referencia->format('m-Y'));
                    $contracheck->setFileName($name);

                    $srvContraCheck->save($contracheck);

                    $retorno['file'][] = [
                        'success' => true,
                        'name' => $name,
                        'msg' => "Item de {$usuario->getNome()} Cadastrado dom sucesso!"

                    ];

                } catch (FileException $e) {
                    $retorno['file'][] = [
                        'success' => false,
                        'name' => $item,
                        'msg' => $e->getMessage()
                    ];
                }
            }
        } catch (\Exception $e) {
            $retorno = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }

        sleep(1);
        return new JsonModel($retorno);
    }
}
