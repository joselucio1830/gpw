<?php
namespace Core\Event ;

interface EventInterface
{

    public function onCreate($data) ;
    public function onUpdate($data) ;
    public function onDelete($data) ;
    public function onExecute($data, $evtName) ;
}