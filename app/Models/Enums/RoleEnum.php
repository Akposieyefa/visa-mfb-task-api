<?php

namespace App\Models\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';
}