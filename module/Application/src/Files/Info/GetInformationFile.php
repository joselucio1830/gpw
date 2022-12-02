<?php


namespace Application\Files\Info;


use Core\Validators\CPF;

class GetInformationFile
{

    // pega o cpf dentro do arquivo
    public static function getCpf($fileItem){
        $file = file_get_contents($fileItem);

        $mat = [];
         if(preg_match('/[\d]{11}/', preg_replace('/[^\d\w\-\.\ ]/', '', $file), $mat)){
             foreach ($mat as $row){
                 if(CPF::validaCPF($row)){
                     return $row ;
                 }
             }
         }
         return null ;
    }
}
