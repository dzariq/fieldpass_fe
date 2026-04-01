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
use Illuminate\Http\UploadedFile;
use Throwable;


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
            $file = $request->file('avatar');
            if ($file instanceof UploadedFile) {
                $early = $this->respondIfUploadInvalid($file, 'avatar', 'Avatar');
                if ($early !== null) {
                    return $early;
                }
            }

            $request->validate([
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:1028' // 2MB = 2048 KB
            ]);
            $file = $request->file('avatar');

            $filename = time() . '_' . $file->getClientOriginalName();
            $destination = public_path('avatars');

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true); // create if doesn't exist
            }

            try {
                $file->move($destination, $filename);
            } catch (Throwable $e) {
                Log::error('Club avatar move failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                $msg = 'Avatar could not be saved: ' . $e->getMessage() . ' (check that ' . $destination . ' exists and is writable).';

                return back()->withInput()
                    ->withErrors(['avatar' => $msg])
                    ->with('upload_alert', $msg);
            }

            $club->avatar = 'avatars/' . $filename; // save relative URL
        }

        if ($request->hasFile('jersey')) {
            $file = $request->file('jersey');
            if ($file instanceof UploadedFile) {
                $early = $this->respondIfUploadInvalid($file, 'jersey', 'Jersey');
                if ($early !== null) {
                    return $early;
                }
            }

            $request->validate([
                'jersey' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:1028' // 2MB = 2048 KB
            ]);
            $file = $request->file('jersey');

            $filename = time() . '_' . $file->getClientOriginalName();
            $destination = public_path('avatars');

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true); // create if doesn't exist
            }

            try {
                $file->move($destination, $filename);
            } catch (Throwable $e) {
                Log::error('Club jersey move failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                $msg = 'Jersey could not be saved: ' . $e->getMessage() . ' (check that ' . $destination . ' exists and is writable).';

                return back()->withInput()
                    ->withErrors(['jersey' => $msg])
                    ->with('upload_alert', $msg);
            }

            $club->jersey = 'avatars/' . $filename; // save relative URL
        }

        $club->save();

        session()->flash('success', 'Club has been updated.');
        return back();
    }

    /**
     * When PHP rejects the upload (size, tmp dir, etc.), Laravel's "uploaded" rule only shows a generic message.
     * Return a redirect with the real reason + optional browser alert for production debugging.
     */
    private function respondIfUploadInvalid(UploadedFile $file, string $errorKey, string $label): ?RedirectResponse
    {
        if ($file->isValid()) {
            return null;
        }

        $detail = $this->uploadFileDiagnosticMessage($file);
        Log::warning('Club file upload invalid', [
            'field' => $errorKey,
            'error_code' => $file->getError(),
            'detail' => $detail,
        ]);

        $msg = $label . ' upload failed: ' . $detail;

        return back()->withInput()
            ->withErrors([$errorKey => $msg])
            ->with('upload_alert', $msg);
    }

    private function uploadFileDiagnosticMessage(UploadedFile $file): string
    {
        $code = $file->getError();
        $phpMsg = match ($code) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds server upload_max_filesize (php.ini).',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds HTML form MAX_FILE_SIZE.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server is missing a temporary folder (upload_tmp_dir).',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
            default => 'Unknown PHP upload error code: ' . $code,
        };

        if (method_exists($file, 'getErrorMessage')) {
            try {
                return $phpMsg . ' Symfony: ' . $file->getErrorMessage();
            } catch (Throwable) {
                // ignore
            }
        }

        return $phpMsg;
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
