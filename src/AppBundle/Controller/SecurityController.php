<?php

namespace AppBundle\Controller;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use AppBundle\Entity\User;

class SecurityController extends DefaultController {

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request, AuthenticationUtils $authUtils) {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();
        $loginForm = $this->createLoginForm();
        return $this->render('security/login.html.twig', array(
                    'last_username' => $lastUsername,
                    'loginForm' => $loginForm->createView(),
                    'error' => $error,
        ));
    }
    
    private function createLoginForm() {
        $user = new User();
        $form = $this->createFormBuilder($user)
                ->setAction($this->generateUrl('login'))
                ->setMethod('POST')
                ->add('username', TextType::class, [
                    'label' => 'Nazwa użytkownika',
                    'attr' => [
                        'class' => 'validate',
                        'id' => 'usernameLogin',
                        'placeholder'=> 'Nazwa użytkownika'
                    ]
                ])
                ->add('password', PasswordType::class, [
                    'label' => 'Hasło',
                    'attr' => ['class' => 'validate',
                        'id' => 'usernameLogin',
                        'placeholder'=> 'Hasło'
                        ]
                ])
                ->add('login', SubmitType::class, [
                    'label' => 'Zaloguj',
                    'attr' => ['class'=> 'waves-effect waves-light btn']
                    ])
                ->getForm();
        return $form;
    }

}
