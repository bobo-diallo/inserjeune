<?php

namespace App\Entity;

use App\Repository\SocialNetworkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'social_network')]
#[ORM\Entity(repositoryClass: SocialNetworkRepository::class)]
class SocialNetwork
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
    private ?int $id;

	#[ORM\ManyToOne(targetEntity: ValidSocialNetwork::class)]
    #[ORM\JoinColumn(name: 'id_valid_social_network', referencedColumnName: 'id')]
    private ValidSocialNetwork $validSocialNetwork;

	#[ORM\Column(name: 'url', type: 'string', length: 255)]
   private string $url;

    public function getId(): ?int {
        return $this->id;
    }

   public function getValidSocialNetwork(): ValidSocialNetwork {
      return $this->validSocialNetwork;
   }

   public function setValidSocialNetwork(ValidSocialNetwork $validSocialNetwork): void {
      $this->validSocialNetwork = $validSocialNetwork;
   }

   public function setUrl(string $url): static {
      $this->url = $url;

      return $this;
   }

   public function getUrl(): string {
      return $this->url;
   }
}
