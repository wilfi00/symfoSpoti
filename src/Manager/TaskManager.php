<?php

namespace App\Manager;

use App\Entity\Todolist\Matiere;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Todolist\Task;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskManager extends AbstractManager
{
    /**
     * FacturationDetailsManager constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityClassName = Task::class;
        parent::__construct($entityManager);
    }

    public function addTask(UserInterface $user, string $name, Matiere $matiere, ?DateTime $dateDeRendu): Task
    {
        $task = (new Task())
            ->setUser($user)
            ->setName($name)
            ->setMatiere($matiere)
            ->setDateDeRendu($dateDeRendu)
            ->setDone(false)
        ;
        $this->save($task);
        return $task;
    }

    public function findForDisplay(UserInterface $user): array
    {
        return $this->getRepository()->createQueryBuilder('task')
            ->where('task.user = :user')
            ->andWhere('task.done = :notDone OR task.done = :done and task.date_de_rendu >= :today')
            ->setParameter('user', $user)
            ->setParameter('notDone', false)
            ->setParameter('done', true)
            ->setParameter('today', new DateTime())
            ->orderBy('task.date_de_rendu')->getQuery()->getResult();
    }
}
