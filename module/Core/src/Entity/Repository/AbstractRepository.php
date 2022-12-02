<?php
/**
 * Created by PhpStorm.
 * User: José Lucio
 * Date: 23/10/16
 * Time: 15:04
 */

namespace Core\Entity\Repository;


use Core\Db\MontarWhere;
use Core\Entity\AbstractEntity;
use Core\Pagination\Pagination;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use phpDocumentor\Reflection\Types\Array_;
use Symfony\Component\Translation\Tests\StringClass;
use Ticket\Entity\Clientes;

abstract class AbstractRepository extends EntityRepository
{
    /**
     * @var array
     *     protected $camposMap = [
     * 'nome' => 'c.nome',
     * 'categoria' => 'c.nome',
     * 'cpf' => 'c.cpf',
     * 'dtCad' => 'c.dtCad',
     * 'sqlSearch' => 'c.nome like :search'
     * ];
     */
    protected $camposMap = [];


    /**
     * @param $sort
     * @param $filter
     * @param string $search
     * @param null $entity
     * @return QueryBuilder
     *
     */
    protected function mountDqlForPagination($sort, $filter, $search = "", $entity = null)
    {
        $qb = $this->createQueryBuilder('c');

        // verifica se tem o mentdo
        if (is_subclass_of($entity, AbstractEntity::class))
            $this->camposMap = $entity::getCampos();

        // monta o where
        $this->getWhere($filter, $search, $qb);

        // set o sort
        $this->addOrder($sort, $qb);

        return $qb;
    }

    /**
     * @param int $start
     * @param int $limit
     * @param int $sort
     * @param string $filter
     * @return  Pagination | array
     */

    public function findForPagination($start, $limit, $sort, $filter, $search = "", $entity = null)
    {

        $qb = $this->mountDqlForPagination($sort, $filter, $search, $entity);


        return $this->getPaginationDoctrine($start, $limit, $qb);

    }

    public function getCampo($key)
    {

        if (!empty($this->camposMap[$key])) {
            return trim($this->camposMap[$key]);
        } else {
            return false;
        }
    }

    protected function addOrder($sort, QueryBuilder &$query)
    {

        if (is_array($sort)) {
            foreach ($sort as $k => $item) {
                if (is_array($item) && $this->getCampo($item['property'])) {
                    $query->addOrderBy(new Query\Expr\OrderBy($this->getCampo($item['property']), $item['direction']));
                }
            }
            if (!empty($sort['property']) && !empty($sort['direction']) && $this->getCampo($sort['property'])) {
                $query->addOrderBy(new Query\Expr\OrderBy($this->getCampo($sort['property']), $sort['direction']));
            }
        }
    }

    /**
     * @param $start
     * @param $limit
     * @param QueryBuilder $query
     * @return Pagination
     */
    public function getPaginationDoctrine($start, $limit, QueryBuilder $query)
    {
        if ($limit > 0)
            $query->setFirstResult($start)
                ->setMaxResults($limit);


        // No need to manually get get the result ($query->getResult())
        $paginator = new Paginator($query, true);
        $paginator->setUseOutputWalkers(true);
        //var_dump($paginator->getIterator()->getArrayCopy());
        //$paginator->getQuery()->setHydrationMode(\Doctrine\ORM\Query::HYDRATE_ARRAY);


        //   var_dump($paginator->getQuery()->getSQL()) ;

        return ['rows' => $paginator->getIterator()->getArrayCopy(),
            'total' => $paginator->count()];

    }

    /**
     * @param $start
     * @param $limitlie
     * @param QueryBuilder $query
     * @return Pagination
     */
    public function getPagination($start, $limit, QueryBuilder $query, $mode = \Doctrine\ORM\Query::HYDRATE_ARRAY)
    {
        if ($limit > 0)
            $query->setFirstResult($start)
                ->setMaxResults($limit);


        // No need to manually get get the result ($query->getResult())
        $paginator = new Paginator($query);
        $paginator->setUseOutputWalkers(false);
        $paginator->getQuery()->setHydrationMode($mode);

        //   var_dump($paginator->getQuery()->getSQL()) ;


        return New Pagination($paginator->getIterator()->getArrayCopy(), $paginator->count());
    }

    /**
     * @param $start
     * @param $limit
     * @param QueryBuilder $query
     * @return array
     */
    public function getPaginationArray($start, $limit, QueryBuilder $query, $mode = \Doctrine\ORM\Query::HYDRATE_ARRAY)
    {

        $pagination = $this->getPagination($start, $limit, $query, $mode);
        return ['rows' => $pagination->getValues(),
            'total' => $pagination->getTotal()];
    }


    public function getWhere($filter, $search, QueryBuilder &$query)
    {
        $where = new MontarWhere($this->camposMap);
        $where->montar($filter);
        $where->search($search);
        if ($where->getWhere()) {
            $query->where($where->getWhere());
            $query->setParameters($where->getValues());
        }
    }
}
