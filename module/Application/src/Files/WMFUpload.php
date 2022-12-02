<?php


namespace Application\Files;


use Core\Exceptions\FileException;
use mysql_xdevapi\Exception;

class  WMFUpload
{
    private $baseDir = __DIR__ . '/../../data/';
    private $name;
    private $atualName;

    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }

    /**
     * @param $file
     * @throws FileException
     */
    public function upload($file)
    {


        if ($file['error']) {
            throw  new FileException($this->codeToMessage($file['error']));
        }

        // pega a extenção
        $ext = pathinfo($file['name'], FILEINFO_EXTENSION);

        // pega o nome do arquivo
        $this->name = md5(uniqid()) . ".png";

        // pega o nome atual do arquivo
        $this->atualName = sha1(uniqid()) . ".{$ext}";

        // move os aruivo
        $this->moveArquivo($file);

        // transforma o arquivo em png
        $error = shell_exec("convert {$this->baseDir}/{$this->atualName} {$this->baseDir}/{$this->name} 2>&1");

        if($error)
            throw new FileException($error);
        return $this->name;
    }

    /**
     * @param $file
     * @throws FileException
     */
    private function moveArquivo($file)
    {

        if (!is_dir($this->baseDir) || !is_writable($this->baseDir)) {
            throw  new FileException('Upload directory is not writable, or does not exist.');
        }

        if (!move_uploaded_file($file['tmp_name'], $this->baseDir . $this->atualName))
            throw  new FileException("Não foi possivel mover o arquivo ");
    }
}
