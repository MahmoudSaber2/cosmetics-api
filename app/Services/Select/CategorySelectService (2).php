<?php

namespace App\Services\Select;

use App\Enums\StatusEnum;
use App\Models\Category;

class CategorySelectService
{
    public function getAllActiveCategories()
    {
        return Category::where('status', StatusEnum::ACTIVE)->get(['id as value', 'name as label']);
    }
}
