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
use Illuminate\Validation\Rule;


class AdminsController extends Controller
{
    public function index(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['admin.view']);

        if (auth()->user()->can('association.create')) {
            return view('backend.pages.admins.index', [
                'admins' => Admin::all(),
            ]);
        } else {
            $admin_obj = Admin::find(auth()->user()->id);
            $associationObj = $admin_obj->associations()->first();
            $club_obj = $admin_obj->clubs()->first();

            if ($associationObj) {
                $admins = Admin::where(function ($query) use ($associationObj) {
                    $query->whereHas('associations', function ($subQuery) use ($associationObj) {
                        $subQuery->where('association.id', $associationObj->id);
                    })
                        ->orWhereHas('clubs');
                })
                    ->with(['associations', 'clubs'])
                    ->get();
            }

            if ($club_obj) {
                $admins = Admin::whereHas('clubs', function ($query) use ($club_obj) {
                    $query->where('club.id', $club_obj->id);
                })->get();
            }

            return view('backend.pages.admins.index', [
                'admins' => $admins,
            ]);
        }
    }

    public function create(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['admin.create']);

        $admin_obj = Admin::find(auth()->user()->id);

        if (auth()->user()->can('association.create')) {
            $associationIds = Association::all()->pluck('id')->toArray();
        } else {
            $associationIds = $admin_obj->associations->pluck('id')->toArray();
        }

        $is_club_admin = false;
        $associationObj = $admin_obj->associations()->first();
        $club_obj = $admin_obj->clubs()->first();

        if ($club_obj)
            $is_club_admin = true;

        $clubIds = $admin_obj->clubs()->pluck('club.id')->toArray();
        $myrole = $admin_obj->roles()->pluck('name')->toArray()[0];

        if ($myrole == 'Association Manager') {
            $roles = Role::whereIn('name', [
                'Club Manager',
                'Tournament Manager',
                'Match Data Specialist',
                'Referee',
                'Analyst'
            ])->get();
        } else if ($myrole == 'Club Manager') {
            $roles = Role::whereIn('name', [
                'Team Coach',
                'Performance Coach',
            ])->get();
        } else {
            $roles = Role::all();
        }

        $associations = Association::whereIn('id', $associationIds)->get();
        $clubs = Club::whereIn('id', $clubIds)->get();

        if (count($clubs) == 0) {
            if (count($associations) > 0)
                $clubs = Club::whereIn('association_id', $associationIds)->get();
            else
                $clubs = Club::all();
        }

        return view('backend.pages.admins.create', [
            'roles' => $roles,
            'associations' => $associations,
            'association_id' => $associationObj ? $associationObj->id : 0,
            'is_club_admin' => $is_club_admin,
            'clubs' => $clubs,
            'club_id' => $club_obj ? $club_obj->id : 0,
        ]);
    }

    public function store(AdminRequest $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['admin.create']);

        if ($request)

            $admin = new Admin();
        $admin->name = $request->name;
        $admin->username = $request->username;
        $admin->email = $request->has('email') ? $request->email : null;
        $admin->phone = $request->phone;
        $admin->country_code = $request->country_code;
        $admin->code = rand(11111111111, 99999999999);
        $admin->referer = auth()->user()->id;
        $admin->password = Hash::make($request->password);
        $admin->status = 'INVITED';
        $admin->save();

        if ($request->roles) {
            $admin->assignRole($request->roles);
        }

        if ($request->has('association_ids') && !empty($request->association_ids)) {
            $admin->associations()->syncWithoutDetaching($request->association_ids);

            $assoc = Association::find($request->association_ids[0]);

            $data = array(
                'action' => 'assoc_invitation',
                'code' => $admin->code,
                'username' => $admin->username,
                'domain' => env('APP_URL'),
                'email' => $admin->email,
                'phone' => $admin->phone,
                'association' => $assoc->name,
                'name' => $request->name
            );
            // $this->sendEmailInvitation($data);
            $this->sendWAInvitation($data);
        }

        if ($request->has('club_ids') && !empty($request->club_ids)) {
            $admin->clubs()->syncWithoutDetaching($request->club_ids);

            $admin_obj = Admin::find(auth()->user()->id);
            $club = Club::find($request->club_ids[0]);
            $associationObj = $admin_obj->associations()->first();

            $data = array(
                'action' => 'club_invitation',
                'code' => $admin->code,
                'email' => $admin->email,
                'domain' => env('APP_URL'),
                'username' => $admin->username,
                'phone' => $admin->phone,
                'country_code' => str_replace('+', '', $admin->country_code), // Remove + sign
                'club' => $club->name,
                'association' => $associationObj->name,
                'name' => $request->name
            );
            // $this->sendEmailInvitation($data);
            $this->sendWAInvitation($data);
        }



        session()->flash('success', __('Admin has been created.'));
        return redirect()->route('admin.admins.index');
    }

    public function reinvite($id)
    {
        $this->checkAuthorization(auth()->user(), ['admin.create', 'admin.edit']);

        $admin = Admin::findOrFail($id);

        // Check if admin is in INVITED status
        if ($admin->status !== 'INVITED') {
            session()->flash('error', 'Only invited admins can be re-invited.');
            return back();
        }

        // Check if admin has clubs or associations assigned
        $club = $admin->clubs()->first();
        $association = $admin->associations()->first();

        if (!$club && !$association) {
            session()->flash('error', 'Admin must be assigned to at least one club or association before sending invitation.');
            return back();
        }

        // Get club and association info
        $clubName = $club ? $club->name : 'N/A';
        $associationName = $association ? $association->name : 'N/A';

        $data = array(
            'action' => 'club_invitation',
            'code' => $admin->code,
            'email' => $admin->email,
            'domain' => env('APP_URL'),
            'username' => $admin->username,
            'phone' => $admin->phone,
            'country_code' => str_replace('+', '', $admin->country_code),
            'club' => $clubName,
            'association' => $associationName,
            'name' => $admin->name
        );

        // Only send if admin has phone number
        if ($admin->phone) {
            $this->sendWAInvitation($data);
            session()->flash('success', 'WhatsApp invitation has been resent to ' . $admin->name . ' (Club: ' . $clubName . ')');
        } else {
            session()->flash('error', 'Cannot send invitation. Admin does not have a phone number.');
        }

        return back();
    }

    function sendEmailInvitation($data)
    {
        $webhookUrl = 'https://n8n.fieldpass.com.my/webhook/email-invitation';

        // Initialize cURL
        $curl = curl_init();

        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => $webhookUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json'
            ),
        ));

        // Execute the request
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        // Close cURL
        curl_close($curl);

        // Handle response
        if ($error) {
            // Log error
            Log::error('Email invitation webhook error: ' . $error);
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode
            ];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'response' => $response,
                'http_code' => $httpCode
            ];
        } else {
            return [
                'success' => false,
                'error' => 'HTTP Error: ' . $httpCode,
                'response' => $response,
                'http_code' => $httpCode
            ];
        }
    }

    public function edit(int $id): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['admin.edit']);

        $admin_obj = Admin::find(auth()->user()->id);

        if (auth()->user()->can('association.create')) {
            $associationIds = Association::all()->pluck('id')->toArray();
        } else {
            $associationIds = $admin_obj->associations->pluck('id')->toArray();
        }

        $associations = Association::whereIn('id', $associationIds)->get();

        $admin = Admin::findOrFail($id);

        // Only list clubs within the editor's association scope (unless superadmin).
        $clubsQuery = Club::query();
        if (!auth()->user()->can('association.create')) {
            $clubsQuery->whereIn('association_id', $associationIds);
        }

        return view('backend.pages.admins.edit', [
            'admin' => $admin,
            'roles' => Role::all(),
            'associations' => $associations,
            'clubs' => $clubsQuery->orderBy('name')->get(),
        ]);
    }

    function sendWAInvitation($data)
    {
        $webhookUrl = 'https://n8n.fieldpass.com.my/webhook/admin-invitation';

        // Initialize cURL
        $curl = curl_init();

        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => $webhookUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json'
            ),
        ));

        // Execute the request
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        // Close cURL
        curl_close($curl);

        // Handle response
        if ($error) {
            // Log error
            Log::error('Email invitation webhook error: ' . $error);
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode
            ];
        }

        if ($httpCode >= 200 && $httpCode < 300) {


            return [
                'success' => true,
                'response' => $response,
                'http_code' => $httpCode
            ];
        } else {

            return [
                'success' => false,
                'error' => 'HTTP Error: ' . $httpCode,
                'response' => $response,
                'http_code' => $httpCode
            ];
        }
    }


    public function update(AdminRequest $request, int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['admin.edit']);

        $editor = Admin::find(auth()->user()->id);
        $associationIds = auth()->user()->can('association.create')
            ? Association::all()->pluck('id')->map(fn ($v) => (int) $v)->all()
            : ($editor ? $editor->associations->pluck('id')->map(fn ($v) => (int) $v)->all() : []);

        // Harden: association admins can only assign clubs within their association(s)
        if ($request->has('club_ids')) {
            $allowedClubIds = auth()->user()->can('association.create')
                ? Club::query()->pluck('id')->map(fn ($v) => (int) $v)->all()
                : Club::query()->whereIn('association_id', $associationIds)->pluck('id')->map(fn ($v) => (int) $v)->all();

            $request->validate([
                'club_ids' => ['array'],
                'club_ids.*' => ['integer', Rule::in($allowedClubIds)],
            ]);
        }



        $admin = Admin::findOrFail($id);
        $admin->name = $request->name;
        $admin->email = $request->has('email') ? $request->email : null;
        $admin->phone = $request->phone;
        $admin->country_code = $request->country_code;
        $admin->username = $request->username;
        if ($request->password) {
            $admin->password = Hash::make($request->password);
        }
        if ($request->status) {
            $admin->status = $request->status;
        }
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

            $admin->avatar = 'avatars/' . $filename; // save relative URL
        }
        $admin->save();

        $admin->roles()->detach();
        if ($request->roles) {
            $admin->assignRole($request->roles);
        }

        if ($request->has('association_ids') && !empty($request->association_ids))
            $admin->associations()->syncWithoutDetaching($request->association_ids);

        // If the clubs field was present on the form, sync to reflect removals too.
        if ($request->boolean('club_ids_present')) {
            $clubIds = $request->input('club_ids', []);
            $admin->clubs()->sync($clubIds);
        }

        session()->flash('success', 'Admin has been updated.');
        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['admin.delete']);

        $admin = Admin::findOrFail($id);
        $admin->delete();
        session()->flash('success', 'Admin has been deleted.');
        return back();
    }
}
