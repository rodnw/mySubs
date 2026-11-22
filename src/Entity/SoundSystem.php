<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\SoundSystemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SoundSystemRepository::class)]
#[ApiResource(security: "is_granted('ROLE_ADMIN')", stateless: false)]
class SoundSystem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Subwoofer>
     */
    #[ORM\OneToMany(targetEntity: Subwoofer::class, mappedBy: 'soundSystem', cascade: ['persist'])]
    private Collection $subwoofers;

    #[ORM\OneToOne(mappedBy: 'soundSystem')]
    #[ApiProperty(readable: false, writable: false)]
    private ?Member $member = null;

    public function __construct()
    {
        $this->subwoofers = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Subwoofer>
     */
    public function getSubwoofers(): Collection
    {
        return $this->subwoofers;
    }

    public function addSubwoofer(Subwoofer $subwoofer): static
    {
        if (!$this->subwoofers->contains($subwoofer)) {
            $this->subwoofers->add($subwoofer);
            $subwoofer->setSoundSystem($this);
        }

        return $this;
    }

    public function removeSubwoofer(Subwoofer $subwoofer): static
    {
        if ($this->subwoofers->removeElement($subwoofer)) {
            // set the owning side to null (unless already changed)
            if ($subwoofer->getSoundSystem() === $this) {
                $subwoofer->setSoundSystem(null);
            }
        }

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }
}
