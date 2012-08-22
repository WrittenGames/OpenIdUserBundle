<?php

namespace WG\OpenIdUserBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;

use Doctrine\Common\Persistence\ObjectManager;

use FOS\UserBundle\Model\UserManagerInterface as FosUserManagerInterface;

use Fp\OpenIdBundle\Security\Core\User\UserManagerInterface as FpUserManagerInterface;
use Fp\OpenIdBundle\Model\IdentityManagerInterface;
use Fp\OpenIdBundle\Model\IdentityInterface;
use Fp\OpenIdBundle\Model\UserIdentityInterface;

use WG\OpenIdUserBundle\Entity\User;

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
    public function loadUserByUsername($username)
    {
        return $this->loadUserByIdentity($username);
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
    protected function loadUserByIdentity($identity)
    {
        $identityModel = $this->identityManager->findByIdentity($identity);
        if (false == $identityModel instanceof IdentityInterface) {
            throw new UsernameNotFoundException(sprintf('Identity %s not found.', $identity));
        }
        if (false == $identityModel instanceof UserIdentityInterface) {
            throw new UsernameNotFoundException('Identity must implement UserIdentityInterface.');
        }
        if (false == $identityModel->getUser() instanceof UserInterface) {
            throw new UsernameNotFoundException('UserIdentity must have a user to be set previously.');
        }

        return $identityModel->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername( $user->getUsername() );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof User;
    }

    /**
     * {@inheritdoc}
     */
    public function createUserFromIdentity($identity, array $attributes = array())
    {
        $user = $this->createUser();
        $user->setUsername( $identity );
        $this->addRole( 'ROLE_USER' );
        $this->objectManager->persist( $user );
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
        $user = new User();
        return $user;
    }

    /**
     * Deletes a user.
     *
     * @param UserInterface $user
     */
    public function deleteUser(UserInterface $user);

    /**
     * Finds one user by the given criteria.
     *
     * @param array $criteria
     *
     * @return UserInterface
     */
    public function findUserBy(array $criteria);

    /**
     * Find a user by its username.
     *
     * @param string $username
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByUsername($username);

    /**
     * Finds a user by its email.
     *
     * @param string $email
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByEmail($email);

    /**
     * Finds a user by its username or email.
     *
     * @param string $usernameOrEmail
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByUsernameOrEmail($usernameOrEmail);

    /**
     * Finds a user by its confirmationToken.
     *
     * @param string $token
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByConfirmationToken($token);

    /**
     * Returns a collection with all user instances.
     *
     * @return \Traversable
     */
    public function findUsers();

    /**
     * Returns the user's fully qualified class name.
     *
     * @return string
     */
    public function getClass();

    /**
     * Reloads a user.
     *
     * @param UserInterface $user
     */
    public function reloadUser(UserInterface $user);

    /**
     * Updates a user.
     *
     * @param UserInterface $user
     */
    public function updateUser(UserInterface $user);

    /**
     * Updates the canonical username and email fields for a user.
     *
     * @param UserInterface $user
     */
    public function updateCanonicalFields(UserInterface $user);

    /**
     * Updates a user password if a plain password is set.
     *
     * @param UserInterface $user
     */
    public function updatePassword(UserInterface $user);
}
