<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'news')]
class News
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    private string $title;

    #[MongoDB\Field(type: 'string')]
    #[MongoDB\UniqueIndex]
    private string $url;

    #[MongoDB\Field(type: 'string', nullable: true)]
    private ?string $description = null;

    #[MongoDB\Field(type: 'date')]
    private \DateTimeInterface $publishedAt;

    #[MongoDB\Field(type: 'string')]
    private string $source;

    public function __construct(string $title, string $url, string $source, \DateTimeInterface $date = null)
    {
        $this->title = $title;
        $this->url = $url;
        $this->source = $source;
        $this->publishedAt = $date ?? new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPublishedAt(): \DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function getSource(): string
    {
        return $this->source;
    }
}
