<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\Association;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssociationRequest;
use App\Models\Association;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;


class AssociationController extends Controller
{
    public function index(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['association.view']);

        return view('backend.pages.associations.index', [
            'associations' => Association::all(),
        ]);
    }

    public function create(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['association.create']);

        return view('backend.pages.associations.create', [
            'roles' => Role::all(),
        ]);
    }

    public function store(AssociationRequest $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['association.create']);

        $association = new Association();
        $association->name = $request->name;
        $association->save();

        session()->flash('success', __('Association has been created.'));
        return redirect()->route('admin.associations.index');
    }

    public function edit(int $id): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['association.edit']);

        $association = Association::findOrFail($id);
        return view('backend.pages.associations.edit', [
            'association' => $association,
        ]);
    }

    public function update(AssociationRequest $request, int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['association.edit']);

        $association = Association::findOrFail($id);
        $association->name = $request->name;

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
            $association->avatar = 'avatars/' . $filename; // save relative URL

        }
        $association->save();

        session()->flash('success', 'Association has been updated.');
        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['association.delete']);

        $association = Association::findOrFail($id);
        $association->delete();
        session()->flash('success', 'Association has been deleted.');
        return back();
    }
}
