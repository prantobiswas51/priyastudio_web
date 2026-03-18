<?php

namespace App\Filament\Resources\Photos\Pages;

use App\Filament\Resources\Photos\PhotoResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Filament\Resources\Pages\CreateRecord;

class CreatePhoto extends CreateRecord
{
    protected static string $resource = PhotoResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $paths = Arr::wrap($data['url'] ?? []);
        $storedFileNames = Arr::wrap($data['uploaded_file_names'] ?? []);
        $description = $data['description'] ?? null;
        $type = $data['type'] ?? null;

        $modelClass = $this->getModel();
        $firstRecord = null;

        foreach ($paths as $path) {
            if (! is_string($path) || blank($path)) {
                continue;
            }

            $originalFileName = $storedFileNames[$path] ?? basename($path);
            $rawName = pathinfo((string) $originalFileName, PATHINFO_FILENAME);
            $normalizedPath = ltrim((string) Str::of($path)
                ->replace('storage/app/public/', '')
                ->replace('public/', ''), '/');

            $record = $modelClass::query()->create([
                'name' => filled($rawName) ? $rawName : pathinfo(basename($path), PATHINFO_FILENAME),
                'url' => $normalizedPath,
                'description' => $description,
                'type' => $type,
            ]);

            $firstRecord ??= $record;
        }

        if ($firstRecord instanceof Model) {
            return $firstRecord;
        }

        return parent::handleRecordCreation($data);
    }
}
