<?php

namespace App\Service\Scraper;

interface NewsScraperInterface
{
    /**
     * Extrae las noticias de la fuente.
     * * @return array<int, array{title: string, url: string, description: ?string}>
     */
    public function scrape(): array;

    /**
     * Devuelve el nombre de la fuente.
     */
    public function getSource(): string;
}
