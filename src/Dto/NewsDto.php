<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class NewsDto
{
    #[Assert\NotBlank(message: "El título es obligatorio")]
    #[Assert\Length(min: 5, minMessage: "El título debe tener al menos 5 caracteres")]
    public ?string $title = null;

    #[Assert\NotBlank(message: "La URL es obligatoria")]
    #[Assert\Url(message: "La URL no es válida")]
    public ?string $url = null;

    public ?string $description = null;

    #[Assert\NotBlank]
    public string $source = 'Manual';
}
