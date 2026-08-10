<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** The bell in the admin topbar and the site header both post here. */
class NotificationController extends Controller
{
    /** Opening an entry marks it read and jumps to whatever it is about. */
    public function read(Request $request, string $notification): RedirectResponse
    {
        // Scoped through the signed-in user, so one inbox cannot open another's.
        $entry = $request->user()->notifications()->findOrFail($notification);

        $entry->markAsRead();

        return redirect()->to($entry->data['url'] ?? url()->previous());
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
