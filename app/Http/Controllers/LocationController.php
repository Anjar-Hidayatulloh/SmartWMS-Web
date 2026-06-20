<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $zone = $request->input('zone');

        $query = Location::latest();

        if ($search) {
            $query->where('bin_code', 'like', "%{$search}%");
        }

        if ($zone) {
            $query->where('zone', $zone);
        }

        $locations = $query->paginate(15)->withQueryString();
        
        // Get unique zones for filter
        $zones = Location::select('zone')->distinct()->pluck('zone');

        return view('master.locations', compact('locations', 'zones', 'search', 'zone'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bin_code' => ['required', 'string', 'max:100', 'unique:locations,bin_code'],
            'zone' => ['required', 'string', 'max:100'],
        ]);

        Location::create($validated);

        return redirect()->route('master.locations.index')
            ->with('success', 'Lokasi bin baru berhasil dibuat.');
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'bin_code' => ['required', 'string', 'max:100', Rule::unique('locations', 'bin_code')->ignore($location->id)],
            'zone' => ['required', 'string', 'max:100'],
        ]);

        $location->update($validated);

        return redirect()->route('master.locations.index')
            ->with('success', 'Data lokasi bin berhasil diperbarui.');
    }

    public function destroy(Location $location)
    {
        // Prevent deletion if active stock exists
        if ($location->stocks()->where('qty', '>', 0)->exists()) {
            return redirect()->route('master.locations.index')
                ->with('error', 'Gagal menghapus. Lokasi bin masih menyimpan stok barang aktif.');
        }

        $location->delete();

        return redirect()->route('master.locations.index')
            ->with('success', 'Lokasi bin berhasil dihapus.');
    }
}
