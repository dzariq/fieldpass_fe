<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequest;
use App\Models\Admin;
use App\Models\Association;
use App\Models\Club;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;


class AjaxController extends Controller
{
    public function getClubsByCompetition($competition_id)
    {
        $clubs = Club::whereHas('competitions', function ($query) use ($competition_id) {
            $query->where('competition_id', $competition_id);
        })->get();

        return json_encode(
            $clubs
        );
    }
}
