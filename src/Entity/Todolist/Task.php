<?php

namespace App\Entity\Todolist;

use App\Entity\User;
use App\Repository\TaskRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?DateTimeInterface $date_de_rendu = null;

    /**
     * @ORM\ManyToOne(targetEntity=Matiere::class, inversedBy="tasks")
     */
    private ?Matiere $matiere = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tasks")
     */
    private UserInterface $user;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    protected bool $done;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDateDeRendu(): ?DateTimeInterface
    {
        return $this->date_de_rendu;
    }

    public function setDateDeRendu(?DateTimeInterface $date_de_rendu): self
    {
        $this->date_de_rendu = $date_de_rendu;

        return $this;
    }

    public function getMatiere(): ?Matiere
    {
        return $this->matiere;
    }

    public function setMatiere(?Matiere $matiere): self
    {
        $this->matiere = $matiere;

        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isDone(): bool
    {
        return $this->done;
    }

    public function setDone(bool $done): self
    {
        $this->done = $done;

        return $this;
    }
}
