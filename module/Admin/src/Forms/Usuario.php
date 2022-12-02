<?php
namespace Admin\Forms;



use Zend\Form\Annotation as Form;

/**
 * @Form\Name("usuario")
 */
class Usuario
{
    /**
     * @Form\Type("Zend\Form\Element\Text")
     * @Form\Required({"required":"true" })
     * @Form\Filter({"name":"StripTags"})
     * @Form\Options({"label":"Login/Email:"})
     * @Form\Attributes({"class":"form-control"})
     */
    public $username;

    /**
     * @Form\Type("Zend\Form\Element\Password")
     * @Form\Required({"required":"true" })
     * @Form\Filter({"name":"StripTags"})
     * @Form\Options({"label":"Senha:"})
     * @Form\Attributes({"class":"form-control"})
     */
    public $password;


    /**
     * @Form\Type("Zend\Form\Element\Button")
     * @Form\Options({"label":" Entrar"})
     * @Form\Attributes({"class":"btn btn-success glyphicon glyphicon-log-in","type":"Submit"})
     */
    public $submit;
    /**
     * @Form\Type("Zend\Form\Element\Button")
     * @Form\Options({"label":" Recuperar Senha"})
     * @Form\Attributes({"class":"btn btn-info glyphicon glyphicon-question-sign"})
     */
    public $newpassword;

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     * @return Usuario
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return Usuario
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRememberme()
    {
        return $this->rememberme;
    }

    /**
     * @param mixed $rememberme
     * @return Usuario
     */
    public function setRememberme($rememberme)
    {
        $this->rememberme = $rememberme;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubmit()
    {
        return $this->submit;
    }

    /**
     * @param mixed $submit
     * @return Usuario
     */
    public function setSubmit($submit)
    {
        $this->submit = $submit;
        return $this;
    }

}