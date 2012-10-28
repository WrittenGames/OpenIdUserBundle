<?php

namespace WG\OpenIdUserBundle\Model;

use Fp\OpenIdBundle\Model\UserIdentity as FpUserIdentity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Storage agnostic user identity object
 */
abstract class UserIdentity extends FpUserIdentity
{
    /**
     * @var Symfony\Component\Security\Core\User\UserInterface
     */
    protected $user;

    /**
     * Set user
     *
     * @param WG\OpenIdUserBundle\Entity\User $user
     * @return UserIdentity
     */
    public function setUser( UserInterface $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return WG\OpenIdUserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
