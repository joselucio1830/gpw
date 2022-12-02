<?php
namespace Core\Pagination ;

class Pagination
{
    /**
     * @var array
     */
    private  $values = [] ;

    /**
     * @var int
     */
    private $total ;

    /**
     * Pagination constructor.
     * @param $values
     * @param $total
     */
    public function __construct($values, $total)
    {
        $this->values = $values;
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param mixed $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }


}