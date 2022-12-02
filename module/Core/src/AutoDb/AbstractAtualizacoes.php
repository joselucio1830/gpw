<?php
    /**
     * Created by PhpStorm.
     * User: Jose Lucio
     * Date: 04/02/19
     * Time: 19:00
     */

    namespace Core\AutoDb ;


    use Core\Service\AbstractService;
    use Doctrine\ORM\EntityManager;
    use Prospeccao\Entity\DtAtualizacoes;

    abstract  class AbstractAtualizacoes extends AbstractService
    {


        protected $em;
        public abstract function execute() ;
        protected function getEm()
        {
            if ($this->em == null) {
                $this->em = $this->sm->get(EntityManager::class);
            }
            return $this->em;
        }

        protected function lastAtualizacao($string)
        {
            $repo = $this->getEm()->getRepository(DtAtualizacoes::class);

            return $repo->findOneBy([
                'sessao'=>$string
            ]);
        }

        protected function saveLastAtualizacao($atualizaca, $string)
        {
            if (!($atualizaca instanceof DtAtualizacoes)) {
                $repo = $this->getEm()->getRepository(DtAtualizacoes::class);

                $atualizaca =  $repo->findOneBy([
                    'sessao' => $string
                ]);
            }


            if (!($atualizaca instanceof DtAtualizacoes)) {
                $atualizaca = new DtAtualizacoes();
                $atualizaca->setSessao($string);
            }
            $dt=new \DateTime('now') ;
            $dt->sub(new \DateInterval('PT15M'));

            $atualizaca->setDtAtualizacao($dt);

            if($atualizaca->getId()>0)
            $this->getEm()->persist($atualizaca) ;
            else
                $this->getEm()->merge($atualizaca);

            $this->getEm()->flush();

        }
    }