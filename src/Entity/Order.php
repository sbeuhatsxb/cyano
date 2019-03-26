<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 */
class Order
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderLine", mappedBy="command")
     */
    private $orderLine;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orders")
     */
    private $linkedUser;

    public function __construct()
    {
        $this->orderLine = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|OrderLine[]
     */
    public function getOrderLine(): Collection
    {
        return $this->orderLine;
    }

    public function addOrderLine(OrderLine $orderLine): self
    {
        if (!$this->orderLine->contains($orderLine)) {
            $this->orderLine[] = $orderLine;
            $orderLine->setCommand($this);
        }

        return $this;
    }

    public function removeOrderLine(OrderLine $orderLine): self
    {
        if ($this->orderLine->contains($orderLine)) {
            $this->orderLine->removeElement($orderLine);
            // set the owning side to null (unless already changed)
            if ($orderLine->getCommand() === $this) {
                $orderLine->setCommand(null);
            }
        }

        return $this;
    }

    public function getLinkedUser(): ?User
    {
        return $this->linkedUser;
    }

    public function setLinkedUser(?User $linkedUser): self
    {
        $this->linkedUser = $linkedUser;

        return $this;
    }
}
