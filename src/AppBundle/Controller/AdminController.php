<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Description of AdminController
 *
 * @author Damian
 */
class AdminController extends DefaultController {

    /**
     * @Route("/admin")
     */
    public function adminAction(EntityManagerInterface $em) {
        $userRepo = $em->getRepository('AppBundle:User');
        $users = $userRepo->findAll();
        $userForm = $this->createUserForm($em);
        return $this->render('admin/users_crud.html.twig', [
                    'users' => $users,
                    'userForm' => $userForm->createView()
        ]);
    }

    public function deleteAction(EntityManagerInterface $em, Request $request) {
        $id = $request->get('id');
        $userRepo = $em->getRepository('AppBundle:User');
        $user = $userRepo->find($id);
        if (!empty($user)) {
            $em->remove($user);
            $em->flush();
            return new JsonResponse([
                'status' => 200,
                'id' => $id,
            ]);
        }
        else
             return new JsonResponse([
                'status' => 404,
                'id' => $id,
            ]);
    }

    private function createUserForm(EntityManagerInterface $em, $id = null) {
        if (empty($id)) {
            $user = new User();
            $method = 'POST';
        } else {
            $user = $em->getRepository('AppBundle:User')->find($id);
            $method = 'PUT';
        }
        $form = $this->createFormBuilder($user)
                ->setAction($this->generateUrl('user_new'))
                ->setMethod($method)
                ->add('username', TextType::class, [
                    'label' => 'Nazwa użytkownika',
                    'attr' => [
                        'class' => 'validate',
                        'placeholder' => 'Nazwa użytkownika'
                    ]
                ])
                ->add('password', PasswordType::class, [
                    'label' => 'Hasło',
                    'attr' => ['class' => 'validate',
                        'placeholder' => 'Hasło'
                    ]
                ])
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
