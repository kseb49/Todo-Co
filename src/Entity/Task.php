<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt;

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
    private ?string $title;

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
    private ?string $content;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isDone;

    #[ORM\ManyToOne(inversedBy: 'task')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'mentionned')]
    private Collection $referer;


    public function __construct()
    {
        $this->createdAt = new \Datetime();
        $this->isDone = false;
        $this->referer = new ArrayCollection();

    }


    public function getId(): ?int
    {
        return $this->id;

    }


    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTimeInterface $createdAt= new DateTime()): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }


    public function getTitle(): ?string
    {
        return $this->title;
    }


    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }


    public function getContent(): ?string
    {
        return $this->content;
    }


    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }


    public function isDone(): ?bool
    {
        return $this->isDone;
    }


    public function toggle(bool $flag): static
    {
        $this->isDone = $flag;
        return $this;
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


    /**
     * @return Collection<int, User>
     */
    public function getReferer(): Collection
    {
        return $this->referer;
    }


    public function addReferer(User $referer): static
    {
        if (!$this->referer->contains($referer)) {
            $this->referer->add($referer);
        }

        return $this;
    }


    public function removeReferer(User $referer): static
    {
        $this->referer->removeElement($referer);

        return $this;
    }


}
