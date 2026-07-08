<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        if (app()->isLocal()) {
            $this->form->fill([
                'email'    => env('FILAMENT_DEV_EMAIL', 'admin@chess.local'),
                'password' => env('FILAMENT_DEV_PASSWORD', 'password123'),
            ]);
        }
    }
}
