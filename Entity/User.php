<?php

namespace WG\OpenIdUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use FOS\UserBundle\Entity\User as BaseUser;

use Fp\OpenIdBundle\Model\UserIdentityInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="openid__user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Pbem\OrganisationBundle\Entity\Organisation")
     * @ORM\JoinTable(name="pbem__organisation_member",
     *      joinColumns={@ORM\JoinColumn(name="member_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="organisation_id", referencedColumnName="id")}
     * )
     */
    protected $organisations;
    
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
     * Add organisations
     *
     * @param Pbem\OrganisationBundle\Entity\Organisation $organisations
     * @return Member
     */
    public function addOrganisation(\Pbem\OrganisationBundle\Entity\Organisation $organisations)
    {
        $this->organisations[] = $organisations;
    
        return $this;
    }

    /**
     * Remove organisations
     *
     * @param Pbem\OrganisationBundle\Entity\Organisation $organisations
     */
    public function removeOrganisation(\Pbem\OrganisationBundle\Entity\Organisation $organisations)
    {
        $this->organisations->removeElement($organisations);
    }

    /**
     * Get organisations
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getOrganisations()
    {
        return $this->organisations;
    }
}