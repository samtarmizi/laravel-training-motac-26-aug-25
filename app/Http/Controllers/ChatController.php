<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat.index');
    }

    public function sendMessage(Request $request)
    {
        \Log::info('ChatController::sendMessage called', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip()
        ]);

        $request->validate([
            'message' => 'required|string|max:2000',
            'model' => 'nullable|string',
            'temperature' => 'nullable|numeric|between:0,2',
        ]);

        try {
            $message = $request->input('message');
            $model = $request->input('model', 'gemma3:1b');
            $temperature = $request->input('temperature', 0.7);

            \Log::info('Processing chat request', [
                'message' => $message,
                'model' => $model,
                'temperature' => $temperature
            ]);

            // Use Ollama Laravel package
            $response = Ollama::agent('You are a helpful AI assistant.')
                ->prompt($message)
                ->model($model)
                ->options(['temperature' => (float)$temperature])
                ->ask();

            return response()->json([
                'success' => true,
                'response' => [
                    'response' => $response
                ],
                'model' => $model,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Sorry, I encountered an error processing your request. Please try again.',
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    public function sendMessageStream(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'model' => 'nullable|string',
            'temperature' => 'nullable|numeric|between:0,2',
        ]);

        try {
            $message = $request->input('message');
            $model = $request->input('model', 'gemma3:1b');
            $temperature = $request->input('temperature', 0.7);

            // Use direct HTTP call for streaming
            $response = Http::timeout(300)->post('http://127.0.0.1:11434/api/generate', [
                'model' => $model,
                'prompt' => $message,
                'stream' => true,
                'options' => [
                    'temperature' => $temperature,
                ]
            ]);

            if ($response->successful()) {
                return response()->stream(function () use ($response) {
                    foreach ($response->stream() as $chunk) {
                        $data = json_decode($chunk, true);
                        if (isset($data['response'])) {
                            echo "data: " . json_encode([
                                'content' => $data['response'],
                                'done' => $data['done'] ?? false
                            ]) . "\n\n";
                            ob_flush();
                            flush();
                        }
                    }
                }, 200, [
                    'Content-Type' => 'text/event-stream',
                    'Cache-Control' => 'no-cache',
                    'Connection' => 'keep-alive',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to connect to Ollama service',
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Chat streaming error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Sorry, I encountered an error processing your request. Please try again.',
            ], 500);
        }
    }

    public function getModels()
    {
        try {
            $response = Http::get('http://127.0.0.1:11434/api/tags');
            
            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'models' => $data['models'] ?? []
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to fetch models'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to connect to Ollama service'
            ], 500);
        }
    }

}
