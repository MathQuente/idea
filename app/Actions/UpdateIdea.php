<?php

namespace App\Actions;

use App\Models\Idea;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateIdea
{
  public function handle(array $attributes, Idea $idea)
  {

    $data = collect($attributes)->only([
      'title',
      'description',
      'status',
      'links'
    ])->toArray();

    if ($attributes['image'] ?? false) {
      if ($idea->image_path) {
        Storage::disk('public')->delete($idea->image_path);
      }

      $data['image_path'] = $attributes['image']->store('ideas', 'public');
    }

    DB::transaction(function () use ($idea, $data, $attributes) {
      $idea->update($data);

      $idea->steps()->delete();

      $idea->steps()->createMany($attributes['steps'] ?? []);
    });
  }
}
