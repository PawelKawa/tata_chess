<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected ?array $galleryPaths = null;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['gallery'] = $this->record->images()
            ->orderBy('order')
            ->pluck('path')
            ->toArray();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->galleryPaths = $data['gallery'] ?? [];
        unset($data['gallery']);
        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->images()->delete();
        foreach (array_values($this->galleryPaths ?? []) as $order => $path) {
            $this->record->images()->create(['path' => $path, 'order' => $order]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
