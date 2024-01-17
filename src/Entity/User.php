<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity("email")]
#[UniqueEntity("username")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email(message:"Le format de l'adresse n'est pas correcte")]
    #[Assert\NotBlank(['message' => "Vous devez saisir une adresse email"])]
    #[Assert\NotNull]
    #[Assert\Length(
        min: 10,
        max: 180,
        minMessage: 'Votre adresse {{ value }}, est trop courte, min = {{ limit }} caractères',
        maxMessage: 'Votre adresse est trop longue',
    )]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Type('string')]
    #[Assert\NotBlank(['message' => 'Mots de passe vide'])]
    #[Assert\NotNull]
    #[Assert\Length(
        min: 6,
        minMessage: 'Votre mot de passe doit faire {{ limit }} caractères minimum'
    )]
    private ?string $password = null;

    #[ORM\Column(length: 25, unique: true)]
    #[Assert\NotBlank(message:"Vous devez saisir un nom d'utilisateur")]
    #[Assert\NotNull]
    #[Assert\Type('string')]
    #[Assert\Length(
        min: 3,
        max: 25,
        minMessage: '{{ value }} est trop court. Votre pseudo doit faire {{ limit }} caractères minimum'
    )]
    private ?string $username = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Task::class)]
    private Collection $task;

    #[ORM\ManyToMany(targetEntity: Task::class, mappedBy: 'referer')]
    private Collection $mentionned;


    public function __construct()
    {
        $this->task = new ArrayCollection();
        $this->mentionned = new ArrayCollection();

    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }


    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }


    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }


    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Guarantee every user at least has ROLE_USER.
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }


    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }


    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }


    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }


    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here.
        // $this->plainPassword = null;.
    }


    public function getUsername(): ?string
    {
        return $this->username;
    }


    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }


    /**
     * @return Collection<int, task>
     */
    public function gettask(): Collection
    {
        return $this->task;
    }


    public function addTask(task $task): static
    {
        if ($this->task->contains($task) === false) {
            $this->task->add($task);
            $task->setUser($this);
        }

        return $this;
    }


    public function removeTask(task $task): static
    {
        if ($this->task->removeElement($task) === true) {
            // Set the owning side to null (unless already changed).
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Task>
     */
    public function getMentionned(): Collection
    {
        return $this->mentionned;
    }


    public function addMentionned(Task $mentionned): static
    {
        if (!$this->mentionned->contains($mentionned)) {
            $this->mentionned->add($mentionned);
            $mentionned->addReferer($this);
        }

        return $this;
    }


    public function removeMentionned(Task $mentionned): static
    {
        if ($this->mentionned->removeElement($mentionned)) {
            $mentionned->removeReferer($this);
        }

        return $this;
    }


}
