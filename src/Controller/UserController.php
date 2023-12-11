<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('users')]
class UserController extends AbstractController
{


    #[Route('/list', name:'user_list')]
    public function listAction(UserRepository $users)
    {
        return $this->render('user/list.html.twig', ['users' => $users->findAll()]);
    }


    #[Route('/create', name:'user_create')]
    public function createAction(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em)
    {
        if($this->getUser() && $this->isGranted('ROLE_ADMIN') === false) {
            $this->addFlash('error', "Vous avez déjà un compte.");
            return $this->redirectToRoute('homepage');
        }

        $user = new User();
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));
            if($form->get('roles')->getData() === true) {
                $this->denyAccessUnlessGranted('ROLE_ADMIN', message:"Vous n'êtes pas autorisé à changer les droits de cet utilisateurs");
                $user->setRoles(['ROLE_ADMIN']);
            }
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', "L'utilisateur a bien été ajouté.");
            return $this->redirectToRoute('homepage');
        }

        return $this->render('user/create.html.twig', ['form' => $form]);
    }


    #[Route('/{id}/edit', name:'user_edit')]
    public function editAction(User $user, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('edit', $user);
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', "L'utilisateur a bien été modifié");
            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);

    }

    #[Route('/{id}/delete', name:'user_delete')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function deleteAction(User $user, EntityManagerInterface $em) : RedirectResponse
    {
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', "L'utilisateur a bien été supprimé");
        return $this->redirectToRoute('user_list');

    }
}
