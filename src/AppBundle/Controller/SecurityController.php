<?php

namespace AppBundle\Controller;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use AppBundle\Entity\User;
use AppBundle\Form\UserRegType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;

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
                        'placeholder' => 'Nazwa użytkownika'
                    ]
                ])
                ->add('password', PasswordType::class, [
                    'label' => 'Hasło',
                    'attr' => ['class' => 'validate',
                        'id' => 'usernameLogin',
                        'placeholder' => 'Hasło'
                    ]
                ])
                ->add('login', SubmitType::class, [
                    'label' => 'Zaloguj',
                    'attr' => ['class' => 'waves-effect waves-light btn']
                ])
                ->getForm();
        return $form;
    }

    /**
     * @Route("/register", name="user_registration")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em) {
        $user = new User();
        $form = $this->createForm(UserRegType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $em->persist($user);
            $em->flush();

            $this->addFlash(
                    'notice', 'Konto zostało utworzone, teraz możesz się zalogować!'
            );
            return $this->redirectToRoute('login');
        }

        return $this->render(
                        'security/register.html.twig', array('regForm' => $form->createView())
        );
    }

}
