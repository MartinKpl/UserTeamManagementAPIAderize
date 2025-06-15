<?php

namespace App\Entity;

use App\Repository\TeamsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: TeamsRepository::class)]
class Teams implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, UsersTeams>
     */
    #[ORM\ManyToMany(targetEntity: UsersTeams::class, mappedBy: 'team_id')]
    private Collection $teamsUsers;

    public function __construct()
    {
        $this->teamsUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'    => $this->getId(),
            'name'  => $this->getName(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'teamsUsers' => $this->getTeamsUsers()->toArray()
        ];
    }

    /**
     * @return Collection<int, UsersTeams>
     */
    public function getTeamsUsers(): Collection
    {
        return $this->teamsUsers;
    }

    public function addTeamsUser(UsersTeams $teamsUser): static
    {
        if (!$this->teamsUsers->contains($teamsUser)) {
            $this->teamsUsers->add($teamsUser);
            $teamsUser->addTeamId($this);
        }

        return $this;
    }

    public function removeTeamsUser(UsersTeams $teamsUser): static
    {
        if ($this->teamsUsers->removeElement($teamsUser)) {
            $teamsUser->removeTeamId($this);
        }

        return $this;
    }
}
