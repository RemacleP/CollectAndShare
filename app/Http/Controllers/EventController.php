<?php
// php
namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Club;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class EventController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $events = Event::orderBy('start_datetime', 'desc')->paginate(10);
        return Inertia::render('events/Index', [
            'events' => $events,
        ]);
    }

    public function create()
    {
        // Au lieu de hasRole('admin'), on utilise ta nouvelle propriété
        $user = auth()->user();

        $clubs = $user->is_admin
            ? Club::select('id', 'name')->get()
            : $user->clubs()->select('clubs.id', 'clubs.name')->get();

        return Inertia::render('events/CreateEvent', [
            'clubs' => $clubs,
        ]);
    }

    public function store(Request $request)
    {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_datetime' => 'required|date',
                'end_datetime' => 'nullable|date|after_or_equal:start_datetime',
                'location_name' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'price' => 'nullable|numeric|min:0',
                'status' => 'required|in:pending,validated,cancelled',
                'registration_required' => 'sometimes|boolean',
                'registration_deadline' => 'nullable|date|before_or_equal:end_datetime',
                'image' => 'nullable|file|image|max:2048',
                'club_id' => 'required|exists:clubs,id',
            ]);


        $data['registration_required'] = $request->boolean('registration_required');

        // Add connect user
        $data['user_id'] = auth()->id();


        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('events', 'public');
        }

        Event::create($data);

        return redirect()->route('events.index')->with('success', 'Événement créé.');
    }


    public function show(Event $event)
    {
        // Load related club and user
        $event->load(['club', 'user']);

        // Check if the authenticated user is registered for the event
        $isRegistered = false;
        if (auth()->check()) {
            $isRegistered = $event->participants()
                ->where('user_id', auth()->id())
                ->exists();
        }

        return Inertia::render('events/ShowEvent', [
            'event' => $event,
            'isRegistered' => $isRegistered,
        ]);
    }

    public function join(Request $request, Event $event)
    {
        if(!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour vous inscrire à un événement.');
        }

        // Check if the user is already registered
        if ($event->participants()->where('user_id', auth()->id())->exists()) {
            return back()->with('error', 'Vous êtes déjà inscrit.');
        }

        // Check registration deadline
        if ($event->registration_deadline && now()->greaterThan($event->registration_deadline)) {
            return back()->with('error', 'La date limite d\'inscription est dépassée.');
        }

        $event->participants()->attach(auth()->id(), [
            'registration_date' => now(),
            'role' => 'guest',
            'status' => 'pending'
        ]);

        return back()->with('success', 'Inscription réussie !');
    }

    public function leave(Event $event)
    {
        $event->participants()->detach(auth()->id());

        return back()->with('success', 'Désinscription effectuée.');
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event); // Check if user can update the event

        $user = auth()->user();

        $clubs = $user->hasRole('admin')
            ? Club::select('id', 'name')->get()
            : $user->clubs()->select('clubs.id', 'clubs.name')->get();

        return Inertia::render('events/EditEvent', [
            'event' => $event,
            'clubs' => $clubs,

        ]);
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'nullable|date|after_or_equal:start_datetime',
            'location_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,validated,cancelled',
            'registration_required' => 'sometimes|boolean',
            'registration_deadline' => 'nullable|date|before_or_equal:end_datetime',
            'image' => 'nullable|file|image|max:2048',
            'club_id' => 'required|exists:clubs,id',
        ]);


        $data['registration_required'] = $request->boolean('registration_required');

        if ($request->hasFile('image')) {
            // Delete past image if exists
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $data['image'] = $request->file('image')->store('events', 'public');
        } else {
            unset($data['image']);
        }

        $event->update($data);

        return redirect()->route('events.index')->with('success', 'Événement mis à jour.');
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        if ($event->image) Storage::disk('public')->delete($event->image);
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Événement supprimé.');
    }
}
