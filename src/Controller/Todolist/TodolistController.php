<?php

namespace App\Controller\Todolist;

use App\Entity\Todolist\Task;
use App\Manager\MatiereManager;
use App\Manager\TaskManager;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;

class TodolistController extends AbstractController
{
    #[Route('/todolist/notconnected', name: 'todolist_not_connected', host: 'home.discovernewmusic.fr')]
    public function notConnected(Security $security)
    {
        if ($security->isGranted('ROLE_SPOTIFY')) {
            return $this->redirectToRoute('displayTodolist');
        }
        return $this->render('todolist/not_connected.html.twig');
    }

    #[Route('/todolist', name: 'displayTodolist', host: 'home.discovernewmusic.fr')]
    public function displayTodoList(Security $security, TaskManager $taskManager): Response
    {
        if (!$security->isGranted('ROLE_SPOTIFY')) {
            return $this->redirectToRoute('todolist_not_connected');
        }

        return $this->render('todolist/display_task.html.twig', [
            'tasks' => $taskManager->findForDisplay($this->getUser())
        ]);
    }

    #[Route('/addtask', name: 'addTask', host: 'home.discovernewmusic.fr')]
    public function addTask(Security $security, Request $request, MatiereManager $matiereManager, TaskManager $taskManager): Response
    {
        if (!$security->isGranted('ROLE_SPOTIFY')) {
            return $this->redirectToRoute('todolist_not_connected');
        }

        if ($request->isMethod('post')) {
            $taskManager->addTask(
                $this->getUser(),
                $request->get('name'),
                $matiereManager->find($request->get('matiere')),
                new DateTime($request->get('date_de_rendu'))
            );
        }

        return $this->render('todolist/add_task.html.twig', [
            'matieres' => $matiereManager->findBy(['user' => $this->getUser()], ['name' => 'ASC']),
        ]);
    }

    #[Route('/setTaskDone/{id}', name: 'setTaskDone', host: 'home.discovernewmusic.fr')]
    public function setTaskDone(Task $task, TaskManager $taskManager): JsonResponse
    {
        $task->setDone(true);
        $taskManager->save($task);
        return $this->json('ok');
    }

    #[Route('/setTaskUnDone/{id}', name: 'setTaskUnDone', host: 'home.discovernewmusic.fr')]
    public function setTaskUnDone(Task $task, TaskManager $taskManager): JsonResponse
    {
        $task->setDone(false);
        $taskManager->save($task);
        return $this->json('ok');
    }
}
