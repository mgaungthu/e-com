<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case Addition = 'addition';

    case Subtraction = 'subtraction';

    case Set = 'set';
}