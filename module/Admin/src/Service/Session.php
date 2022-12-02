<?php
    /**
     * Created by PhpStorm.
     * User: Jose lucio
     * Date: 05/12/18
     * Time: 12:16
     */

    namespace Admin\Service;



    use Admin\Entity\Repository\UsuarioRepository;
    use Admin\Entity\SessaoApi;
    use Admin\Entity\Usuario;
    use Core\Service\AbstractEnityService;

    class Session extends AbstractEnityService
    {

        public function start($login)
        {

            if (!($login instanceof Usuario)) {

                /** @var UsuarioRepository $repo */
                $repo = $this->getEm()->getRepository(Usuario::class);

                $login = $repo->find($login);
            }


            // verifica se existe uma sessao
            $session = $this->getEm()->find(SessaoApi::class, $login);

            if ($session instanceof SessaoApi) {
                $this->delete($session);
            }


            $newSession = new SessaoApi();
            $newSession->setLogin($login->getLogin());
            $this->save($newSession);

            return $newSession;


        }


        public function create($data, array $extraParams = [])
        {
            // TODO: Implement create() method.
        }

        public function update($data, $id, array $extraParams = [])
        {
            // TODO: Implement update() method.
        }

        public function delete($id, array $extraParams = [])
        {
            if (!($id instanceof SessaoApi)) {
                $id = $this->getEm()->find(SessaoApi::class, $id);
            }

            // faz a remoção da entidade
            $this->removeEntity($id);

        }
    }