<?php

namespace App\Entity;

use App\Repository\PrefectureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

#[ORM\Entity(repositoryClass: PrefectureRepository::class)]
class Prefecture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Region::class, inversedBy: 'prefectures')]
        #[ORM\JoinColumn(name: 'id_region', referencedColumnName: 'id', nullable: false)]
        #[Assert\NotBlank]
    private ?Region $region = null;

    #[ORM\OneToMany(mappedBy: 'prefecture', targetEntity: City::class)]
    private Collection $cities;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getCountry(): Country {
        return $this->region->getCountry();
    }

    /**
     * @return Collection<int, City>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): self
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
            $city->setPrefecture($this);
        }

        return $this;
    }

    public function removeCity(City $city): self
    {
        if ($this->cities->removeElement($city)) {
            // set the owning side to null (unless already changed)
            if ($city->getPrefecture() === $this) {
                $city->setPrefecture(null);
            }
        }

        return $this;
    }

    public function __toString(): string {
        return  $this->getName();
    }
}
