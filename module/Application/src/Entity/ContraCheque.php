<?php


namespace Application\Entity;


use Admin\Entity\Usuario;
use Core\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="\Application\Entity\Repository\ContraChequeRepository")
 * @ORM\Table(name="contracheque_contraCheques")
 * @ORM\HasLifecycleCallbacks
 */
class ContraCheque extends AbstractEntity
{
    /**
     * @var string Description
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     *
     */

    protected $id;
    /**
     * @var string Description
     * @Assert\NotBlank(message="O mês e ano não pode estar vazio!")
     * @ORM\Column(type="string", length=10 )
     */

    protected $referencia;
    /**
     * @var string Description
     * @ORM\Column(type="string", length=45)
     */

    protected $fileName;
    /**
     * @var string Description
     * @Assert\NotBlank(message="O mês e ano não pode estar vazio!")
     * @ORM\Column(type="datetime")
     */

    protected $dtCad;

    /**
     * @var Usuario
     * @ORM\ManyToOne(targetEntity="\Admin\Entity\Usuario")
     * @ORM\JoinColumn(name="usuario_login", referencedColumnName="login",nullable=false)
     */
    private $usuario  ;

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }



    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getReferencia()
    {
        return $this->referencia;
    }

    /**
     * @param string $referencia
     */
    public function setReferencia($referencia)
    {
        $this->referencia = $referencia;
    }

    /**
     * @return string
     */
    public function getDtCad()
    {
        return $this->dtCad;
    }

    /**
     * @param string $dtCad
     */
    public function setDtCad($dtCad)
    {
        $this->dtCad = $dtCad;
    }

    /**
     * @return Usuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * @param Usuario $usuario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }





    /**
     * @ORM\PrePersist
     * @throws \Exception
     */
    public function prePersist(){
        if(!$this->dtCad){
            $this->dtCad = new \DateTime('now');
        }
    }

    public static function getCampos()
    {
        return [
            'c.dtCad'=>'data',
            'usuario'=>'u.login'
        ];
    }
}
