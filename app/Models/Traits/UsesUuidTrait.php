<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait UsesUuidTrait
{
    protected static function bootUsesUuidTrait()  {
        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
