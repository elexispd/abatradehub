<?php

namespace App\Filament\Resources\Enquiries\Schemas;

use App\Enums\EnquiryStatus;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EnquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')->columns(2)->columnSpanFull()->schema([
                    TextInput::make('reference')->disabled()->dehydrated(false)->placeholder('Generated on save'),
                    Select::make('status')->options(EnquiryStatus::class)->default(EnquiryStatus::New->value)->required(),
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('phone')->tel()->required(),
                    TextInput::make('email')->email(),
                    TextInput::make('company'),
                    TextInput::make('country'),
                    TextInput::make('city'),
                    Select::make('source')->options(['form' => 'Website form', 'whatsapp' => 'WhatsApp'])->default('form')->required(),
                    DateTimePicker::make('contacted_at'),
                    Textarea::make('message')->rows(3)->columnSpanFull(),
                ]),
                Section::make('Items')->columnSpanFull()->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->columns(4)
                        ->defaultItems(0)
                        ->addActionLabel('Add item')
                        ->schema([
                            Select::make('product_id')
                                ->label('Product')
                                ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function (Set $set, $state) {
                                    if ($product = Product::find($state)) {
                                        $set('product_name', $product->name);
                                        $set('product_sku', $product->sku);
                                        $set('quantity', $product->moq);
                                    }
                                })
                                ->columnSpan(2),
                            TextInput::make('quantity')->numeric()->minValue(1)->required(),
                            TextInput::make('product_sku')->label('SKU'),
                            TextInput::make('product_name')->label('Product name')->required()->columnSpan(2),
                            TextInput::make('note')->columnSpan(2),
                        ]),
                ]),
                Section::make('Internal')->columnSpanFull()->schema([
                    Textarea::make('admin_notes')->rows(3)->helperText('Only visible to staff.'),
                ]),
            ]);
    }
}