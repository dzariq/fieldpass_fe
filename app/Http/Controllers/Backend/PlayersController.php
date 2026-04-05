<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Association;
use App\Models\Club;
use App\Models\Player;
use App\Models\PlayerClubHistory;
use App\Models\PlayerContract;
use App\Models\PlayerTermination;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

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

    /**
     * Any admin with player read access may view club history & match performance (same scope as invite / players listing, not limited by managed clubs).
     */
    private function adminMayViewPlayerClubHistory(): bool
    {
        $user = auth()->user();

        return $user && ($user->can('players.view') || $user->can('players.edit'));
    }

    /**
     * Club managers may edit players linked to their clubs; association-wide admins may edit all (same scope as players index).
     */
    private function adminMayEditPlayer(Player $player): bool
    {
        if (! auth()->user()->can('players.edit')) {
            return false;
        }
        if (auth()->user()->can('association.view')) {
            return true;
        }
        $admin = Admin::findOrFail(auth()->user()->id);
        $clubIds = $admin->clubs()->pluck('club.id')->map(fn ($id) => (int) $id)->all();
        if (count($clubIds) === 0) {
            return false;
        }

        if ($player->clubs()->wherePivotIn('club_id', $clubIds)->exists()) {
            return true;
        }

        // After terminate-contract the player may have no clubs; allow same manager to open edit if they
        // terminated from a club they manage (avoids 403 on the edit URL).
        if (! $player->clubs()->exists()) {
            return PlayerTermination::query()
                ->where('player_id', $player->id)
                ->whereIn('club_id', $clubIds)
                ->exists();
        }

        return false;
    }

    /**
     * Clubs this admin may terminate for the given player (player must currently belong to the club).
     *
     * @return Collection<int, Club>
     */
    private function clubsEligibleForTermination(Player $player): Collection
    {
        $player->loadMissing('clubs');
        if (auth()->user()->can('association.view')) {
            return $player->clubs->values();
        }

        $admin = Admin::findOrFail(auth()->user()->id);
        $adminClubIds = $admin->clubs()->pluck('club.id')->map(fn ($id) => (int) $id)->all();

        return $player->clubs
            ->filter(fn (Club $c) => in_array((int) $c->id, $adminClubIds, true))
            ->values();
    }

    /**
     * Club-scoped admins (no association-wide access) may adjust contact/jersey/avatar only — not core identity or market value.
     */
    private function isClubManagerPlayerEditor(): bool
    {
        $user = auth()->user();

        return $user
            && $user->can('players.edit')
            && ! $user->can('association.view');
    }

    /**
     * Normalize 12-digit Malaysian IC input to XXXXXX-XX-XXXX.
     */
    private function normalizeMalaysiaIcNumber(?string $raw): string
    {
        $raw = trim((string) $raw);
        $digits = preg_replace('/\D/', '', $raw);
        if (strlen($digits) === 12) {
            return substr($digits, 0, 6).'-'.substr($digits, 6, 2).'-'.substr($digits, 8, 4);
        }

        return $raw;
    }

    /**
     * @return 'malaysia_ic'|'foreign_id'|null
     */
    private function parseBulkUploadIdType(string $raw): ?string
    {
        $k = strtolower(trim(preg_replace('/\s+/', ' ', $raw)));
        $aliases = [
            'malaysia ic' => 'malaysia_ic',
            'malaysia_ic' => 'malaysia_ic',
            'foreign id' => 'foreign_id',
            'foreign_id' => 'foreign_id',
        ];

        return $aliases[$k] ?? null;
    }

    /**
     * Public URL for a player's avatar, or the default image when missing/invalid (e.g. DB "0" or deleted file).
     */
    private function resolvePlayerPublicAvatarUrl(?string $stored): string
    {
        $default = asset('backend/assets/images/default-avatar.png');
        $path = trim((string) ($stored ?? ''));
        if ($path === '' || strcasecmp($path, 'null') === 0 || $path === '0') {
            return $default;
        }
        if (str_contains($path, '..')) {
            return $default;
        }
        $fullPath = public_path($path);
        if (! is_file($fullPath)) {
            return $default;
        }

        return asset($path);
    }

    /**
     * Payload for invite-page search results (single or multiple rows).
     *
     * @return array<string, mixed>
     */
    private function playerInviteSearchPayload(Player $player): array
    {
        $player->loadMissing('clubs');
        $hasClub = $player->clubs->isNotEmpty();
        $clubLabels = $player->clubs->sortBy('name')->map(function (Club $c) {
            $long = trim((string) ($c->long_name ?? ''));

            return $long !== '' ? $long : (string) ($c->name ?? '');
        })->filter()->values()->all();

        return [
            'id' => $player->id,
            'name' => $player->name,
            'avatar_url' => $this->resolvePlayerPublicAvatarUrl($player->avatar),
            'identity_number' => $player->identity_number,
            'identity_type' => $player->identity_type ?? 'malaysia_ic',
            'position' => $player->position,
            'status' => $player->status,
            'has_club' => $hasClub,
            'clubs_display' => $hasClub ? implode(', ', $clubLabels) : '—',
            'country_code' => $player->country_code,
            'phone' => $player->phone,
            'email' => $player->email,
        ];
    }

    /**
     * Same n8n message as player list "send invitation" (profile login nudge).
     */
    private function tryPostN8nLoginProfileMessage(Player $player, string $countryCode, string $phoneDigits): bool
    {
        $phoneNumber = $countryCode.$phoneDigits;
        $club = $this->resolveClubForInvitation($player);
        $longName = $club !== null
            ? (string) ((trim((string) ($club->long_name ?? '')) !== '') ? $club->long_name : ($club->name ?? 'Fieldpass'))
            : 'Fieldpass';
        $loginUrl = route('player.login', [], true);
        $message = sprintf(
            "Login here to update your profile:\n%s - %s",
            $loginUrl,
            $longName
        );

        $url = (string) config('services.n8n.send_message_url');
        if ($url === '') {
            Log::warning('n8n send_message_url not configured; skipping login nudge', ['player_id' => $player->id]);

            return false;
        }

        try {
            $response = Http::timeout(45)
                ->acceptJson()
                ->post($url, [
                    'phone_number' => $phoneNumber,
                    'message' => $message,
                ]);
        } catch (\Throwable $e) {
            Log::error('n8n send-message failed', ['exception' => $e->getMessage(), 'player_id' => $player->id]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('n8n send-message returned error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'player_id' => $player->id,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Club context for invitation messages (same scope as list edit: admin’s club if linked, else first player club).
     */
    private function resolveClubForInvitation(Player $player): ?Club
    {
        $admin = Admin::findOrFail(auth()->user()->id);
        $playerClubs = $player->clubs->sortBy('name')->values();

        if ($playerClubs->isEmpty()) {
            return null;
        }

        $adminClubIds = $admin->clubs()->pluck('club.id')->map(fn ($id) => (int) $id)->all();
        if (count($adminClubIds) > 0) {
            $match = $playerClubs->first(fn ($c) => in_array((int) $c->id, $adminClubIds, true));
            if ($match) {
                return $match;
            }
        }

        return $playerClubs->first();
    }

    public function index(): Renderable
    {
        // $this->checkAuthorization(auth()->user(), ['players.view']);

        if (auth()->user()->can('association.view')) {
            $players = Player::with(['clubs'])->orderBy('name')->paginate(30);
        } else {
            $admin_obj = Admin::find(auth()->user()->id);
            $clubIds = $admin_obj->clubs()->pluck('club.id')->toArray();

            $players = Player::whereHas('clubs', function ($query) use ($clubIds) {
                $query->whereIn('club_id', $clubIds);
            })
                ->with(['clubs'])
                ->orderBy('name')
                ->paginate(30);
        }

        return view('backend.pages.players.index', [
            'players' => $players,
        ]);
    }

    public function create(): Renderable
    {
        if (auth()->user()->hasRole('Club Manager')) {
            abort(403);
        }

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
        if (auth()->user()->hasRole('Club Manager')) {
            abort(403);
        }

        $this->checkAuthorization(auth()->user(), ['players.create']);

        $rawType = $request->input('identity_type');
        $identityType = in_array($rawType, ['malaysia_ic', 'foreign_id'], true) ? $rawType : '';
        if ($identityType === 'malaysia_ic') {
            $request->merge(['identity_number' => $this->normalizeMalaysiaIcNumber($request->input('identity_number'))]);
        } elseif ($identityType === 'foreign_id') {
            $request->merge(['identity_number' => trim((string) $request->input('identity_number', ''))]);
        }

        $identityNumberRules = ['required', 'string', 'max:50', Rule::unique('players', 'identity_number')];
        if ($identityType === 'malaysia_ic') {
            $identityNumberRules[] = 'regex:/^\d{6}-\d{2}-\d{4}$/';
        }

        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:players,email',
            'username' => 'required|string|max:255|unique:players,username',
            'identity_type' => 'required|in:malaysia_ic,foreign_id',
            'identity_number' => $identityNumberRules,
            'phone' => 'required|string|max:20|unique:players,phone',
            'country_code' => 'required|string|max:4|regex:/^\d{1,4}$/',
            'position' => 'required|in:Goalkeeper,Defender,Midfielder,Forward',
            'salary' => 'nullable|numeric|min:0',
            'club_ids' => 'required|array|min:1',
            'club_ids.*' => 'exists:club,id',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
        ], [
            'identity_type.required' => 'Please select an identity type.',
            'identity_number.required' => 'Identity number is required.',
            'identity_number.unique' => 'This identity number is already registered.',
            'identity_number.regex' => 'Malaysia IC must be in format XXXXXX-XX-XXXX (12 digits).',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already registered.',
            'username.unique' => 'This username is already taken.',
            'email.unique' => 'This email is already registered.',
            'club_ids.required' => 'Please assign at least one club.',
            'end.after_or_equal' => 'End date must be after or equal to start date.',
        ]);

        $player = new Player;
        $player->name = $request->name;
        $player->identity_type = $validated['identity_type'];
        $player->identity_number = $validated['identity_number'];
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
        $player->market_value = 50;
        $player->save();

        if ($request->has('club_ids') && ! empty($request->club_ids)) {
            $existingClubIds = $player->clubs()->pluck('club.id')->map(fn ($id) => (int) $id)->all();
            $player->clubs()->syncWithoutDetaching($request->club_ids);
            $requestedIds = array_map('intval', $request->club_ids);
            foreach (array_diff($requestedIds, $existingClubIds) as $cid) {
                PlayerClubHistory::record($player->id, $cid, 'assigned', (int) auth()->id());
            }
        }

        $clubId = $request->club_ids[0];
        $clubObj = Club::find($clubId);

        $endDate = $request->end ?? date('Y-m-d', strtotime($request->start.' +1 year'));

        $contract = new PlayerContract;
        $contract->player_id = $player->id;
        $contract->club_id = $clubObj->id;
        $contract->start_date = $request->start;
        $contract->end_date = $endDate;
        $contract->salary = $request->salary ?? 0;
        $contract->status = 'active';
        $contract->save();

        $formattedStartDate = date('F j, Y', strtotime($contract->start_date));
        $formattedEndDate = date('F j, Y', strtotime($contract->end_date)); // Fixed: was using start_date

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
        }
    }

    public function sendWAInvitation($data)
    {
        $webhookUrl = config('services.n8n.admin_invitation_url');

        // Initialize cURL
        $curl = curl_init();

        // Set cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => $webhookUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        // Execute the request
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        // Close cURL
        curl_close($curl);

        // Handle response
        if ($error) {
            // Log error
            Log::error('n8n admin-invitation webhook error: '.$error);

            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode,
            ];
        }

        if ($httpCode >= 200 && $httpCode < 300) {

            return [
                'success' => true,
                'response' => $response,
                'http_code' => $httpCode,
            ];
        } else {

            return [
                'success' => false,
                'error' => 'HTTP Error: '.$httpCode,
                'response' => $response,
                'http_code' => $httpCode,
            ];
        }
    }

    public function edit(int $id): Renderable
    {
        // $this->checkAuthorization(auth()->user(), ['players.edit', 'admin.create']);

        $player = Player::findOrFail($id);
        if (! $this->adminMayEditPlayer($player)) {
            abort(403, 'Sorry !! You are unauthorized to perform this action.');
        }

        return view('backend.pages.players.edit', [
            'player' => $player,
            'allowFullPlayerFieldEdit' => auth()->user()->can('association.view'),
            'clubsForTermination' => $this->clubsEligibleForTermination($player),
        ]);
    }

    public function terminateContract(Request $request, int $player): RedirectResponse
    {
        $playerModel = Player::query()->with('clubs')->findOrFail($player);
        if (! $this->adminMayEditPlayer($playerModel)) {
            abort(403, 'Sorry !! You are unauthorized to perform this action.');
        }

        $eligible = $this->clubsEligibleForTermination($playerModel);
        $allowedIds = $eligible->pluck('id')->map(fn ($cid) => (int) $cid)->all();

        if (count($allowedIds) === 0) {
            return back()->with('error', 'This player is not assigned to a club you can terminate.');
        }

        $validated = $request->validate([
            'club_id' => ['required', 'integer', Rule::in($allowedIds)],
            'remark' => ['required', 'string', 'max:5000'],
        ], [
            'club_id.in' => 'Choose a valid club the player is currently assigned to.',
        ]);

        $clubId = (int) $validated['club_id'];
        if (! $playerModel->clubs->contains('id', $clubId)) {
            return back()->with('error', 'The player is not linked to that club.');
        }

        $now = now();

        DB::transaction(function () use ($playerModel, $clubId, $validated, $now): void {
            PlayerTermination::query()->create([
                'player_id' => $playerModel->id,
                'club_id' => $clubId,
                'remark' => $validated['remark'],
                'terminated_at' => $now,
                'admin_id' => (int) auth()->id(),
            ]);

            PlayerClubHistory::record(
                $playerModel->id,
                $clubId,
                'terminated',
                (int) auth()->id(),
                $validated['remark'],
                $now
            );

            $playerModel->clubs()->detach($clubId);

            PlayerContract::query()
                ->where('player_id', $playerModel->id)
                ->where('club_id', $clubId)
                ->where('status', 'active')
                ->update(['status' => 'terminated']);
        });

        return redirect()
            ->route('admin.players.edit', $playerModel->id)
            ->with('success', 'Contract terminated. The player has been removed from the selected club.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        // $this->checkAuthorization(auth()->user(), ['players.edit', 'admin.create']);

        $player = Player::findOrFail($id);
        if (! $this->adminMayEditPlayer($player)) {
            abort(403, 'Sorry !! You are unauthorized to perform this action.');
        }

        $phoneDigits = preg_replace('/\D/', '', (string) $request->input('phone', ''));
        $request->merge(['phone' => $phoneDigits === '' ? null : $phoneDigits]);
        $ccDigits = preg_replace('/\D/', '', (string) $request->input('country_code', ''));
        $request->merge(['country_code' => $ccDigits === '' ? null : $ccDigits]);

        $jerseyIn = $request->input('jersey_number');
        if ($jerseyIn === '' || $jerseyIn === null) {
            $request->merge(['jersey_number' => null]);
        }

        if ($this->isClubManagerPlayerEditor()) {
            $validated = $request->validate([
                'email' => 'nullable|email|max:255|unique:players,email,'.$id,
                'country_code' => ['nullable', 'string', 'max:4', 'regex:/^\d{1,4}$/'],
                'phone' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[0-9]{7,15}$/',
                    Rule::unique('players', 'phone')->ignore($id),
                ],
                'jersey_number' => 'nullable|integer|min:1|max:99999',
                'salary' => 'nullable|numeric|min:0',
                'club_ids' => 'nullable|array',
                'club_ids.*' => 'exists:club,id',
                'start' => 'nullable|date',
                'end' => 'nullable|date|after_or_equal:start',
                'status' => 'nullable|in:ACTIVE,INACTIVE,INVITED',
            ], [
                'phone.unique' => 'This phone number is already registered.',
                'email.unique' => 'This email is already registered.',
                'end.after_or_equal' => 'End date must be after or equal to start date.',
            ]);
        } else {
            $rawType = $request->input('identity_type');
            $identityType = in_array($rawType, ['malaysia_ic', 'foreign_id'], true) ? $rawType : '';
            if ($identityType === 'malaysia_ic') {
                $request->merge(['identity_number' => $this->normalizeMalaysiaIcNumber($request->input('identity_number'))]);
            } elseif ($identityType === 'foreign_id') {
                $request->merge(['identity_number' => trim((string) $request->input('identity_number', ''))]);
            }

            $identityNumberRulesUpdate = [
                'required',
                'string',
                'max:50',
                Rule::unique('players', 'identity_number')->ignore($id),
            ];
            if ($identityType === 'malaysia_ic') {
                $identityNumberRulesUpdate[] = 'regex:/^\d{6}-\d{2}-\d{4}$/';
            }

            // Validate request with unique rules excluding current player
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:players,email,'.$id,
                'username' => 'required|string|max:255|unique:players,username,'.$id,
                'identity_type' => 'required|in:malaysia_ic,foreign_id',
                'identity_number' => $identityNumberRulesUpdate,
                'country_code' => ['nullable', 'string', 'max:4', 'regex:/^\d{1,4}$/'],
                'phone' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[0-9]{7,15}$/',
                    Rule::unique('players', 'phone')->ignore($id),
                ],
                'position' => 'required|in:Goalkeeper,Defender,Midfielder,Forward',
                'salary' => 'nullable|numeric|min:0',
                'club_ids' => 'nullable|array',
                'club_ids.*' => 'exists:club,id',
                'start' => 'nullable|date',
                'end' => 'nullable|date|after_or_equal:start',
                'status' => 'nullable|in:ACTIVE,INACTIVE,INVITED',
            ], [
                'identity_type.required' => 'Please select an identity type.',
                'identity_number.unique' => 'This identity number is already registered.',
                'identity_number.regex' => 'Malaysia IC must be in format XXXXXX-XX-XXXX (12 digits).',
                'phone.unique' => 'This phone number is already registered.',
                'username.unique' => 'This username is already taken.',
                'email.unique' => 'This email is already registered.',
                'end.after_or_equal' => 'End date must be after or equal to start date.',
            ]);
        }

        if (! $this->isClubManagerPlayerEditor()) {
            $player->name = $request->name;
            $player->email = $request->has('email') ? $request->email : null;
            $player->username = $request->username;
            $player->identity_number = $request->identity_number;
            $player->identity_type = $validated['identity_type'] ?? $player->identity_type;
            $player->position = $request->position;
        } else {
            $player->email = $request->has('email') ? $request->email : null;
        }
        $player->country_code = $validated['country_code'] ?? null;
        $player->phone = $validated['phone'] ?? null;
        $player->jersey_number = $request->jersey_number;

        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:1024',
            ]);

            $file = $request->file('avatar');
            $filename = time().'_'.$file->getClientOriginalName();
            $destination = public_path('avatars');

            if (! file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            if ($player->avatar && file_exists(public_path($player->avatar))) {
                unlink(public_path($player->avatar));
            }

            $file->move($destination, $filename);
            $player->avatar = 'avatars/'.$filename;
        }

        if ($request->password) {
            $player->password = Hash::make($request->password);
        }

        if ($request->status) {
            $player->status = $request->status;
        }

        $player->save();

        // Update roles (association-wide editors only)
        if (! $this->isClubManagerPlayerEditor()) {
            $player->roles()->detach();
            if ($request->roles) {
                $player->assignRole($request->roles);
            }
        }

        // Club assignment is changed via invite / bulk upload / terminate — not on this form
        if ($request->filled('club_ids')) {
            $oldClubIds = $player->clubs()->pluck('club.id')->map(fn ($id) => (int) $id)->all();
            $player->clubs()->sync($request->club_ids);
            $player->load('clubs');
            $newClubIds = $player->clubs->pluck('id')->map(fn ($id) => (int) $id)->all();
            $adminId = (int) auth()->id();
            foreach (array_diff($newClubIds, $oldClubIds) as $cid) {
                PlayerClubHistory::record($player->id, $cid, 'assigned', $adminId);
            }
            foreach (array_diff($oldClubIds, $newClubIds) as $cid) {
                PlayerClubHistory::record($player->id, $cid, 'removed', $adminId, __('Removed from club (admin update)'));
            }
        } else {
            $player->load('clubs');
        }

        // Check if player has any clubs assigned
        if ($player->clubs->isNotEmpty()) {
            // Update or create contract
            $contract = PlayerContract::where('player_id', $player->id)
                ->where('status', 'active')
                ->first();

            if (! $contract) {
                $contract = new PlayerContract;
                $contract->player_id = $player->id;
                $contract->status = 'active';
            }

            // Update contract details
            if ($request->filled('club_ids')) {
                $contract->club_id = (int) $request->club_ids[0];
            } elseif (! $contract->club_id) {
                $contract->club_id = (int) $player->clubs->first()->id;
            }

            // Default start date to today if not provided
            $startDate = $request->start ?? ($contract->start_date ?? date('Y-m-d'));

            // Default end date to 1 year from start if not provided
            $endDate = $request->end ?? date('Y-m-d', strtotime($startDate.' +1 year'));

            $contract->start_date = $startDate;
            $contract->end_date = $endDate;
            $contract->salary = $request->salary ?? 0;
            $contract->save();
        }
        // Send invitation if status is INVITED
        if ($player->status == 'INVITED') {
            $clubId = $request->filled('club_ids')
                ? (int) $request->club_ids[0]
                : (int) ($player->clubs->first()->id ?? 0);
            $clubId = $clubId > 0 ? $clubId : null;

            if ($clubId) {
                $clubObj = Club::find($clubId);
                $inviteContract = PlayerContract::where('player_id', $player->id)
                    ->where('status', 'active')
                    ->first();

                if ($clubObj && $inviteContract) {
                    $formattedStartDate = date('F j, Y', strtotime($inviteContract->start_date));
                    $formattedEndDate = date('F j, Y', strtotime($inviteContract->end_date));

                    $data = [
                        'action' => 'player_invitation',
                        'username' => $player->username,
                        'code' => $player->code,
                        'email' => $request->email,
                        'club_name' => $clubObj->name,
                        'start' => $formattedStartDate,
                        'end' => $formattedEndDate,
                        'name' => $request->name,
                    ];

                    // $this->sendWAlInvitation($player);
                }
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

        $data = [
            'action' => 'player_invitation',
            'username' => $player->username,
            'code' => $player->code,
            'email' => $player->email,
            'phone' => $player->phone,
            'club_name' => $clubName,
            'start' => '',
            'domain' => env('APP_URL'),
            'end' => '',
            'name' => $player->name,
        ];

        // Only send if player has phone number
        if ($player->phone) {
            $this->sendWAInvitation($data);
            session()->flash('success', 'WhatsApp invitation has been resent to '.$player->name);
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
            'identity_search_type' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('search_type') === 'identity_number'),
                'in:malaysia_ic,foreign_id',
            ],
        ], [
            'identity_search_type.required' => 'Please select Malaysia IC or Foreign ID before searching by identity number.',
        ]);

        $searchType = $request->search_type;
        $searchValue = trim((string) $request->search_value);

        if ($searchType === 'identity_number') {
            $identitySearchType = $request->input('identity_search_type');
            if ($identitySearchType === 'malaysia_ic') {
                $normalized = $this->normalizeMalaysiaIcNumber($searchValue);
                $digitsOnly = preg_replace('/\D/', '', $searchValue);
                $player = Player::query()
                    ->with('clubs')
                    ->where(function ($q) {
                        $q->where('identity_type', 'malaysia_ic')->orWhereNull('identity_type');
                    })
                    ->where(function ($q) use ($searchValue, $digitsOnly, $normalized) {
                        $q->where('identity_number', $searchValue)
                            ->orWhere('identity_number', $normalized);
                        if ($digitsOnly !== '') {
                            $q->orWhereRaw(
                                "REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(identity_number,''), '-', ''), ' ', ''), '.', ''), CHAR(9), '') = ?",
                                [$digitsOnly]
                            );
                        }
                    })
                    ->first();
            } else {
                $lower = mb_strtolower($searchValue, 'UTF-8');
                $player = Player::query()
                    ->with('clubs')
                    ->where('identity_type', 'foreign_id')
                    ->where(function ($q) use ($searchValue, $lower) {
                        $q->where('identity_number', $searchValue)
                            ->orWhere(DB::raw('LOWER(identity_number)'), $lower);
                    })
                    ->first();
            }
        } else {
            // Prefix match from start of name (strip LIKE wildcards from user input)
            $prefix = mb_strtolower($searchValue, 'UTF-8');
            $prefix = str_replace(['%', '_'], '', $prefix);
            if ($prefix === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Enter at least one letter or number to search by name.',
                ]);
            }
            $players = Player::query()
                ->with('clubs')
                ->where(DB::raw('LOWER(name)'), 'LIKE', $prefix.'%')
                ->orderBy('name')
                ->get();

            if ($players->count() > 1) {
                return response()->json([
                    'success' => false,
                    'multiple' => true,
                    'message' => 'Multiple players found. Use Invite for players without a club; others are shown for reference only.',
                    'players' => $players->map(fn ($p) => $this->playerInviteSearchPayload($p)),
                ]);
            }

            $player = $players->first();
        }

        if (! $player) {
            return response()->json([
                'success' => false,
                'message' => 'Player not found.',
            ]);
        }

        $payload = $this->playerInviteSearchPayload($player);

        return response()->json([
            'success' => true,
            'can_invite' => ! $payload['has_club'],
            'player' => $payload,
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
            'id_type',
            'identity_number',
            'country_code',
            'phone',
            'position',
            'market_value',
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for proper encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, $columns);

            // id_type: use exactly "Malaysia IC" or "Foreign ID" (see template note row in instructions)

            // Add example row 1 - Malaysia IC (strict XXXXXX-XX-XXXX; tab prefix helps Excel treat cells as text)
            fputcsv($file, [
                'John Doe',
                'Malaysia IC',
                "\t900101-01-1234",
                "\t60",
                "\t192201222",
                'Midfielder',
                "\t50",
            ]);

            // Add example row 2 - Malaysia IC
            fputcsv($file, [
                'Jane Smith',
                'Malaysia IC',
                "\t910202-02-1234",
                "\t65",
                "\t987654321",
                'Forward',
                "\t75",
            ]);

            // Add example row 3 - Foreign ID
            fputcsv($file, [
                'Alex Wong',
                'Foreign ID',
                'E12345678A',
                "\t62",
                "\t812345678",
                'Goalkeeper',
                "\t100",
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

        // Validate header (must match current template)
        $expectedHeaders = ['name', 'id_type', 'identity_number', 'country_code', 'phone', 'position', 'market_value'];

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
                $idTypeRaw = trim((string) ($row[1] ?? ''));
                $identityType = $this->parseBulkUploadIdType($idTypeRaw);
                if ($identityType === null) {
                    $errorCount++;
                    $uploadErrors[] = "Row {$rowNumber}: ID Type must be \"Malaysia IC\" or \"Foreign ID\" (got: ".($idTypeRaw !== '' ? $idTypeRaw : 'empty').').';

                    continue;
                }

                $identityRaw = trim(ltrim($row[2] ?? '', "'\t"));

                $data = [
                    'name' => trim($row[0] ?? ''),
                    'identity_number' => $identityRaw,
                    'country_code' => trim(ltrim($row[3] ?? '', "'\t")),
                    'phone' => trim(ltrim($row[4] ?? '', "'\t")),
                    'position' => trim($row[5] ?? ''),
                    'market_value' => trim(ltrim($row[6] ?? '', "'\t")),
                ];

                $identityRules = [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('players', 'identity_number'),
                ];
                if ($identityType === 'malaysia_ic') {
                    $identityRules[] = 'regex:/^\d{6}-\d{2}-\d{4}$/';
                }

                // Validate row
                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'identity_number' => $identityRules,
                    'country_code' => 'nullable|string|max:4|regex:/^\d{1,4}$/',
                    'phone' => 'nullable|string|max:20|unique:players,phone',
                    'position' => 'required|in:Goalkeeper,Defender,Midfielder,Forward',
                    'market_value' => 'required|integer|min:40|max:150',
                ], [
                    'identity_number.regex' => 'Malaysia IC must be in format XXXXXX-XX-XXXX.',
                    'identity_number.unique' => 'This identity number is already registered.',
                    'market_value.required' => 'Market value is required (40–150).',
                    'market_value.integer' => 'Market value must be a whole number between 40 and 150.',
                    'market_value.min' => 'Market value must be at least 40.',
                    'market_value.max' => 'Market value may not be greater than 150.',
                ]);

                if ($validator->fails()) {
                    $errorCount++;
                    $uploadErrors[] = "Row {$rowNumber}: ".implode(', ', $validator->errors()->all());

                    continue;
                }

                // Generate unique username
                $baseUsername = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($data['name']));
                $baseUsername = substr($baseUsername, 0, 10);
                $timestamp = substr((string) time(), -6);
                $username = $baseUsername.$timestamp;

                $counter = 1;
                while (Player::where('username', $username)->exists()) {
                    $username = $baseUsername.$timestamp.$counter;
                    $counter++;
                }

                // Create player
                $player = new Player;
                $player->name = $data['name'];
                $player->email = null;
                $player->identity_number = $data['identity_number'];
                $player->identity_type = $identityType;
                $player->country_code = ! empty($data['country_code']) ? $data['country_code'] : null;
                $player->phone = ! empty($data['phone']) ? $data['phone'] : null;
                $player->username = $username;
                $player->position = $data['position'];
                $player->code = rand(11111111111, 99999999999);
                $player->referer = auth()->user()->id;
                $player->password = Hash::make($this->randomPassword(6));
                $player->status = 'ACTIVE';
                $player->market_value = (int) $data['market_value'];
                $player->save();

                $player->clubs()->sync([$clubId]);

                PlayerClubHistory::record($player->id, $clubId, 'assigned', (int) auth()->id(), __('Bulk upload'));

                $contractStart = date('Y-m-d');
                $contractEnd = date('Y-m-d', strtotime($contractStart.' +1 year'));

                $contract = new PlayerContract;
                $contract->player_id = $player->id;
                $contract->club_id = $clubObj->id;
                $contract->start_date = $contractStart;
                $contract->end_date = $contractEnd;
                $contract->salary = 0;
                $contract->status = 'active';
                $contract->save();

                // Add to notification queue instead of sending immediately
                if (! empty($player->phone)) {
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
                        'name' => $player->name,
                    ];
                }

                $successCount++;
            }

            DB::commit();

            // Send notifications in background (after commit)
            if (! empty($playersToNotify)) {
                // Dispatch job to send WhatsApp messages in background
                dispatch(function () use ($playersToNotify) {
                    foreach ($playersToNotify as $data) {
                        try {
                            $this->sendWAInvitation($data);
                            sleep(5); // Sleep in background job, not blocking the request
                        } catch (\Exception $e) {
                            \Log::error('WhatsApp invitation failed: '.$e->getMessage());
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
                $message .= ', Skipped: '.count($skipped);
            }

            if (! empty($playersToNotify)) {
                $message .= '. WhatsApp invitations are being sent in the background.';
            }

            session()->flash('success', $message);

            if (! empty($uploadErrors)) {
                session()->flash('upload_errors', $uploadErrors);
            }

            if (! empty($skipped)) {
                session()->flash('skipped', $skipped);
            }

            return redirect()->route('admin.players.list');
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'An error occurred during upload: '.$e->getMessage());

            return back();
        }
    }

    public function updateMarketValue(Request $request, int $id): JsonResponse
    {
        $player = Player::findOrFail($id);
        if (! $this->adminMayEditPlayer($player)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($this->isClubManagerPlayerEditor()) {
            return response()->json(['message' => 'Not allowed to change player value.'], 403);
        }

        $request->validate([
            'market_value' => 'required|integer|min:40|max:150',
        ], [
            'market_value.min' => 'Market value must be at least 40.',
            'market_value.max' => 'Market value may not be greater than 150.',
        ]);

        $player->market_value = $request->market_value;
        $player->save();

        return response()->json([
            'success' => true,
            'message' => 'Market value updated.',
            'market_value' => $player->market_value,
        ]);
    }

    /**
     * Inline update from players list (name, identity_number, position, jersey, country code, phone, avatar, market_value).
     */
    public function updateInline(Request $request, int $id): JsonResponse
    {
        $player = Player::findOrFail($id);
        if (! $this->adminMayEditPlayer($player)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $phoneSanitized = preg_replace('/\D/', '', (string) $request->input('phone', ''));
        $request->merge(['phone' => $phoneSanitized === '' ? null : $phoneSanitized]);

        $ccRaw = preg_replace('/\D/', '', (string) $request->input('country_code', ''));
        $request->merge(['country_code' => $ccRaw === '' ? null : $ccRaw]);

        $jerseyRaw = $request->input('jersey_number');
        if ($jerseyRaw === '' || $jerseyRaw === null) {
            $request->merge(['jersey_number' => null]);
        }

        if ($this->isClubManagerPlayerEditor()) {
            $validated = $request->validate([
                'jersey_number' => 'nullable|integer|min:1|max:99999',
                'country_code' => ['nullable', 'string', 'in:60,65,62,84'],
                'phone' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[0-9]{7,15}$/',
                    Rule::unique('players', 'phone')->ignore($id),
                ],
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:1024',
            ], [
                'phone.unique' => 'This phone number is already registered to another player.',
                'phone.regex' => 'Enter 7–15 digits only (no spaces), or leave empty.',
            ]);

            $player->jersey_number = $validated['jersey_number'] ?? null;
            $player->country_code = $validated['country_code'] ?? null;
            $player->phone = $validated['phone'] ?? null;
        } else {
            $positionRaw = $request->input('position');
            $request->merge(['position' => ($positionRaw === '' || $positionRaw === null) ? null : $positionRaw]);

            $storedIdType = (string) ($player->identity_type ?? 'malaysia_ic');
            if ($storedIdType === 'malaysia_ic') {
                $request->merge(['identity_number' => $this->normalizeMalaysiaIcNumber($request->input('identity_number'))]);
            } else {
                $request->merge(['identity_number' => trim((string) $request->input('identity_number', ''))]);
            }

            $inlineIdentityRules = [
                'required',
                'string',
                'max:50',
                Rule::unique('players', 'identity_number')->ignore($id),
            ];
            if ($storedIdType === 'malaysia_ic') {
                $inlineIdentityRules[] = 'regex:/^\d{6}-\d{2}-\d{4}$/';
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'identity_number' => $inlineIdentityRules,
                'position' => ['nullable', 'string', Rule::in(['Goalkeeper', 'Defender', 'Midfielder', 'Forward'])],
                'jersey_number' => 'nullable|integer|min:1|max:99999',
                'country_code' => ['nullable', 'string', 'in:60,65,62,84'],
                'phone' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[0-9]{7,15}$/',
                    Rule::unique('players', 'phone')->ignore($id),
                ],
                'market_value' => 'required|integer|min:40|max:150',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:1024',
            ], [
                'identity_number.unique' => 'This identity number is already registered to another player.',
                'identity_number.regex' => 'Malaysia IC must be in format XXXXXX-XX-XXXX (12 digits).',
                'phone.unique' => 'This phone number is already registered to another player.',
                'phone.regex' => 'Enter 7–15 digits only (no spaces), or leave empty.',
                'market_value.min' => 'Market value must be at least 40.',
                'market_value.max' => 'Market value may not be greater than 150.',
            ]);

            $player->name = $validated['name'];
            $player->identity_number = $validated['identity_number'];
            $player->position = $validated['position'] ?? null;
            $player->jersey_number = $validated['jersey_number'] ?? null;
            $player->country_code = $validated['country_code'] ?? null;
            $player->phone = $validated['phone'] ?? null;
            $player->market_value = $validated['market_value'];
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time().'_'.$file->getClientOriginalName();
            $destination = public_path('avatars');
            if (! file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            if ($player->avatar && file_exists(public_path($player->avatar))) {
                unlink(public_path($player->avatar));
            }
            $file->move($destination, $filename);
            $player->avatar = 'avatars/'.$filename;
        }

        $player->save();

        return response()->json([
            'success' => true,
            'message' => 'Saved.',
            'avatar_url' => $this->resolvePlayerPublicAvatarUrl($player->avatar),
        ]);
    }

    /**
     * POST phone_number + message to n8n (WhatsApp / messaging webhook).
     */
    public function sendPlayerInvitation(Request $request, int $id): JsonResponse
    {
        $player = Player::with('clubs')->findOrFail($id);
        if (! $this->adminMayEditPlayer($player)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $countryCode = preg_replace('/\D/', '', (string) $request->input('country_code', ''));
        $phoneDigits = preg_replace('/\D/', '', (string) $request->input('phone', ''));

        if (! in_array($countryCode, ['60', '65', '62', '84'], true)) {
            return response()->json(['message' => 'Select a valid country code.'], 422);
        }

        if (strlen($phoneDigits) < 7 || strlen($phoneDigits) > 15) {
            return response()->json(['message' => 'Enter a valid phone number (7–15 digits) before sending.'], 422);
        }

        if (! $this->tryPostN8nLoginProfileMessage($player, $countryCode, $phoneDigits)) {
            $url = (string) config('services.n8n.send_message_url');
            if ($url === '') {
                return response()->json(['message' => 'Messaging webhook is not configured.'], 503);
            }

            return response()->json([
                'message' => 'Invitation could not be sent. Please try again.',
            ], 502);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invitation sent.',
        ]);
    }

    /**
     * Assign player (no clubs) to the current admin's clubs + optional n8n login nudge when phone is valid.
     */
    public function quickInviteAssign(Request $request): JsonResponse
    {
        $this->checkAuthorization(auth()->user(), ['players.view']);

        $request->validate([
            'player_id' => 'required|integer|exists:players,id',
        ]);

        $user = auth()->user();
        $userClubs = $user->clubs()->orderBy('name')->get();
        if ($userClubs->isEmpty()) {
            return response()->json(['message' => 'You have no clubs assigned.'], 403);
        }

        $clubIds = $userClubs->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $primaryClubId = $clubIds[0];

        $player = Player::findOrFail((int) $request->input('player_id'));
        if ($player->clubs()->exists()) {
            return response()->json(['message' => 'This player already belongs to a club.'], 422);
        }

        $player->clubs()->syncWithoutDetaching($clubIds);

        $adminId = (int) auth()->id();
        foreach ($clubIds as $cid) {
            PlayerClubHistory::record($player->id, $cid, 'assigned', $adminId, __('Quick invite from search'));
        }

        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime($startDate.' +1 year'));

        $contract = PlayerContract::where('player_id', $player->id)
            ->where('club_id', $primaryClubId)
            ->first();

        if (! $contract) {
            $contract = new PlayerContract;
            $contract->player_id = $player->id;
            $contract->club_id = $primaryClubId;
        }

        $contract->start_date = $startDate;
        $contract->end_date = $endDate;
        $contract->salary = 0;
        $contract->status = 'active';
        $contract->save();

        $player->save();

        $player->refresh();
        $player->load('clubs');

        $invitationSent = false;
        $countryCode = preg_replace('/\D/', '', (string) ($player->country_code ?? ''));
        $phoneDigits = preg_replace('/\D/', '', (string) ($player->phone ?? ''));
        if (in_array($countryCode, ['60', '65', '62', '84'], true)
            && strlen($phoneDigits) >= 7
            && strlen($phoneDigits) <= 15) {
            $invitationSent = $this->tryPostN8nLoginProfileMessage($player, $countryCode, $phoneDigits);
        }

        $msg = $invitationSent
            ? 'Player added to your club(s). Login reminder sent by message.'
            : 'Player added to your club(s). No message sent (add a valid phone and country code for +60/+65/+62/+84 to send the login reminder).';

        return response()->json([
            'success' => true,
            'message' => $msg,
            'invitation_sent' => $invitationSent,
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
        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('identity_number', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%");
            });
        }

        // Filter by position
        if ($request->has('position') && ! empty($request->position)) {
            $query->where('position', $request->position);
        }

        // Filter by status
        if ($request->has('status') && ! empty($request->status)) {
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

        $user = auth()->user();

        $phoneDigits = preg_replace('/\D/', '', (string) $request->input('phone', ''));
        $ccDigits = preg_replace('/\D/', '', (string) $request->input('country_code', ''));
        $request->merge([
            'phone' => $phoneDigits === '' ? null : $phoneDigits,
            'country_code' => $ccDigits === '' ? null : $ccDigits,
        ]);

        $request->validate([
            'player_id' => 'required|exists:players,id',
            'club_ids' => 'required|array|min:1',
            'club_ids.*' => 'exists:club,id',
            'salary' => 'nullable|numeric|min:0',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'country_code' => ['nullable', 'required_with:phone', 'string', 'max:4', 'regex:/^\d{1,4}$/'],
            'phone' => [
                'nullable',
                'required_with:country_code',
                'string',
                'max:20',
                'regex:/^[0-9]{7,15}$/',
                Rule::unique('players', 'phone')->ignore((int) $request->input('player_id')),
            ],
        ], [
            'country_code.required_with' => 'Enter a country code when providing a phone number.',
            'phone.required_with' => 'Enter a phone number when providing a country code.',
            'phone.unique' => 'This phone number is already registered to another player.',
        ]);

        $player = Player::findOrFail((int) $request->player_id);

        // Validate that user can only assign their own clubs
        $userClubIds = $user->clubs->pluck('id')->toArray();
        $requestedClubIds = $request->club_ids;

        // Check if all requested clubs belong to the user
        $unauthorizedClubs = array_diff($requestedClubIds, $userClubIds);

        if (! empty($unauthorizedClubs)) {
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
        if ($request->has('club_ids') && ! empty($request->club_ids)) {
            $existingClubIds = $player->clubs()->pluck('club.id')->map(fn ($id) => (int) $id)->all();
            $player->clubs()->syncWithoutDetaching($request->club_ids);
            $requestedIds = array_map('intval', $request->club_ids);
            foreach (array_diff($requestedIds, $existingClubIds) as $cid) {
                PlayerClubHistory::record($player->id, $cid, 'assigned', (int) auth()->id(), __('Invite — assigned to club'));
            }
        }

        if ($request->filled('phone')) {
            $player->phone = (string) $request->phone;
            $player->country_code = $request->country_code;
        }

        // Update player status to INVITED
        // $player->status = 'INVITED';
        $player->save();

        // Create or update contract
        $clubId = $request->club_ids[0];
        $clubObj = Club::find($clubId);

        // Default end date to 1 year from start if not provided
        $startDate = $request->start;
        $endDate = $request->end ?? date('Y-m-d', strtotime($startDate.' +1 year'));

        $contract = PlayerContract::where('player_id', $player->id)
            ->where('club_id', $clubId)
            ->first();

        if (! $contract) {
            $contract = new PlayerContract;
            $contract->player_id = $player->id;
            $contract->club_id = $clubId;
        }

        $contract->start_date = $startDate;
        $contract->end_date = $endDate;
        $contract->salary = $request->salary ?? 0;
        $contract->status = 'active';
        $contract->save();

        // Send invitation email
        $formattedStartDate = date('F j, Y', strtotime($contract->start_date));
        $formattedEndDate = date('F j, Y', strtotime($contract->end_date));

        $data = [
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
            'name' => $player->name,
        ];
        $this->sendWAInvitation($data);

        session()->flash('success', 'Player has been invited successfully!');

        return redirect()->route('admin.players.invite');
    }

    public function playerClubHistoryPerformance(int $player): JsonResponse
    {
        $playerModel = Player::query()->findOrFail($player);
        if (! $this->adminMayViewPlayerClubHistory()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $history = PlayerClubHistory::query()
            ->where('player_id', $playerModel->id)
            ->with(['club:id,name', 'admin:id,name'])
            ->orderByDesc('event_at')
            ->limit(200)
            ->get()
            ->map(function (PlayerClubHistory $h) {
                $label = match ($h->event_type) {
                    'assigned' => __('Joined club'),
                    'terminated' => __('Contract terminated'),
                    'removed' => __('Removed from club'),
                    default => $h->event_type,
                };

                return [
                    'event_type' => $h->event_type,
                    'event_label' => $label,
                    'club_name' => $h->club?->name ?? '—',
                    'event_at' => $h->event_at?->format('Y-m-d H:i'),
                    'remark' => $h->remark,
                    'admin_name' => $h->admin?->name,
                ];
            });

        return response()->json([
            'player' => [
                'id' => $playerModel->id,
                'name' => $playerModel->name,
            ],
            'history' => $history,
            'performance' => $this->buildPlayerMatchPerformanceSummary($playerModel->id),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPlayerMatchPerformanceSummary(int $playerId): array
    {
        if (! Schema::hasTable('match_events') || ! Schema::hasTable('match') || ! Schema::hasTable('club')) {
            return [
                'available' => false,
                'totals' => [],
                'recent' => [],
                'message' => __('Match statistics are not available.'),
            ];
        }

        $totals = DB::table('match_events')
            ->where('player_id', $playerId)
            ->selectRaw('event_type, COUNT(*) as c')
            ->groupBy('event_type')
            ->pluck('c', 'event_type')
            ->all();

        $select = [
            'me.event_type',
            'me.minute_in_match',
            'm.date',
            'm.home_club_id',
            'm.away_club_id',
            'me.club_id',
            'hc.name as home_name',
            'ac.name as away_name',
        ];

        $query = DB::table('match_events as me')
            ->join('match as m', 'm.id', '=', 'me.match_id')
            ->leftJoin('club as hc', 'hc.id', '=', 'm.home_club_id')
            ->leftJoin('club as ac', 'ac.id', '=', 'm.away_club_id')
            ->where('me.player_id', $playerId)
            ->orderByDesc('m.date')
            ->orderByDesc('me.minute_in_match')
            ->limit(40);

        if (Schema::hasTable('competition')) {
            $query->leftJoin('competition as comp', 'comp.id', '=', 'm.competition_id');
            $select[] = 'comp.name as competition_name';
        }

        $recent = $query->select($select)->get()->map(function ($row) {
            $opponent = '—';
            $cid = (int) $row->club_id;
            if ($cid === (int) $row->home_club_id) {
                $opponent = (string) ($row->away_name ?? '—');
            } elseif ($cid === (int) $row->away_club_id) {
                $opponent = (string) ($row->home_name ?? '—');
            }

            $ts = $row->date;
            $matchDate = null;
            if (is_numeric($ts)) {
                $matchDate = date('Y-m-d', (int) $ts);
            } elseif (is_string($ts) && $ts !== '') {
                $parsed = strtotime($ts);
                $matchDate = $parsed ? date('Y-m-d', $parsed) : null;
            }

            $eventLabel = match ($row->event_type) {
                'goal' => __('Goal'),
                'assist' => __('Assist'),
                'sub_in' => __('Substituted in'),
                'sub_out' => __('Substituted out'),
                'own_goal' => __('Own goal'),
                default => (string) $row->event_type,
            };

            return [
                'event_type' => $row->event_type,
                'event_label' => $eventLabel,
                'minute_in_match' => $row->minute_in_match,
                'match_date' => $matchDate,
                'competition' => isset($row->competition_name) ? $row->competition_name : null,
                'opponent' => $opponent,
            ];
        });

        return [
            'available' => true,
            'totals' => $totals,
            'recent' => $recent->values()->all(),
        ];
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
