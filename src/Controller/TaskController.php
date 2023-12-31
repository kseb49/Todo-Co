<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Form\TaskForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{


    #[Route("/tasks", name: "task_list")]
    public function listAction(TaskRepository $task)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $tasks = $task->findAll();
        return $this->render
        (
            'task/list.html.twig',
            ['tasks' => $tasks],
        );

    }


    #[Route("/tasks/create", name: "task_create")]
    public function createAction(Request $request, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('create');
        $task = new Task();
        $form = $this->createForm(TaskForm::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $task->setUser($this->getUser());
            $em->persist($task);
            $em->flush();
            $this->addFlash('success', 'La tâche a été bien été ajoutée.');
            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form]);
    }


    #[Route("/tasks/{id}/edit", name: "task_edit")]
    public function editAction(Task $task, Request $request, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('edit', $task, "Vous ne pouvez pas éditer cette tâche");
        $form = $this->createForm(TaskForm::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $em->flush();
            $this->addFlash('success', 'La tâche a bien été modifiée.');
            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form,
            'task' => $task,
        ]);

    }


    #[Route("/tasks/{id}/toggle", name: "task_toggle")]
    public function toggleTaskAction(Task $task, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('toggle', $task, "Seul le créateur de la tâche peut en changer son état");
        $task->toggle(!$task->isDone());
        $em->flush();
        $message = $task->isDone() === true ? "terminée" : "en cours";
        $this->addFlash('success', sprintf('La tâche %s a bien été marquée %s.', $task->getTitle(), $message));
        return $this->redirectToRoute('task_list');

    }


    #[Route("/tasks/{id}/delete", name: "task_delete")]
    public function deleteTaskAction(Task $task, EntityManagerInterface $em, Request $request)
    {
        $this->denyAccessUnlessGranted('delete', $task, "Vous ne pouvez pas supprimer la tâche d'un autre utilisateur");
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-task', $submittedToken)) {
            $em->remove($task);
            $em->flush();
            $this->addFlash('success', 'La tâche a bien été supprimée.');
            return $this->redirectToRoute('task_list');
        }
        $this->addFlash('error', "Vous n'êtes pas autorisé à supprimer cette tâche");
        return $this->redirectToRoute('task_list');

    }

}
