<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Controller made to do CRUD on User model
 *
 * @author Damian
 */
class AdminController extends DefaultController {

    /**
     * @Route("/admin", name="admin")
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function adminAction(EntityManagerInterface $em) {
        $userRepo = $em->getRepository('AppBundle:User');
        $users = $userRepo->findAll();
        $userForm = $this->createUserForm($em);
        return debug($this->render('admin/users_crud.html.twig', [
                    'users' => $users,
                    'userForm' => $userForm->createView()
        ]));
    }

    /**
     * Delete user depending on id.
     * 
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return  Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction(EntityManagerInterface $em, Request $request) {
        $id = $request->get('id');
        $userRepo = $em->getRepository('AppBundle:User');
        $user = $userRepo->find($id);
        if (!empty($user)) {
            $em->remove($user);
            $em->flush();
            return new JsonResponse([
                'status' => 200,
                'message' => 'Pomyślnie usunięto użytkownika.',
            ]);
        } else
            return new JsonResponse([
                'status' => 404,
                'message' => 'Użytkownik nie został odnaleziony w systemie.'
            ]);
    }

    /**
     * Find User object by id
     * 
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return  Symfony\Component\HttpFoundation\JsonResponse
     * 
     */
    public function getAction(EntityManagerInterface $em, Request $request) {
        $id = $request->get('id');
        $userRepo = $em->getRepository('AppBundle:User');
        $user = $userRepo->find($id);
        if (!empty($user)) {
            return new JsonResponse([
                'status' => 201,
                'message' => 'Odnaleziono użytkownika.',
                'user' => $user->toArray()
            ]);
        } else
            return new JsonResponse([
                'status' => 404,
                'message' => 'Użytkownik nie został odnaleziony w systemie.'
            ]);
    }

    /**
     * Save User object
     * 
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return  Symfony\Component\HttpFoundation\JsonResponse
     * 
     */
    public function saveAction(EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $passwordEncoder) {

        $id = (int) $request->get('id_user');
        if (empty($id)) {
            $userForm = $this->createUserForm($em);
        } else {
            $userForm = $this->createUserForm($em, $id);
        }
        $fromRequest = $userForm->handleRequest($request);
        if ($fromRequest->isSubmitted()) {

            $user = $fromRequest->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                $errorsString = '';
                foreach ($errors as $e) {
                    $mes = $e->getMessage();
                    $errorsString .= $mes;
                }
                return new JsonResponse([
                    'status' => 400,
                    'message' => $errorsString,
                ]);
            }
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $em->persist($user);
            $em->flush();
            return new JsonResponse([
                'status' => 201,
                'message' => 'Pomyślnie utworzono użytkownika!',
            ]);
        } else
            return new JsonResponse([
                'status' => 400,
                'message' => $fromRequest->getErrors(),
            ]);
    }
    /**
     * create Form for User object
     * 
     * @param EntityManagerInterface $em
     * @param int $id
     * @return  Symfony\Component\Form\Form
     * 
     */
    private function createUserForm(EntityManagerInterface $em, $id = null) {
        if (empty($id)) {
            $user = new User();
        } else {
            $user = $em->getRepository('AppBundle:User')->find($id);
        }
        $form = $this->createFormBuilder($user)
                ->setAction($this->generateUrl('user_save'))
                ->setMethod('POST')
                ->add('id_user', HiddenType::class, [
                    'mapped' => false
                ])
                ->add('username', TextType::class, [
                    'label' => 'Nazwa użytkownika',
                    'attr' => [
                        'class' => 'validate',
                        'placeholder' => 'Nazwa użytkownika'
                    ]
                ])
                ->add('plainPassword', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'first_options' => array('label' => 'Hasło'),
                    'second_options' => array('label' => 'Powtórz hasło'),
                ))
                ->add('email', EmailType::class, [
                    'label' => 'Email',
                    'attr' => ['class' => 'validate',
                        'placeholder' => 'E-mail'
                    ]
                ])
                ->add('isActive', CheckboxType::class, [
                    'label' => 'Czy aktywny',
                    'attr' => ['class' => 'validate',
                    ]
                ])
                ->add('zapisz', SubmitType::class, [
                    'label' => 'Zapisz',
                    'attr' => ['class' => 'waves-effect waves-light btn']
                ])
                ->getForm();
        return $form;
    }

}
