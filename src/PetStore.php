<?php

namespace Bebs\Petstore;

use Bebs\Petstore\Models\Pet;

interface PetStore
{
    /**
     * Add pet
     *
     * @param string $name
     * @param string $status
     * @param array|null $photoUrls
     * @param string|null $category
     * @param array|null $tags
     *
     * @return Pet
     */
    public function add(
        string $name,
        string $status,
        ?array $photoUrls = null,
        ?string $category = null,
        ?array $tags = null
    ): Pet;
}
