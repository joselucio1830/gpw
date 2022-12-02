<?php
/**
 * Created by PhpStorm.
 * User: José Lucio
 * Date: 24/03/17
 * Time: 11:54
 */

namespace Core\Api;


interface Urls {
    public function getApiSecret() ;
    public function getUrl($name) ;
}