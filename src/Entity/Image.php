<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
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
    private ?string $url = null;

    #[ORM\OneToOne(mappedBy: 'image', cascade: ['persist', 'remove'])]
    private ?Alcohol $alcohol = null;

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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getAlcohol(): ?Alcohol
    {
        return $this->alcohol;
    }

    public function setAlcohol(?Alcohol $alcohol): self
    {
        // unset the owning side of the relation if necessary
        if ($alcohol === null && $this->alcohol !== null) {
            $this->alcohol->setImage(null);
        }

        // set the owning side of the relation if necessary
        if ($alcohol !== null && $alcohol->getImage() !== $this) {
            $alcohol->setImage($this);
        }

        $this->alcohol = $alcohol;

        return $this;
    }
}
