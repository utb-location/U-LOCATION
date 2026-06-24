<?php

namespace App\Http\Controllers;

use App\Models\AnnouncementMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementMessageController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements.index', [
            'messages' => AnnouncementMessage::orderBy('position')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.form', [
            'announcement' => new AnnouncementMessage(['active' => true, 'style' => 'info']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['active'] = (bool) ($data['active'] ?? false);
        AnnouncementMessage::create($data);

        return redirect()->route('admin.announcements.index')->with('admin_success', 'Message cree et pret pour la diffusion.');
    }

    public function edit(AnnouncementMessage $announcement): View
    {
        return view('admin.announcements.form', compact('announcement'));
    }

    public function update(Request $request, AnnouncementMessage $announcement): RedirectResponse
    {
        $data = $this->validated($request);
        $data['active'] = (bool) ($data['active'] ?? false);
        $announcement->update($data);

        return back()->with('admin_success', 'Message mis a jour.');
    }

    public function destroy(AnnouncementMessage $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('admin_success', 'Message supprime.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'nullable|string|max:120',
            'message' => 'required|string|max:500',
            'style' => 'required|in:info,urgent,success',
            'position' => 'required|integer|min:0|max:999',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'active' => 'nullable|boolean',
        ]);
    }
}
