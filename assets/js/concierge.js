/**
 * AI Concierge JavaScript
 * AIコンシェルジュ機能
 * 
 * @version 2.0
 */

(function() {
    'use strict';

    /**
     * AI コンシェルジュクラス
     */
    class AIConcierge {
        constructor() {
            this.isOpen = false;
            this.isRecording = false;
            this.isProcessing = false;
            this.conversationHistory = [];
            this.recognition = null;
            this.mediaRecorder = null;
            this.audioChunks = [];
            
            this.config = {
                apiEndpoint: '/wp-admin/admin-ajax.php',
                maxHistoryLength: 10,
                voiceTimeout: 5000,
                debounceDelay: 300
            };

            this.init();
        }

        /**
         * 初期化
         */
        init() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setup());
            } else {
                this.setup();
            }
        }

        /**
         * セットアップ
         */
        setup() {
            try {
                this.createUI();
                this.setupEventListeners();
                this.setupVoiceRecognition();
                this.loadConversationHistory();
                
                console.log('AI Concierge initialized');
            } catch (error) {
                console.error('AI Concierge setup error:', error);
            }
        }

        /**
         * UI作成
         */
        createUI() {
            const conciergeHTML = `
                <div class="gi-ai-concierge" id="gi-ai-concierge">
                    <!-- トリガーボタン -->
                    <button class="gi-concierge-trigger" id="gi-concierge-trigger" aria-label="AIコンシェルジュを開く">
                        <span class="gi-concierge-icon">🤖</span>
                        <div class="gi-voice-recognition-indicator" style="display: none;"></div>
                    </button>
                    
                    <!-- チャット画面 -->
                    <div class="gi-concierge-chat" id="gi-concierge-chat">
                        <div class="gi-concierge-header">
                            <h3 class="gi-concierge-title">AIコンシェルジュ</h3>
                            <button class="gi-concierge-close" id="gi-concierge-close" aria-label="閉じる">×</button>
                        </div>
                        
                        <div class="gi-concierge-messages" id="gi-concierge-messages">
                            <div class="gi-message assistant">
                                <div class="gi-message-avatar">🤖</div>
                                <div class="gi-message-content">
                                    こんにちは！助成金検索のお手伝いをします。何かお困りのことがあればお聞かせください。
                                </div>
                            </div>
                        </div>
                        
                        <div class="gi-concierge-input-area">
                            <div class="gi-concierge-input-container">
                                <textarea 
                                    class="gi-concierge-input" 
                                    id="gi-concierge-input" 
                                    placeholder="メッセージを入力してください..."
                                    rows="1"></textarea>
                                <button class="gi-voice-input-btn" id="gi-voice-input-btn" aria-label="音声入力">🎤</button>
                                <button class="gi-send-btn" id="gi-send-btn" aria-label="送信">📤</button>
                            </div>
                            <div class="gi-quick-replies" id="gi-quick-replies">
                                <button class="gi-quick-reply" data-message="助成金を検索したい">助成金を検索したい</button>
                                <button class="gi-quick-reply" data-message="申請方法を知りたい">申請方法を知りたい</button>
                                <button class="gi-quick-reply" data-message="締切が近い助成金は？">締切が近い助成金は？</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // bodyに追加
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = conciergeHTML;
            document.body.appendChild(tempDiv.firstElementChild);
        }

        /**
         * イベントリスナー設定
         */
        setupEventListeners() {
            // トリガーボタン
            const triggerBtn = document.getElementById('gi-concierge-trigger');
            triggerBtn.addEventListener('click', () => this.toggleChat());

            // 閉じるボタン
            const closeBtn = document.getElementById('gi-concierge-close');
            closeBtn.addEventListener('click', () => this.closeChat());

            // 送信ボタン
            const sendBtn = document.getElementById('gi-send-btn');
            sendBtn.addEventListener('click', () => this.sendMessage());

            // 音声入力ボタン
            const voiceBtn = document.getElementById('gi-voice-input-btn');
            voiceBtn.addEventListener('click', () => this.toggleVoiceInput());

            // 入力フィールド
            const input = document.getElementById('gi-concierge-input');
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            // 自動リサイズ
            input.addEventListener('input', () => this.autoResizeTextarea(input));

            // クイック返信
            const quickReplies = document.getElementById('gi-quick-replies');
            quickReplies.addEventListener('click', (e) => {
                if (e.target.classList.contains('gi-quick-reply')) {
                    const message = e.target.dataset.message;
                    this.sendQuickReply(message);
                }
            });

            // 外部クリックで閉じる
            document.addEventListener('click', (e) => {
                if (this.isOpen && !e.target.closest('.gi-ai-concierge')) {
                    this.closeChat();
                }
            });

            // ESCキーで閉じる
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.closeChat();
                }
            });
        }

        /**
         * 音声認識セットアップ
         */
        setupVoiceRecognition() {
            // Web Speech API
            if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                this.recognition = new SpeechRecognition();
                
                this.recognition.continuous = false;
                this.recognition.interimResults = false;
                this.recognition.lang = 'ja-JP';
                
                this.recognition.onstart = () => {
                    this.isRecording = true;
                    this.updateVoiceButtonState();
                };
                
                this.recognition.onresult = (event) => {
                    const result = event.results[0][0].transcript;
                    document.getElementById('gi-concierge-input').value = result;
                    this.sendMessage();
                };
                
                this.recognition.onerror = (event) => {
                    console.error('Speech recognition error:', event.error);
                    this.showError('音声認識でエラーが発生しました');
                    this.isRecording = false;
                    this.updateVoiceButtonState();
                };
                
                this.recognition.onend = () => {
                    this.isRecording = false;
                    this.updateVoiceButtonState();
                };
            }
            
            // MediaRecorder API フォールバック
            this.setupMediaRecorder();
        }

        /**
         * MediaRecorder セットアップ
         */
        async setupMediaRecorder() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.mediaRecorder = new MediaRecorder(stream);
                
                this.mediaRecorder.ondataavailable = (event) => {
                    this.audioChunks.push(event.data);
                };
                
                this.mediaRecorder.onstop = () => {
                    const audioBlob = new Blob(this.audioChunks, { type: 'audio/wav' });
                    this.sendAudioToServer(audioBlob);
                    this.audioChunks = [];
                };
            } catch (error) {
                console.log('MediaRecorder setup failed:', error);
            }
        }

        /**
         * チャット切り替え
         */
        toggleChat() {
            if (this.isOpen) {
                this.closeChat();
            } else {
                this.openChat();
            }
        }

        /**
         * チャットを開く
         */
        openChat() {
            const chat = document.getElementById('gi-concierge-chat');
            chat.classList.add('active');
            this.isOpen = true;
            
            // フォーカス
            setTimeout(() => {
                document.getElementById('gi-concierge-input').focus();
            }, 300);
        }

        /**
         * チャットを閉じる
         */
        closeChat() {
            const chat = document.getElementById('gi-concierge-chat');
            chat.classList.remove('active');
            this.isOpen = false;
        }

        /**
         * メッセージ送信
         */
        async sendMessage() {
            const input = document.getElementById('gi-concierge-input');
            const message = input.value.trim();
            
            if (!message || this.isProcessing) return;
            
            // ユーザーメッセージを表示
            this.addMessage(message, 'user');
            input.value = '';
            this.autoResizeTextarea(input);
            
            // タイピングインジケーター表示
            this.showTypingIndicator();
            
            try {
                this.isProcessing = true;
                const response = await this.sendToAPI(message);
                
                this.hideTypingIndicator();
                
                if (response.success) {
                    this.addMessage(response.data.message, 'assistant');
                    
                    // 検索結果があれば表示
                    if (response.data.grants) {
                        this.displaySearchResults(response.data.grants);
                    }
                } else {
                    this.showError('申し訳ありませんが、エラーが発生しました。');
                }
            } catch (error) {
                this.hideTypingIndicator();
                this.showError('通信エラーが発生しました。');
                console.error('API Error:', error);
            } finally {
                this.isProcessing = false;
            }
        }

        /**
         * クイック返信送信
         */
        sendQuickReply(message) {
            document.getElementById('gi-concierge-input').value = message;
            this.sendMessage();
        }

        /**
         * 音声入力切り替え
         */
        toggleVoiceInput() {
            if (this.isRecording) {
                this.stopVoiceInput();
            } else {
                this.startVoiceInput();
            }
        }

        /**
         * 音声入力開始
         */
        startVoiceInput() {
            if (this.recognition) {
                try {
                    this.recognition.start();
                } catch (error) {
                    console.error('Speech recognition start error:', error);
                    this.fallbackToMediaRecorder();
                }
            } else {
                this.fallbackToMediaRecorder();
            }
        }

        /**
         * 音声入力停止
         */
        stopVoiceInput() {
            if (this.recognition && this.isRecording) {
                this.recognition.stop();
            }
            
            if (this.mediaRecorder && this.mediaRecorder.state === 'recording') {
                this.mediaRecorder.stop();
            }
        }

        /**
         * MediaRecorder フォールバック
         */
        fallbackToMediaRecorder() {
            if (this.mediaRecorder) {
                this.mediaRecorder.start();
                this.isRecording = true;
                this.updateVoiceButtonState();
                
                // 5秒後に自動停止
                setTimeout(() => {
                    if (this.isRecording) {
                        this.stopVoiceInput();
                    }
                }, this.config.voiceTimeout);
            }
        }

        /**
         * 音声データをサーバーに送信
         */
        async sendAudioToServer(audioBlob) {
            const formData = new FormData();
            formData.append('audio', audioBlob, 'voice.wav');
            formData.append('action', 'gi_handle_voice_to_text');
            formData.append('nonce', window.gi_ajax_nonce || '');

            try {
                const response = await fetch(this.config.apiEndpoint, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success && result.data.text) {
                    document.getElementById('gi-concierge-input').value = result.data.text;
                    this.sendMessage();
                } else {
                    this.showError('音声の変換に失敗しました');
                }
            } catch (error) {
                console.error('Voice upload error:', error);
                this.showError('音声の送信に失敗しました');
            }
        }

        /**
         * APIにメッセージ送信
         */
        async sendToAPI(message) {
            // 会話履歴を含めて送信
            const data = {
                action: 'gi_handle_real_ai_chat',
                message: message,
                conversation_id: this.getConversationId(),
                history: this.conversationHistory.slice(-this.config.maxHistoryLength),
                nonce: window.gi_ajax_nonce || ''
            };

            const response = await fetch(this.config.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data).toString()
            });

            return response.json();
        }

        /**
         * メッセージ追加
         */
        addMessage(content, sender) {
            const messagesContainer = document.getElementById('gi-concierge-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `gi-message ${sender}`;
            
            const avatar = sender === 'user' ? '👤' : '🤖';
            
            messageDiv.innerHTML = `
                <div class="gi-message-avatar">${avatar}</div>
                <div class="gi-message-content">${this.escapeHtml(content)}</div>
            `;
            
            messagesContainer.appendChild(messageDiv);
            this.scrollToBottom();
            
            // 履歴に追加
            this.conversationHistory.push({
                role: sender === 'user' ? 'user' : 'assistant',
                content: content,
                timestamp: Date.now()
            });
            
            // ローカルストレージに保存
            this.saveConversationHistory();
        }

        /**
         * 検索結果表示
         */
        displaySearchResults(grants) {
            const messagesContainer = document.getElementById('gi-concierge-messages');
            const resultsDiv = document.createElement('div');
            resultsDiv.className = 'gi-message assistant';
            
            let resultsHtml = `
                <div class="gi-message-avatar">🔍</div>
                <div class="gi-message-content">
                    <div class="gi-search-results">
                        <h4>見つかった助成金 (${grants.length}件)</h4>
            `;
            
            grants.slice(0, 3).forEach(grant => {
                resultsHtml += `
                    <div class="gi-grant-result">
                        <h5><a href="${grant.url}" target="_blank">${this.escapeHtml(grant.title)}</a></h5>
                        <p>金額: ${grant.amount || '未定'}</p>
                        <p>締切: ${grant.deadline || '未定'}</p>
                    </div>
                `;
            });
            
            resultsHtml += `
                    </div>
                </div>
            `;
            
            resultsDiv.innerHTML = resultsHtml;
            messagesContainer.appendChild(resultsDiv);
            this.scrollToBottom();
        }

        /**
         * タイピングインジケーター表示
         */
        showTypingIndicator() {
            const messagesContainer = document.getElementById('gi-concierge-messages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'gi-message assistant';
            typingDiv.id = 'gi-typing-indicator';
            
            typingDiv.innerHTML = `
                <div class="gi-message-avatar">🤖</div>
                <div class="gi-typing-indicator">
                    <div class="gi-typing-dots">
                        <div class="gi-typing-dot"></div>
                        <div class="gi-typing-dot"></div>
                        <div class="gi-typing-dot"></div>
                    </div>
                </div>
            `;
            
            messagesContainer.appendChild(typingDiv);
            this.scrollToBottom();
        }

        /**
         * タイピングインジケーター非表示
         */
        hideTypingIndicator() {
            const typingIndicator = document.getElementById('gi-typing-indicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        /**
         * エラー表示
         */
        showError(message) {
            this.addMessage(`エラー: ${message}`, 'assistant');
        }

        /**
         * 音声ボタン状態更新
         */
        updateVoiceButtonState() {
            const voiceBtn = document.getElementById('gi-voice-input-btn');
            const indicator = document.querySelector('.gi-voice-recognition-indicator');
            
            if (this.isRecording) {
                voiceBtn.classList.add('recording');
                voiceBtn.innerHTML = '⏹️';
                if (indicator) indicator.style.display = 'block';
            } else {
                voiceBtn.classList.remove('recording');
                voiceBtn.innerHTML = '🎤';
                if (indicator) indicator.style.display = 'none';
            }
        }

        /**
         * テキストエリア自動リサイズ
         */
        autoResizeTextarea(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 80) + 'px';
        }

        /**
         * 下部にスクロール
         */
        scrollToBottom() {
            const messagesContainer = document.getElementById('gi-concierge-messages');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        /**
         * HTML エスケープ
         */
        escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        /**
         * 会話ID取得
         */
        getConversationId() {
            if (!localStorage.getItem('gi_conversation_id')) {
                localStorage.setItem('gi_conversation_id', 'conv_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9));
            }
            return localStorage.getItem('gi_conversation_id');
        }

        /**
         * 会話履歴保存
         */
        saveConversationHistory() {
            try {
                const history = this.conversationHistory.slice(-this.config.maxHistoryLength);
                localStorage.setItem('gi_conversation_history', JSON.stringify(history));
            } catch (error) {
                console.error('Failed to save conversation history:', error);
            }
        }

        /**
         * 会話履歴読み込み
         */
        loadConversationHistory() {
            try {
                const saved = localStorage.getItem('gi_conversation_history');
                if (saved) {
                    this.conversationHistory = JSON.parse(saved);
                    
                    // 古い履歴は削除（24時間以上前）
                    const oneDayAgo = Date.now() - 24 * 60 * 60 * 1000;
                    this.conversationHistory = this.conversationHistory.filter(
                        msg => msg.timestamp > oneDayAgo
                    );
                }
            } catch (error) {
                console.error('Failed to load conversation history:', error);
                this.conversationHistory = [];
            }
        }
    }

    // グローバル初期化
    window.AIConcierge = AIConcierge;

    // 自動初期化
    new AIConcierge();

})();