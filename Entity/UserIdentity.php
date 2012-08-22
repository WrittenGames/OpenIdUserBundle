<?php

namespace WG\OpenIdUserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\ORM\Mapping as ORM;

use Fp\OpenIdBundle\Entity\UserIdentity as BaseUserIdentity;
use Fp\OpenIdBundle\Model\UserIdentityInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="openid__identity")
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
     * @ORM\OneToOne(targetEntity="WG\OpenIdUserBundle\Entity\User")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id")
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
     * @return OpenIdIdentity
     */
    public function setMember( \WG\OpenIdUserBundle\Entity\User $user = null )
    {
        $this->user = $user;
        return $user;
    }

    /**
     * Get user
     *
     * @return WG\OpenIdUserBundle\Entity\User
     */
    public function getMember()
    {
        return $this->user;
    }
}