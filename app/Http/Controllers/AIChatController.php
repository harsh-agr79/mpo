<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Http;

class AIChatController extends Controller {
    public function stream( Request $request ): StreamedResponse {
        $prompt = $request->input( 'prompt' );

        $headers = [
            'Authorization' => 'Bearer ' . env( 'HUGGINGFACE_API_TOKEN' ),
            'Content-Type' => 'application/json',
        ];

        $body = [
            'messages' => [
                [ 'role' => 'user', 'content' => $prompt ],
            ],
            'model' => 'accounts/fireworks/models/deepseek-v3',
            'stream' => true,
        ];

        return response()->stream( function () use ( $headers, $body ) {
            $response = Http::withHeaders( $headers )
            ->withBody( json_encode( $body ), 'application/json' )
            ->send( 'POST', 'https://router.huggingface.co/fireworks-ai/inference/v1/chat/completions', [
                'stream' => true,
            ] );

            foreach ( $response->getBody() as $chunk ) {
                echo 'data: ' . $chunk . '\n\n';
                ob_flush();
                flush();
            }
        }
        , 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no', // for Nginx streaming
        ] );
    }

   public function reply(Request $request)
    {
        $prompt = $request->input('prompt');

        $response = Http::withToken(env('HUGGINGFACE_API_TOKEN'))
            ->post('https://router.huggingface.co/fireworks-ai/inference/v1/chat/completions', [
                'model' => 'accounts/fireworks/models/deepseek-v3',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        $result = $response->json();

        // Extract assistant reply content
        $reply = $result['choices'][0]['message']['content'] ?? 'No response from model.';

        return response()->json(['reply' => $reply]);
    }
}
