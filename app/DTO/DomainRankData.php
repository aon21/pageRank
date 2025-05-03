<?php

namespace App\DTO;

readonly class DomainRankData
{
    public function __construct(
        public string $domain,
        public int    $rank,
    ) {}
}
