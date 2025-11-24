<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserBiodata;
use App\Models\UserLocation;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $biodata = $user->biodata;

        $userRole = $user->getRoleNames()->first() ?? '';
        $location = null;

        if ($userRole === 'SUPER ADMIN – KECAMATAN') {
            $location = $user->userLocations()
                ->whereHas('location', fn($q) => $q->where('type', 'kecamatan'))
                ->with('location')
                ->first()?->location;

            $desaTerkait = $user->userLocations()
                ->whereHas('location', fn($q) => $q->where('type', 'desa'))
                ->with('location')
                ->get()
                ->pluck('location.name')
                ->join(', ');
        } else {
            $location = $user->userLocations()
                ->whereHas('location', fn($q) => $q->where('type', 'desa'))
                ->with('location')
                ->first()?->location;

            $desaTerkait = null;
        }

        return view('page.profile.show', compact('user', 'biodata', 'location', 'desaTerkait', 'userRole'));
    }

    public function edit()
    {
        $user = Auth::user();
        $biodata = $user->biodata;
        $userRole = $user->getRoleNames()->first() ?? '';

        $userLocationRecords = $user->userLocations()->with('location')->get();

        $userDesaIds = $userLocationRecords
            ->where('location.type', 'desa')
            ->pluck('location_id')
            ->toArray();

        $userKecamatanId = $userLocationRecords
            ->where('location.type', 'kecamatan')
            ->first()?->location_id;

        $kecamatans = Location::where('type', 'kecamatan')->get();

        if ($userKecamatanId) {
            $desas = Location::where('type', 'desa')->where('parent_id', $userKecamatanId)->get();
        } else {
            $desas = Location::where('type', 'desa')->get();
        }

        return view('page.profile.edit', compact(
            'user', 'biodata', 'userDesaIds', 'userKecamatanId',
            'kecamatans', 'desas', 'userRole'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->getRoleNames()->first() ?? '';

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'nik' => 'nullable|string|max:20',
            'jabatan' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
        ];

        if ($userRole === 'SUPER ADMIN – KECAMATAN') {
            $rules['kecamatan'] = 'required|exists:locations,id';
        } else {
            $rules['locations'] = 'nullable|array';
            $rules['locations.*'] = 'exists:locations,id';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            if ($request->hasFile('foto')) {
                $biodata = $user->biodata()->firstOrCreate(['user_id' => $user->id]);

                if ($biodata->foto) {
                    Storage::disk('public')->delete('profil/' . $biodata->foto);
                }

                $fotoPath = $request->file('foto')->store('profil', 'public');
                $fotoName = basename($fotoPath);

                $biodata->update(['foto' => $fotoName]);
            }

            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            $user->biodata()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => $request->nik,
                    'jabatan' => $request->jabatan,
                    'alamat' => $request->alamat,
                    'no_hp' => $request->no_hp,
                ]
            );

            if ($userRole === 'SUPER ADMIN – KECAMATAN') {
                UserLocation::where('user_id', $user->id)->delete();

                $selectedKecamatanId = $request->input('kecamatan');

                UserLocation::create([
                    'user_id' => $user->id,
                    'location_id' => $selectedKecamatanId,
                ]);

                $desaIds = Location::where('parent_id', $selectedKecamatanId)
                    ->where('type', 'desa')
                    ->pluck('id')
                    ->toArray();

                foreach ($desaIds as $desaId) {
                    UserLocation::create([
                        'user_id' => $user->id,
                        'location_id' => $desaId,
                    ]);
                }
            } else {
                $user->userLocations()->delete();

                $selectedLocations = $request->input('locations', []);

                foreach ($selectedLocations as $locId) {
                    UserLocation::create([
                        'user_id' => $user->id,
                        'location_id' => $locId,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui profil. Detail: ' . $e->getMessage());
        }
    }
}
