<?php

namespace App\Dtos;
use Spatie\Data\Data;

class AllProductResponseDTO
{
    public string $id;
    public string $name;
    public string $price;
    public string $img;

    public function __construct(string $id, string $name, string $price, string $img)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->img = $img;
    }


    // Getter và Setter có thể được thêm vào nếu cần
    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getImg(): string
    {
        return $this->img;
    }

    public function setImg(string $img): self
    {
        $this->img = $img;
        return $this;
    }
}
