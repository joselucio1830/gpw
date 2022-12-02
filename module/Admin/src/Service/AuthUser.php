<?php
/**
 * Created by PhpStorm.
 * User: José Lucio
 * Date: 25/10/16
 * Time: 14:53
 */

namespace Admin\Service;



use Admin\Entity\Usuario;
use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthUser
{
    /** @var  AuthenticationService */
    private $auth;

    /** @var  EntityManager */
    private $em;

    public function __construct(ServiceLocatorInterface $sm)
    {
        $this->auth = $sm->get(AuthenticationService::class);
        $this->em = $sm->get(EntityManager::class);
    }

    public function isConnected()
    {
        return $this->auth->hasIdentity();
    }

    /**
     * @return bool|Usuario
     */
    public function getUsuario()
    {
        if ($this->isConnected()) {
            $indentify = $this->auth->getIdentity();
            if ($indentify instanceof Usuario) {
                return $indentify;
            } else {
                $usuario = $this->em->find(Usuario::class, $indentify);
                if ($usuario instanceof Usuario) {
                    return $usuario;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public function changePassword($data)
    {
        $usuario = $this->em->find(Usuario::class, $data['login']);

        if (!($usuario instanceof Usuario)) {
            throw  new \Exception('Usuario ou senha inválido');
        } elseif ($usuario->getSenha() != md5($data['senha'])) {
            throw  new \Exception('Usuario ou senha inválido');
        } elseif ($data['novaSenha'] != $data['confirmarNovaSenha']) {
            throw  new \Exception('As senhas digitadas nao conferem!');
        }

        $usuario->setSenha(md5($data['novaSenha']));
        $this->em->merge($usuario);
        $this->em->flush();
    }
}