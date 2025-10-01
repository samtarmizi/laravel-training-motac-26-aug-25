<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'filename',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'content',
        'embeddings',
        'chunks',
        'is_processed'
    ];

    protected $casts = [
        'embeddings' => 'array',
        'chunks' => 'array',
        'is_processed' => 'boolean'
    ];

    /**
     * Get the file size in human readable format
     */
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file has been processed (has content and embeddings)
     */
    public function isProcessed()
    {
        return $this->is_processed && !empty($this->content) && !empty($this->embeddings);
    }

    /**
     * Get processing status
     */
    public function getStatusAttribute()
    {
        if ($this->isProcessed()) {
            return 'ready';
        } elseif (!empty($this->content) && empty($this->embeddings)) {
            return 'processing';
        } else {
            return 'uploaded';
        }
    }
}