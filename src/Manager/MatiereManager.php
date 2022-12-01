<?php

namespace App\Manager;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Todolist\Matiere;
use http\Exception\BadUrlException;
use LogicException;
use Symfony\Component\Security\Core\User\UserInterface;

class MatiereManager extends AbstractManager
{
    /**
     * FacturationDetailsManager constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityClassName = Matiere::class;
        parent::__construct($entityManager);
    }

    public function findForDisplay(UserInterface $user): array
    {
        $matieresForDisplay = [];
        $matieres = $this->findBy(['user' => $user], ['name' => 'ASC']);
        foreach ($matieres as $matiere) {
            $matieresForDisplay[] = [
                'matiere' => $matiere,
                'canBeDeleted' => $this->canBeDeleted($matiere),
            ];
        }
        return $matieresForDisplay;
    }

    public function canBeDeleted(Matiere $matiere): bool
    {
        foreach ($matiere->getTask() as $task) {
            if (!$task->isDone() && $task->getDateDeRendu() >= (new DateTime())) {
                return false;
            }
        }

        return true;
    }

    public function add(UserInterface $user, string $name, string $couleur): Matiere
    {
        $matiere = (new Matiere())
            ->setUser($user)
            ->setName($name)
            ->setCouleur($couleur);
        $this->save($matiere);
        return $matiere;
    }

    public function edit(Matiere $matiere, UserInterface $user, string $name, string $couleur): Matiere
    {
        if ($matiere->getUser() !== $user) {
            throw new LogicException(sprintf(
                "Impossible d'éditer une matière de quelqu'un d'autre. User : %s Matiere : %s",
                $user->getUserIdentifier(), $matiere->getUser()->getUserIdentifier()
            ));
        }

        $matiere->setName($name)->setCouleur($couleur);
        $this->save($matiere);
        return $matiere;
    }

    public function deleteMatiere(Matiere $matiere, UserInterface $user): void
    {
        if ($matiere->getUser() !== $user) {
            throw new LogicException(sprintf(
                "Impossible de supprimer une matière de quelqu'un d'autre. User : %s: Matiere : %s",
                $user->getUserIdentifier(), $matiere->getUser()->getUserIdentifier()
            ));
        }
        $this->delete($matiere);
    }
}
