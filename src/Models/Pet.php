<?php

namespace Bebs\Petstore\Models;

class Pet
{
    public function __construct(
        public readonly int $id,
        public string $name,
        public string $status,
        public array|null $category = null,
        public array|null $photoUrls = null,
        public array|null $tags = null,
    ) {
    }
}
