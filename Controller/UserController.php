<?php

namespace WG\OpenIdUserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class ProfileController extends ContainerAware
{
    /**
     * Show the user
     */
    public function showAction()
    {
        $user = $this->container->get( 'security.context' )->getToken()->getUser();
        if ( !is_object( $user ) || !$user instanceof UserInterface )
        {
            throw new AccessDeniedException( "You don't have access to this section." );
        }
        return $this->container->get( 'templating' )
                               ->renderResponse( 'WGOpenIdUserBundle:Profile:show.html.twig', array(
                                   'user' => $user,
                               ));
    }

    /**
     * Edit the user
     */
    public function editAction()
    {
        $user = $this->container->get( 'security.context' )->getToken()->getUser();
        if ( !is_object( $user ) || !$user instanceof UserInterface )
        {
            throw new AccessDeniedException( "You don't have access to this section." );
        }
        $defaultData = array(
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
        );
        $formBuilder = $this->container->get('form.factory')->createBuilder( 'form', $defaultData );
        $form = $formBuilder
                    ->add( 'username', 'text' )
                    ->add( 'email', 'email' )
                    ->getForm();
        $request = $this->container->get( 'request' );
        if ( $request->getMethod() == 'POST' )
        {
            $form->bind( $request );
            $data = $form->getData();   // data is an array with "username" and "email" keys
            // TODO: check if email is unique, canonicalise etc., then store
            //$user->setUsername( $data['usermame'] );
            //$user->setEmail( $data['email'] );
            $this->container->get( 'session' )->setFlash( 'fos_user_success', 'profile.flash.updated' );
            return new RedirectResponse(
                            $this->container->get( 'router' )->generate( 'wg_openiduser_profile_show' )
                        );
        }
        return $this->container->get('templating')->renderResponse(
            'WGOpenIdUserBundle:Profile:edit.html.twig', array(
                'form' => $form->createView(),
        ));
    }
}
