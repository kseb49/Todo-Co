<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskForm;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{


    #[Route("/tasks", name: "task_list")]
    /**
     * Display the tasks list page
     *
     * @param TaskRepository $task
     * @param TagAwareCacheInterface $cache
     * @param UserRepository $userRepo
     * @return Response
     */
    public function list(TaskRepository $task, TagAwareCacheInterface $cache, UserRepository $userRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $userRepo->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $key = preg_replace('#@.#','',$this->getUser()->getUserIdentifier());
        $datas = $cache->get(
            'task_list_'.$key,
            function (ItemInterface $item) use ($task, $user) {
                $item->expiresAfter(3600);
                $user_tasks = $task->findBy(['user' => $user]);
                $ment = $user->getMentionned();
                if (count($ment) !== 0) {
                    $tasks_ids = [];
                    foreach ($ment as $value) {
                        array_push($tasks_ids, $value->getId());
                    }

                    $tasks = $task->findExcept($user->getId(), $tasks_ids);
                } else {
                    $tasks = $task->findExcept($user->getId());
                }
                $item->tag('task_list');
                return $this->render(
                    'task/list.html.twig',
                    [
                     'tasks'      => $tasks,
                     'user_tasks' => $user_tasks,
                     'refs'       => $ment
                    ],
                );
            },
        );
        return $datas;

    }


    #[Route("/tasks/create", name: "task_create")]
    /**
     * Creating a task
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param TagAwareCacheInterface $cache
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $task = new Task();
        $form = $this->createForm(TaskForm::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $task->setUser($this->getUser());
            $entityManager->persist($task);
            $entityManager->flush();
            $cache->invalidateTags(['task_list']);
            $this->addFlash('success', 'La tâche a été bien été ajoutée.');
            return $this->redirectToRoute('task_list');
        }

        return $this->render(
            'task/create.html.twig',
            ['form' => $form]
        );

    }


    #[Route("/tasks/{id}/edit", name: "task_edit")]
    /**
     * Editing a task
     *
     * @param Task $task
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param TagAwareCacheInterface $cache
     * @return Response
     */
    public function edit(Task $task, Request $request, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): Response
    {
        $this->denyAccessUnlessGranted('edit', $task, "Vous ne pouvez pas éditer cette tâche");
        $form = $this->createForm(TaskForm::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $entityManager->flush();
            $cache->invalidateTags(['task_list']);
            $this->addFlash('success', 'La tâche a bien été modifiée.');
            return $this->redirectToRoute('task_list');
        }

        return $this->render(
            'task/edit.html.twig',
            [
            'form' => $form,
            'task' => $task,
            ]
        );

    }


    #[Route("/tasks/{id}/toggle", name: "task_toggle")]
    /**
     * Toggle the task state
     *
     * @param Task $task
     * @param EntityManagerInterface $entityManager
     * @param TagAwareCacheInterface $cache
     * @return RedirectResponse
     */
    public function toggle(Task $task, EntityManagerInterface $entityManager, Request $request, TagAwareCacheInterface $cache): RedirectResponse
    {
        $this->denyAccessUnlessGranted('toggle', $task, "Seul le créateur de la tâche peut en changer son état");
        $submittedToken = $request->request->get('token');
        $cache->invalidateTags(['task_list']);
        if ($this->isCsrfTokenValid('toggle-state', $submittedToken) === true) {
            $task->toggle(!$task->isDone());
            $entityManager->flush();
            $message = $task->isDone() === true ? "terminée" : "en cours";
            $this->addFlash('success', sprintf('La tâche %s a bien été marquée %s.', $task->getTitle(), $message));
            return $this->redirectToRoute('task_list');
        }

        $this->addFlash('error', "Vous n'êtes pas autorisé à modifier cette tâche");
        return $this->redirectToRoute('task_list');

    }


    #[Route("/tasks/{id}/delete", name: "task_delete")]
    /**
     * Delete a task
     *
     * @param Task $task
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param TagAwareCacheInterface $cache
     * @return RedirectResponse
     */
    public function deleteTask(Task $task, EntityManagerInterface $entityManager, Request $request, TagAwareCacheInterface $cache): RedirectResponse
    {
        $this->denyAccessUnlessGranted('delete', $task, "Vous ne pouvez pas supprimer la tâche d'un autre utilisateur");
        $submittedToken = $request->request->get('token');
        $cache->invalidateTags(['task_list']);
        if ($this->isCsrfTokenValid('delete-task', $submittedToken) === true) {
            $entityManager->remove($task);
            $entityManager->flush();
            $this->addFlash('success', 'La tâche a bien été supprimée.');
            return $this->redirectToRoute('task_list');
        }

        $this->addFlash('error', "Vous n'êtes pas autorisé à supprimer cette tâche");
        return $this->redirectToRoute('task_list');

    }


}
