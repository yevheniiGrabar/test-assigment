<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int id
 * @property string name
 * @property string email
 * @property string phone
 * @property Carbon created_at
 * @property string photo
 */
class UserResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => new PositionResource($this->whenLoaded('position')),
            'created_at' => $this->created_at,
            'photo' => $this->photo
        ];
    }
}
