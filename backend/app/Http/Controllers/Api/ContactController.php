<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Resend\Laravel\Facades\Resend;

class ContactController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['nullable', 'email', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $replyTo  = $data['email'] ?? null;
        $fromName = $data['name'];
        $body     = $data['message'];

        $html = view('emails.contact', compact('fromName', 'body', 'replyTo'))->render();

        Resend::emails()->send([
            'from'     => env('MAIL_FROM_ADDRESS', 'noreply@useiskra.pl'),
            'to'       => [env('CONTACT_TO_EMAIL', 'romankawa@gmail.com')],
            'reply_to' => $replyTo ? [$replyTo] : [],
            'subject'  => "Wiadomość ze strony szachowej od {$fromName}",
            'html'     => $html,
        ]);

        return response()->json(['message' => 'Wiadomość wysłana.']);
    }
}
