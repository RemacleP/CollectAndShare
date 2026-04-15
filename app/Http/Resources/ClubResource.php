<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            // On vérifie si le logo existe, sinon image par défaut
            'image_url' => $this->logo ? asset('storage/' . $this->logo) : asset('img/default-club.png'),
            'members' => $this->users_count ?? 0,
            // On adapte à ta table addresses polymorphique
            'location' => $this->address ? [
                'city' => $this->address->city,
                'postal_code' => $this->address->postal_code,
                'street' => $this->address->street,
                'number' => $this->address->number,
            ] : null,
        ];
    }
}
