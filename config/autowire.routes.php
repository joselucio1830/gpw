<?php
return array(
    'route-2' => array(
        'type' => 'Zend\\Router\\Http\\Segment',
        'options' => array(
            'route' => '/api/contra-check/view/:name',
            'defaults' => array(
                'controller' => 'Application\\Controller\\IndexController',
                'action' => 'display',
            ),
        ),
    ),
    'route-1' => array(
        'type' => 'Zend\\Router\\Http\\Segment',
        'options' => array(
            'route' => '/api/contra-check/remove/:id',
            'defaults' => array(
                'controller' => 'Application\\Controller\\IndexController',
                'action' => 'remove',
            ),
        ),
    ),
    'route-0' => array(
        'type' => 'Zend\\Router\\Http\\Literal',
        'options' => array(
            'route' => '/api/contra-check/list',
            'defaults' => array(
                'controller' => 'Application\\Controller\\IndexController',
                'action' => 'listContrachecks',
            ),
        ),
    ),
    'route-3' => array(
        'type' => 'Zend\\Router\\Http\\Literal',
        'options' => array(
            'route' => '/api/contra-check/upload',
            'defaults' => array(
                'controller' => 'Application\\Controller\\IndexController',
                'action' => 'upload',
            ),
        ),
    ),
);
