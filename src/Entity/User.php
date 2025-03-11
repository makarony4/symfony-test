<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_login', columns: ['login'])]
#[UniqueEntity(fields: ['login'], message: 'This login is already taken.')]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    //вся валідація закоментована, так як неправильно ставити таке обмеження для всіх параметрів
    //також не ставив унікальний ідентифікатор для паролю, так як це також неправильно
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
//    #[Assert\Length(
//        max: 8,
//        maxMessage: "Id should be max 8 characters"
//    )]
    #[Groups(["user:put-output", "user:post-output"])]
    private ?int $id = null;

    #[ORM\Column(type: 'json')]
    #[Groups(["user:post", "user:put"])]
    private array $roles = [];

    #[ORM\Column(length: 255)]
//        #[Assert\Length(
//        max: 8,
//        maxMessage: "Phone number should be max 8 characters"
//    )]
    #[Groups(['user:post', 'user:put', "user:post-output", "user:get-output"])]
    #[Assert\NotBlank(message: 'Phone number is required.')]
    private ?string $phone = null;


    #[ORM\Column(name: 'login', length: 255, unique: true)]
    #[Groups(['user:post', 'user:put', "user:post-output", "user:get-output"])]
    #[Assert\NotBlank(message: 'Login is required.')]
    #[Assert\Length(
        max: 8,
        maxMessage: "The login must be no longer than 8 characters."
    )]
    private ?string $login = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Password is required.')]
//    #[Assert\Length(
//        max: 8,
//        maxMessage: "Password should be max 8 characters"
//    )]
    #[Groups(['user:post', 'user:put', "user:post-output", "user:get-output"])]
    private ?string $pass = null;

    public function getUserIdentifier(): string
    {
        return (string) $this->login;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->pass;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function setPass(string $pass): static
    {
        $this->pass = $pass;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
