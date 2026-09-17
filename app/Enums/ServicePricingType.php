<?php

namespace App\Enums;

enum ServicePricingType: string
{
    case FIXED = 'fixed';
    case STARTING_FROM = 'starting_from';
    case VARIABLE = 'variable';
}
