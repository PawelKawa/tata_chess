<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected ?array $galleryPaths = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->galleryPaths = $data['gallery'] ?? [];
        unset($data['gallery']);
        return $data;
    }

    protected function afterCreate(): void
    {
        foreach (array_values($this->galleryPaths ?? []) as $order => $path) {
            $this->record->images()->create(['path' => $path, 'order' => $order]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
