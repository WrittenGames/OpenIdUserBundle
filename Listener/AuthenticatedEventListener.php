<?php

namespace WG\OpenIdUserBundle\Listener;

use Symfony\Component\Security\Core\Event\AuthenticationEvent;

use Doctrine\Common\Persistence\ObjectManager;

use FOS\UserBundle\Model\UserInterface;

class AuthenticatedEventListener
{
    protected $objectManager;
    
    public function __construct( ObjectManager $objectManager )
    {
        $this->objectManager = $objectManager;
    }
    
    public function onAuthenticationSuccess( AuthenticationEvent $event )
    {
        $user = $event->getAuthenticationToken()->getUser();
        if ( $user instanceof UserInterface )
        {
            $user->setLastLogin( new \DateTime() );
            $this->objectManager->flush();
        }
    }
}
