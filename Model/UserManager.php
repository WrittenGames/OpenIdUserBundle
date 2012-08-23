<?php

namespace WG\OpenIdUserBundle\Model;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Persistence\ObjectManager;

use FOS\UserBundle\Model\UserManagerInterface as FosUserManagerInterface;
use FOS\UserBundle\Model\UserInterface as FosUserInterface;
use FOS\UserBundle\Util\CanonicalizerInterface;

use Fp\OpenIdBundle\Security\Core\User\UserManagerInterface as FpUserManagerInterface;
use Fp\OpenIdBundle\Model\IdentityManagerInterface;
use Fp\OpenIdBundle\Model\IdentityInterface;
use Fp\OpenIdBundle\Model\UserIdentityInterface;

use WG\OpenIdUserBundle\Entity\User;
use WG\OpenIdUserBundle\Entity\UserIdentity;

class UserManager implements FpUserManagerInterface, FosUserManagerInterface
{
    /**
     * @var \Fp\OpenIdBundle\Model\IdentityManagerInterface
     */
    protected $identityManager;
    protected $objectManager;
    protected $canonicaliser;

    /**
     * @param IdentityManagerInterface $identityManager
     * @param ObjectManager $objectManager
     */
    public function __construct( IdentityManagerInterface $identityManager, ObjectManager $objectManager, CanonicalizerInterface $canonicaliser )
    {
        $this->identityManager = $identityManager;
        $this->objectManager = $objectManager;
        $this->canonicaliser = $canonicaliser;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername( $username )
    {
        return $this->loadUserByIdentity( $username );
    }

    /**
     * @param string $identity
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException if identity not found.
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException if identity does not implement UserIdentityInterface.
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException if user identity does not a user instance set.
     *
     * @return UserInterface
     */
    protected function loadUserByIdentity( $identity )
    {
        $identityModel = $this->identityManager->findByIdentity( $identity );
        if ( false == $identityModel instanceof IdentityInterface) {
            throw new UsernameNotFoundException( sprintf( 'Identity %s not found.', $identity ) );
        }
        if ( false == $identityModel instanceof UserIdentityInterface ) {
            throw new UsernameNotFoundException( 'Identity must implement UserIdentityInterface.' );
        }
        if ( false == $identityModel->getUser() instanceof UserInterface ) {
            throw new UsernameNotFoundException( 'UserIdentity must have a user to be set previously.' );
        }

        return $identityModel->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser( UserInterface $user )
    {
        return $this->findUserByEmail( $user->getEmail() );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass( $class )
    {
        return $class instanceof User;
    }

    /**
     * {@inheritdoc}
     */
    public function createUserFromIdentity( $identity, array $attributes = array() )
    {
        $time = new \DateTime();
        $user = $this->createUserFromAttributes( $attributes );
        $user->setEnabled( true );
        $user->addRole( 'ROLE_USER' );
        $user->setLastLogin( $time );
        $user->setCreatedAt( $time );
        $this->objectManager->persist( $user ); // Are we supposed to do that here? o.O
        $this->objectManager->flush();
        $identityModel = new UserIdentity();
        $identityModel->setIdentity( $identity );
        $identityModel->setUser( $user );
        $identityModel->setAttributes( $attributes );
        $this->objectManager->persist( $identityModel );
        $this->objectManager->flush();
        return $user;
    }
    
    /**
     * Creates an empty user instance.
     *
     * @return UserInterface
     */
    public function createUser()
    {
        return new User();
    }
    
    /**
     * Creates a user instance from provided attributes.
     *
     * @return UserInterface
     */
    protected function createUserFromAttributes( array $attributes = array() )
    {
        $user = $this->createUser();
        if ( !isset( $attributes['contact/email'] ) )
        {
            throw new AuthenticationServiceException( "I'm sorry but we do require your OpenID service provider to respond to the 'contact/email' request." );
        }
        $user->setEmail( $attributes['contact/email'] );
        $user->setEmailCanonical( $this->canonicaliseEmail( $attributes['contact/email'] ) );
        $username = isset( $attributes['namePerson/first'] ) || isset( $attributes['namePerson/last'] )
                    ?
                        ( isset( $attributes['namePerson/first'] ) ? $attributes['namePerson/first'] : '' )
                      . (
                            isset( $attributes['namePerson/last'] )
                            ? ( isset( $attributes['namePerson/first'] ) ? ' ' : '' ) . $attributes['namePerson/last']
                            : ''
                        )
                    : $this->createUsernameFromEmail( $attributes['contact/email'] );
        $user->setUsername( $username );
        $user->setUsernameCanonical( $this->canonicaliseUsername( $username ) );
        return $user;
    }
    
    protected function createUsernameFromEmail( $str )
    {
        $pos = strpos( $str, '@' );
        return false !== $pos ? substr( $str, 0, $pos ) : $str;
    }

    /**
     * Deletes a user.
     *
     * @param UserInterface $user
     */
    public function deleteUser( FosUserInterface $user )
    {
    }

    /**
     * Finds one user by the given criteria.
     *
     * @param array $criteria
     *
     * @return UserInterface
     */
    public function findUserBy( array $criteria )
    {
    }

    /**
     * Find a user by its username.
     *
     * @param string $username
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByUsername( $username )
    {
        $usernameCanonical = $this->canonicaliseUsername( $username );
        //die( $username . ' => ' . $usernameCanonical );
        $user = $this->objectManager
                     ->getRepository( 'WGOpenIdUserBundle:User' )
                     ->findOneBy( array(
                         'usernameCanonical' => $usernameCanonical
                     ));
        return $user;
    }

    /**
     * Finds a user by its email.
     *
     * @param string $email
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByEmail( $email )
    {
        return $this->objectManager
                    ->getRepository( 'WGOpenIdUserBundle:User' )
                    ->findOneBy( array( 'emailCanonical' => $this->canonicaliseEmail( $email ) ) );
    }

    /**
     * Finds a user by its username or email.
     *
     * @param string $usernameOrEmail
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByUsernameOrEmail( $usernameOrEmail )
    {
    }

    /**
     * Finds a user by its confirmationToken.
     *
     * @param string $token
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByConfirmationToken( $token )
    {
    }

    /**
     * Returns a collection with all user instances.
     *
     * @return \Traversable
     */
    public function findUsers()
    {
        return $this->objectManager->getRepository( 'WGOpenIdUserBundle:User' )->findAll();
    }

    /**
     * Returns the user's fully qualified class name.
     *
     * @return string
     */
    public function getClass()
    {
    }

    /**
     * Reloads a user.
     *
     * @param UserInterface $user
     */
    public function reloadUser( FosUserInterface $user )
    {
        return $this->refreshUser( $user );
    }

    /**
     * Updates a user.
     *
     * @param UserInterface $user
     */
    public function updateUser( FosUserInterface $user )
    {
    }

    /**
     * Updates the canonical username and email fields for a user.
     *
     * @param UserInterface $user
     */
    public function updateCanonicalFields( FosUserInterface $user )
    {
    }

    /**
     * Updates a user password if a plain password is set.
     *
     * @param UserInterface $user
     */
    public function updatePassword( FosUserInterface $user )
    {
    }

    /**
     * Canonicalizes an email
     *
     * @param string $email
     *
     * @return string
     */
    protected function canonicaliseEmail( $email )
    {
        //return $this->emailCanonicalizer->canonicalize( $email );
        return $this->canonicaliser->canonicalize( $email );
    }

    /**
     * Canonicalizes a username
     *
     * @param string $username
     *
     * @return string
     */
    protected function canonicaliseUsername( $username )
    {
        //return $this->usernameCanonicalizer->canonicalize( $username );
        return $this->canonicaliser->canonicalize( $username );
    }
}
