<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>File Upload & RAG Management</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        .upload-area {
            border: 3px dashed #007bff;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            background: #f8f9ff;
        }
        
        .upload-area:hover {
            border-color: #0056b3;
            background: #e3f2fd;
            transform: translateY(-2px);
        }
        
        .upload-area.dragover {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .file-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .file-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        
        .status-uploaded {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .status-processing {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .status-ready {
            background: #e8f5e8;
            color: #2e7d32;
        }
        
        .progress-ring {
            width: 40px;
            height: 40px;
        }
        
        .progress-ring circle {
            fill: transparent;
            stroke: #007bff;
            stroke-width: 3;
            stroke-linecap: round;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        
        .nav-pills .nav-link {
            border-radius: 25px;
            margin: 0 0.5rem;
        }
        
        .nav-pills .nav-link.active {
            background: linear-gradient(45deg, #007bff, #0056b3);
        }
        
        .btn-gradient {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.4);
            color: white;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .file-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .search-box {
            border-radius: 25px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }
        
        .search-box:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="main-container p-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h3 mb-1 text-primary">
                                <i class="fas fa-upload me-2"></i>
                                File Upload & RAG Management
                            </h1>
                            <p class="text-muted mb-0">Upload documents for AI-powered search and chat</p>
                        </div>
                        <div>
                            <a href="{{ route('chat.index') }}" class="btn btn-gradient">
                                <i class="fas fa-comments me-2"></i>
                                Go to Chat
                            </a>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="upload-tab" data-bs-toggle="pill" data-bs-target="#upload" type="button" role="tab">
                                <i class="fas fa-upload me-2"></i>Upload Files
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="files-tab" data-bs-toggle="pill" data-bs-target="#files" type="button" role="tab">
                                <i class="fas fa-folder me-2"></i>My Files
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="search-tab" data-bs-toggle="pill" data-bs-target="#search" type="button" role="tab">
                                <i class="fas fa-search me-2"></i>Search Files
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="pills-tabContent">
                        <!-- Upload Tab -->
                        <div class="tab-pane fade show active" id="upload" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <div class="upload-area" id="uploadArea">
                                        <div class="file-icon">
                                            <i class="fas fa-cloud-upload-alt text-primary"></i>
                                        </div>
                                        <h4 class="mb-3">Drag & Drop Files Here</h4>
                                        <p class="text-muted mb-4">or click to browse files</p>
                                        <input type="file" id="fileInput" class="d-none" multiple accept=".txt,.pdf,.doc,.docx">
                                        <button class="btn btn-gradient" onclick="document.getElementById('fileInput').click()">
                                            <i class="fas fa-plus me-2"></i>Choose Files
                                        </button>
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                Supported formats: PDF, TXT, DOC, DOCX (Max 10MB each)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Progress -->
                            <div id="uploadProgress" class="mt-4" style="display: none;">
                                <h5 class="mb-3">Upload Progress</h5>
                                <div id="progressContainer"></div>
                            </div>
                        </div>

                        <!-- Files Tab -->
                        <div class="tab-pane fade" id="files" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Uploaded Files</h5>
                                <button class="btn btn-outline-primary btn-sm" onclick="loadFiles()">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                </button>
                            </div>
                            
                            <div id="filesList">
                                <div class="text-center py-5">
                                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                    <p class="text-muted mt-2">Loading files...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Search Tab -->
                        <div class="tab-pane fade" id="search" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <div class="search-box-container mb-4">
                                        <div class="input-group">
                                            <input type="text" class="form-control search-box" id="searchQuery" placeholder="Search through your documents...">
                                            <button class="btn btn-gradient" type="button" onclick="searchFiles()">
                                                <i class="fas fa-search me-1"></i>Search
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div id="searchResults">
                                        <div class="text-center py-5 text-muted">
                                            <i class="fas fa-search fa-2x mb-3"></i>
                                            <p>Enter a search query to find relevant content in your documents</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let uploadedFiles = [];

        document.addEventListener('DOMContentLoaded', function() {
            initializeUploadArea();
            loadFiles();
        });

        function initializeUploadArea() {
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('fileInput');

            // Drag and drop events
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                const files = Array.from(e.dataTransfer.files);
                handleFiles(files);
            });

            // Click to upload
            uploadArea.addEventListener('click', function() {
                fileInput.click();
            });

            fileInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                handleFiles(files);
            });
        }

        function handleFiles(files) {
            files.forEach(file => uploadFile(file));
        }

        function uploadFile(file) {
            console.log('Uploading file:', file.name);
            
            const formData = new FormData();
            formData.append('file', file);
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                formData.append('_token', csrfToken.getAttribute('content'));
            }

            // Show upload progress
            const progressId = 'progress-' + Date.now();
            const progressHtml = `
                <div class="card file-card" id="${progressId}">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-file text-primary fa-2x"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${file.name}</h6>
                                <small class="text-muted">${formatFileSize(file.size)}</small>
                                <div class="mt-2">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="ms-3">
                                <span class="badge status-badge status-uploaded">Uploading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('progressContainer').innerHTML += progressHtml;
            document.getElementById('uploadProgress').style.display = 'block';

            fetch('/files/upload', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateProgressCard(progressId, 'success', 'Uploaded & Processing...');
                    setTimeout(() => {
                        document.getElementById(progressId).remove();
                        loadFiles(); // Refresh files list
                    }, 2000);
                } else {
                    updateProgressCard(progressId, 'error', 'Upload failed: ' + data.error);
                }
            })
            .catch(error => {
                updateProgressCard(progressId, 'error', 'Upload error: ' + error.message);
            });
        }

        function updateProgressCard(progressId, status, message) {
            const card = document.getElementById(progressId);
            if (!card) return;

            const badge = card.querySelector('.badge');
            const progressBar = card.querySelector('.progress-bar');

            if (status === 'success') {
                badge.className = 'badge status-badge status-processing';
                badge.textContent = message;
                progressBar.style.width = '100%';
                progressBar.classList.remove('progress-bar-animated');
            } else {
                badge.className = 'badge bg-danger';
                badge.textContent = message;
                progressBar.classList.add('bg-danger');
            }
        }

        function loadFiles() {
            fetch('/files/list')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayFiles(data.files);
                }
            })
            .catch(error => {
                console.error('Error loading files:', error);
                document.getElementById('filesList').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading files. Please try again.
                    </div>
                `;
            });
        }

        function displayFiles(files) {
            const container = document.getElementById('filesList');
            
            if (files.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No files uploaded yet</h5>
                        <p class="text-muted">Upload some documents to get started with RAG</p>
                    </div>
                `;
                return;
            }

            let html = '';
            files.forEach(file => {
                const statusClass = getStatusClass(file.status);
                const statusText = getStatusText(file.status);
                const statusIcon = getStatusIcon(file.status);
                
                html += `
                    <div class="card file-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-file text-primary fa-2x"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${file.original_name}</h6>
                                    <small class="text-muted">
                                        ${file.file_size_human} • 
                                        ${new Date(file.created_at).toLocaleDateString()}
                                    </small>
                                    ${file.content ? `<div class="mt-2"><small class="text-muted">${file.content.substring(0, 100)}...</small></div>` : ''}
                                </div>
                                <div class="ms-3">
                                    <span class="badge status-badge ${statusClass}">
                                        <i class="${statusIcon} me-1"></i>
                                        ${statusText}
                                    </span>
                                </div>
                                <div class="ms-3">
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteFile(${file.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        function getStatusClass(status) {
            switch(status) {
                case 'ready': return 'status-ready';
                case 'processing': return 'status-processing';
                default: return 'status-uploaded';
            }
        }

        function getStatusText(status) {
            switch(status) {
                case 'ready': return 'Ready for RAG';
                case 'processing': return 'Processing...';
                default: return 'Uploaded';
            }
        }

        function getStatusIcon(status) {
            switch(status) {
                case 'ready': return 'fas fa-check-circle';
                case 'processing': return 'fas fa-spinner fa-spin';
                default: return 'fas fa-upload';
            }
        }

        function deleteFile(fileId) {
            if (!confirm('Are you sure you want to delete this file?')) {
                return;
            }

            fetch(`/files/${fileId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadFiles(); // Refresh the list
                } else {
                    alert('Failed to delete file: ' + data.error);
                }
            })
            .catch(error => {
                alert('Error deleting file: ' + error.message);
            });
        }

        function searchFiles() {
            const query = document.getElementById('searchQuery').value.trim();
            if (!query) return;

            const resultsContainer = document.getElementById('searchResults');
            resultsContainer.innerHTML = `
                <div class="text-center py-3">
                    <i class="fas fa-spinner fa-spin me-2"></i>
                    Searching...
                </div>
            `;

            fetch('/files/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    query: query,
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySearchResults(data.results, query);
                } else {
                    resultsContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Search failed: ${data.error}
                        </div>
                    `;
                }
            })
            .catch(error => {
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Search error: ${error.message}
                    </div>
                `;
            });
        }

        function displaySearchResults(results, query) {
            const container = document.getElementById('searchResults');
            
            if (results.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-2x text-muted mb-3"></i>
                        <h5 class="text-muted">No results found</h5>
                        <p class="text-muted">Try a different search term</p>
                    </div>
                `;
                return;
            }

            let html = `<h5 class="mb-3">Search Results for "${query}"</h5>`;
            
            results.forEach(result => {
                const similarity = Math.round(result.similarity * 100);
                html += `
                    <div class="card file-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-1">
                                    <i class="fas fa-file me-2"></i>
                                    ${result.file_name}
                                </h6>
                                <span class="badge bg-primary">${similarity}% match</span>
                            </div>
                            <p class="text-muted mb-0">${result.chunk_text}</p>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Auto-refresh files list every 30 seconds
        setInterval(loadFiles, 30000);
    </script>
</body>
</html>
