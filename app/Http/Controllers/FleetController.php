<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Aircraft;
use App\Models\Airline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FleetController extends Controller
{
    /**
     * Display a listing of aircraft and airlines (tabbed view).
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'aircraft');
        $fleet = Aircraft::with('airline')->orderBy('registration', 'asc')->get();
        $airlines = Airline::withCount('aircraft')->orderBy('name', 'asc')->get();

        return view('fleet.index', compact('fleet', 'airlines', 'tab'));
    }

    /**
     * Get available layouts by scanning view files
     */
    private function getLayoutOptions()
    {
        $layouts = [];
        $files = glob(resource_path('views/aircraft/*.blade.php'));

        foreach ($files as $file) {
            $filename = basename($file, '.blade.php');

            // Generate a readable name
            // e.g. "b737-e46" -> "B737 E46"
            $name = strtoupper(str_replace('-', ' ', $filename));

            $layouts[$filename] = $name.' ('.$filename.')';
        }

        ksort($layouts);

        return $layouts;
    }

    // ==================== AIRCRAFT CRUD ====================

    /**
     * Show the form for creating a new aircraft.
     */
    public function create()
    {
        $layoutOptions = $this->getLayoutOptions();
        $airlines = Airline::orderBy('name')->get();

        return view('fleet.create', compact('layoutOptions', 'airlines'));
    }

    /**
     * Store a newly created aircraft in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'registration' => 'required|unique:aircraft,registration',
            'airline_id' => 'required|exists:airlines,id',
            'type' => 'required',
            'layout' => 'required',
            'status' => 'required|in:active,prolong',
        ]);

        // Force Uppercase
        $data = $request->all();
        $data['registration'] = strtoupper($request->registration);
        $data['type'] = strtoupper($request->type);

        Aircraft::create($data);

        return redirect()->route('fleet.index')->with('success', 'Aircraft added successfully.');
    }

    /**
     * Show the form for editing the specified aircraft.
     */
    public function edit(string $id)
    {
        $aircraft = Aircraft::findOrFail($id);
        $layoutOptions = $this->getLayoutOptions();
        $airlines = Airline::orderBy('name')->get();

        return view('fleet.edit', compact('aircraft', 'layoutOptions', 'airlines'));
    }

    /**
     * Update the specified aircraft in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'airline_id' => 'required|exists:airlines,id',
            'type' => 'required',
            'status' => 'required|in:active,prolong',
        ]);

        $aircraft = Aircraft::findOrFail($id);
        $oldPn = [
            'adult' => $aircraft->pn_adult,
            'crew' => $aircraft->pn_crew,
            'infant' => $aircraft->pn_infant,
        ];

        // Allow updating airline_id, type and status.
        // Registration and Layout are structural and shouldn't change.
        $aircraft->update($request->only(['airline_id', 'type', 'status', 'pn_adult', 'pn_crew', 'pn_infant']));

        // Log if PN changed
        $newPn = [
            'adult' => $aircraft->pn_adult,
            'crew' => $aircraft->pn_crew,
            'infant' => $aircraft->pn_infant,
        ];

        if ($oldPn !== $newPn) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'registration' => $aircraft->registration,
                'action' => 'pn_update',
                'details' => [
                    'old' => $oldPn,
                    'new' => $newPn,
                ],
            ]);
        }

        return redirect()->route('fleet.index')->with('success', 'Aircraft updated successfully.');
    }

    /**
     * Remove the specified aircraft from storage.
     */
    public function destroy(string $id)
    {
        $aircraft = Aircraft::findOrFail($id);
        $aircraft->delete();

        return redirect()->route('fleet.index')->with('success', 'Aircraft deleted successfully.');
    }

    // ==================== AIRLINE CRUD ====================

    /**
     * Show the form for creating a new airline.
     */
    public function createAirline()
    {
        return view('fleet.airline-create');
    }

    /**
     * Store a newly created airline in storage.
     */
    public function storeAirline(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:airlines,name',
            'code' => 'nullable|max:10',
        ]);

        $data = $request->all();
        $data['code'] = strtoupper($request->code ?? '');

        Airline::create($data);

        return redirect()->route('fleet.index', ['tab' => 'airlines'])->with('success', 'Airline added successfully.');
    }

    /**
     * Show the form for editing the specified airline.
     */
    public function editAirline(string $id)
    {
        $airline = Airline::findOrFail($id);

        return view('fleet.airline-edit', compact('airline'));
    }

    /**
     * Update the specified airline in storage.
     */
    public function updateAirline(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|unique:airlines,name,'.$id,
            'code' => 'nullable|max:10',
        ]);

        $airline = Airline::findOrFail($id);

        $data = $request->only(['name', 'code', 'icon']);
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        $airline->update($data);

        return redirect()->route('fleet.index', ['tab' => 'airlines'])->with('success', 'Airline updated successfully.');
    }

    /**
     * Remove the specified airline from storage.
     */
    public function destroyAirline(string $id)
    {
        $airline = Airline::findOrFail($id);

        // Check if airline has aircraft
        if ($airline->aircraft()->count() > 0) {
            return redirect()->route('fleet.index', ['tab' => 'airlines'])
                ->with('error', 'Cannot delete airline with assigned aircraft. Please reassign aircraft first.');
        }

        $airline->delete();

        return redirect()->route('fleet.index', ['tab' => 'airlines'])->with('success', 'Airline deleted successfully.');
    }
}
