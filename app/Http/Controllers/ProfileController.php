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
use Storage;// Tambahkan ini untuk transaksi

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $biodata = $user->biodata;
        $location = $user->locations()->first();

        return view('page.profile.show', compact('user', 'biodata', 'location'));
    }

    public function edit()
    {
        $user = Auth::user();
        $biodata = $user->biodata;
        $userLocations = $user->locations()->pluck('location_id')->toArray();
        $locations = Location::where('type', 'desa')->get();
$biodata?->foto_url;
        return view('page.profile.edit', compact('user', 'biodata', 'userLocations', 'locations'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi file gambar
            'nik' => 'nullable|string|max:20',
            'jabatan' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'locations' => 'nullable|array',
            'locations.*' => 'exists:locations,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // --- Perubahan Utama: Logika untuk upload foto sekarang di UserBiodata ---
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                // Ambil biodata user, buat jika belum ada
                $biodata = $user->biodata()->firstOrCreate(['user_id' => $user->id]);

                // Hapus foto lama jika ada
                if ($biodata->foto) {
                    Storage::delete('profil/' . $biodata->foto); // Sesuaikan folder jika berbeda
                }

                // Simpan foto baru
                $fotoFile = $request->file('foto');
                $fotoPath = $fotoFile->store('profil', 'public'); // Simpan di storage/app/public/profil

                // Ambil nama file saja dari path yang dikembalikan
                $fotoName = basename($fotoPath);

                // Update biodata dengan nama file foto
                $biodata->update(['foto' => $fotoName]);
            }
            // ---

            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            // Update/Create biodata (tanpa foto, karena sudah ditangani di atas)
            $user->biodata()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => $request->nik,
                    'jabatan' => $request->jabatan,
                    'alamat' => $request->alamat,
                    'no_hp' => $request->no_hp,
                    // Jangan sertakan 'foto' di sini karena sudah di-update sebelumnya
                ]
            );

            // Gunakan sync() untuk Many-to-Many
            $user->locations()->sync($request->input('locations', []));

            DB::commit();

            return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update Profile Error: ' . $e->getMessage());
            \Log::error('Update Profile Trace: ', $e->getTraceAsString());
            return redirect()->back()->with('error', 'Gagal memperbarui profil. Detail: ' . $e->getMessage());
        }
    }
}