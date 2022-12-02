<?php


namespace Application\Entity\Repository;


use Core\Entity\Repository\AbstractRepository;

class ContraChequeRepository extends AbstractRepository
{
    public function findForPagination($start, $limit, $sort, $filter, $search = "", $entity = null)
    {

        $qb = $this->mountDqlForPagination($sort, $filter, $search, $entity);

        $qb->innerJoin('c.usuario','u') ;
        $qb->addOrderBy('c.dtCad','DESC') ;



        return $this->getPaginationDoctrine($start, $limit, $qb);

    }
}
