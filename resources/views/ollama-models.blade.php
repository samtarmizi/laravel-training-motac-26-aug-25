<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ollama Models</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .model-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border: none;
            border-radius: 12px;
        }
        .model-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .model-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }
        .model-size {
            background-color: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .model-modified {
            color: #6c757d;
            font-size: 0.9em;
        }
        .status-badge {
            font-size: 0.8em;
            padding: 6px 12px;
        }
        .model-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        .detail-value {
            color: #6c757d;
            font-family: 'Courier New', monospace;
        }
        .refresh-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
        }
        .error-alert {
            border-radius: 12px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h2 mb-1">
                            <i class="fas fa-robot text-primary me-2"></i>
                            Ollama Models
                        </h1>
                        <p class="text-muted mb-0">Available AI models in your Ollama instance</p>
                    </div>
                    <div>
                        <a href="{{ route('chat.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-comments me-2"></i>Go to Chat
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($response->successful())
            @php
                $data = $response->json();
                $models = $data['models'] ?? [];
                $totalSize = 0;
                $totalModels = count($models);
            @endphp

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <i class="fas fa-database fa-2x mb-2"></i>
                            <h3 class="mb-1">{{ $totalModels }}</h3>
                            <p class="mb-0">Total Models</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <i class="fas fa-hdd fa-2x mb-2"></i>
                            <h3 class="mb-1">
                                @php
                                    foreach($models as $model) {
                                        $totalSize += $model['size'] ?? 0;
                                    }
                                    echo number_format($totalSize / (1024 * 1024 * 1024), 2);
                                @endphp
                                GB
                            </h3>
                            <p class="mb-0">Total Size</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <h3 class="mb-1">
                                @if($totalModels > 0)
                                    {{ \Carbon\Carbon::parse($models[0]['modified_at'] ?? now())->diffForHumans() }}
                                @else
                                    N/A
                                @endif
                            </h3>
                            <p class="mb-0">Last Updated</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <i class="fas fa-server fa-2x mb-2"></i>
                            <h3 class="mb-1">Online</h3>
                            <p class="mb-0">Ollama Status</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($totalModels > 0)
                <!-- Models Grid -->
                <div class="row">
                    @foreach($models as $model)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card model-card h-100">
                                <div class="model-header p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1">{{ $model['name'] ?? 'Unknown Model' }}</h5>
                                            <p class="mb-0 opacity-75">{{ $model['model'] ?? 'N/A' }}</p>
                                        </div>
                                        <span class="badge bg-success status-badge">
                                            <i class="fas fa-check-circle me-1"></i>Available
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="model-size">
                                            <i class="fas fa-weight-hanging me-1"></i>
                                            {{ number_format(($model['size'] ?? 0) / (1024 * 1024 * 1024), 2) }} GB
                                        </span>
                                        <span class="model-modified">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            {{ \Carbon\Carbon::parse($model['modified_at'] ?? now())->format('M d, Y') }}
                                        </span>
                                    </div>

                                    <!-- Model Details -->
                                    <div class="model-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Model Name:</span>
                                            <span class="detail-value">{{ $model['name'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Model ID:</span>
                                            <span class="detail-value">{{ $model['model'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Size:</span>
                                            <span class="detail-value">{{ number_format($model['size'] ?? 0) }} bytes</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Digest:</span>
                                            <span class="detail-value">{{ substr($model['digest'] ?? 'N/A', 0, 12) }}...</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Modified:</span>
                                            <span class="detail-value">{{ \Carbon\Carbon::parse($model['modified_at'] ?? now())->format('Y-m-d H:i:s') }}</span>
                                        </div>
                                        @if(isset($model['details']))
                                            <div class="detail-item">
                                                <span class="detail-label">Format:</span>
                                                <span class="detail-value">{{ $model['details']['format'] ?? 'N/A' }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Family:</span>
                                                <span class="detail-value">{{ $model['details']['family'] ?? 'N/A' }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Parameter Size:</span>
                                                <span class="detail-value">{{ $model['details']['parameter_size'] ?? 'N/A' }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Quantization Level:</span>
                                                <span class="detail-value">{{ $model['details']['quantization_level'] ?? 'N/A' }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="mt-3 d-grid gap-2">
                                        <a href="{{ route('chat.index') }}?model={{ urlencode($model['name']) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-comments me-1"></i>Use in Chat
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- No Models Found -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-robot fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No Models Found</h4>
                                <p class="text-muted">No models are currently available in your Ollama instance.</p>
                                <a href="https://ollama.ai/library" target="_blank" class="btn btn-primary">
                                    <i class="fas fa-download me-2"></i>Download Models
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        @else
            <!-- Error State -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger error-alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Connection Error</h5>
                                <p class="mb-0">
                                    Failed to connect to Ollama service. 
                                    Status: {{ $response->status() }} - {{ $response->reason() }}
                                </p>
                                <small class="text-muted">
                                    Make sure Ollama is running on http://127.0.0.1:11434
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Refresh Button -->
    <button class="btn btn-primary btn-lg rounded-circle refresh-btn" onclick="location.reload()" title="Refresh Models">
        <i class="fas fa-sync-alt"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-refresh every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);

        // Add loading state to refresh button
        document.querySelector('.refresh-btn').addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        });
    </script>
</body>
</html>
