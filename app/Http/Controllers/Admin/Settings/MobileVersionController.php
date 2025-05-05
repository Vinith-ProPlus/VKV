<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\MobileVersionRequest;
use App\Models\MobileVersion;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MobileVersionController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(): View|Factory|Application
    {
        $this->authorize('Edit Mobile Versions');
        $version = MobileVersion::first();
        return view('admin.settings.mobile_versions.version', compact('version'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(MobileVersionRequest $request): RedirectResponse
    {
        $this->authorize('Edit Mobile Versions');
        DB::beginTransaction();
        try {
            $version = MobileVersion::first();

            if (!$version) {
                $version = new MobileVersion();
            }

            $data = $request->validated();

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old file if exists
                if ($version->logo && Storage::exists($version->logo)) {
                    Storage::delete($version->logo);
                }

                // Store new file
                $data['logo'] = $request->file('logo')?->store('uploads/mobile_version', 'public');
            }

            $version->fill($data);
            $version->save();

            DB::commit();
            return redirect()->route('mobile_version.index')->with('success', 'Mobile Version updated successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            info('Error::Place@MobileVersionController@update - ' . $exception->getMessage());
            return redirect()->back()->withInput()->with("warning", "Something went wrong: " . $exception->getMessage());
        }
    }
}
