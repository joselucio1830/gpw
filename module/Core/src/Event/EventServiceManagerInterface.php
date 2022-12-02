<?php
/**
 * Created by PhpStorm.
 * User: José Lucio
 * Date: 31/10/16
 * Time: 16:14
 */

namespace Core\Event;


use Zend\ServiceManager\ServiceLocatorInterface;

interface EventServiceManagerInterface
{
   public function  __construct(ServiceLocatorInterface $sm);
}