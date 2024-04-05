<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class RouteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {

        $added = false;
        if (Auth::check()) {
            $added = $this->wishlist()->where('user_id', Auth::id())->exists();
        }

        return array_merge(parent::toArray($request), [
            'added' => $added,
        ]);
    }
}
