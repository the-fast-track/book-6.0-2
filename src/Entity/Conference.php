<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ConferenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\Slugger\SluggerInterface;

#[ORM\Entity(repositoryClass: ConferenceRepository::class)]
#[UniqueEntity('slug')]
#[ApiResource(
    collectionOperations: ['get' => ['normalization_context' => ['groups' => 'conference:list']]],
    itemOperations: ['get' => ['normalization_context' => ['groups' => 'conference:item']]],
    order: ['year' => 'DESC', 'city' => 'ASC'],
    paginationEnabled: false,
)]
class Conference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['conference:list', 'conference:item'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['conference:list', 'conference:item'])]
    private $city;

    #[ORM\Column(type: 'string', length: 4)]
    #[Groups(['conference:list', 'conference:item'])]
    private $year;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['conference:list', 'conference:item'])]
    private $isInternational;

    #[ORM\OneToMany(mappedBy: 'conference', targetEntity: Comment::class, orphanRemoval: true)]
    private $comments;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups(['conference:list', 'conference:item'])]
    private $slug;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->city.' '.$this->year;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function computeSlug(SluggerInterface $slugger)
    {
        if (!$this->slug || '-' === $this->slug) {
            $this->slug = (string) $slugger->slug((string) $this)->lower();
        }
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(string $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function isIsInternational(): ?bool
    {
        return $this->isInternational;
    }

    public function setIsInternational(bool $isInternational): self
    {
        $this->isInternational = $isInternational;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setConference($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getConference() === $this) {
                $comment->setConference(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
