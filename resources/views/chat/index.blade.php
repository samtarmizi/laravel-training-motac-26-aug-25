<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Chat - Standalone</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            min-height: 100vh;
            border-right: 1px solid #dee2e6;
            background: #fff;
        }
        
        .chat-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .message {
            margin-bottom: 1rem;
            animation: fadeIn 0.3s ease-in;
        }
        
        .message.user {
            text-align: right;
        }
        
        .message.assistant {
            text-align: left;
        }
        
        .message-content {
            display: inline-block;
            max-width: 70%;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            word-wrap: break-word;
        }
        
        .message.user .message-content {
            background: #007bff;
            color: white;
            border-bottom-right-radius: 0.25rem;
        }
        
        .message.assistant .message-content {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #dee2e6;
            border-bottom-left-radius: 0.25rem;
        }
        
        /* Markdown styling */
        .message-content strong {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .message-content em {
            font-style: italic;
            color: #6c757d;
        }
        
        .message-content code {
            background: #e9ecef;
            color: #e83e8c;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.875em;
        }
        
        .message-content ol, .message-content ul {
            margin: 0.5rem 0;
            padding-left: 1.5rem;
        }
        
        .message-content li {
            margin: 0.25rem 0;
            line-height: 1.4;
        }
        
        .message-time {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c757d;
            font-style: italic;
        }
        
        .typing-dots {
            display: flex;
            gap: 0.25rem;
        }
        
        .typing-dots span {
            width: 6px;
            height: 6px;
            background: #6c757d;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }
        
        .typing-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-10px);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .prompt-suggestion:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .status-indicator .badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                opacity: 1;
            }
        }
        
        #messageInput {
            border-radius: 1.5rem;
            padding: 0.75rem 1rem;
        }
        
        #sendBtn {
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal.show {
            display: block;
        }
        
        .modal-dialog {
            position: relative;
            width: auto;
            margin: 1.75rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 1.75rem;
        }
        
        .modal-content {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            background-color: #fff;
            border: 1px solid rgba(0,0,0,.2);
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
        }
        
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .modal-body {
            position: relative;
            flex: 1 1 auto;
            padding: 1rem;
        }
        
        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 1rem;
            border-top: 1px solid #dee2e6;
        }
        
        .btn-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .modal-open {
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="container-fluid h-100">
        <div class="row h-100">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-primary">AI Assistant</h5>
                        <small class="text-muted">Powered by Ollama</small>
                    </div>
                    
                    <!-- Model Selection -->
                    <div class="mb-3">
                        <label for="modelSelect" class="form-label small">Model</label>
                        <select class="form-select form-select-sm" id="modelSelect">
                            <option value="gemma3:1b">Gemma 3:1b</option>
                            <option value="llama2">Llama 2</option>
                            <option value="codellama">Code Llama</option>
                        </select>
                    </div>

                    <!-- Temperature Slider -->
                    <div class="mb-3">
                        <label for="temperatureRange" class="form-label small">Temperature: <span id="temperatureValue">0.7</span></label>
                        <input type="range" class="form-range" min="0" max="2" step="0.1" value="0.7" id="temperatureRange">
                    </div>

                    <!-- New Chat Button -->
                    <button class="btn btn-outline-primary btn-sm w-100 mb-3" id="newChatBtn">
                        <i class="fas fa-plus"></i> New Chat
                    </button>

                    <!-- File Upload Button -->
                    <a href="{{ route('files.index') }}" class="btn btn-outline-success btn-sm w-100 mb-3">
                        <i class="fas fa-upload"></i> Upload Files
                    </a>

                    <!-- Chat History -->
                    <div class="chat-history">
                        <h6 class="text-muted small">Recent Chats</h6>
                        <div id="chatHistory" class="list-group list-group-flush">
                            <!-- Chat history will be populated here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4">
                <div class="d-flex flex-column h-100">
                    <!-- Chat Header -->
                    <div class="chat-header border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">AI Chat</h4>
                            <div class="d-flex align-items-center">
                                <div class="status-indicator me-2">
                                    <span class="badge bg-success" id="statusIndicator">Online</span>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm" id="settingsBtn">
                                    <i class="fas fa-cog"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Container -->
                    <div class="flex-grow-1 overflow-auto p-3" id="messagesContainer">
                        <div class="welcome-message text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-robot text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-muted">Welcome to AI Assistant</h5>
                            <p class="text-muted">Ask me anything! I'm here to help you with your questions.</p>
                            <div class="suggested-prompts mt-4">
                                <button class="btn btn-outline-primary btn-sm me-2 mb-2 prompt-suggestion" data-prompt="Explain quantum computing in simple terms">
                                    Explain quantum computing
                                </button>
                                <button class="btn btn-outline-primary btn-sm me-2 mb-2 prompt-suggestion" data-prompt="Help me write a Laravel controller">
                                    Laravel help
                                </button>
                                <button class="btn btn-outline-primary btn-sm me-2 mb-2 prompt-suggestion" data-prompt="What are the best practices for database design?">
                                    Database design
                                </button>
                                <button class="btn btn-outline-primary btn-sm me-2 mb-2 prompt-suggestion" data-prompt="Write a JavaScript function to sort an array">
                                    JavaScript help
                                </button>
                                <button class="btn btn-outline-danger btn-sm me-2 mb-2" id="testButton">
                                    Test Connection
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="chat-input border-top p-3">
                        <form id="chatForm" class="d-flex">
                            <div class="input-group">
                                <textarea 
                                    class="form-control" 
                                    id="messageInput" 
                                    placeholder="Type your message here..." 
                                    rows="1"
                                    style="resize: none; max-height: 120px;"
                                ></textarea>
                                <button class="btn btn-primary" type="button" id="sendBtn">
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

    <!-- Settings Modal -->
    <div class="modal" id="settingsModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modalModelSelect" class="form-label">Model</label>
                        <select class="form-select" id="modalModelSelect">
                            <option value="gemma3:1b">Gemma 3:1b</option>
                            <option value="llama2">Llama 2</option>
                            <option value="codellama">Code Llama</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modalTemperatureRange" class="form-label">Temperature: <span id="modalTemperatureValue">0.7</span></label>
                        <input type="range" class="form-range" min="0" max="2" step="0.1" value="0.7" id="modalTemperatureRange">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveSettings">Save Settings</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Chat interface initializing...');
            
            // Get all elements
            const messagesContainer = document.getElementById('messagesContainer');
            const messageInput = document.getElementById('messageInput');
            const chatForm = document.getElementById('chatForm');
            const sendBtn = document.getElementById('sendBtn');
            const modelSelect = document.getElementById('modelSelect');
            const temperatureRange = document.getElementById('temperatureRange');
            const temperatureValue = document.getElementById('temperatureValue');
            const newChatBtn = document.getElementById('newChatBtn');
            const settingsBtn = document.getElementById('settingsBtn');
            const settingsModalElement = document.getElementById('settingsModal');
            const statusIndicator = document.getElementById('statusIndicator');

            // Check if all required elements exist
            if (!messagesContainer || !messageInput || !chatForm || !sendBtn) {
                console.error('Required elements not found');
                return;
            }
            
            console.log('All required elements found, initializing event listeners...');

            // Initialize Bootstrap modal
            let settingsModal = null;
            if (settingsModalElement && typeof bootstrap !== 'undefined') {
                settingsModal = new bootstrap.Modal(settingsModalElement);
                console.log('Bootstrap modal initialized');
            } else {
                console.warn('Bootstrap not available or modal element not found');
                // Simple fallback modal implementation
                settingsModal = {
                    show: function() {
                        if (settingsModalElement) {
                            settingsModalElement.style.display = 'block';
                            settingsModalElement.classList.add('show');
                            document.body.classList.add('modal-open');
                            
                            // Add click outside to close
                            settingsModalElement.addEventListener('click', function(e) {
                                if (e.target === settingsModalElement) {
                                    this.hide();
                                }
                            });
                        }
                    },
                    hide: function() {
                        if (settingsModalElement) {
                            settingsModalElement.style.display = 'none';
                            settingsModalElement.classList.remove('show');
                            document.body.classList.remove('modal-open');
                        }
                    }
                };
            }

            let isTyping = false;
            let currentConversation = [];

            // Auto-resize textarea
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });

            // Handle Enter key
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Temperature range update
            if (temperatureRange && temperatureValue) {
                temperatureRange.addEventListener('input', function() {
                    temperatureValue.textContent = this.value;
                });
            }

            // Prompt suggestions
            document.querySelectorAll('.prompt-suggestion').forEach(btn => {
                btn.addEventListener('click', function() {
                    messageInput.value = this.dataset.prompt;
                    messageInput.focus();
                });
            });

            // Test button
            const testButton = document.getElementById('testButton');
            if (testButton) {
                testButton.addEventListener('click', function() {
                    console.log('Test button clicked');
                    messageInput.value = 'Hello, this is a test message';
                    sendMessage();
                });
            }

            // New chat
            if (newChatBtn) {
                newChatBtn.addEventListener('click', function() {
                    console.log('New chat clicked');
                    clearChat();
                });
            }

            // Settings
            if (settingsBtn) {
                settingsBtn.addEventListener('click', function() {
                    console.log('Settings button clicked');
                    if (settingsModal) {
                        settingsModal.show();
                    } else {
                        console.warn('Settings modal not available');
                        alert('Settings modal not available. Please refresh the page.');
                    }
                });
            }

            // Save settings
            const saveSettingsBtn = document.getElementById('saveSettings');
            if (saveSettingsBtn) {
                saveSettingsBtn.addEventListener('click', function() {
                    console.log('Save settings clicked');
                    const modalModelSelect = document.getElementById('modalModelSelect');
                    const modalTemperatureRange = document.getElementById('modalTemperatureRange');
                    
                    if (modalModelSelect && modalTemperatureRange) {
                        modelSelect.value = modalModelSelect.value;
                        temperatureRange.value = modalTemperatureRange.value;
                        temperatureValue.textContent = temperatureRange.value;
                        
                        if (settingsModal) {
                            settingsModal.hide();
                        }
                        console.log('Settings saved');
                    } else {
                        console.error('Modal form elements not found');
                    }
                });
            }

            // Modal temperature range update
            const modalTemperatureRange = document.getElementById('modalTemperatureRange');
            if (modalTemperatureRange) {
                modalTemperatureRange.addEventListener('input', function() {
                    const modalTemperatureValue = document.getElementById('modalTemperatureValue');
                    if (modalTemperatureValue) {
                        modalTemperatureValue.textContent = this.value;
                    }
                });
            }

            // Form submission
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Form submitted');
                sendMessage();
            });

            // Send button click
            sendBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Send button clicked');
                sendMessage();
            });

            function sendMessage() {
                console.log('sendMessage function called');
                const message = messageInput.value.trim();
                if (!message || isTyping) return;

                // Add user message to UI
                addMessage('user', message);
                messageInput.value = '';
                messageInput.style.height = 'auto';

                // Show typing indicator
                showTypingIndicator();

                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    hideTypingIndicator();
                    addMessage('assistant', 'Error: CSRF token not found. Please refresh the page.');
                    return;
                }

                console.log('Sending message:', message);
                console.log('CSRF Token:', csrfToken.getAttribute('content'));
                console.log('Model:', modelSelect.value);
                console.log('Temperature:', temperatureRange.value);

                // Send to server using FormData for better CSRF compatibility
                const formData = new FormData();
                formData.append('message', message);
                formData.append('model', modelSelect.value);
                formData.append('temperature', temperatureRange.value);
                formData.append('_token', csrfToken.getAttribute('content'));

                fetch('{{ route("chat.send") }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    return response.json();
                })
                .then(data => {
                    hideTypingIndicator();
                    console.log('Response data:', data);
                    
                    if (data.success) {
                        addMessage('assistant', data.response.response.response);
                        updateStatus('Online', 'success');
                    } else {
                        addMessage('assistant', 'Sorry, I encountered an error: ' + (data.error || 'Unknown error'));
                        updateStatus('Error', 'danger');
                    }
                })
                .catch(error => {
                    hideTypingIndicator();
                    console.error('Fetch error:', error);
                    addMessage('assistant', 'Sorry, I encountered a network error. Please check your connection and try again.');
                    updateStatus('Offline', 'danger');
                });
            }

            function addMessage(sender, content) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${sender}`;
                
                const messageContent = document.createElement('div');
                messageContent.className = 'message-content';
                
                // Convert markdown to HTML for assistant messages
                if (sender === 'assistant') {
                    messageContent.innerHTML = convertMarkdownToHtml(content);
                } else {
                    messageContent.textContent = content;
                } 
                
                const messageTime = document.createElement('div');
                messageTime.className = 'message-time';
                messageTime.textContent = new Date().toLocaleTimeString();
                
                messageDiv.appendChild(messageContent);
                messageDiv.appendChild(messageTime);
                
                // Remove welcome message if it exists
                const welcomeMessage = messagesContainer.querySelector('.welcome-message');
                if (welcomeMessage) {
                    welcomeMessage.remove();
                }
                
                messagesContainer.appendChild(messageDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                
                // Store in conversation
                currentConversation.push({ sender, content, timestamp: new Date() });
            }

            function convertMarkdownToHtml(text) {
                // Convert **text** to <strong>text</strong>
                text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                
                // Convert *text* to <em>text</em> (but not if it's part of **)
                text = text.replace(/(?<!\*)\*([^*]+)\*(?!\*)/g, '<em>$1</em>');
                
                // Convert `text` to <code>text</code>
                text = text.replace(/`(.*?)`/g, '<code>$1</code>');
                
                // Convert line breaks to <br>
                text = text.replace(/\n/g, '<br>');
                
                // Convert numbered lists (1. item)
                text = text.replace(/^(\d+)\.\s(.+)$/gm, '<li><strong>$1.</strong> $2</li>');
                
                // Convert bullet points (- item or * item)
                text = text.replace(/^[-*]\s(.+)$/gm, '<li>$1</li>');
                
                // Wrap consecutive list items in appropriate tags
                text = text.replace(/(<li><strong>\d+\.<\/strong>.*<\/li>)/gs, '<ol>$1</ol>');
                text = text.replace(/(<li>(?!<strong>\d+\.<\/strong>).*<\/li>)/gs, '<ul>$1</ul>');
                
                return text;
            }

            function showTypingIndicator() {
                isTyping = true;
                sendBtn.disabled = true;
                
                const typingDiv = document.createElement('div');
                typingDiv.className = 'message assistant typing-indicator';
                typingDiv.id = 'typingIndicator';
                
                typingDiv.innerHTML = `
                    <div class="message-content">
                        <span>AI is typing</span>
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
                sendBtn.disabled = false;
                
                const typingIndicator = document.getElementById('typingIndicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
            }

            function clearChat() {
                messagesContainer.innerHTML = `
                    <div class="welcome-message text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-robot text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-muted">Welcome to AI Assistant</h5>
                        <p class="text-muted">Ask me anything! I'm here to help you with your questions.</p>
                        <div class="suggested-prompts mt-4">
                            <button class="btn btn-outline-primary btn-sm me-2 mb-2 prompt-suggestion" data-prompt="Explain quantum computing in simple terms">
                                Explain quantum computing
                            </button>
                            <button class="btn btn-outline-primary btn-sm me-2 mb-2 prompt-suggestion" data-prompt="Help me write a Laravel controller">
                                Laravel help
                            </button>
                            <button class="btn btn-outline-primary btn-sm me-2 mb-2 prompt-suggestion" data-prompt="What are the best practices for database design?">
                                Database design
                            </button>
                            <button class="btn btn-outline-primary btn-sm me-2 mb-2 prompt-suggestion" data-prompt="Write a JavaScript function to sort an array">
                                JavaScript help
                            </button>
                            <button class="btn btn-outline-danger btn-sm me-2 mb-2" id="testButton">
                                Test Connection
                            </button>
                        </div>
                    </div>
                `;
                
                currentConversation = [];
                
                // Re-attach event listeners to new prompt suggestions
                document.querySelectorAll('.prompt-suggestion').forEach(btn => {
                    btn.addEventListener('click', function() {
                        messageInput.value = this.dataset.prompt;
                        messageInput.focus();
                    });
                });

                // Re-attach test button event listener
                const newTestButton = document.getElementById('testButton');
                if (newTestButton) {
                    newTestButton.addEventListener('click', function() {
                        console.log('Test button clicked');
                        messageInput.value = 'Hello, this is a test message';
                        sendMessage();
                    });
                }
            }

            function updateStatus(text, type) {
                statusIndicator.textContent = text;
                statusIndicator.className = `badge bg-${type}`;
            }

            // Load available models on page load
            fetch('{{ route("chat.models") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.models) {
                        const modelSelect = document.getElementById('modelSelect');
                        const modalModelSelect = document.getElementById('modalModelSelect');
                        
                        // Clear existing options
                        modelSelect.innerHTML = '';
                        modalModelSelect.innerHTML = '';
                        
                        data.models.forEach(model => {
                            const option = document.createElement('option');
                            option.value = model.name;
                            option.textContent = model.name;
                            modelSelect.appendChild(option);
                            
                            const modalOption = document.createElement('option');
                            modalOption.value = model.name;
                            modalOption.textContent = model.name;
                            modalModelSelect.appendChild(modalOption);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading models:', error);
                });
            
            console.log('Chat interface initialization complete!');
        });
    </script>
</body>
</html>