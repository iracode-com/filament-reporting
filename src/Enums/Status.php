<?php

namespace IracodeCom\FilamentReporting\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Status: int implements HasLabel, HasColor, HasIcon
{
    case INACTIVE = 0;
    case ACTIVE   = 1;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INACTIVE => __('InActive'),
            self::ACTIVE   => __('Active'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INACTIVE => 'gray',
            self::ACTIVE   => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::INACTIVE => 'heroicon-o-x-circle',
            self::ACTIVE   => 'heroicon-o-check-circle',
        };
    }
}