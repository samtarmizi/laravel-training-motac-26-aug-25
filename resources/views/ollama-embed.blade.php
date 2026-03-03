<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ollama Embed - File-based AI Chat</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .main-container {
            min-height: 100vh;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            background: #fff;
        }
        .upload-area:hover {
            border-color: #007bff;
            background-color: #f8f9ff;
        }
        .upload-area.dragover {
            border-color: #007bff;
            background-color: #e3f2fd;
        }
        .file-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .file-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .chat-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            min-height: 500px;
        }
        .message {
            margin-bottom: 1rem;
            animation: fadeIn 0.3s ease;
        }
        .message.user {
            text-align: right;
        }
        .message.assistant {
            text-align: left;
        }
        .message-content {
            display: inline-block;
            max-width: 85%;
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            text-align: left;
        }
        .message.user .message-content {
            background: #007bff;
            color: white;
        }
        .message.assistant .message-content {
            display: block;
            max-width: 85%;
            background: #f8f9fa;
            color: #333;
            border: 1px solid #dee2e6;
        }
        .message-content strong { font-weight: 600; color: #2c3e50; }
        .message-content em { font-style: italic; color: #6c757d; }
        .message-content code {
            background: #e9ecef;
            color: #e83e8c;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.875em;
            white-space: pre-wrap;
        }
        .message-content pre {
            margin: 0.5rem 0;
            padding: 0.75rem 1rem;
            background: #2d2d2d;
            color: #f8f8f2;
            border-radius: 0.375rem;
            overflow-x: auto;
            white-space: pre;
            tab-size: 4;
        }
        .message-content pre code { background: none; color: inherit; padding: 0; font-size: 0.85em; }
        .message-content h1, .message-content h2, .message-content h3, .message-content h4 {
            margin: 0.75rem 0 0.35rem 0;
            font-weight: 600;
            color: #2c3e50;
            line-height: 1.3;
        }
        .message-content h1 { font-size: 1.25em; }
        .message-content h2 { font-size: 1.15em; }
        .message-content h3 { font-size: 1.05em; }
        .message-content h4 { font-size: 1em; }
        .message-content blockquote {
            margin: 0.5rem 0;
            padding: 0.25rem 0 0.25rem 1rem;
            border-left: 4px solid #007bff;
            color: #495057;
        }
        .message-content ol, .message-content ul { margin: 0.5rem 0; padding-left: 1.5rem; }
        .message-content li { margin: 0.25rem 0; line-height: 1.4; }
        .similarity-badge {
            font-size: 0.75em;
            padding: 2px 6px;
            border-radius: 10px;
        }
        .source-reference {
            font-size: 0.85em;
            color: #6c757d;
            margin-top: 8px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #007bff;
        }
        .progress-container {
            display: none;
            margin: 20px 0;
        }
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6c757d;
            font-style: italic;
        }
        .typing-dots {
            display: flex;
            gap: 4px;
        }
        .typing-dots span {
            width: 6px;
            height: 6px;
            background: #6c757d;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }
        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .suggestion-chip {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 0.85em;
            margin: 2px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .suggestion-chip:hover {
            background: #bbdefb;
            transform: translateY(-1px);
        }
        .file-status {
            font-size: 0.8em;
            padding: 2px 8px;
            border-radius: 12px;
        }
        .status-processing {
            background: #fff3cd;
            color: #856404;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="container-fluid py-4">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h2 mb-1">
                                <i class="fas fa-brain text-primary me-2"></i>
                                Ollama Embed
                            </h1>
                            <p class="text-muted mb-0">Upload files and chat with AI based on their content</p>
                        </div>
                        <div>
                            <a href="/chat" class="btn btn-outline-primary me-2">
                                <i class="fas fa-comments me-2"></i>Regular Chat
                            </a>
                            <a href="/ollama-models" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-2"></i>View Models
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- File Upload Section -->
                <div class="col-lg-4 mb-4">
                    <div class="card file-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-upload me-2"></i>Upload Files
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Upload Area -->
                            <div class="upload-area" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h6>Drag & Drop Files Here</h6>
                                <p class="text-muted mb-1">or click to browse</p>
                                <p class="text-muted small mb-3">Max 5 MB per file (PDF, Word, text, images)</p>
                                <input type="file" id="fileInput" multiple accept=".txt,.pdf,.doc,.docx,.jpg,.jpeg,.png,.gif" style="display: none;">
                                <button class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                                    <i class="fas fa-folder-open me-2"></i>Choose Files
                                </button>
                                <p class="text-muted mt-2 small">Supported: PDF, DOCX, TXT, Images (Max: 5MB each)</p>
                            </div>

                            <!-- Progress Container -->
                            <div class="progress-container" id="progressContainer">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-cog fa-spin text-primary me-2"></i>
                                    <span>Processing files and generating embeddings...</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>

                            <!-- Uploaded Files List -->
                            <div id="uploadedFiles" class="mt-3">
                                <!-- Files will be displayed here -->
                            </div>

                            <!-- Clear All Button -->
                            <div class="mt-3" id="clearAllContainer" style="display: none;">
                                <button class="btn btn-outline-danger btn-sm w-100" onclick="clearAllFiles()">
                                    <i class="fas fa-trash me-2"></i>Clear All Files
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Section -->
                <div class="col-lg-8">
                    <div class="chat-container">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-comments me-2"></i>AI Chat
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Messages Container -->
                            <div id="messagesContainer" style="height: 400px; overflow-y: auto; padding: 20px;">
                                <div class="welcome-message text-center py-5">
                                    <div class="mb-4">
                                        <i class="fas fa-robot text-primary" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="text-muted">Welcome to Ollama Embed</h5>
                                    <p class="text-muted">Upload files to start chatting with AI based on their content!</p>
                                    
                                    <!-- Suggested Prompts -->
                                    <div class="mt-4">
                                        <h6 class="text-muted">Try asking:</h6>
                                        <div class="suggestion-chip" onclick="setPrompt('What is this document about?')">What is this document about?</div>
                                        <div class="suggestion-chip" onclick="setPrompt('Summarize the main points')">Summarize the main points</div>
                                        <div class="suggestion-chip" onclick="setPrompt('Find specific information about...')">Find specific information about...</div>
                                        <div class="suggestion-chip" onclick="setPrompt('What are the key takeaways?')">What are the key takeaways?</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Input Area -->
                            <div class="border-top p-3">
                                <form id="chatForm" class="d-flex">
                                    <div class="input-group">
                                        <textarea 
                                            class="form-control" 
                                            id="messageInput" 
                                            placeholder="Ask a question about your uploaded files..." 
                                            rows="2"
                                            style="resize: none;"
                                        ></textarea>
                                        <button class="btn btn-success" type="submit" id="sendBtn">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </form>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Press <kbd>Enter</kbd> to send, <kbd>Shift + Enter</kbd> for new line
                                    </small>
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
        let isTyping = false;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            loadUploadedFiles();
        });

        function initializeEventListeners() {
            // File input change
            document.getElementById('fileInput').addEventListener('change', handleFileSelect);
            
            // Drag and drop
            const uploadArea = document.getElementById('uploadArea');
            uploadArea.addEventListener('dragover', handleDragOver);
            uploadArea.addEventListener('dragleave', handleDragLeave);
            uploadArea.addEventListener('drop', handleDrop);
            uploadArea.addEventListener('click', () => document.getElementById('fileInput').click());
            
            // Chat form
            document.getElementById('chatForm').addEventListener('submit', handleChatSubmit);
            
            // Message input
            document.getElementById('messageInput').addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    handleChatSubmit(e);
                }
            });
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.currentTarget.classList.add('dragover');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.currentTarget.classList.remove('dragover');
        }

        function handleDrop(e) {
            e.preventDefault();
            e.currentTarget.classList.remove('dragover');
            const files = e.dataTransfer.files;
            handleFiles(files);
        }

        function handleFileSelect(e) {
            const files = e.target.files;
            handleFiles(files);
        }

        function handleFiles(files) {
            if (files.length === 0) return;
            
            showProgress();
            
            Array.from(files).forEach((file, index) => {
                uploadFile(file, index, files.length);
            });
        }

        function uploadFile(file, index, total) {
            const formData = new FormData();
            formData.append('file', file);
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                formData.append('_token', csrfToken.getAttribute('content'));
            }

            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
            }

            fetch('/ollama-embed/upload', {
                method: 'POST',
                headers: headers,
                body: formData
            })
            .then(response => Promise.all([response.status, response.text()]))
            .then(([status, text]) => {
                const trimmed = text.trim();
                if (trimmed.startsWith('<')) {
                    console.error('Server returned HTML instead of JSON:', status, trimmed.substring(0, 300));
                    if (status === 413) {
                        throw new Error('File too large (HTTP 413). Use a file under 5 MB, or ask the server admin to increase upload_max_filesize and post_max_size in PHP and client_max_body_size in Nginx.');
                    }
                    throw new Error('Server returned an error page (HTTP ' + status + '). Try refreshing the page, check you are logged in, and upload again.');
                }
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', trimmed.substring(0, 200));
                    throw new Error('Server returned invalid response. Try refreshing the page and uploading again.');
                }
            })
            .then(data => {
                if (data.success) {
                    uploadedFiles.push(data.file);
                    displayUploadedFile(data.file);
                    updateProgress((index + 1) / total * 100);
                } else {
                    const msg = data.error || data.message || (data.errors && typeof data.errors === 'object' ? Object.values(data.errors).flat().join(' ') : null) || 'Unknown error';
                    showError('Upload failed: ' + msg);
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                showError('Upload failed: ' + (error.message || 'Network error. Try refreshing the page.'));
            });
        }

        function displayUploadedFile(file) {
            const container = document.getElementById('uploadedFiles');
            const fileDiv = document.createElement('div');
            fileDiv.className = 'file-item mb-2 p-2 border rounded';
            fileDiv.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-file text-primary me-2"></i>
                        <span class="fw-bold">${file.original_name}</span>
                        <span class="file-status status-completed">Processed</span>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteFile('${file.id}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="text-muted small">
                    ${(file.file_size / 1024).toFixed(1)} KB • ${file.embeddings.length} chunks
                </div>
            `;
            container.appendChild(fileDiv);
            
            // Show clear all button if files exist
            if (uploadedFiles.length > 0) {
                document.getElementById('clearAllContainer').style.display = 'block';
            }
        }

        function deleteFile(fileId) {
            fetch(`/ollama-embed/file/${fileId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove from local array
                    uploadedFiles = uploadedFiles.filter(file => file.id !== fileId);
                    
                    // Remove from UI
                    const fileItems = document.querySelectorAll('.file-item');
                    fileItems.forEach(item => {
                        if (item.querySelector('button').onclick.toString().includes(fileId)) {
                            item.remove();
                        }
                    });
                    
                    // Hide clear all button if no files
                    if (uploadedFiles.length === 0) {
                        document.getElementById('clearAllContainer').style.display = 'none';
                    }
                } else {
                    showError('Delete failed: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                showError('Delete failed: ' + error.message);
            });
        }

        function clearAllFiles() {
            if (confirm('Are you sure you want to clear all files?')) {
                fetch('/ollama-embed/clear', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        uploadedFiles = [];
                        document.getElementById('uploadedFiles').innerHTML = '';
                        document.getElementById('clearAllContainer').style.display = 'none';
                        showSuccess('All files cleared successfully');
                    } else {
                        showError('Clear failed: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Clear error:', error);
                    showError('Clear failed: ' + error.message);
                });
            }
        }

        function handleChatSubmit(e) {
            e.preventDefault();
            
            if (uploadedFiles.length === 0) {
                showError('Please upload at least one file before chatting');
                return;
            }
            
            const message = document.getElementById('messageInput').value.trim();
            if (!message || isTyping) return;

            // Add user message
            addMessage('user', message);
            document.getElementById('messageInput').value = '';
            
            // Show typing indicator
            showTypingIndicator();

            // Send to server
            fetch('/ollama-embed/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ prompt: message })
            })
            .then(response => response.json())
            .then(data => {
                hideTypingIndicator();
                
                if (data.success) {
                    addMessage('assistant', data.response, data.relevant_content, data.similarity_scores, data.sources);
                } else {
                    addMessage('assistant', 'Error: ' + data.error);
                }
            })
            .catch(error => {
                hideTypingIndicator();
                console.error('Chat error:', error);
                addMessage('assistant', 'Error: Failed to get response from server');
            });
        }

        function convertMarkdownToHtml(text) {
            if (!text || typeof text !== 'string') return '';
            const escape = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            text = text.replace(/\uFF0A/g, '*').replace(/\u2217/g, '*');
            const codeBlocks = [];
            text = text.replace(/```(\w*)\n?([\s\S]*?)```/g, (_, lang, code) => {
                const i = codeBlocks.length;
                codeBlocks.push('<pre><code>' + escape(code.trim()) + '</code></pre>');
                return '\x00CB' + i + '\x00';
            });
            function processInline(s) {
                s = escape(s);
                const boldParts = s.split(/\*\*/);
                if (boldParts.length > 1) s = boldParts.map((part, i) => i % 2 === 1 ? '<strong>' + part + '</strong>' : part).join('');
                s = s.replace(/(?<!\*)\*([^*]+)\*(?!\*)/g, '<em>$1</em>');
                s = s.replace(/`([^`]+)`/g, '<code>$1</code>');
                return s;
            }
            const lines = text.split('\n');
            const out = [];
            let i = 0;
            while (i < lines.length) {
                const line = lines[i];
                if (line.match(/^(#{1,4})\s+(.+)$/)) {
                    const hMatch = line.match(/^(#{1,4})\s+(.+)$/);
                    out.push('<h' + hMatch[1].length + '>' + processInline(hMatch[2]) + '</h' + hMatch[1].length + '>');
                    i++; continue;
                }
                if (line.startsWith('> ')) {
                    const quoteLines = [];
                    while (i < lines.length && lines[i].startsWith('> ')) { quoteLines.push(processInline(lines[i].slice(2))); i++; }
                    out.push('<blockquote>' + quoteLines.join('<br>') + '</blockquote>'); continue;
                }
                if (/^[-*]\s+/.test(line)) {
                    const items = [];
                    while (i < lines.length && /^[-*]\s+/.test(lines[i])) { items.push('<li>' + processInline(lines[i].replace(/^[-*]\s+/, '')) + '</li>'); i++; }
                    out.push('<ul>' + items.join('') + '</ul>'); continue;
                }
                if (/^\d+\.\s+/.test(line)) {
                    const items = [];
                    while (i < lines.length && /^\d+\.\s+/.test(lines[i])) { items.push('<li>' + processInline(lines[i].replace(/^\d+\.\s+/, '')) + '</li>'); i++; }
                    out.push('<ol>' + items.join('') + '</ol>'); continue;
                }
                if (line.trim() === '') { out.push('<br>'); i++; continue; }
                out.push(processInline(line) + '<br>');
                i++;
            }
            let result = out.join('');
            codeBlocks.forEach((html, idx) => { result = result.replace('\x00CB' + idx + '\x00', html); });
            return result.replace(/<br>\s*$/, '');
        }

        function addMessage(sender, content, relevantContent = null, similarityScores = null, sources = null) {
            const messagesContainer = document.getElementById('messagesContainer');
            
            const welcomeMessage = messagesContainer.querySelector('.welcome-message');
            if (welcomeMessage) welcomeMessage.remove();
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content';
            const str = typeof content === 'string' ? content : (content ? String(content) : '');
            if (sender === 'assistant') {
                messageContent.innerHTML = convertMarkdownToHtml(str);
            } else {
                messageContent.textContent = str;
            }
            
            // Add relevant content references if available
            if (relevantContent && relevantContent.length > 0) {
                const referencesDiv = document.createElement('div');
                referencesDiv.className = 'source-reference mt-2';
                referencesDiv.innerHTML = '<strong>Sources:</strong><br>';
                
                relevantContent.forEach((item, index) => {
                    const similarity = similarityScores ? (similarityScores[index] * 100).toFixed(1) : 'N/A';
                    referencesDiv.innerHTML += `
                        <div class="mb-1">
                            <span class="similarity-badge bg-primary text-white">${similarity}% match</span>
                            <span class="text-muted">from ${sources ? sources[index] : 'Unknown'}</span>
                        </div>
                    `;
                });
                
                messageContent.appendChild(referencesDiv);
            }
            
            const messageTime = document.createElement('div');
            messageTime.className = 'text-muted small mt-1';
            messageTime.textContent = new Date().toLocaleTimeString();
            
            messageDiv.appendChild(messageContent);
            messageDiv.appendChild(messageTime);
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function showTypingIndicator() {
            isTyping = true;
            document.getElementById('sendBtn').disabled = true;
            
            const messagesContainer = document.getElementById('messagesContainer');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message assistant typing-indicator';
            typingDiv.id = 'typingIndicator';
            
            typingDiv.innerHTML = `
                <div class="message-content">
                    <span>AI is thinking</span>
                    <div class="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            `;
            
            messagesContainer.appendChild(typingDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function hideTypingIndicator() {
            isTyping = false;
            document.getElementById('sendBtn').disabled = false;
            
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        function setPrompt(prompt) {
            document.getElementById('messageInput').value = prompt;
            document.getElementById('messageInput').focus();
        }

        function showProgress() {
            document.getElementById('progressContainer').style.display = 'block';
        }

        function updateProgress(percentage) {
            const progressBar = document.querySelector('.progress-bar');
            progressBar.style.width = percentage + '%';
            
            if (percentage >= 100) {
                setTimeout(() => {
                    document.getElementById('progressContainer').style.display = 'none';
                    progressBar.style.width = '0%';
                }, 1000);
            }
        }

        function showError(message) {
            // Simple error display - you can enhance this
            alert('Error: ' + message);
        }

        function showSuccess(message) {
            // Simple success display - you can enhance this
            console.log('Success: ' + message);
        }

        function loadUploadedFiles() {
            // This would load files from session if needed
            // For now, we start with empty array
        }
    </script>
</body>
</html>
