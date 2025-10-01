<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Display the file upload page
     */
    public function index()
    {
        $files = File::orderBy('created_at', 'desc')->get();
        return view('files.index', compact('files'));
    }

    /**
     * Display a listing of uploaded files (API)
     */
    public function list()
    {
        $files = File::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'files' => $files
        ]);
    }

    /**
     * Upload and process a file
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt,pdf,doc,docx|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('uploads', $filename, 'public');

            // Create file record
            $fileRecord = File::create([
                'filename' => $filename,
                'original_name' => $originalName,
                'file_path' => $filePath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'is_processed' => false
            ]);

            // Process file asynchronously
            $this->processFile($fileRecord);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully. Processing...',
                'file' => $fileRecord
            ]);

        } catch (\Exception $e) {
            Log::error('File upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to upload file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process uploaded file - extract content and generate embeddings
     */
    private function processFile(File $fileRecord)
    {
        try {
            $filePath = storage_path('app/public/' . $fileRecord->file_path);
            
            // Extract text content based on file type
            $content = $this->extractTextContent($filePath, $fileRecord->mime_type);
            
            if (empty($content)) {
                throw new \Exception('Could not extract text content from file');
            }

            // Update file with content
            $fileRecord->update(['content' => $content]);

            // Split content into chunks
            $chunks = $this->splitIntoChunks($content);
            $fileRecord->update(['chunks' => $chunks]);

            // Generate embeddings for each chunk
            $embeddings = $this->generateEmbeddings($chunks);
            $fileRecord->update([
                'embeddings' => $embeddings,
                'is_processed' => true
            ]);

            Log::info("File processed successfully: {$fileRecord->original_name}");

        } catch (\Exception $e) {
            Log::error("File processing error for {$fileRecord->original_name}: " . $e->getMessage());
        }
    }

    /**
     * Extract text content from different file types
     */
    private function extractTextContent($filePath, $mimeType)
    {
        switch ($mimeType) {
            case 'text/plain':
                return file_get_contents($filePath);
            
            case 'application/pdf':
                return $this->extractPdfText($filePath);
            
            case 'application/msword':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                return "Word document content extraction not implemented yet.";
            
            default:
                return file_get_contents($filePath);
        }
    }

    /**
     * Simple PDF text extraction (basic implementation)
     */
    private function extractPdfText($filePath)
    {
        // This is a very basic implementation
        // In production, use a proper PDF parser like Smalot\PdfParser
        $content = file_get_contents($filePath);
        
        // Remove PDF headers and binary data (very basic)
        $content = preg_replace('/[^\x20-\x7E]/', ' ', $content);
        $content = preg_replace('/\s+/', ' ', $content);
        
        return trim($content);
    }

    /**
     * Split content into chunks for embedding
     */
    private function splitIntoChunks($content, $chunkSize = 1000, $overlap = 200)
    {
        $chunks = [];
        $content = trim($content);
        $length = strlen($content);
        
        for ($i = 0; $i < $length; $i += $chunkSize - $overlap) {
            $chunk = substr($content, $i, $chunkSize);
            if (!empty(trim($chunk))) {
                $chunks[] = trim($chunk);
            }
        }
        
        return $chunks;
    }

    /**
     * Generate embeddings using Ollama
     */
    private function generateEmbeddings($chunks)
    {
        $embeddings = [];
        
        foreach ($chunks as $index => $chunk) {
            try {
                // Use Ollama to generate embeddings
                $embedding = Ollama::embed($chunk, 'nomic-embed-text');
                $embeddings[] = [
                    'chunk_index' => $index,
                    'text' => $chunk,
                    'embedding' => $embedding
                ];
            } catch (\Exception $e) {
                Log::error("Failed to generate embedding for chunk {$index}: " . $e->getMessage());
            }
        }
        
        return $embeddings;
    }

    /**
     * Search for relevant content using semantic search
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:1000',
            'limit' => 'nullable|integer|min:1|max:10'
        ]);

        try {
            $query = $request->input('query');
            $limit = $request->input('limit', 5);

            // Generate embedding for the query
            $queryEmbedding = Ollama::embed($query, 'nomic-embed-text');

            // Get all files with embeddings
            $files = File::where('is_processed', true)->whereNotNull('embeddings')->get();
            
            $results = [];

            foreach ($files as $file) {
                $embeddings = $file->embeddings;
                
                foreach ($embeddings as $embeddingData) {
                    $similarity = $this->cosineSimilarity($queryEmbedding, $embeddingData['embedding']);
                    
                    $results[] = [
                        'file_id' => $file->id,
                        'file_name' => $file->original_name,
                        'chunk_text' => $embeddingData['text'],
                        'similarity' => $similarity
                    ];
                }
            }

            // Sort by similarity and limit results
            usort($results, function($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });

            $results = array_slice($results, 0, $limit);

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate cosine similarity between two vectors
     */
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

    /**
     * Delete a file
     */
    public function destroy($id)
    {
        try {
            $file = File::findOrFail($id);
            
            // Delete physical file
            Storage::disk('public')->delete($file->file_path);
            
            // Delete database record
            $file->delete();

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('File deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually trigger file processing
     */
    public function process($id)
    {
        try {
            $file = File::findOrFail($id);
            $this->processFile($file);
            return response()->json(['success' => true, 'message' => 'File processing completed']);
        } catch (\Exception $e) {
            Log::error('Manual file processing error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to process file: ' . $e->getMessage()], 500);
        }
    }
}