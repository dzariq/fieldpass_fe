<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\Competition;

use App\Models\CompetitionClub;
use App\Notifications\NewCompetitionInviteNotification;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionRequest;
use App\Models\Association;
use App\Models\Club;
use App\Models\Competition;
use App\Models\Admin;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http as FacadesHttp;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use League\Uri\Http;

class CompetitionController extends Controller
{
    public function index(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['competition.view']);

        $admin_obj = Admin::find(auth()->user()->id);
        $associationIds = $admin_obj->associations->pluck('id')->toArray();

        if (count($associationIds) == 0) {
            return view('backend.pages.competitions.index', [
                'competitions' => Competition::all(),
            ]);
        } else {
            $admin_obj = Admin::find(auth()->user()->id);
            $associationIds = $admin_obj->associations->pluck('id')->toArray();

            return view('backend.pages.competitions.index', [
                'competitions' => Competition::whereIn('association_id', $associationIds)->get(),
            ]);
        }
    }

    public function approveWithPayment(Request $request)
    {
        $this->checkAuthorization(auth()->user(), ['competition.details']);

        $inviteId = $request->invite_id;
        $invite = CompetitionClub::findOrFail($inviteId);
        $competition = $invite->competition;
        $price = $competition->price ?? 0;

        // Verify price is greater than 2.00
        if ($price <= 2.00) {
            session()->flash('error', 'Payment not required for this competition.');
            return redirect()->route('admin.competition.invites.index');
        }

        $user = auth()->user();
        $club = $user->clubs->first();

        // Prepare webhook data
        $webhookData = [
            'invite_id' => $inviteId,
            'competition_id' => $competition->id,
            'competition_name' => $competition->name,
            'competition_club_id' => $invite->id,
            'admin_id' => $user->id,
            'admin_name' => $user->name,
            'admin_email' => $user->email,
            'club_id' => $club ? $club->id : null,
            'club_name' => $club ? $club->name : null,
            'fee' => $price,
            'status' => 'pending',
            'name' => $user->name,
            'email' => $user->email ? $user->email : 'admin@fieldpass.com.my',
            'phone' => str_replace('+', '', $user->country_code) . $user->phone,
            'order_id' => $competition->id . '-' . $club->id,
            'detail' => 'Competition Entry Fee',
            'currency' => 'MYR',
            'timestamp' => now()->toIso8601String(),
        ];

        try {
            // Call n8n webhook
            $response = FacadesHttp::timeout(30)->post('https://n8n.fieldpass.com.my/webhook/competition-fee', $webhookData);

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info("Competition Payment | Webhook success for invite: {$inviteId}", [
                    'response' => $responseData
                ]);


                // Check if webhook returns a redirect URL
                if (isset($responseData[0]['paymentUrl']) && !empty($responseData[0]['paymentUrl'])) {
                    // Redirect to payment gateway or external URL
                    Log::info("Competition Payment | Redirecting to: " . $responseData[0]['paymentUrl']);
                    return redirect()->away($responseData[0]['paymentUrl']);
                } else {
                    // No redirect URL, payment completed
                    session()->flash('error', 'Payment unable to proceed! Please try again later.');
                    return redirect()->route('admin.competition.invites.index');
                }
            } else {
                Log::error("Competition Payment | Webhook failed for invite: {$inviteId}", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                session()->flash('error', 'Payment processing failed. Please try again or contact support.');
                return back();
            }
        } catch (\Exception $e) {
            Log::error("Competition Payment | Exception for invite: {$inviteId}", [
                'error' => $e->getMessage()
            ]);

            session()->flash('error', 'An error occurred during payment processing: ' . $e->getMessage());
            return back();
        }
    }

    public function create(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['competition.create']);

        return view('backend.pages.competitions.create', [
            'associations' => Association::all(),
        ]);
    }

    public function store(CompetitionRequest $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['competition.create']);

        $competition = new Competition();
        $competition->name = $request->name;
        $competition->price = $request->price;
        $competition->start = strtotime($request->start);
        $competition->end = strtotime($request->end);
        $competition->type = $request->type;
        $competition->max_participants = $request->max_participants;
        $competition->description = $request->description;
        $competition->association_id = $request->association_id;

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

            $competition->avatar = 'avatars/' . $filename; // save relative URL
        }

        $competition->save();

        session()->flash('success', __('Competition has been created.'));
        return redirect()->route('admin.competitions.index');
    }

    public function edit(int $id): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['competition.edit']);
        $competition = Competition::findOrFail($id);

        $clubs =  Club::where('association_id', $competition->association_id)->get();

        return view('backend.pages.competitions.edit', [
            'competition' => $competition,
            'associations' => Association::all(),
            'clubs' => $clubs
        ]);
    }

    public function update(CompetitionRequest $request, int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['competition.edit']);

        $competition = Competition::findOrFail($id);
        $competition->name = $request->name;
        $competition->association_id = $request->association_id;
        $competition->start = strtotime($request->start);
        $competition->end = strtotime($request->end);
        $competition->max_participants = $request->max_participants;
        $competition->description = $request->description;
        $competition->price = $request->price;

        // Handle Banner Upload
        if ($request->hasFile('banner')) {
            $request->validate([
                'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            $file = $request->file('banner');
            $filename = time() . '_banner_' . $file->getClientOriginalName();
            $destination = public_path('avatars');

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            // Delete old banner if exists
            if ($competition->banner && file_exists(public_path($competition->banner))) {
                unlink(public_path($competition->banner));
            }

            $file->move($destination, $filename);
            $competition->banner = 'avatars/' . $filename;
        }

        // Handle Pitch Image Upload
        if ($request->hasFile('pitch_image')) {
            $request->validate([
                'pitch_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            $file = $request->file('pitch_image');
            $filename = time() . '_pitch_' . $file->getClientOriginalName();
            $destination = public_path('avatars');

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            // Delete old pitch image if exists
            if ($competition->pitch_image && file_exists(public_path($competition->pitch_image))) {
                unlink(public_path($competition->pitch_image));
            }

            $file->move($destination, $filename);
            $competition->pitch_image = 'avatars/' . $filename;
        }

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            $file = $request->file('avatar');
            $filename = time() . '_avatar_' . $file->getClientOriginalName();
            $destination = public_path('avatars');

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            // Delete old avatar if exists
            if ($competition->avatar && file_exists(public_path($competition->avatar))) {
                unlink(public_path($competition->avatar));
            }

            $file->move($destination, $filename);
            $competition->avatar = 'avatars/' . $filename;
        }

        $competition->save();

        Log::info("BOARD | Processing club invitations | Has club_ids: " . json_encode($request->has('club_ids')));

        // Handle Club Invitations
        if ($request->has('club_ids') && !empty($request->club_ids)) {
            // Sync clubs
            $results = $competition->clubs()->sync($request->club_ids, false);

            // Send invitations to newly attached clubs
            if (!empty($results['attached'])) {
                Log::info("BOARD | New clubs attached: " . count($results['attached']));

                foreach ($results['attached'] as $clubId) {
                    $invitation = CompetitionClub::where('status', 'INVITED')
                        ->where('competition_id', $competition->id)
                        ->where('club_id', $clubId)
                        ->first();

                    if ($invitation) {
                        $clubObj = Club::find($invitation->club_id);
                        Log::info("BOARD | Processing club id | " . $clubId);

                        if ($clubObj) {
                            Log::info("BOARD | Processing club name | " . $clubObj->name);

                            foreach ($clubObj->admins as $clubAdmin) {
                                $pendingCount = 0;

                                // Send notification
                                $pendingCount = $clubObj->competitions()
                                    ->where('competition_club.status', 'INVITED')
                                    ->where('club_id', $clubObj->id)
                                    ->count();

                                Log::info("BOARD | club admin id | " . $clubAdmin->id);
                                Log::info("BOARD | pending count: | " . $pendingCount);

                                $clubAdmin->notify(new NewCompetitionInviteNotification($pendingCount));

                                $data = array(
                                    'name' => $clubAdmin->name,
                                    'domain' => env('APP_URL'),
                                    'username' => $clubAdmin->username,
                                    'action' => 'competition_invitation',
                                    'phone' => str_replace('+', '', $clubAdmin->country_code) . $clubAdmin->phone,
                                    'date' => date("F j, Y", $competition->start),
                                    'club_name' => $clubObj->name,
                                    'competition' => $competition->name
                                );
                                $this->sendWAInvitation($data);
                                sleep(5);
                            }
                        }
                    }
                }
            }
        }

        // Handle Re-invite Pending Clubs
        if ($request->has('reinvite_pending') && $request->reinvite_pending == 1) {
            Log::info("BOARD | Re-inviting pending clubs");

            // Get all clubs with INVITED status
            $invitedClubs = CompetitionClub::where('competition_id', $competition->id)
                ->where('status', 'INVITED')
                ->get();

            $reinviteCount = 0;

            foreach ($invitedClubs as $invitation) {
                $clubObj = Club::find($invitation->club_id);

                if ($clubObj) {
                    Log::info("BOARD | Re-inviting club name | " . $clubObj->name);

                    foreach ($clubObj->admins as $clubAdmin) {
                        $pendingCount = 0;

                        // Send notification
                        $pendingCount = $clubObj->competitions()
                            ->where('competition_club.status', 'INVITED')
                            ->where('club_id', $clubObj->id)
                            ->count();

                        Log::info("BOARD | club admin id | " . $clubAdmin->id);
                        Log::info("BOARD | pending count: | " . $pendingCount);

                        $clubAdmin->notify(new NewCompetitionInviteNotification($pendingCount));

                        $data = array(
                            'name' => $clubAdmin->name,
                            'domain' => env('APP_URL'),
                            'username' => $clubAdmin->username,
                            'action' => 'competition_invitation',
                            'phone' => str_replace('+', '', $clubAdmin->country_code) . $clubAdmin->phone,
                            'date' => date("F j, Y", $competition->start),
                            'club_name' => $clubObj->name,
                            'competition' => $competition->name
                        );
                        $this->sendWAInvitation($data);
                        sleep(5);
                        $reinviteCount++;
                    }
                }
            }

            if ($reinviteCount > 0) {
                session()->flash('success', "Competition updated successfully. Re-invitations sent to {$reinviteCount} club admin(s).");
                return back();
            }
        }

        session()->flash('success', 'Competition has been updated successfully.');
        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['competition.delete']);

        $competition = Competition::findOrFail($id);
        $competition->delete();
        session()->flash('success', 'Competition has been deleted.');
        return back();
    }

    public function invites()
    {
        $this->checkAuthorization(auth()->user(), ['competition.manage_invites']);

        $adminObj = Admin::find(auth()->user()->id);

        $clubIds = $adminObj->clubs()->pluck('club_id')->toArray();

        return view('backend.pages.competitions.invites', [
            'invites' => CompetitionClub::where('status', 'INVITED')->whereIn('club_id', $clubIds)->get(),
        ]);
    }

    public function approve($id)
    {
        $this->checkAuthorization(auth()->user(), ['competition.manage_invites']);

        CompetitionClub::find($id)?->update([
            'status' => 'ACTIVE',
            // Add more columns as needed
        ]);

        return redirect()->route('admin.competition.invites.index')->with('success', 'You have successfully joined competition.');
    }

    public function reject($id)
    {
        $this->checkAuthorization(auth()->user(), ['competition.manage_invites']);

        CompetitionClub::find($id)?->update([
            'status' => 'REJECTED',
            // Add more columns as needed
        ]);

        return redirect()->route('admin.competition.invites.index')->with('success', 'You have successfully rejected to join competition.');
    }

    public function details($id)
    {
        $this->checkAuthorization(auth()->user(), ['competition.details']);

        return view('backend.pages.competitions.details', [
            'competition' => Competition::find($id),
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
            Log::error('Email invitation webhook failed', [
                'http_code' => $httpCode,
                'response' => $response,
                'data' => $data
            ]);

            return [
                'success' => false,
                'error' => 'HTTP Error: ' . $httpCode,
                'response' => $response,
                'http_code' => $httpCode
            ];
        }
    }
}
