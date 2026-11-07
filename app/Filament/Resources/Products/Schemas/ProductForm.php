<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product')->columns(2)->columnSpanFull()->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                    TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('sku')->label('SKU')->required()->maxLength(64)->unique(ignoreRecord: true),
                    Select::make('category_id')->relationship('category', 'name')->searchable()->preload()->required(),
                    Select::make('supplier_id')->relationship('supplier', 'name')->searchable()->preload()
                        ->helperText('Internal only, never shown publicly.'),
                    Select::make('status')->options(ProductStatus::class)->default(ProductStatus::Draft->value)->required(),
                    Textarea::make('short_description')->rows(2)->maxLength(255)->columnSpanFull(),
                    RichEditor::make('description')->columnSpanFull(),
                ]),
                Section::make('Images')->columnSpanFull()->schema([
                    SpatieMediaLibraryFileUpload::make('images')
                        ->collection('images')
                        ->multiple()
                        ->reorderable()
                        ->image()
                        ->imageEditor()
                        ->maxFiles(8)
                        ->maxSize(5120)
                        ->conversion('thumb')
                        ->helperText('First image is the main one. WebP versions are generated automatically.'),
                ]),
                Section::make('Order terms')->columns(3)->columnSpanFull()->schema([
                    TextInput::make('moq')->label('MOQ')->numeric()->minValue(1)->default(1)->required(),
                    TextInput::make('moq_unit')->label('MOQ unit')->default('pieces')->required(),
                    TextInput::make('lead_time_min_days')->label('Lead time min (days)')->numeric()->minValue(0),
                    TextInput::make('lead_time_max_days')->label('Lead time max (days)')->numeric()->minValue(0)->gte('lead_time_min_days'),
                    TextInput::make('currency')->default('NGN')->maxLength(3)->required(),
                    TextInput::make('base_price')->label('Base price (per unit)')->numeric()->minValue(0)
                        ->helperText('Used when no price tiers are set.'),
                ]),
                Section::make('Price tiers (optional)')->columnSpanFull()->schema([
                    Repeater::make('priceTiers')
                        ->relationship()
                        ->columns(3)
                        ->defaultItems(0)
                        ->addActionLabel('Add tier')
                        ->schema([
                            TextInput::make('min_quantity')->label('From qty')->numeric()->minValue(1)->required(),
                            TextInput::make('max_quantity')->label('To qty')->numeric()->helperText('Leave empty for "and above"'),
                            TextInput::make('unit_price')->label('Unit price')->numeric()->minValue(0)->required(),
                        ]),
                ]),
                Section::make('Stock & visibility')->columns(3)->columnSpanFull()->schema([
                    TextInput::make('stock_quantity')->numeric()->helperText('Leave empty if made to order.'),
                    TextInput::make('low_stock_threshold')->numeric()->default(10)->required(),
                    Toggle::make('is_featured')->label('Featured on home page')->inline(false),
                    DateTimePicker::make('published_at')->helperText('Leave empty to publish immediately when status is Published.'),
                ]),
                Section::make('SEO')->collapsed()->columnSpanFull()->schema([
                    TextInput::make('meta_title')->maxLength(70),
                    Textarea::make('meta_description')->rows(2)->maxLength(160),
                ]),
            ]);
    }
}