<?php

namespace App\Enums;

enum FeedStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
