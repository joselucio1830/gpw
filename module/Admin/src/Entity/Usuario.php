<?php

namespace Admin\Entity;


use Core\Entity\AbstractEntity;
use Core\Utils\UUID;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Admin\Entity\Repository\UsuarioRepository")
 * @ORM\Table(name="admin_usuario")
 * @ORM\HasLifecycleCallbacks
 */
class Usuario extends AbstractEntity
{
    /**
     * @var string Description
     * @ORM\Id
     * @ORM\Column(type="string", length=120,unique=TRUE, name="login" )
     * @Assert\NotBlank(message="O login não pode estar vazio")
     *
     */

    protected $login;
    /**
     * @var string Description
     * @Assert\NotBlank(message="A Senha não pode estar vazia!")
     * @ORM\Column(type="string", length=32,unique=false, name="senha" )
     */

    protected $senha;

    /**
     * @var boolean
     * @ORM\Column(type="boolean" )
     */
    protected $ativo = false;

    /**
     * @var string Description
     * @ORM\Column(type="string", length=90,unique=true, name="email", nullable=true )
     */
    protected $email;
    /**
     * @var string Description
     * @ORM\Column(type="string" )
     * @Assert\NotBlank(message="Insira o perifil do usuário!")
     */
    protected $perfil;
    /**
     * @var string Nome
     * @Assert\NotBlank(message="Insira o Nome do usuário!")
     * @ORM\Column(type="string",)
     */
    protected $nome;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime" )
     */
    protected $dtCad;

    /**
     * @var string Nome
     * @ORM\Column(type="string" , unique=true)
     */
    protected $token;

    /**
     * @var string Nome
     * @ORM\Column(type="string", unique=true)
     */
    protected $apiToken;

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * @param string $apiToken
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;
    }


    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $login
     * @return Usuario
     */
    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @return string
     */
    public function getSenha()
    {
        return $this->senha;
    }

    /**
     * @param string $senha
     * @return Usuario
     */
    public function setSenha($senha)
    {
        if (strlen($senha) < 32)
            $this->senha = md5($senha);
        else
            $this->senha = $senha;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAtivo()
    {
        return $this->ativo == 'S';
    }

    /**
     * @param boolean $ativo
     * @return Usuario
     */
    public function setAtivo($ativo)
    {
        $this->ativo = $ativo;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Usuario
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPerfil()
    {
        return strtolower($this->perfil);
    }

    /**
     * @param string $perfil
     * @return Usuario
     */
    public function setPerfil($perfil)
    {
        $this->perfil = strtolower($perfil);
        return $this;
    }

    /**
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param string $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return \DateTime
     */
    public function getDtCad()
    {
        return $this->dtCad;
    }

    /**
     * @param \DateTime $dtCad
     */
    public function setDtCad($dtCad)
    {
        $this->dtCad = $dtCad;
    }


    /**
     * @ORM\PrePersist()
     * @throws \Core\Entity\Validators\ValidateException
     */
    public function __prePesrist()
    {
        // valida os dados
        $this->validate();

        if (empty($this->dtCad))
            $this->dtCad = new \DateTime('now');

        // se nao tiver senha coloca senha
        if (empty($this->token)) {
            $this->token = md5("{$this->login}{$this->senha}{$this->dtCad->format('Ymd')}");
        }

        // gera uma rash para o uso de api
        if (empty($this->apiToken))
            $this->apiToken = UUID::GERAR();


    }


    public static function getCampos()
    {
        return [
            'login' => 'c.login',
            'nome' => 'c.nome',
            'perfil'=>'c.perfil'
        ];
    }
}
