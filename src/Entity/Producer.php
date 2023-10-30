<?php

namespace App\Entity;

use App\Repository\ProducerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProducerRepository::class)]
class Producer
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Assert\Uuid]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['alcohol'])]
    #[Assert\NotNull]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['alcohol'])]
    #[Assert\NotNull]
    private ?string $country = null;

    #[ORM\OneToMany(mappedBy: 'producer', targetEntity: Alcohol::class)]
    private Collection $alcohols;

    public function __construct()
    {
        $this->alcohols = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection<int, Alcohol>
     */
    public function getAlcohols(): Collection
    {
        return $this->alcohols;
    }

    public function addAlcohol(Alcohol $alcohol): self
    {
        if (!$this->alcohols->contains($alcohol)) {
            $this->alcohols->add($alcohol);
            $alcohol->setProducer($this);
        }

        return $this;
    }

    public function removeAlcohol(Alcohol $alcohol): self
    {
        if ($this->alcohols->removeElement($alcohol)) {
            // set the owning side to null (unless already changed)
            if ($alcohol->getProducer() === $this) {
                $alcohol->setProducer(null);
            }
        }

        return $this;
    }
}
