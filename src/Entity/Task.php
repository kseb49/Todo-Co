<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TaskRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id =null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private $createdAt;

    #[ORM\Column(length: 50, type: Types::TEXT)]
    #[Assert\NotBlank(message:"Vous devez saisir un titre.")]
    #[Assert\NotNull]
    #[Assert\Type('string')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: '{{ value }} est trop court. Le titre de la tâche ne peut pas être infèrieur à {{ limit }} caractères',
        maxMessage: '{{ value }} est trop long. Le titre de la tâche ne peut pas être supèrieur à {{ limit }} caractères'
    )]
    private $title;

    #[ORM\Column(length: 1000, type: Types::TEXT)]
    #[Assert\NotBlank(message:"Vous devez saisir du contenu.")]
    #[Assert\NotNull]
    #[Assert\Type('string')]
    #[Assert\Length(
        min: 5,
        max: 1000,
        minMessage: '{{ value }} est trop court. Le contenu de la tâche ne peut pas être infèrieur à {{ limit }} caractères',
        maxMessage: '{{ value }} est trop long. Le contenu de la tâche ne peut pas être supèrieur à {{ limit }} caractères'
    )]
    private $content;

    #[ORM\Column(type: Types::BOOLEAN)]
    private $isDone;

    #[ORM\ManyToOne(inversedBy: 'task')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new \Datetime();
        $this->isDone = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function isDone()
    {
        return $this->isDone;
    }

    public function toggle(bool $flag)
    {
        $this->isDone = $flag;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
