<?php

namespace Core;

//use Core\Doctrine\Functions\MD5;

use Core\Doctrine\Functions\Concat;
use Core\Doctrine\Functions\DateFormat;
use Core\Doctrine\Functions\MD5;

return [

    'doctrine' => array(

        'configuration' => array(
            'orm_default' => array(
                'numeric_functions' => array(
                    'MD5'  => MD5::class
                ),
                'datetime_functions' => array(

                    'DATE_FORMAT' => DateFormat::class
                ),
                'string_functions'   => array(
    //'CONCAT'=>Concat::class
                ),
    //            'metadata_cache'     => 'filesystem',
         //        'query_cache'        => 'filesystem
                ////        'result_cache'       => 'filesystem',
            )
        )
    )


];
