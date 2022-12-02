<?php
return [
    'permission' =>
        ['roles' => [
            ['name' => 'visitante', 'parent' => ['anonymous']],
            ['name' => 'func', 'parent' => ['visitante']],
        ]]
];
