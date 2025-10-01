<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;

class OllamaEmbedController extends Controller
{
    private $uploadedFiles = [];
    private $fileEmbeddings = [];

    public function index()
    {
        return view('ollama-embed', [
            'uploadedFiles' => $this->uploadedFiles,
            'fileEmbeddings' => $this->fileEmbeddings
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt,pdf,doc,docx,jpg,jpeg,png,gif|max:5120', // 5MB max
        ]);

        try {
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();
            $mimeType = $uploadedFile->getMimeType();
            $fileSize = $uploadedFile->getSize();

            // Generate unique filename
            $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
            $filePath = $uploadedFile->storeAs('temp-uploads', $filename, 'public');

            // Extract text content
            $content = $this->extractTextContent(storage_path('app/public/' . $filePath), $mimeType);
            
            // Generate embeddings
            $embeddings = $this->generateEmbeddings($content);

            $fileData = [
                'id' => Str::uuid(),
                'filename' => $filename,
                'original_name' => $originalName,
                'file_path' => $filePath,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'content' => $content,
                'embeddings' => $embeddings,
                'uploaded_at' => now()->toISOString()
            ];

            // Store in session for temporary management
            $uploadedFiles = session('uploaded_files', []);
            $uploadedFiles[] = $fileData;
            session(['uploaded_files' => $uploadedFiles]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded and processed successfully',
                'file' => $fileData
            ]);

        } catch (\Exception $e) {
            Log::error('File upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to upload file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function chat(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:2000',
        ]);

        try {
            $prompt = $request->input('prompt');
            $uploadedFiles = session('uploaded_files', []);
            
            if (empty($uploadedFiles)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No files uploaded. Please upload a file first.'
                ], 400);
            }

            // Generate embedding for the prompt
            $promptEmbedding = $this->generatePromptEmbedding($prompt);

            // Find most relevant content from uploaded files
            $relevantContent = $this->findRelevantContent($promptEmbedding, $uploadedFiles);

            // Build enhanced prompt with file context
            $enhancedPrompt = $this->buildEnhancedPrompt($prompt, $relevantContent);

            // Get response from Ollama
            $response = Http::timeout(60)->post('http://127.0.0.1:11434/api/generate', [
                'model' => 'gemma3:1b',
                'prompt' => $enhancedPrompt,
                'stream' => false,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                return response()->json([
                    'success' => true,
                    'response' => $responseData['response'] ?? 'No response received',
                    'relevant_content' => $relevantContent,
                    'similarity_scores' => array_column($relevantContent, 'similarity'),
                    'sources' => array_column($relevantContent, 'source')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to get response from Ollama'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to process chat request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteFile(Request $request, $fileId)
    {
        try {
            $uploadedFiles = session('uploaded_files', []);
            $fileIndex = null;

            foreach ($uploadedFiles as $index => $file) {
                if ($file['id'] === $fileId) {
                    $fileIndex = $index;
                    break;
                }
            }

            if ($fileIndex !== null) {
                // Delete physical file
                $file = $uploadedFiles[$fileIndex];
                Storage::disk('public')->delete($file['file_path']);

                // Remove from session
                unset($uploadedFiles[$fileIndex]);
                session(['uploaded_files' => array_values($uploadedFiles)]);

                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'File not found'
                ], 404);
            }

        } catch (\Exception $e) {
            Log::error('File deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clearFiles()
    {
        try {
            $uploadedFiles = session('uploaded_files', []);
            
            // Delete all physical files
            foreach ($uploadedFiles as $file) {
                Storage::disk('public')->delete($file['file_path']);
            }

            // Clear session
            session()->forget('uploaded_files');

            return response()->json([
                'success' => true,
                'message' => 'All files cleared successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Clear files error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear files: ' . $e->getMessage()
            ], 500);
        }
    }

    private function extractTextContent($filePath, $mimeType)
    {
        try {
            if (str_contains($mimeType, 'text/plain')) {
                return file_get_contents($filePath);
            } elseif (str_contains($mimeType, 'application/pdf')) {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($filePath);
                return $pdf->getText();
            } elseif (str_contains($mimeType, 'application/msword') || str_contains($mimeType, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')) {
                $phpWord = WordIOFactory::load($filePath);
                $sections = $phpWord->getSections();
                $content = '';
                foreach ($sections as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $content .= $element->getText() . "\n";
                        }
                    }
                }
                return $content;
            } else {
                return 'Unsupported file type for text extraction';
            }
        } catch (\Exception $e) {
            Log::error('Text extraction error: ' . $e->getMessage());
            return 'Error extracting text from file';
        }
    }

    private function generateEmbeddings($content)
    {
        try {
            // Chunk the content
            $chunks = $this->chunkText($content, 500, 50);
            $embeddings = [];

            foreach ($chunks as $index => $chunk) {
                $embedding = $this->getEmbedding($chunk);
                $embeddings[] = [
                    'chunk_index' => $index,
                    'text' => $chunk,
                    'embedding' => $embedding
                ];
            }

            return $embeddings;
        } catch (\Exception $e) {
            Log::error('Embedding generation error: ' . $e->getMessage());
            return [];
        }
    }

    private function generatePromptEmbedding($prompt)
    {
        return $this->getEmbedding($prompt);
    }

    private function getEmbedding($text)
    {
        try {
            $response = Http::timeout(30)->post('http://127.0.0.1:11434/api/embeddings', [
                'model' => 'nomic-embed-text',
                'prompt' => $text
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['embedding'] ?? [];
            } else {
                Log::error('Embedding API error: ' . $response->body());
                return [];
            }
        } catch (\Exception $e) {
            Log::error('Embedding request error: ' . $e->getMessage());
            return [];
        }
    }

    private function findRelevantContent($promptEmbedding, $uploadedFiles, $limit = 3)
    {
        $results = [];

        foreach ($uploadedFiles as $file) {
            foreach ($file['embeddings'] as $embeddingData) {
                $similarity = $this->cosineSimilarity($promptEmbedding, $embeddingData['embedding']);
                
                if ($similarity > 0.3) { // Only include relevant results
                    $results[] = [
                        'text' => $embeddingData['text'],
                        'similarity' => $similarity,
                        'source' => $file['original_name'],
                        'chunk_index' => $embeddingData['chunk_index']
                    ];
                }
            }
        }

        // Sort by similarity and limit results
        usort($results, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return array_slice($results, 0, $limit);
    }

    private function buildEnhancedPrompt($prompt, $relevantContent)
    {
        if (empty($relevantContent)) {
            return $prompt;
        }

        $contextText = "Based on the following uploaded documents:\n\n";
        
        foreach ($relevantContent as $index => $item) {
            $contextText .= "From {$item['source']} (relevance: " . round($item['similarity'] * 100, 1) . "%):\n";
            $contextText .= "{$item['text']}\n\n";
        }

        $contextText .= "Please answer the following question using the information from the documents above when relevant:\n\n";
        $contextText .= $prompt;

        return $contextText;
    }

    private function chunkText($text, $chunkSize = 500, $overlap = 50)
    {
        $chunks = [];
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $numWords = count($words);

        for ($i = 0; $i < $numWords; $i += ($chunkSize - $overlap)) {
            $chunk = array_slice($words, $i, $chunkSize);
            $chunks[] = implode(' ', $chunk);
        }

        return $chunks;
    }

    private function cosineSimilarity($vectorA, $vectorB)
    {
        if (count($vectorA) !== count($vectorB)) {
            return 0;
        }

        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $normA += $vectorA[$i] * $vectorA[$i];
            $normB += $vectorB[$i] * $vectorB[$i];
        }

        if ($normA == 0 || $normB == 0) {
            return 0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
