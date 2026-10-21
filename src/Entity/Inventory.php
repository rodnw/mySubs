<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
#[ApiResource]
class Inventory
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
    #[ORM\OneToMany(targetEntity: Subwoofer::class, mappedBy: 'inventory')]
    private Collection $subwoofers;

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
            $subwoofer->setInventory($this);
        }

        return $this;
    }

    public function removeSubwoofer(Subwoofer $subwoofer): static
    {
        if ($this->subwoofers->removeElement($subwoofer)) {
            // set the owning side to null (unless already changed)
            if ($subwoofer->getInventory() === $this) {
                $subwoofer->setInventory(null);
            }
        }

        return $this;
    }
}
