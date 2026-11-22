<?php

namespace App\Entity;

use App\Repository\ShowcaseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShowcaseRepository::class)]
class Showcase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private bool $published = false;

    #[ORM\ManyToOne(inversedBy: 'showcases')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Member $creator = null;

    /**
     * @var Collection<int, Subwoofer>
     */
    #[ORM\ManyToMany(targetEntity: Subwoofer::class, inversedBy: 'showcases')]
    private Collection $subwoofers;

    public function __construct()
    {
        $this->subwoofers = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): static
    {
        $this->published = $published;

        return $this;
    }

    public function getCreator(): ?Member
    {
        return $this->creator;
    }

    public function setCreator(?Member $creator): static
    {
        $this->creator = $creator;

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
        }

        return $this;
    }

    public function removeSubwoofer(Subwoofer $subwoofer): static
    {
        $this->subwoofers->removeElement($subwoofer);

        return $this;
    }
}
