<?php

namespace WG\OpenIdUserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\ORM\Mapping as ORM;

use Fp\OpenIdBundle\Entity\UserIdentity as BaseUserIdentity;

/**
 * @ORM\Entity
 * @ORM\Table(name="openiduser__identity")
 */
class UserIdentity extends BaseUserIdentity
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Symfony\Component\Security\Core\User\UserInterface
     *
     * @ORM\OneToOne(targetEntity="WG\OpenIdUserBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

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