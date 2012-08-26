<?php

namespace WG\OpenIdUserBundle\Model;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Persistence\ObjectManager;

use FOS\UserBundle\Model\UserManagerInterface as FosUserManagerInterface;
use FOS\UserBundle\Model\UserInterface as FosUserInterface;

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

    /**
     * @param IdentityManagerInterface $identityManager
     * @param ObjectManager $objectManager
     */
    public function __construct( IdentityManagerInterface $identityManager, ObjectManager $objectManager )
    {
        $this->identityManager = $identityManager;
        $this->objectManager = $objectManager;
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
        return $this->findUserBy( array( 'id' => $user->getId() ) );
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
        if ( isset( $attributes['contact/email'] ) )
        {
            $user->setEmail( strtolower( $attributes['contact/email'] ) );
            $user->setRequestedEmail( strtolower( $attributes['contact/email'] ) );
        }
        $username = isset( $attributes['namePerson'] )
                    ? $attributes['namePerson'] // Yahoo
                    : ( isset( $attributes['namePerson/first'] ) || isset( $attributes['namePerson/last'] ) // Google
                        ?
                            ( isset( $attributes['namePerson/first'] ) ? $attributes['namePerson/first'] : '' )
                          . (
                                isset( $attributes['namePerson/last'] )
                                ? ( isset( $attributes['namePerson/first'] ) ? ' ' : '' ) . $attributes['namePerson/last']
                                : ''
                            )
                        : ( $user->getEmail() ? $this->createUsernameFromEmail( $user->getEmail() ) : 'User' ) );
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
        $this->objectManager->remove( $user );
        $this->objectManager->flush();
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
        return $this->objectManager
                    ->getRepository( 'WGOpenIdUserBundle:User' )
                    ->findOneBy( $criteria );
    }

    /**
     * Find a user by their username.
     *
     * @param string $username
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByUsername( $username )
    {
        return $this->findUserBy( array( 'username' => $username ) );
    }

    /**
     * Finds a user by their email.
     *
     * @param string $email
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByEmail( $email )
    {
        return $this->findUserBy( array( 'email' => $email ) );
    }

    /**
     * Finds a user by their username or email.
     *
     * @param string $usernameOrEmail
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByUsernameOrEmail( $usernameOrEmail )
    {
        return false !== strpos( $usernameOrEmail, '@' )
                ? $this->findUserByEmail( $usernameOrEmail )
                : $this->findUserByUsername( $usernameOrEmail );
    }

    /**
     * Finds a user by their ID or slug.
     *
     * @param string $IdOrSlug
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByIdOrSlug( $IdOrSlug )
    {
        return is_numeric( $IdOrSlug )
                ? $this->findUserBy( array( 'id' => $IdOrSlug ) )
                : $this->findUserBy( array( 'slug' => $IdOrSlug ) );
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
        // TODO
    }

    /**
     * Returns the user's fully qualified class name.
     *
     * @return string
     */
    public function getClass()
    {
        // TODO
    }

    /**
     * Not implemented
     */
    public function updateCanonicalFields( FosUserInterface $user )
    {
    }

    /**
     * Not implemented
     */
    public function updatePassword( FosUserInterface $user )
    {
    }

    /**
     * Not implemented
     */
    public function findUserByConfirmationToken( $token ){}
}
