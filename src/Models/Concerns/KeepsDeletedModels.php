<?php

namespace Spatie\DeletedModels\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Spatie\DeletedModels\Models\DeletedModel;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait KeepsDeletedModels
{
    protected bool $shouldKeep = true;

    public static function bootKeepsDeletedModels(): void
    {
        static::deleted(function (Model $model) {
            if (! $model->shouldKeep) {
                return;
            }

            /** @var class-string<DeletedModel> $deletedModelClass */
            $deletedModelClass = config('deleted-models.model');

            $deletedModelClass::create([
                'key' => $model->getKey(),
                'model' => $model->getMorphClass(),
                'values' => $model->prepareForKeeping(),
            ]);
        });
    }

    public function prepareForKeeping(): array
    {
        return $this->toArray();
    }

    public function deleteWithoutKeeping()
    {
        $this->shouldKeep = false;

        $this->delete();

        return tap($this->delete(), fn() => $this->shouldKeep = true);
    }
}
