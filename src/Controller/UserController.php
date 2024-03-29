<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\UserForm;
use App\Form\EditUserForm;
use App\Form\ToggleRoleForm;
use App\Form\EditPasswordForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('users')]
class UserController extends AbstractController
{


    #[Route('/list', name:'user_list')]
    /**
     * Display the users list page
     *
     * @param UserRepository $users
     * @param TagAwareCacheInterface $cache
     * @return Response
     */
    public function list(UserRepository $users, TagAwareCacheInterface $cache): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $key = preg_replace('#@.#','',$this->getUser()->getUserIdentifier());
        $datas = $cache->get(
            'user_list'.$key,
            function (ItemInterface $item) use ($users) {
                $item->expiresAfter(3600);
                $item->tag('user_list');
                return $this->render('user/list.html.twig', ['users' => $users->findAll()]);
            },
        );
        return $datas;

    }


    #[Route('/create', name:'user_create')]
    /**
     * Create a user account
     *
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param EntityManagerInterface $entityManager
     * @param TagAwareCacheInterface $cache
     * @return Response
     */
    public function create(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): Response
    {
        if ($this->getUser() !== null) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $user = new User();
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));
            if ($form->get('roles')->getData() === true) {
                // Will redirect on login page if not connected.
                $this->denyAccessUnlessGranted('ROLE_ADMIN');
                $user->setRoles(['ROLE_ADMIN']);
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $cache->invalidateTags(['user_list']);
            $this->addFlash('success', "L'utilisateur a bien été ajouté.");
            return $this->redirectToRoute('homepage');
        }

        return $this->render('user/create.html.twig', ['form' => $form]);

    }


    #[Route('/{id}/edit', name:'user_edit')]
    /**
     * Edit a user account
     *
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param TagAwareCacheInterface $cache
     * @return Response
     */
    public function edit(User $user, Request $request, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): Response
    {
        $this->denyAccessUnlessGranted('edit', $user, message:"Edition");
        $form = $this->createForm(EditUserForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() === true) {
            if ($form->isValid() === true) {
                $entityManager->persist($user);
                $entityManager->flush();
                $cache->invalidateTags(['user_list']);
                $cache->invalidateTags(['task_list']);
                $this->addFlash('success', "Modification réussie");
                return $this->redirectToRoute('homepage');
            }

            $entityManager->refresh($user);
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);

    }


    #[Route('/{id}/toggle', name:'user_toggle_role')]
    /**
     * Toggle a user role ROLE_USER to ROLE_ADMIN or ROLE_ADMIN to ROLE_USER
     *
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param TagAwareCacheInterface $cache
     * @return Response
     */
    public function toggleRole(User $user, EntityManagerInterface $entityManager, Request $request, TagAwareCacheInterface $cache): Response
    {
        $this->denyAccessUnlessGranted('authorize', subject : $user, message: "Vous n'êtes pas autorisé à changer les droits de cet utilisateurs");
        $form = $this->createForm(ToggleRoleForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            if ($form->get('roles')->getData() !== false) {
                switch ($user->getRoles()[0]) {
                case 'ROLE_ADMIN':
                    $user->setRoles([]);
                    break;
                case 'ROLE_USER':
                    $user->setRoles(['ROLE_ADMIN']);
                    break;
                default:
                    $this->addFlash('error', "Erreur");
                    $cache->invalidateTags(['user_list']);
                    return $this->redirectToRoute('user_list');
                }

                $entityManager->persist($user);
                $entityManager->flush();
                $cache->invalidateTags(['user_list']);
                $this->addFlash('success', "Le rôle a bien était modifié");
                return $this->redirectToRoute('user_list');
            }

            $cache->invalidateTags(['user_list']);
            $this->addFlash('error', "Vous n'avez pas modifié les droits de ce compte");
            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/toggle.html.twig', ['form' => $form->createView(), 'user' => $user]);

    }


    #[Route('/{id}/editpass', name:'user_password_change')]
    /**
     * user Password change
     *
     * @param User $user
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function editPassword(User $user, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', $user, message:"Edition");
        $form = $this->createForm(EditPasswordForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() === true) {
            if ($form->isValid() === true) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', "Votre mot de passe a était modifié");
                return $this->redirectToRoute('homepage');
            }
            $entityManager->refresh($user);
        }

        return $this->render('user/editpassword.html.twig', ['form' => $form->createView(), 'user' => $user]);

    }


    #[Route('/{id}/delete', name:'user_delete')]
    /**
     * Delete an user account
     *
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param UserRepository $anonymous
     * @param TagAwareCacheInterface $cache
     * @return RedirectResponse
     */
    public function delete(User $user, EntityManagerInterface $entityManager, Request $request, UserRepository $anonymous, TagAwareCacheInterface $cache) : RedirectResponse
    {
        $this->denyAccessUnlessGranted('delete', $user);
        $cache->invalidateTags(['user_list']);
        $token = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-item', $token) === true) {
            $task = $entityManager->getRepository(Task::class);
            $tasks = $task->findBy(['user' => $user->getId()]);
            $anonymousUser = $anonymous->findOneBy(['username' => 'anonyme']);
            foreach ($tasks as $task) {
                $task->setUser($anonymousUser);
            }

            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', "L'utilisateur a bien été supprimé");
            return $this->redirectToRoute('user_list');
        }

        $this->addFlash('error', "Vous n'êtes pas autorisé à supprimer ce compte");
        return $this->redirectToRoute('user_list');

    }


}
