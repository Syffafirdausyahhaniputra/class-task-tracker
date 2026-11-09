<?php

namespace App\Http\Controllers;

use App\Http\Resources\SchoolClassResource;
use App\Models\SchoolClass;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClassController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return SchoolClassResource::collection(
            SchoolClass::query()->orderBy('name')->get()
        );
    }
}
