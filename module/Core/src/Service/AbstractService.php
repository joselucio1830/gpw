<?php
/**
 * Created by PhpStorm.
 * User: José Lucio
 * Date: 23/11/16
 * Time: 15:07
 */

namespace Core\Service;


use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractService
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $sm ;
    public function __construct(ServiceLocatorInterface $sm)
    {
        $this->sm = $sm;

    }
}