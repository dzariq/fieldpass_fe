<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerRequest;
use App\Models\Admin;
use App\Models\Player;
use App\Models\Association;
use App\Models\Club;
use App\Models\PlayerContract;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;



class PlayersController extends Controller
{
    /**
     * Clubs the current admin may assign when bulk-uploading players (association scope or direct club assignment).
     */
    private function clubsForBulkUpload(Admin $admin): Collection
    {
        $associationIds = $admin->associations->pluck('id')->toArray();
        if (count($associationIds) > 0) {
            return Club::whereIn('association_id', $associationIds)->orderBy('name')->get();
        }

        $clubIds = $admin->clubs()->pluck('club.id')->toArray();
        if (count($clubIds) > 0) {
            return Club::whereIn('id', $clubIds)->orderBy('name')->get();
        }

        return Club::orderBy('name')->get();
    }

    /**
     * Club IDs used to scope the admin players list (association clubs, else assigned clubs; null = no restriction).
     *
     * @return array<int>|null
     */
    private function allowedClubIdsForPlayersList(Admin $admin): ?array
    {
        $associationIds = $admin->associations->pluck('id')->toArray();
        if (count($associationIds) > 0) {
            return Club::whereIn('association_id', $associationIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        $clubIds = $admin->clubs()->pluck('club.id')->map(fn ($id) => (int) $id)->values()->all();
        if (count($clubIds) > 0) {
            return $clubIds;
        }

        return null;
    }

    public function index(): Renderable
    {
        // $this->checkAuthorization(auth()->user(), ['players.view']);

        if (auth()->user()->can('association.view')) {
            return view('backend.pages.players.index', [
                'players' => Player::all(),
            ]);
        } else {
            $admin_obj = Admin::find(auth()->user()->id);
            $clubIds = $admin_obj->clubs()->pluck('club.id')->toArray();

            $players = Player::whereHas('clubs', function ($query) use ($clubIds) {
                $query->whereIn('club_id', $clubIds);
            })
                ->with(['clubs']) // Eager load associations and clubs
                ->get();

            return view('backend.pages.players.index', [
                'players' => $players,
            ]);
        }
    }

    public function create(): Renderable
    {
        $admin_obj = Admin::find(auth()->user()->id);
        $clubIds = $admin_obj->clubs()->pluck('club.id')->toArray();

        $this->checkAuthorization(auth()->user(), ['admin.create']);

        if (count($clubIds) > 0) {
            $clubs = Club::whereIn('id', $clubIds)->get();
        } else {
            $clubs = Club::all();
        }

        return view('backend.pages.players.create', [
            'roles' => Role::all(),
            'associations' => Association::all(),
            'clubs' => $clubs,
        ]);
    }

    public function details($id)
    {
        // Dummy data
        $performanceData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            'goals' => [2, 4, 1, 3, 5],
            'assists' => [1, 3, 2, 2, 4],
        ];

        $history = [
            ['match_date' => '2025-01-10', 'opponent' => 'Team A', 'goals' => 1, 'assists' => 0, 'played' => true],
            ['match_date' => '2025-01-24', 'opponent' => 'Team B', 'goals' => 0, 'assists' => 1, 'played' => true],
            ['match_date' => '2025-02-14', 'opponent' => 'Team C', 'goals' => 2, 'assists' => 1, 'played' => true],
            ['match_date' => '2025-03-03', 'opponent' => 'Team D', 'goals' => 0, 'assists' => 0, 'played' => false],
        ];

        $player = Player::find($id);

        return view('backend.pages.players.details', compact('performanceData', 'history', 'player'));
    }

    private function randomPassword($length = 6)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['players.create']);

        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:players,email',
            'username' => 'required|string|max:255|unique:players,username',
            'identity_number' => 'required|string|max:50|unique:players,identity_number',
            'phone' => 'required|string|max:20|unique:players,phone',
            'country_code' => 'required|string|max:4|regex:/^\d{1,4}$/',
            'position' => 'required|in:Goalkeeper,Defender,Midfielder,Forward',
            'salary' => 'nullable|numeric|min:0',
            'club_ids' => 'required|array|min:1',
            'club_ids.*' => 'exists:club,id',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
        ], [
            'identity_number.required' => 'Identity number is required.',
            'identity_number.unique' => 'This identity number is already registered.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already registered.',
            'username.unique' => 'This username is already taken.',
            'email.unique' => 'This email is already registered.',
            'club_ids.required' => 'Please assign at least one club.',
            'end.after_or_equal' => 'End date must be after or equal to start date.',
        ]);

        $player = new Player();
        $player->name = $request->name;
        $player->identity_number = $request->identity_number;
        $player->phone = $request->phone;
        $player->country_code = $request->country_code;
        $player->username = $request->username;
        $player->jersey_number = $request->jersey_number;
        $player->email = $request->has('email') ? $request->email : null;
        $player->code = rand(11111111111, 99999999999);
        $player->referer = auth()->user()->id;
        $player->password = Hash::make($this->randomPassword(6));
        $player->status = 'ACTIVE';
        $player->position = $request->position;
        $player->save();

        if ($request->has('club_ids') && !empty($request->club_ids))
            $player->clubs()->syncWithoutDetaching($request->club_ids);

        $clubId = $request->club_ids[0];
        $clubObj = Club::find($clubId);

        $endDate = $request->end ?? date('Y-m-d', strtotime($request->start . ' +1 year'));

        $contract = new PlayerContract();
        $contract->player_id = $player->id;
        $contract->club_id = $clubObj->id;
        $contract->start_date = $request->start;
        $contract->end_date = $endDate;
        $contract->salary = $request->salary ?? 0;
        $contract->status = 'active';
        $contract->save();

        $formattedStartDate = date("F j, Y", strtotime($contract->start_date));
        $formattedEndDate = date("F j, Y", strtotime($contract->end_date)); // Fixed: was using start_date

        $data = [
            'action' => 'player_invitation',
            'username' => $player->username,
            'code' => $player->code,
            'email' => $request->filled('email') ? $request->email : null,
            'phone' => (string) $player->phone,
            'country_code' => preg_replace('/\D/', '', (string) $request->country_code),
            'club_name' => $clubObj->name,
            'start' => $formattedStartDate,
            'domain' => config('app.url'),
            'end' => $formattedEndDate,
            'name' => $request->name,
        ];
        $this->sendWAInvitation($data);

        session()->flash('success', __('Player has been created.'));
        // Check if user has admin.create permission
        if (auth()->user()->can('club.create')) {
            return redirect()->route('admin.players.list');
        } else {
            return redirect()->route('admin.players.index');
        }    }

    function sendWAInvitation($data)
    {
        $webhookUrl = config('services.n8n.admin_invitation_url');

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
            Log::error('n8n admin-invitation webhook error: ' . $error);
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
        // $this->checkAuthorization(auth()->user(), ['players.edit', 'admin.create']);

        $player = Player::findOrFail($id);
        return view('backend.pages.players.edit', [
            'player' => $player,
            'clubs' => Club::all(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        // $this->checkAuthorization(auth()->user(), ['players.edit', 'admin.create']);

        // Validate request with unique rules excluding current player
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:players,email,' . $id,
            'username' => 'required|string|max:255|unique:players,username,' . $id,
            'identity_number' => 'required|string|max:50|unique:players,identity_number,' . $id,
            'phone' => 'required|string|max:20|unique:players,phone,' . $id,
            'position' => 'required|in:Goalkeeper,Defender,Midfielder,Forward',
            'salary' => 'nullable|numeric|min:0',
            'club_ids' => 'nullable|array',
            'club_ids.*' => 'exists:club,id',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'status' => 'nullable|in:ACTIVE,INACTIVE,INVITED',
        ], [
            'identity_number.unique' => 'This identity number is already registered.',
            'phone.unique' => 'This phone number is already registered.',
            'username.unique' => 'This username is already taken.',
            'email.unique' => 'This email is already registered.',
            'end.after_or_equal' => 'End date must be after or equal to start date.',
        ]);

        $player = Player::findOrFail($id);
        $player->name = $request->name;
        $player->email = $request->has('email') ? $request->email : null;
        $player->username = $request->username;
        $player->identity_number = $request->identity_number;
        $player->phone = $request->phone;
        $player->position = $request->position;
        $player->jersey_number = $request->jersey_number;

        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $destination = public_path('avatars');

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            if ($player->avatar && file_exists(public_path($player->avatar))) {
                unlink(public_path($player->avatar));
            }

            $file->move($destination, $filename);
            $player->avatar = 'avatars/' . $filename;
        }


        if ($request->password) {
            $player->password = Hash::make($request->password);
        }

        if ($request->status) {
            $player->status = $request->status;
        }

        $player->save();

        // Update roles
        $player->roles()->detach();
        if ($request->roles) {
            $player->assignRole($request->roles);
        }

        // Update clubs - sync instead of detach
        if ($request->has('club_ids') && !empty($request->club_ids)) {
            $player->clubs()->sync($request->club_ids);
        } else {
            $player->clubs()->detach();
        }

        // Check if player has any clubs assigned
        if ($player->clubs()->exists()) {
            // Update or create contract
            $contract = PlayerContract::where('player_id', $player->id)
                ->where('status', 'active')
                ->first();

            if (!$contract) {
                $contract = new PlayerContract();
                $contract->player_id = $player->id;
                $contract->status = 'active';
            }

            // Update contract details
            if ($request->has('club_ids') && !empty($request->club_ids)) {
                $contract->club_id = $request->club_ids[0];
            }

            // Default start date to today if not provided
            $startDate = $request->start ?? ($contract->start_date ?? date('Y-m-d'));

            // Default end date to 1 year from start if not provided
            $endDate = $request->end ?? date('Y-m-d', strtotime($startDate . ' +1 year'));

            $contract->start_date = $startDate;
            $contract->end_date = $endDate;
            $contract->salary = $request->salary ?? 0;
            $contract->save();
        }
        // Send invitation if status is INVITED
        if ($player->status == 'INVITED') {
            $clubId = $request->club_ids[0] ?? null;

            if ($clubId) {
                $clubObj = Club::find($clubId);

                $formattedStartDate = date("F j, Y", strtotime($contract->start_date));
                $formattedEndDate = date("F j, Y", strtotime($contract->end_date)); // Fixed: was using start_date

                $data = array(
                    'action' => 'player_invitation',
                    'username' => $player->username,
                    'code' => $player->code,
                    'email' => $request->email,
                    'club_name' => $clubObj->name,
                    'start' => $formattedStartDate,
                    'end' => $formattedEndDate,
                    'name' => $request->name
                );

                // $this->sendWAlInvitation($player);
            }
        }


        session()->flash('success', 'Player has been updated.');

        // Check if user has admin.create permission
        if (auth()->user()->can('club.create')) {
            return redirect()->route('admin.players.list');
        } else {
            return redirect()->route('admin.players.index');
        }
    }

    public function reinvite($id)
    {
        // $this->checkAuthorization(auth()->user(), ['players.edit', 'admin.create']);

        $player = Player::findOrFail($id);

        // Check if player is in INVITED status
        if ($player->status !== 'INVITED') {
            session()->flash('error', 'Only invited players can be re-invited.');
            return back();
        }

        // Get club name from PlayerClub relationship
        $clubObj = $player->clubs()->first(); // Get first club
        $clubName = $clubObj ? $clubObj->name : 'N/A';

        $data = array(
            'action' => 'player_invitation',
            'username' => $player->username,
            'code' => $player->code,
            'email' => $player->email,
            'phone' => $player->phone,
            'club_name' => $clubName,
            'start' => '',
            'domain' => env('APP_URL'),
            'end' => '',
            'name' => $player->name
        );

        // Only send if player has phone number
        if ($player->phone) {
            $this->sendWAInvitation($data);
            session()->flash('success', 'WhatsApp invitation has been resent to ' . $player->name);
        } else {
            session()->flash('error', 'Cannot send invitation. Player does not have a phone number.');
        }

        return back();
    }

    public function inviteForm()
    {
        $this->checkAuthorization(auth()->user(), ['players.view']);

        $user = auth()->user();

        // Get only clubs that belong to the logged-in user
        $clubs = $user->clubs; // Assuming user has clubs relationship

        // If no clubs assigned to user, show error
        if ($clubs->isEmpty()) {
            session()->flash('error', 'You do not have any clubs assigned. Please contact administrator.');
            return redirect()->route('admin.dashboard');
        }

        return view('backend.pages.players.invite', compact('clubs'));
    }

    public function searchPlayer(Request $request)
    {
        $request->validate([
            'search_value' => 'required|string',
            'search_type' => 'required|in:identity_number,name',
        ]);

        $searchType = $request->search_type;
        $searchValue = $request->search_value;

        if ($searchType == 'identity_number') {
            $player = Player::where('identity_number', $searchValue)->first();
        } else {
            // Search by name - case insensitive
            $player = Player::where('name', 'LIKE', '%' . $searchValue . '%')->first();

            // If multiple players found, return list
            $players = Player::where('name', 'LIKE', '%' . $searchValue . '%')->get();

            if ($players->count() > 1) {
                return response()->json([
                    'success' => false,
                    'multiple' => true,
                    'message' => 'Multiple players found. Please be more specific or use IC number.',
                    'players' => $players->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'name' => $p->name,
                            'identity_number' => $p->identity_number,
                            'position' => $p->position,
                            'status' => $p->status,
                        ];
                    })
                ]);
            }
        }

        if (!$player) {
            return response()->json([
                'success' => false,
                'message' => 'Player not found.'
            ]);
        }

        // Check if player already has club AND status is ACTIVE
        $hasClub = $player->clubs()->exists();

        if ($hasClub) {
            return response()->json([
                'success' => false,
                'message' => 'Player is already active with a club and cannot be invited.'
            ]);
        }

        return response()->json([
            'success' => true,
            'player' => [
                'id' => $player->id,
                'name' => $player->name,
                'email' => $player->email,
                'phone' => $player->phone,
                'identity_number' => $player->identity_number,
                'position' => $player->position,
                'status' => $player->status,
                'has_club' => $hasClub,
            ]
        ]);
    }

    public function bulkUploadForm()
    {
        $this->checkAuthorization(auth()->user(), ['admin.create']);

        $admin = Admin::findOrFail(auth()->user()->id);
        $clubs = $this->clubsForBulkUpload($admin);

        return view('backend.pages.players.bulk-upload', compact('clubs'));
    }

    public function downloadTemplate()
    {
        $this->checkAuthorization(auth()->user(), ['admin.create']);

        // Create CSV template
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="players_upload_template.csv"',
        ];

        $columns = [
            'name',
            'email',
            'identity_number',
            'country_code',
            'phone',
            'position',
            'salary'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for proper encoding
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, $columns);

            // Add example row 1 - Use tab prefix to force text format
            fputcsv($file, [
                'John Doe',
                'john@example.com',
                "\t900101011234",  // Tab prefix forces text
                "\t60",           // Tab prefix forces text
                "\t192201222",     // Tab prefix forces text
                'Midfielder',
                '5000'
            ]);

            // Add example row 2
            fputcsv($file, [
                'Jane Smith',
                'jane@example.com',
                "\t910202021234",  // Tab prefix forces text
                "\t65",           // Tab prefix forces text
                "\t987654321",     // Tab prefix forces text
                'Forward',
                '6000'
            ]);

            // Add example row 3
            fputcsv($file, [
                'Ahmad Ali',
                '',  // Email optional
                "\t920303031234",  // Tab prefix forces text
                "\t62",           // Tab prefix forces text
                "\t812345678",     // Tab prefix forces text
                'Goalkeeper',
                ''  // Salary optional
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function bulkUploadStore(Request $request)
    {
        $this->checkAuthorization(auth()->user(), ['admin.create']);

        $admin = Admin::findOrFail(auth()->user()->id);
        $allowedClubIds = $this->clubsForBulkUpload($admin)->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (count($allowedClubIds) === 0) {
            session()->flash('error', 'No clubs are available for your account. You cannot bulk upload players until a club is available.');
            return back();
        }

        $request->validate([
            'club_id' => ['required', 'integer', Rule::in($allowedClubIds)],
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ], [
            'club_id.required' => 'Please select a club before uploading.',
            'club_id.in' => 'The selected club is not valid for your account.',
        ]);

        $clubId = (int) $request->input('club_id');
        $clubObj = Club::findOrFail($clubId);

        $file = $request->file('file');
        $fileData = array_map('str_getcsv', file($file->getRealPath()));

        // Remove header row
        $header = array_shift($fileData);

        // Remove BOM from first column if present
        $header[0] = str_replace("\xEF\xBB\xBF", '', $header[0]);
        $header = array_map('trim', $header);

        // Validate header
        $expectedHeaders = ['name', 'email', 'identity_number', 'country_code', 'phone', 'position', 'salary'];

        if ($header !== $expectedHeaders) {
            session()->flash('error', 'Invalid CSV format. Please download the template and use the correct format.');
            return back();
        }

        // Check row limit (excluding header)
        $totalRows = count($fileData);
        if ($totalRows > 100) {
            session()->flash('error', "Upload limit exceeded. You can only upload up to 100 records at a time. Your file contains {$totalRows} records.");
            return back();
        }

        if ($totalRows == 0) {
            session()->flash('error', 'The CSV file is empty. Please add data and try again.');
            return back();
        }

        $successCount = 0;
        $errorCount = 0;
        $uploadErrors = [];
        $skipped = [];
        $playersToNotify = []; // Store players for later notification

        DB::beginTransaction();

        try {
            foreach ($fileData as $index => $row) {
                $rowNumber = $index + 2;

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Map row to data - clean BOM and quotes
                $data = [
                    'name' => trim($row[0] ?? ''),
                    'email' => trim($row[1] ?? ''),
                    'identity_number' => trim(ltrim($row[2] ?? '', "'\t")),
                    'country_code' => trim(ltrim($row[3] ?? '', "'\t")),
                    'phone' => trim(ltrim($row[4] ?? '', "'\t")),
                    'position' => trim($row[5] ?? ''),
                    'salary' => trim($row[6] ?? 0),
                ];

                // Validate row
                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'nullable|email|max:255|unique:players,email',
                    'identity_number' => 'required|string|max:50|unique:players,identity_number',
                    'country_code' => 'nullable|string|max:4|regex:/^\d{1,4}$/',
                    'phone' => 'nullable|string|max:20|unique:players,phone',
                    'position' => 'required|in:Goalkeeper,Defender,Midfielder,Forward',
                    'salary' => 'nullable|numeric|min:0',
                ]);

                if ($validator->fails()) {
                    $errorCount++;
                    $uploadErrors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // Generate unique username
                $baseUsername = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($data['name']));
                $baseUsername = substr($baseUsername, 0, 10);
                $timestamp = substr((string)time(), -6);
                $username = $baseUsername . $timestamp;

                $counter = 1;
                while (Player::where('username', $username)->exists()) {
                    $username = $baseUsername . $timestamp . $counter;
                    $counter++;
                }

                // Create player
                $player = new Player();
                $player->name = $data['name'];
                $player->email = !empty($data['email']) ? $data['email'] : null;
                $player->identity_number = $data['identity_number'];
                $player->country_code = !empty($data['country_code']) ? $data['country_code'] : null;
                $player->phone = !empty($data['phone']) ? $data['phone'] : null;
                $player->username = $username;
                $player->position = $data['position'];
                $player->code = rand(11111111111, 99999999999);
                $player->referer = auth()->user()->id;
                $player->password = Hash::make($this->randomPassword(6));
                $player->status = 'INVITED';
                $player->save();

                $player->clubs()->sync([$clubId]);

                $defaultSalary = 50.0;
                $salaryValue = $data['salary'] !== '' && is_numeric($data['salary'])
                    ? (float) $data['salary']
                    : $defaultSalary;

                $contractStart = date('Y-m-d');
                $contractEnd = date('Y-m-d', strtotime($contractStart . ' +1 year'));

                $contract = new PlayerContract();
                $contract->player_id = $player->id;
                $contract->club_id = $clubObj->id;
                $contract->start_date = $contractStart;
                $contract->end_date = $contractEnd;
                $contract->salary = $salaryValue;
                $contract->status = 'active';
                $contract->save();

                // Add to notification queue instead of sending immediately
                if (!empty($player->phone)) {
                    $playersToNotify[] = [
                        'action' => 'player_invitation',
                        'code' => $player->code,
                        'email' => $player->email,
                        'phone' => $player->phone,
                        'username' => $player->username,
                        'domain' => env('APP_URL'),
                        'country_code' => $player->country_code,
                        'club_name' => $clubObj->name,
                        'start' => date('F j, Y', strtotime($contractStart)),
                        'end' => date('F j, Y', strtotime($contractEnd)),
                        'name' => $player->name
                    ];
                }

                $successCount++;
            }

            DB::commit();

            // Send notifications in background (after commit)
            if (!empty($playersToNotify)) {
                // Dispatch job to send WhatsApp messages in background
                dispatch(function () use ($playersToNotify) {
                    foreach ($playersToNotify as $data) {
                        try {
                            $this->sendWAInvitation($data);
                            sleep(5); // Sleep in background job, not blocking the request
                        } catch (\Exception $e) {
                            \Log::error('WhatsApp invitation failed: ' . $e->getMessage());
                        }
                    }
                })->afterResponse(); // Send after HTTP response
            }

            // Prepare summary message
            $message = "Bulk upload completed. Success: {$successCount}";

            if ($errorCount > 0) {
                $message .= ", Errors: {$errorCount}";
            }

            if (count($skipped) > 0) {
                $message .= ", Skipped: " . count($skipped);
            }

            if (!empty($playersToNotify)) {
                $message .= ". WhatsApp invitations are being sent in the background.";
            }

            session()->flash('success', $message);

            if (!empty($uploadErrors)) {
                session()->flash('upload_errors', $uploadErrors);
            }

            if (!empty($skipped)) {
                session()->flash('skipped', $skipped);
            }

            return redirect()->route('admin.players.list');
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'An error occurred during upload: ' . $e->getMessage());
            return back();
        }
    }

    public function updateMarketValue(Request $request, int $id)
    {
        $this->checkAuthorization(auth()->user(), ['admin.create']);

        $request->validate([
            'market_value' => 'required|numeric|min:0|max:9999999999',
        ]);

        $player = Player::findOrFail($id);
        $player->market_value = $request->market_value;
        $player->save();

        return response()->json([
            'success' => true,
            'message' => 'Market value updated successfully!',
            'market_value' => ($player->market_value),
        ]);
    }

    public function playersList(Request $request)
    {
        $this->checkAuthorization(auth()->user(), ['admin.create']);

        $admin = Admin::findOrFail(auth()->user()->id);
        $allowedClubIds = $this->allowedClubIdsForPlayersList($admin);

        $clubs = $allowedClubIds === null
            ? Club::orderBy('name')->get()
            : Club::whereIn('id', $allowedClubIds)->orderBy('name')->get();

        $selectedClubId = $request->input('club_id');
        if ($selectedClubId !== null && $selectedClubId !== '' && $allowedClubIds !== null) {
            if (! in_array((int) $selectedClubId, $allowedClubIds, true)) {
                $selectedClubId = null;
            }
        }

        // Query players
        $query = Player::with(['clubs', 'contracts' => function ($q) {
            $q->where('status', 'active')->latest();
        }]);

        // Only players linked to clubs this admin may see (association / assigned clubs); super admin => no base filter
        if ($allowedClubIds !== null) {
            $scopeClubIds = $selectedClubId
                ? [(int) $selectedClubId]
                : $allowedClubIds;
            if (count($scopeClubIds) === 0) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereHas('clubs', function ($q) use ($scopeClubIds) {
                    // Qualify to avoid ambiguity with player_club.id
                    $q->whereIn('club.id', $scopeClubIds);
                });
            }
        } elseif ($selectedClubId) {
            $query->whereHas('clubs', function ($q) use ($selectedClubId) {
                $q->where('club.id', (int) $selectedClubId);
            });
        }

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('identity_number', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%");
            });
        }

        // Filter by position
        if ($request->has('position') && !empty($request->position)) {
            $query->where('position', $request->position);
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Order by name
        $query->orderBy('name', 'asc');

        // Paginate
        $players = $query->paginate(20)->appends($request->all());

        return view('backend.pages.players.list', compact('players', 'clubs', 'selectedClubId'));
    }
    public function sendInvite(Request $request)
    {
        $this->checkAuthorization(auth()->user(), ['players.view']);

        $request->validate([
            'player_id' => 'required|exists:players,id',
            'club_ids' => 'required|array|min:1',
            'club_ids.*' => 'exists:club,id',
            'salary' => 'nullable|numeric|min:0',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
        ]);

        $user = auth()->user();
        $player = Player::findOrFail($request->player_id);

        // Validate that user can only assign their own clubs
        $userClubIds = $user->clubs->pluck('id')->toArray();
        $requestedClubIds = $request->club_ids;

        // Check if all requested clubs belong to the user
        $unauthorizedClubs = array_diff($requestedClubIds, $userClubIds);

        if (!empty($unauthorizedClubs)) {
            session()->flash('error', 'You can only assign clubs that are assigned to you.');
            return back();
        }

        // Check if player can be invited
        $hasClub = $player->clubs()->exists();

        if ($player->status == 'ACTIVE' && $hasClub) {
            session()->flash('error', 'Player is already active with a club and cannot be invited.');
            return back();
        }

        // Add clubs to player (player_club pivot table)
        if ($request->has('club_ids') && !empty($request->club_ids)) {
            $player->clubs()->syncWithoutDetaching($request->club_ids);
        }

        // Update player status to INVITED
        // $player->status = 'INVITED';
        $player->save();

        // Create or update contract
        $clubId = $request->club_ids[0];
        $clubObj = Club::find($clubId);

        // Default end date to 1 year from start if not provided
        $startDate = $request->start;
        $endDate = $request->end ?? date('Y-m-d', strtotime($startDate . ' +1 year'));

        $contract = PlayerContract::where('player_id', $player->id)
            ->where('club_id', $clubId)
            ->first();

        if (!$contract) {
            $contract = new PlayerContract();
            $contract->player_id = $player->id;
            $contract->club_id = $clubId;
        }

        $contract->start_date = $startDate;
        $contract->end_date = $endDate;
        $contract->salary = $request->salary ?? 0;
        $contract->status = 'active';
        $contract->save();

        // Send invitation email
        $formattedStartDate = date("F j, Y", strtotime($contract->start_date));
        $formattedEndDate = date("F j, Y", strtotime($contract->end_date));

        $data = array(
            'action' => 'player_club_notification',
            'username' => $player->username,
            'phone' => $player->phone,
            'country_code' => $player->country_code,
            'code' => $player->code,
            'email' => $player->email,
            'domain' => env('APP_URL'),
            'club' => $clubObj->name,
            'start' => $formattedStartDate,
            'end' => $formattedEndDate,
            'name' => $player->name
        );
        $this->sendWAInvitation($data);

        session()->flash('success', 'Player has been invited successfully!');
        return redirect()->route('admin.players.invite');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['players.delete']);

        $player = Player::findOrFail($id);
        $player->delete();
        session()->flash('success', 'Player has been deleted.');
        return back();
    }
}
