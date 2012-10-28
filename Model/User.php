<?php

namespace WG\OpenIdUserBundle\Model;

use Fp\OpenIdBundle\Model\UserIdentityInterface;
use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\GroupableInterface;
use FOS\UserBundle\Model\UserInterface;

/**
 * Storage agnostic user object
 */
abstract class User implements UserInterface, GroupableInterface
{
    protected $id;
    
    /**
     * @var string
     */
    protected $username;
    /**
     * @var string
     */
    protected $slug;
    
    /**
     * @var string
     */
    protected $email;
    
    /**
     * @var string
     */
    protected $requestedEmail;
    
    /**
     * @var array
     */
    protected $roles;
    
    /**
     * @var boolean
     */
    protected $enabled;
    
    /**
     * @var boolean
     */
    protected $locked;
    
    /**
     * @var \DateTime
     */
    protected $lastLogin;
    
    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;
    
    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var Collection
     */
    protected $groups;

    /**
     * @var Collection
     */
    protected $identities;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->enabled = false;
        $this->locked = false;
        $this->roles = array();
    }

    /**
     * Tells if the the given user is this user.
     *
     * Useful when not hydrating all fields.
     *
     * @param UserInterface $user
     *
     * @return Boolean
     */
    public function isUser( UserInterface $user = null )
    {
        return $this->getId() == $user->getId();
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
     * Set id
     *
     * @param integer $id
     */
    public function setId( $id )
    {
        $this->id = $id;
    }

    /**
     * Sets the username.
     *
     * @param string $username
     */
    public function setUsername( $username )
    {
        $this->username = $username;
    }

    /**
     * Gets the username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Gets email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email.
     *
     * @param string $email
     */
    public function setEmail( $email )
    {
        $this->email = $email;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return Boolean true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param Boolean $enable
     */
    public function setEnabled( $enable )
    {
        $this->enabled = $enable;
    }

    /**
     * Get locked
     *
     * @return boolean 
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Sets the locking status of the user.
     *
     * @param Boolean $lock
     */
    public function setLocked( $lock )
    {
        $this->lock = $lock;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime 
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Sets the last login time
     *
     * @param \DateTime $time
     */
    public function setLastLogin( \DateTime $time )
    {
        $this->lastLogin = $time;
    }

    /**
     * Tells if the the given user has the super admin role.
     *
     * @return Boolean
     */
    public function isSuperAdmin()
    {
        return $this->hasRole( UserInterface::ROLE_SUPER_ADMIN );
    }

    /**
     * Sets the super admin status
     *
     * @param Boolean $setSuperAdmin
     */
    public function setSuperAdmin( $setSuperAdmin )
    {
        if ( $setSuperAdmin )
        {
            $this->addRole( UserInterface::ROLE_SUPER_ADMIN );
        }
        else
        {
            $this->removeRole( UserInterface::ROLE_SUPER_ADMIN );
        }
    }

    /**
     * Never use this to check if this user has access to anything!
     *
     * Use the SecurityContext, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $securityContext->isGranted('ROLE_USER');
     *
     * @param string $role
     *
     * @return Boolean
     */
    public function hasRole( $role )
    {
        return in_array(strtoupper($role), $this->roles, true);
    }

    /**
     * Sets the roles of the user.
     *
     * This overwrites any previous roles.
     *
     * @param array $roles
     */
    public function setRoles( array $roles )
    {
        $this->roles = $roles;
    }

    /**
     * Adds a role to the user.
     *
     * @param string $role
     */
    public function addRole( $role )
    {
        if ( !$this->hasRole( $role ) )
        {
            $this->roles[] = strtoupper( $role );
        }

        return $this;
    }

    /**
     * Removes a role to the user.
     *
     * @param string $role
     */
    public function removeRole( $role )
    {
        if ( false !== $key = array_search(strtoupper( $role ), $this->roles, true ) )
        {
            unset( $this->roles[$key] );
            $this->roles = array_values( $this->roles );
        }
        return $this;
    }

    /**
     * Returns the roles of the user.
     *
     * @param string $role
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Add groups
     *
     * @param WG\OpenIdUserBundle\Entity\Group $groups
     * @return User
     */
    public function addGroup( GroupInterface $groups )
    {
        $this->groups[] = $groups;
        return $this;
    }

    /**
     * Remove groups
     *
     * @param WG\OpenIdUserBundle\Entity\Group $groups
     */
    public function removeGroup( GroupInterface $groups )
    {
        $this->groups->removeElement( $groups );
    }

    /**
     * Get groups
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Add identity
     *
     * @param UserIdentityInterface $identity
     * @return User
     */
    public function addIdentity( UserIdentityInterface $identity )
    {
        $this->identities[] = $identity;
        return $this;
    }

    /**
     * Remove identity
     *
     * @param UserIdentity $identity
     */
    public function removeIdentity( UserIdentityInterface $identity )
    {
        $this->identities->removeElement( $identity );
    }

    /**
     * Get groups
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getIdentities()
    {
        return $this->identities;
    }
    
    /**
     * Gets the name of the groups which includes the user.
     *
     * @return array
     */
    public function getGroupNames()
    {
        $names = array();
        foreach ( $this->groups as $group ) $names[] = $group->getName();
        return $names;
    }

    /**
     * Indicates whether the user belongs to the specified group or not.
     *
     * @param string $name Name of the group
     *
     * @return Boolean
     */
    public function hasGroup( $name )
    {
        return in_array( $name, $this->getGroupNames() );
    }

    /**
     * Serializes the user.
     *
     * The serialized data have to contain the fields used by the equals method and the username.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->email,
            $this->locked,
            $this->enabled,
            $this->roles,
        ));
    }

    /**
     * Unserializes the user.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 2, null));

        list(
            $this->id,
            $this->username,
            $this->email,
            $this->locked,
            $this->enabled,
            $this->roles
        ) = $data;
    }
    
    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return Boolean true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return Boolean true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return Boolean true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Not implemented
     */
    public function eraseCredentials(){}

    /**
     * Not implemented
     */
    public function getPlainPassword(){}

    /**
     * Not implemented
     */
    public function setPlainPassword($password){}

    /**
     * Not implemented
     */
    public function getPassword(){}

    /**
     * Not implemented
     */
    public function setPassword($password){}

    /**
     * Not implemented
     */
    public function getSalt(){}

    /**
     * Not implemented
     */
    public function getConfirmationToken(){}

    /**
     * Not implemented
     */
    public function setConfirmationToken($confirmationToken){}

    /**
     * Not implemented
     */
    public function setPasswordRequestedAt(\DateTime $date = null){}

    /**
     * Not implemented
     */
    public function isPasswordRequestNonExpired($ttl){}

    /**
     * Not implemented
     */
    public function getUsernameCanonical(){}

    /**
     * Not implemented
     */
    public function setUsernameCanonical($usernameCanonical){}

    /**
     * Not implemented
     */
    public function getEmailCanonical(){}

    /**
     * Not implemented
     */
    public function setEmailCanonical($emailCanonical){}

    /**
     * Set slug
     *
     * @param string $slug
     * @return User
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set requestedEmail
     *
     * @param string $requestedEmail
     * @return User
     */
    public function setRequestedEmail( $requestedEmail )
    {
        $this->requestedEmail = $requestedEmail;
    
        return $this;
    }

    /**
     * Get requestedEmail
     *
     * @return string 
     */
    public function getRequestedEmail()
    {
        return $this->requestedEmail;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
