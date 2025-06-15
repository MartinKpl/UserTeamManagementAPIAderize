<?php

namespace App\Entity;

use App\Repository\UsersTeamsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsersTeamsRepository::class)]
class UsersTeams
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Users>
     */
    #[ORM\ManyToMany(targetEntity: Users::class, inversedBy: 'userTeams')]
    private Collection $user_id;

    /**
     * @var Collection<int, Teams>
     */
    #[ORM\ManyToMany(targetEntity: Teams::class, inversedBy: 'teamsUsers')]
    private Collection $team_id;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    public function __construct()
    {
        $this->user_id = new ArrayCollection();
        $this->team_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Users>
     */
    public function getUserId(): Collection
    {
        return $this->user_id;
    }

    public function addUserId(Users $userId): static
    {
        if (!$this->user_id->contains($userId)) {
            $this->user_id->add($userId);
        }

        return $this;
    }

    public function removeUserId(Users $userId): static
    {
        $this->user_id->removeElement($userId);

        return $this;
    }

    /**
     * @return Collection<int, Teams>
     */
    public function getTeamId(): Collection
    {
        return $this->team_id;
    }

    public function addTeamId(Teams $teamId): static
    {
        if (!$this->team_id->contains($teamId)) {
            $this->team_id->add($teamId);
        }

        return $this;
    }

    public function removeTeamId(Teams $teamId): static
    {
        $this->team_id->removeElement($teamId);

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }
}
