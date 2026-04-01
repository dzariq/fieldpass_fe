<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClubRequest;
use App\Models\Association;
use App\Models\Club;
use App\Models\Admin;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;


class ClubController extends Controller
{
    public function index(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['club.view']);

        //superadmin
        if (auth()->user()->can('association.create')) {
            return view('backend.pages.clubs.index', [
                'clubs' => Club::all(),
            ]);
        } elseif (auth()->user()->can('association.create')) {
            //association admin
            $admin_obj = Admin::find(auth()->user()->id);
            $associationIds = $admin_obj->associations->pluck('id')->toArray();

            return view('backend.pages.clubs.index', [
                'clubs' => Club::whereIn('association_id', $associationIds)->get(),
            ]);
        } elseif (auth()->user()->can('club.create')) {
            $admin_obj = Admin::find(auth()->user()->id);
            $association = $admin_obj->associations()->first();

            return view('backend.pages.clubs.index', [
                'clubs' => $association ? $association->clubs : collect(),  // safe access
            ]);
        } elseif (auth()->user()->can('club.edit')) {
            //is club admin
            $admin_obj = Admin::find(auth()->user()->id);
            $clubIds = $admin_obj->clubs()->pluck('club_id')->toArray();
            return view('backend.pages.clubs.index', [
                'clubs' => Club::whereIn('id', $clubIds)->get(),
            ]);
        }

        return view('backend.pages.clubs.index', [
            'clubs' => array(),
        ]);
    }

    public function create(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['club.create']);

        return view('backend.pages.clubs.create', [
            'associations' => Association::all(),
        ]);
    }

    public function store(ClubRequest $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['club.create']);

        $club = new Club();
        $club->name = $request->name;
        $club->long_name = $request->long_name;
        $club->association_id = $request->association_id;
        $club->save();

        session()->flash('success', __('Club has been created.'));
        return redirect()->route('admin.clubs.index');
    }


    public function edit(int $id): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['club.edit']);

        $club = Club::findOrFail($id);
        return view('backend.pages.clubs.edit', [
            'club' => $club,
            'associations' => Association::all(),
        ]);
    }

    public function update(ClubRequest $request, int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['club.edit']);

        $club = Club::findOrFail($id);
        $club->name = $request->name;
        $club->long_name = $request->long_name;
        $club->association_id = $request->association_id;

        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:1028' // 2MB = 2048 KB
            ]);
            $file = $request->file('avatar');

            $filename = time() . '_' . $file->getClientOriginalName();
            $destination = public_path('avatars');

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true); // create if doesn't exist
            }

            $file->move($destination, $filename);

            $club->avatar = 'avatars/' . $filename; // save relative URL
        }

         if ($request->hasFile('jersey')) {
            $request->validate([
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:1028' // 2MB = 2048 KB
            ]);
            $file = $request->file('jersey');

            $filename = time() . '_' . $file->getClientOriginalName();
            $destination = public_path('avatars');

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true); // create if doesn't exist
            }

            $file->move($destination, $filename);

            $club->jersey = 'avatars/' . $filename; // save relative URL
        }

        $club->save();

        session()->flash('success', 'Club has been updated.');
        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['club.delete']);

        $club = Club::findOrFail($id);
        $club->delete();
        session()->flash('success', 'Club has been deleted.');
        return back();
    }
}
