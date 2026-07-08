<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $modelLabel       = 'Post';
    protected static ?string $pluralModelLabel = 'Posty';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label('Tytuł')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(
                    fn(string $operation, $state, Forms\Set $set) =>
                        $operation === 'create'
                            ? $set('slug', Str::slug($state))
                            : null
                ),

            Forms\Components\TextInput::make('slug')
                ->label('Slug (adres URL posta)')
                ->required()
                ->maxLength(255)
                ->unique(Post::class, 'slug', ignoreRecord: true),

            Forms\Components\FileUpload::make('cover_image')
                ->label('Zdjęcie główne')
                ->image()
                ->disk('r2')
                ->directory('covers')
                ->nullable(),

            TiptapEditor::make('content')
                ->label('Treść')
                ->tools([
                    'heading', 'hr', 'bullet-list', 'ordered-list',
                    'bold', 'italic', 'underline',
                    'link', 'table', 'media',
                ])
                ->nullable()
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('gallery')
                ->label('Galeria zdjęć')
                ->image()
                ->multiple()
                ->disk('r2')
                ->directory('galleries')
                ->reorderable()
                ->nullable()
                ->columnSpanFull(),

            Forms\Components\DateTimePicker::make('published_at')
                ->label('Data publikacji')
                ->nullable()
                ->helperText('Zostaw puste aby zapisać jako szkic'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => $state ? 'Opublikowany' : 'Szkic')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Data')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit'   => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
