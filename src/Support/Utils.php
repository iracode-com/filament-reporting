<?php

namespace IracodeCom\FilamentReporting\Support;

use Filament\Facades\Filament;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;

class Utils
{
    public static function translate($key): string
    {
        return __(
            str($key)
                ->headline()
                ->lower()
                ->ucfirst()
                ->before('.')
                ->value()
        );
    }

    public static function getModelColumns($model): array
    {
        $modelObject = app($model);
        return $modelObject->getConnection()->getSchemaBuilder()->getColumns($modelObject->getTable());
    }

    public static function getModelFillables($model): array
    {
        return app($model)->getFillable();
    }

    public static function getModelRelationships($model): array
    {
        $reflector     = new ReflectionClass($model);
        $relationships = collect($reflector->getMethods())
            ->reject(fn($method) => empty($method->getReturnType()))
            ->reject(fn($method) => ! in_array(class_basename($method->getReturnType()->getName()), ['HasOne', 'HasMany', 'BelongsTo', 'BelongsToMany', 'MorphToMany', 'MorphMany', 'MorphTo']))
            ->pluck('name')
            ->toArray();

        $relations = [];
        foreach ($relationships as $relationship) {
            $record = app($model)::first()?->$relationship?->toArray();

            if (! $record) {
                continue;
            }

            $record = is_array(Arr::first($record))
                ? Arr::first($record)
                : $record;

            $relations[$relationship] = array_keys($record);
        }


        return [
            ...static::getResources()[$model],
            'relations' => $relations,
        ];
    }

    public static function getForeignKeys($model): array
    {
        $foreignKeys = Schema::getConnection()->getSchemaBuilder()->getForeignKeys(app($model)->getTable());
        return collect($foreignKeys)
            ->map(fn($column) => $column['columns'])
            ->flatten()
            ->toArray();
    }

    public static function getResources(): ?array
    {
        $resources = Filament::getResources();
        return collect($resources)
            ->mapWithKeys(function ($resource) {
                return [
                    $resource::getModel() => [
                        'model' => $resource::getModel(),
                        'fqcn'  => $resource,
                    ],
                ];
            })
            ->sortKeys()
            ->toArray();
    }
}