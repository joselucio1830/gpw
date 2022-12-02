<?php
    /**
     * Created by PhpStorm.
     * User: José Lucio
     * Date: 26/10/16
     * Time: 10:12
     */

    namespace Admin\Entity\Repository;


    use Core\Entity\Repository\AbstractRepository;
    use Core\Pagination\Pagination;


    class UsuarioRepository extends AbstractRepository
    {

        public function findByToken($token)
        {

            return $this->findOneBy(['token' => $token]);
        }


        public function findByApiToken($token)
        {
            return $this->findOneBy(['apiToken' => $token]);
        }
    }