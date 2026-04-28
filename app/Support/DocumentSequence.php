<?php

namespace App\Support;

use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentSequence
{
    public static function next(string $modelClass, bool $lock = true): int
    {
        $query = in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)
            ? $modelClass::withTrashed()
            : $modelClass::query();

        if ($lock) {
            $query->lockForUpdate();
        }

        return ((int) $query->orderByDesc('sequence')->value('sequence')) + 1;
    }
}
