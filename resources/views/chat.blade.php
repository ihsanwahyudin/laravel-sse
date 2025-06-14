<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Realtime Chat - Laravel SSE</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Realtime Chat</h2>
                    <p id="connection-status" class="text-sm text-gray-500">Connecting...</p>
                </div>

                <!-- Chat Messages Container -->
                <div id="chat-messages" class="p-4 h-96 overflow-y-auto space-y-4">
                    <!-- Messages will be inserted here -->
                </div>

                <!-- Chat Input Form -->
                <div class="p-4 border-t border-gray-200">
                    <form id="chat-form" class="flex space-x-2">
                        <input type="text"
                               id="message-input"
                               class="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:border-blue-500"
                               placeholder="Type your message...">
                        <button type="submit"
                                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                            Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatMessages = document.getElementById('chat-messages');
            const chatForm = document.getElementById('chat-form');
            const messageInput = document.getElementById('message-input');
            const connectionStatus = document.getElementById('connection-status');
            let eventSource = null;

            function connectSSE() {
                if (eventSource) {
                    eventSource.close();
                }

                eventSource = new EventSource('/chat/stream');
                // eventSource = new EventSource('/sse/chat-counter');

                eventSource.onopen = function() {
                    connectionStatus.textContent = 'Connected';
                    connectionStatus.className = 'text-sm text-green-500';
                };

                eventSource.onmessage = function(event) {
                    const message = JSON.parse(event.data);
                    if(message.event === 'new-message') {
                        appendMessage(message.data);
                    }
                };

                eventSource.onerror = function(error) {
                    console.error('SSE Error:', error);
                    connectionStatus.textContent = 'Disconnected - Reconnecting...';
                    connectionStatus.className = 'text-sm text-red-500';
                    eventSource.close();

                    // Attempt to reconnect after 5 seconds
                    setTimeout(connectSSE, 5000);
                };

                // Handle ping events to keep connection alive
                eventSource.addEventListener('ping', function(e) {
                    console.log('Received ping:', e.data);
                });
            }

            // Initial connection
            connectSSE();

            // Send message
            chatForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const message = messageInput.value.trim();
                if (!message) return;

                try {
                    const response = await fetch('/chat/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ message })
                    });

                    if (response.ok) {
                        messageInput.value = '';
                    } else {
                        console.error('Failed to send message:', await response.text());
                    }
                } catch (error) {
                    console.error('Error sending message:', error);
                }
            });

            function appendMessage(message) {
                const messageElement = document.createElement('div');
                messageElement.className = 'p-3 rounded-lg bg-gray-100';
                messageElement.innerHTML = `
                    <p class="font-semibold text-gray-800">${escapeHtml(message.user)}</p>
                    <p class="text-gray-600">${escapeHtml(message.message)}</p>
                    <p class="text-xs text-gray-400">${new Date(message.timestamp).toLocaleTimeString()}</p>
                `;
                chatMessages.appendChild(messageElement);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Helper function to escape HTML and prevent XSS
            function escapeHtml(unsafe) {
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        });
    </script>
</body>
</html>