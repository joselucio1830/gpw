<?php
/**
 * Created by PhpStorm.
 * User: José Lucio
 * Date: 24/10/16
 * Time: 13:52
 */

namespace Admin\Storage;



use Zend\Authentication\Storage\Session as ZendAuthenticationStorageSession;

/**
 * Class Session
 * @package Armenio\Authentication\Storage
 */
class Session extends ZendAuthenticationStorageSession
{
    /**
     * @return \Zend\Session\ManagerInterface
     */
    public function getManager()
    {
        return $this->session->getManager();
    }

    /**
     * Set the TTL (in seconds) for the session cookie expiry
     *
     * Can safely be called in the middle of a session.
     *
     * @param  int $ttl
     * @return $this;
     */
    public function rememberMe($ttl = 30 * 24 * 60 * 60)
    {
        $this->getManager()->rememberMe($ttl);
        return $this;
    }

    /**
     * Set a 0s TTL for the session cookie
     *
     * Can safely be called in the middle of a session.
     *
     * @return $this;
     */
    public function forgetMe()
    {
        $this->getManager()->forgetMe();
        return $this;
    }
}