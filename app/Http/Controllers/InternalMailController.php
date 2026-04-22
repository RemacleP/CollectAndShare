<?php

namespace App\Http\Controllers;

use App\Models\InternalMail;
use App\Models\User; // <--- TRÈS IMPORTANT : ne pas oublier cet import
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InternalMailController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $messages = InternalMail::where('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($mail) {
                // Utilisation de firstname/lastname car 'name' n'existe peut-être pas tel quel
                $sender = $mail->sender_id
                    ? User::find($mail->sender_id)
                    : null;

                return [
                    'id' => $mail->id,
                    'sender_name' => $sender ? "{$sender->firstname} {$sender->lastname}" : 'Système',
                    'subject' => $mail->subject,
                    'body' => $mail->body,
                    'created_at_human' => $mail->created_at->diffForHumans(),
                    'read_at' => $mail->read_at, // On garde le nom de la DB pour plus de clarté
                    'reference_type' => $mail->reference_type,
                    'reference_id' => $mail->reference_id,
                ];
            });

        return Inertia::render('mail/index', [
            'messages' => $messages
        ]);
    }

    public function markAsRead(InternalMail $mail)
    {
        if ($mail->receiver_id === Auth::id()) {
            $mail->update(['read_at' => now()]);
        }
        return back();
    }
}
