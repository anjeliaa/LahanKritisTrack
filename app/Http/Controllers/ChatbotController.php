<?php

namespace App\Http\Controllers;

use App\Support\Chatbot;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function ask(Request $request, Chatbot $chatbot)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'history' => ['nullable', 'array', 'max:8'],
        ]);

        try {
            $answer = $chatbot->reply(
                $validated['message'],
                $validated['history'] ?? []
            );

            return response()->json([
                'reply' => $answer,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Maaf, chatbot sedang mengalami gangguan. Silakan coba lagi.',
            ], 500);
        }
    }
}
