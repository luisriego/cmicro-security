<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DoctrineUserRepository;
use App\Trait\IdentifierTrait;
use App\Trait\IsActiveTrait;
use App\Trait\TimestampableTrait;
use App\Trait\WhoTrait;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

use function array_unique;
use function sha1;
use function uniqid;

#[ORM\Entity(repositoryClass: DoctrineUserRepository::class)]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use IdentifierTrait;
    use TimestampableTrait;
    use IsActiveTrait;
    use WhoTrait;

    public const NAME_MIN_LENGTH = 2;
    public const NAME_MAX_LENGTH = 80;

    #[ORM\Column(type: 'string', length: 80)]
    private ?string $name;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private readonly ?string $email;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string', length: 40, nullable: true)]
    private ?string $token;

    #[ORM\Column(type: 'string', length: 255, options: [
        'comment' => 'The hashed password',
    ])]
    private ?string $password;


    private function __construct(
        ?string $name,
        ?string $email,
        ?string $password,
        ?string $token,
    ) {
        $this->id = Uuid::v4()->toRfc4122();
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->token = $token;
        $this->isActive = false;
        $this->createdOn = new DateTimeImmutable();
        $this->markAsUpdated();
    }

    public static function create($name, $email, $password): self
    {
        return new User(
            $name,
            $email,
            $password,
            sha1(uniqid()),
        );
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getEmail(): ?string
    {
        return $this->email;
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
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
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

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    public function equals(User $user): bool
    {
        return $this->getId() === $user->getId();
    }
}
