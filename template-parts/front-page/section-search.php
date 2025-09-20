<?php
/**
 * AI-Powered Grant Search Section - Complete Integration
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// セッションID生成
$session_id = 'gi_session_' . wp_generate_uuid4();
$nonce = wp_create_nonce('gi_ai_search_nonce');
?>

<!-- AI Grant Search Section -->
<section id="ai-search-section" class="ai-search-wrapper" data-session-id="<?php echo esc_attr($session_id); ?>">
    <div class="ai-container">
        
        <!-- Section Header -->
        <div class="ai-header">
            <span class="ai-badge">AI POWERED</span>
            <h2 class="ai-title">
                <span class="title-en">GRANT SEARCH</span>
                <span class="title-jp">補助金AI検索</span>
            </h2>
            <p class="ai-subtitle">最適な補助金を瞬時に発見</p>
        </div>

        <!-- Main Search Interface -->
        <div class="ai-search-interface">
            
            <!-- Search Bar -->
            <div class="ai-search-bar">
                <div class="search-input-wrapper">
                    <input 
                        type="text" 
                        id="ai-search-input" 
                        class="search-input"
                        placeholder="業種、地域、目的などを入力..."
                        autocomplete="off">
                    <div class="search-actions">
                        <button class="voice-btn" aria-label="音声入力">
                            <svg width="16" height="16" viewBox="0 0 16 16">
                                <path d="M8 11c1.66 0 3-1.34 3-3V3c0-1.66-1.34-3-3-3S5 1.34 5 3v5c0 1.66 1.34 3 3 3z"/>
                                <path d="M13 8c0 2.76-2.24 5-5 5s-5-2.24-5-5H1c0 3.53 2.61 6.43 6 6.92V16h2v-1.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                            </svg>
                        </button>
                        <button id="ai-search-btn" class="search-btn">
                            <span class="btn-text">検索</span>
                            <svg class="btn-icon" width="20" height="20" viewBox="0 0 20 20">
                                <path d="M9 2a7 7 0 100 14A7 7 0 009 2zm0 12a5 5 0 110-10 5 5 0 010 10z"/>
                                <path d="M13.5 13.5L18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="search-suggestions" id="search-suggestions"></div>
            </div>

            <!-- Quick Filters -->
            <div class="quick-filters">
                <button class="filter-chip active" data-filter="all">すべて</button>
                <button class="filter-chip" data-filter="it">IT導入</button>
                <button class="filter-chip" data-filter="manufacturing">ものづくり</button>
                <button class="filter-chip" data-filter="startup">創業支援</button>
                <button class="filter-chip" data-filter="sustainability">持続化</button>
                <button class="filter-chip" data-filter="innovation">事業再構築</button>
                <button class="filter-chip" data-filter="employment">雇用関連</button>
            </div>

            <!-- AI Chat & Results -->
            <div class="ai-main-content">
                
                <!-- Left: AI Assistant -->
                <div class="ai-assistant-panel">
                    <div class="assistant-header">
                        <div class="assistant-avatar">
                            <div class="avatar-ring"></div>
                            <span class="avatar-icon">AI</span>
                        </div>
                        <div class="assistant-info">
                            <h3 class="assistant-name">補助金AIアシスタント</h3>
                            <span class="assistant-status">オンライン</span>
                        </div>
                    </div>
                    
                    <div class="chat-messages" id="chat-messages">
                        <div class="message message-ai">
                            <div class="message-bubble">
                                どのような補助金をお探しですか？<br>
                                業種や目的をお聞かせください。
                            </div>
                        </div>
                    </div>
                    
                    <div class="chat-input-area">
                        <div class="typing-indicator" id="typing-indicator">
                            <span></span><span></span><span></span>
                        </div>
                        <textarea 
                            id="chat-input" 
                            class="chat-input"
                            placeholder="質問を入力..."
                            rows="1"></textarea>
                        <button id="chat-send" class="chat-send-btn">
                            <svg width="18" height="18" viewBox="0 0 18 18">
                                <path d="M2 9l14-7-5 7 5 7z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Quick Questions -->
                    <div class="quick-questions">
                        <button class="quick-q" data-q="申請の流れを教えて">申請の流れ</button>
                        <button class="quick-q" data-q="必要書類は？">必要書類</button>
                        <button class="quick-q" data-q="締切はいつ？">締切確認</button>
                        <button class="quick-q" data-q="採択率は？">採択率</button>
                    </div>
                </div>

                <!-- Right: Search Results -->
                <div class="search-results-panel">
                    <div class="results-header">
                        <h3 class="results-title">
                            <span id="results-count">0</span>件の補助金
                        </h3>
                        <div class="view-controls">
                            <button class="view-btn active" data-view="grid">
                                <svg width="16" height="16" viewBox="0 0 16 16">
                                    <rect x="1" y="1" width="6" height="6"/>
                                    <rect x="9" y="1" width="6" height="6"/>
                                    <rect x="1" y="9" width="6" height="6"/>
                                    <rect x="9" y="9" width="6" height="6"/>
                                </svg>
                            </button>
                            <button class="view-btn" data-view="list">
                                <svg width="16" height="16" viewBox="0 0 16 16">
                                    <rect x="1" y="2" width="14" height="2"/>
                                    <rect x="1" y="7" width="14" height="2"/>
                                    <rect x="1" y="12" width="14" height="2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="results-container" id="results-container">
                        <!-- Initial Featured Grants -->
                        <div class="featured-grants">
                            <?php
                            // 注目の補助金を表示
                            $featured_grants = get_posts([
                                'post_type' => 'grant',
                                'posts_per_page' => 6,
                                'meta_key' => 'is_featured',
                                'meta_value' => '1',
                                'orderby' => 'date',
                                'order' => 'DESC'
                            ]);
                            
                            foreach ($featured_grants as $grant):
                                $amount = get_post_meta($grant->ID, 'max_amount', true);
                                $deadline = get_post_meta($grant->ID, 'deadline', true);
                                $organization = get_post_meta($grant->ID, 'organization', true);
                                $success_rate = get_post_meta($grant->ID, 'grant_success_rate', true);
                            ?>
                            <div class="grant-card" data-id="<?php echo $grant->ID; ?>">
                                <div class="card-badge">注目</div>
                                <h4 class="card-title"><?php echo esc_html($grant->post_title); ?></h4>
                                <div class="card-meta">
                                    <span class="meta-item">
                                        <span class="meta-label">最大</span>
                                        <span class="meta-value"><?php echo esc_html($amount ?: '未定'); ?></span>
                                    </span>
                                    <span class="meta-item">
                                        <span class="meta-label">締切</span>
                                        <span class="meta-value"><?php echo esc_html($deadline ?: '随時'); ?></span>
                                    </span>
                                </div>
                                <p class="card-org"><?php echo esc_html($organization); ?></p>
                                <?php if ($success_rate): ?>
                                <div class="card-rate">
                                    <div class="rate-bar">
                                        <div class="rate-fill" style="width: <?php echo $success_rate; ?>%"></div>
                                    </div>
                                    <span class="rate-text">採択率 <?php echo $success_rate; ?>%</span>
                                </div>
                                <?php endif; ?>
                                <a href="<?php echo get_permalink($grant->ID); ?>" class="card-link">
                                    詳細を見る
                                    <svg width="12" height="12" viewBox="0 0 12 12">
                                        <path d="M2 6h8m0 0L7 3m3 3L7 9"/>
                                    </svg>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="results-loading" id="results-loading">
                        <div class="loading-spinner"></div>
                        <span>検索中...</span>
                    </div>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-number" data-count="<?php echo wp_count_posts('grant')->publish; ?>">0</span>
                    <span class="stat-label">登録補助金</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" data-count="47">0</span>
                    <span class="stat-label">対応都道府県</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">AI対応</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">0.3秒</span>
                    <span class="stat-label">平均応答時間</span>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* AI Search Section Styles */
.ai-search-wrapper {
    position: relative;
    padding: 80px 0;
    background: #fff;
    font-family: -apple-system, "SF Pro Display", "Helvetica Neue", "Hiragino Sans", sans-serif;
    overflow: hidden;
}

.ai-search-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, #000, transparent);
}

.ai-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Header */
.ai-header {
    text-align: center;
    margin-bottom: 60px;
}

.ai-badge {
    display: inline-block;
    padding: 6px 16px;
    background: #000;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.15em;
    border-radius: 20px;
    margin-bottom: 20px;
}

.ai-title {
    margin: 0;
}

.title-en {
    display: block;
    font-size: 48px;
    font-weight: 900;
    letter-spacing: -0.02em;
    line-height: 1;
    margin-bottom: 8px;
    background: linear-gradient(135deg, #000 0%, #333 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.title-jp {
    display: block;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 0.1em;
    color: #666;
}

.ai-subtitle {
    margin: 16px 0 0;
    font-size: 16px;
    color: #333;
}

/* Search Bar */
.ai-search-bar {
    position: relative;
    max-width: 720px;
    margin: 0 auto 32px;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: #f8f8f8;
    border: 2px solid transparent;
    border-radius: 60px;
    transition: all 0.3s;
}

.search-input-wrapper:focus-within {
    background: #fff;
    border-color: #000;
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}

.search-input {
    flex: 1;
    padding: 18px 24px;
    background: none;
    border: none;
    font-size: 16px;
    outline: none;
}

.search-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-right: 8px;
}

.voice-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: none;
    color: #999;
    cursor: pointer;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.voice-btn:hover {
    background: #f0f0f0;
    color: #000;
}

.voice-btn.recording {
    background: #ef4444;
    color: #fff;
    animation: pulse-recording 1s infinite;
}

@keyframes pulse-recording {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Voice Status */
.voice-status {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    margin-top: 8px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    z-index: 100;
    display: none;
    white-space: nowrap;
}

.voice-status-recording {
    background: #ef4444;
    color: #fff;
}

.voice-status-processing {
    background: #f59e0b;
    color: #fff;
}

.voice-status-success {
    background: #10b981;
    color: #fff;
}

.voice-status-error {
    background: #ef4444;
    color: #fff;
}

.voice-status-waiting {
    background: #6b7280;
    color: #fff;
}

/* Search Improvements */
.search-improvements {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 8px;
    background: #fffbeb;
    border: 1px solid #fbbf24;
    border-radius: 8px;
    padding: 12px;
    font-size: 13px;
    z-index: 10;
    transition: opacity 0.3s;
}

.improvement-header {
    font-weight: 600;
    color: #d97706;
    margin-bottom: 8px;
}

.improvement-list {
    margin: 0;
    padding-left: 20px;
    color: #92400e;
}

.improvement-list li {
    margin-bottom: 4px;
}

/* Chat Suggestions */
.chat-suggestions {
    margin-top: 12px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.suggestions-label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 8px;
}

.suggestions-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.suggestion-btn {
    padding: 6px 12px;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 16px;
    font-size: 11px;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}

.suggestion-btn:hover {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
}

/* Typing Cursor */
.typing-cursor {
    animation: blink 1s infinite;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }

.search-btn {
    height: 44px;
    padding: 0 24px;
    background: #000;
    color: #fff;
    border: none;
    border-radius: 22px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.search-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.search-btn:active {
    transform: scale(0.98);
}

.btn-icon {
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
}

/* Search Suggestions */
.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 8px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    display: none;
    z-index: 10;
}

.search-suggestions.active {
    display: block;
}

.suggestion-item {
    padding: 12px 20px;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    gap: 12px;
}

.suggestion-item:hover {
    background: #f8f8f8;
}

.suggestion-icon {
    width: 32px;
    height: 32px;
    background: #f0f0f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

/* Quick Filters */
.quick-filters {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 48px;
}

.filter-chip {
    padding: 10px 20px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 24px;
    font-size: 13px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-chip:hover {
    border-color: #000;
    color: #000;
    transform: translateY(-2px);
}

.filter-chip.active {
    background: #000;
    color: #fff;
    border-color: #000;
}

/* Main Content */
.ai-main-content {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 32px;
    margin-bottom: 48px;
}

/* AI Assistant Panel */
.ai-assistant-panel {
    background: #fafafa;
    border-radius: 20px;
    border: 1px solid #e0e0e0;
    display: flex;
    flex-direction: column;
    height: 600px;
}

.assistant-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.assistant-avatar {
    position: relative;
    width: 48px;
    height: 48px;
}

.avatar-ring {
    position: absolute;
    inset: 0;
    border: 2px solid #000;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.05); }
}

.avatar-icon {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
    color: #fff;
    border-radius: 50%;
    font-size: 14px;
    font-weight: 700;
}

.assistant-name {
    font-size: 14px;
    font-weight: 600;
    margin: 0;
}

.assistant-status {
    font-size: 11px;
    color: #10b981;
}

/* Chat Messages */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.message {
    display: flex;
    gap: 12px;
    animation: messageIn 0.3s ease-out;
}

@keyframes messageIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message-user {
    flex-direction: row-reverse;
}

.message-bubble {
    max-width: 80%;
    padding: 12px 16px;
    background: #fff;
    border-radius: 16px;
    font-size: 13px;
    line-height: 1.6;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.message-user .message-bubble {
    background: #000;
    color: #fff;
}

/* Chat Input */
.chat-input-area {
    padding: 16px;
    border-top: 1px solid #e0e0e0;
    position: relative;
}

.typing-indicator {
    position: absolute;
    top: -24px;
    left: 20px;
    display: none;
    gap: 4px;
}

.typing-indicator.active {
    display: flex;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background: #999;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

.chat-input {
    width: 100%;
    padding: 12px 48px 12px 16px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 24px;
    font-size: 13px;
    resize: none;
    outline: none;
    transition: all 0.2s;
}

.chat-input:focus {
    border-color: #000;
}

.chat-send-btn {
    position: absolute;
    right: 24px;
    bottom: 24px;
    width: 32px;
    height: 32px;
    background: #000;
    color: #fff;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.chat-send-btn:hover {
    transform: scale(1.1);
}

.chat-send-btn:active {
    transform: scale(0.95);
}

/* Quick Questions */
.quick-questions {
    padding: 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.quick-q {
    padding: 6px 12px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 16px;
    font-size: 11px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
}

.quick-q:hover {
    background: #000;
    color: #fff;
    border-color: #000;
}

/* Search Results Panel */
.search-results-panel {
    background: #fafafa;
    border-radius: 20px;
    padding: 24px;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.results-title {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

#results-count {
    font-size: 24px;
    font-weight: 900;
    color: #000;
}

.view-controls {
    display: flex;
    gap: 4px;
    padding: 4px;
    background: #fff;
    border-radius: 8px;
}

.view-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: none;
    color: #999;
    cursor: pointer;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.view-btn:hover {
    background: #f0f0f0;
}

.view-btn.active {
    background: #000;
    color: #fff;
}

.view-btn svg {
    fill: currentColor;
}

/* Grant Cards */
.featured-grants,
.results-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.grant-card {
    position: relative;
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    border: 1px solid #e0e0e0;
    transition: all 0.3s;
    cursor: pointer;
}

.grant-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    border-color: #000;
}

.card-badge {
    position: absolute;
    top: -8px;
    right: 16px;
    padding: 4px 12px;
    background: #000;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.05em;
    border-radius: 12px;
}

.card-title {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 12px;
    line-height: 1.4;
}

.card-meta {
    display: flex;
    gap: 16px;
    margin-bottom: 12px;
}

.meta-item {
    display: flex;
    flex-direction: column;
}

.meta-label {
    font-size: 10px;
    color: #999;
    margin-bottom: 2px;
}

.meta-value {
    font-size: 14px;
    font-weight: 700;
    color: #000;
}

.card-org {
    font-size: 11px;
    color: #666;
    margin: 0 0 12px;
}

.card-rate {
    margin-bottom: 16px;
}

.rate-bar {
    height: 4px;
    background: #e0e0e0;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 4px;
}

.rate-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #34d399);
    transition: width 1s ease-out;
}

.rate-text {
    font-size: 10px;
    color: #666;
}

.card-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #000;
    text-decoration: none;
    transition: all 0.2s;
}

.card-link:hover {
    gap: 10px;
}

.card-link svg {
    stroke: currentColor;
    stroke-width: 2;
    fill: none;
}

/* Loading State */
.results-loading {
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px;
    color: #666;
    font-size: 14px;
}

.results-loading.active {
    display: flex;
}

.loading-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #f0f0f0;
    border-top-color: #000;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-bottom: 12px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Stats Bar */
.stats-bar {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    padding: 32px;
    background: linear-gradient(135deg, #f8f8f8 0%, #fff 100%);
    border-radius: 20px;
    text-align: center;
}

.stat-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.stat-number {
    font-size: 32px;
    font-weight: 900;
    color: #000;
    font-variant-numeric: tabular-nums;
}

.stat-label {
    font-size: 12px;
    color: #666;
    letter-spacing: 0.05em;
}

/* Responsive */
@media (max-width: 1024px) {
    .ai-main-content {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    .ai-assistant-panel {
        height: 400px;
    }
}

@media (max-width: 768px) {
    .title-en {
        font-size: 32px;
    }
    
    .quick-filters {
        overflow-x: auto;
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
    }
    
    .stats-bar {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        padding: 20px;
    }
    
    .featured-grants,
    .results-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        API_URL: '<?php echo admin_url("admin-ajax.php"); ?>',
        NONCE: '<?php echo $nonce; ?>',
        SESSION_ID: '<?php echo $session_id; ?>',
        TYPING_DELAY: 30,
        DEBOUNCE_DELAY: 300,
    };

    // AI Search Controller
    class AISearchController {
        constructor() {
            this.state = {
                isSearching: false,
                isTyping: false,
                currentFilter: 'all',
                currentView: 'grid',
                results: [],
                chatHistory: [],
            };
            
            this.elements = {};
            this.init();
        }

        init() {
            this.cacheElements();
            this.bindEvents();
            this.initAnimations();
            this.animateStats();
        }

        cacheElements() {
            this.elements = {
                searchInput: document.getElementById('ai-search-input'),
                searchBtn: document.getElementById('ai-search-btn'),
                suggestions: document.getElementById('search-suggestions'),
                filterChips: document.querySelectorAll('.filter-chip'),
                chatMessages: document.getElementById('chat-messages'),
                chatInput: document.getElementById('chat-input'),
                chatSend: document.getElementById('chat-send'),
                typingIndicator: document.getElementById('typing-indicator'),
                resultsContainer: document.getElementById('results-container'),
                resultsLoading: document.getElementById('results-loading'),
                resultsCount: document.getElementById('results-count'),
                viewBtns: document.querySelectorAll('.view-btn'),
                quickQuestions: document.querySelectorAll('.quick-q'),
                voiceBtn: document.querySelector('.voice-btn'),
            };
        }

        bindEvents() {
            // Search events
            this.elements.searchInput?.addEventListener('input', this.debounce(this.handleSearchInput.bind(this), CONFIG.DEBOUNCE_DELAY));
            this.elements.searchInput?.addEventListener('focus', this.showSuggestions.bind(this));
            this.elements.searchBtn?.addEventListener('click', this.performSearch.bind(this));
            
            // Enter key for search
            this.elements.searchInput?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.performSearch();
                }
            });

            // Filter chips
            this.elements.filterChips.forEach(chip => {
                chip.addEventListener('click', this.handleFilterClick.bind(this));
            });

            // Chat events
            this.elements.chatInput?.addEventListener('input', this.autoResizeTextarea.bind(this));
            this.elements.chatSend?.addEventListener('click', this.sendChatMessage.bind(this));
            
            // Enter key for chat (Shift+Enter for new line)
            this.elements.chatInput?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendChatMessage();
                }
            });

            // Quick questions
            this.elements.quickQuestions.forEach(btn => {
                btn.addEventListener('click', this.handleQuickQuestion.bind(this));
            });

            // View controls
            this.elements.viewBtns.forEach(btn => {
                btn.addEventListener('click', this.handleViewChange.bind(this));
            });

            // Voice input
            this.elements.voiceBtn?.addEventListener('click', this.startVoiceInput.bind(this));

            // Click outside to close suggestions
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.ai-search-bar')) {
                    this.hideSuggestions();
                }
            });
        }

        // Search Methods - Enhanced with real API integration
        async handleSearchInput(e) {
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                this.hideSuggestions();
                return;
            }

            // リアルタイム検索候補を取得
            try {
                const suggestions = await this.fetchSuggestions(query);
                this.displaySuggestions(suggestions);
            } catch (error) {
                console.warn('検索候補の取得に失敗:', error);
                this.hideSuggestions();
            }
        }

        async fetchSuggestions(query) {
            const formData = new FormData();
            formData.append('action', 'gi_get_search_suggestions');
            formData.append('nonce', CONFIG.NONCE);
            formData.append('query', query);
            formData.append('limit', '8');

            const response = await fetch(CONFIG.API_URL, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();
            
            if (data.success && data.data) {
                return data.data.map(item => ({
                    icon: this.getIconForType(item.type),
                    text: item.label || item.value,
                    value: item.value,
                    type: item.type,
                    url: item.url,
                    filter: item.filter
                }));
            }
            
            // フォールバック用のサジェスト
            return this.getFallbackSuggestions(query);
        }
        
        getIconForType(type) {
            const icons = {
                'grant': '📋',
                'organization': '🏢', 
                'grant_category': '📁',
                'grant_prefecture': '📍',
                'grant_tag': '🏷️'
            };
            return icons[type] || '🔍';
        }
        
        getFallbackSuggestions(query) {
            const fallbacks = [
                { icon: '🏭', text: 'ものづくり補助金', type: 'grant' },
                { icon: '💻', text: 'IT導入補助金', type: 'grant' },
                { icon: '🏪', text: '小規模事業者持続化補助金', type: 'grant' },
                { icon: '🔄', text: '事業再構築補助金', type: 'grant' },
                { icon: '👥', text: '雇用調整助成金', type: 'grant' },
                { icon: '🌱', text: '創業支援補助金', type: 'grant' }
            ];
            
            return fallbacks.filter(s => 
                s.text.toLowerCase().includes(query.toLowerCase()) ||
                query.toLowerCase().includes(s.text.toLowerCase())
            ).slice(0, 5);
        }

        displaySuggestions(suggestions) {
            const container = this.elements.suggestions;
            if (!container) return;

            if (suggestions.length === 0) {
                this.hideSuggestions();
                return;
            }

            container.innerHTML = suggestions.map(s => `
                <div class="suggestion-item" data-text="${s.text}">
                    <span class="suggestion-icon">${s.icon}</span>
                    <span>${s.text}</span>
                </div>
            `).join('');

            container.classList.add('active');

            // Bind click events
            container.querySelectorAll('.suggestion-item').forEach(item => {
                item.addEventListener('click', () => {
                    this.elements.searchInput.value = item.dataset.text;
                    this.hideSuggestions();
                    this.performSearch();
                });
            });
        }

        showSuggestions() {
            if (this.elements.searchInput.value.length >= 2) {
                this.elements.suggestions?.classList.add('active');
            }
        }

        hideSuggestions() {
            this.elements.suggestions?.classList.remove('active');
        }

        async performSearch() {
            const query = this.elements.searchInput.value.trim();
            if (!query || this.state.isSearching) return;

            this.state.isSearching = true;
            this.showLoading();

            try {
                const formData = new FormData();
                formData.append('action', 'gi_ai_search');
                formData.append('nonce', CONFIG.NONCE);
                formData.append('query', query);
                formData.append('filter', this.state.currentFilter);
                formData.append('session_id', CONFIG.SESSION_ID);

                const response = await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.success) {
                    this.displayResults(data.data.grants);
                    this.updateResultsCount(data.data.count);
                    
                    // Add AI response to chat
                    if (data.data.ai_response) {
                        this.addChatMessage(data.data.ai_response, 'ai');
                    }
                } else {
                    this.showError('検索エラーが発生しました');
                }
            } catch (error) {
                console.error('Search error:', error);
                this.showError('通信エラーが発生しました');
            } finally {
                this.state.isSearching = false;
                this.hideLoading();
            }
        }

        displayResults(grants) {
            const container = this.elements.resultsContainer;
            if (!container || !grants) return;

            if (grants.length === 0) {
                container.innerHTML = '<div class="no-results">該当する補助金が見つかりませんでした</div>';
                return;
            }

            container.innerHTML = grants.map(grant => this.createGrantCard(grant)).join('');
            this.animateCards();
        }

        createGrantCard(grant) {
            return `
                <div class="grant-card" data-id="${grant.id}" style="animation-delay: ${Math.random() * 0.2}s">
                    ${grant.featured ? '<div class="card-badge">注目</div>' : ''}
                    <h4 class="card-title">${grant.title}</h4>
                    <div class="card-meta">
                        <span class="meta-item">
                            <span class="meta-label">最大</span>
                            <span class="meta-value">${grant.amount || '未定'}</span>
                        </span>
                        <span class="meta-item">
                            <span class="meta-label">締切</span>
                            <span class="meta-value">${grant.deadline || '随時'}</span>
                        </span>
                    </div>
                    <p class="card-org">${grant.organization || ''}</p>
                    ${grant.success_rate ? `
                        <div class="card-rate">
                            <div class="rate-bar">
                                <div class="rate-fill" style="width: ${grant.success_rate}%"></div>
                            </div>
                            <span class="rate-text">採択率 ${grant.success_rate}%</span>
                        </div>
                    ` : ''}
                    <a href="${grant.permalink}" class="card-link">
                        詳細を見る
                        <svg width="12" height="12" viewBox="0 0 12 12">
                            <path d="M2 6h8m0 0L7 3m3 3L7 9"/>
                        </svg>
                    </a>
                </div>
            `;
        }

        updateResultsCount(count) {
            if (this.elements.resultsCount) {
                this.animateNumber(this.elements.resultsCount, count);
            }
        }

        // Filter Methods
        handleFilterClick(e) {
            const filter = e.currentTarget.dataset.filter;
            
            // Update active state
            this.elements.filterChips.forEach(chip => {
                chip.classList.toggle('active', chip.dataset.filter === filter);
            });

            this.state.currentFilter = filter;
            
            // Perform search with new filter
            if (this.elements.searchInput.value) {
                this.performSearch();
            }
        }

        // Chat Methods
        async sendChatMessage() {
            const message = this.elements.chatInput.value.trim();
            if (!message || this.state.isTyping) return;

            // Clear input
            this.elements.chatInput.value = '';
            this.autoResizeTextarea();

            // Add user message
            this.addChatMessage(message, 'user');

            // Show typing indicator
            this.showTyping();

            try {
                const formData = new FormData();
                formData.append('action', 'gi_ai_chat');
                formData.append('nonce', CONFIG.NONCE);
                formData.append('message', message);
                formData.append('session_id', CONFIG.SESSION_ID);

                const response = await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.success) {
                    // Type AI response
                    this.typeMessage(data.data.response);
                    
                    // Update search results if needed
                    if (data.data.related_grants) {
                        this.displayResults(data.data.related_grants);
                    }
                } else {
                    this.addChatMessage('申し訳ございません。エラーが発生しました。', 'ai');
                }
            } catch (error) {
                console.error('Chat error:', error);
                this.addChatMessage('通信エラーが発生しました。', 'ai');
            } finally {
                this.hideTyping();
            }
        }

        addChatMessage(text, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message message-${type}`;
            messageDiv.innerHTML = `<div class="message-bubble">${text}</div>`;
            
            this.elements.chatMessages.appendChild(messageDiv);
            this.scrollChatToBottom();
        }

        typeMessage(text) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message message-ai';
            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            messageDiv.appendChild(bubble);
            
            this.elements.chatMessages.appendChild(messageDiv);
            
            let index = 0;
            const typeChar = () => {
                if (index < text.length) {
                    bubble.textContent += text[index];
                    index++;
                    this.scrollChatToBottom();
                    setTimeout(typeChar, CONFIG.TYPING_DELAY);
                }
            };
            
            typeChar();
        }

        handleQuickQuestion(e) {
            const question = e.currentTarget.dataset.q;
            this.elements.chatInput.value = question;
            this.autoResizeTextarea();
            this.sendChatMessage();
        }

        autoResizeTextarea() {
            const textarea = this.elements.chatInput;
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        }

        scrollChatToBottom() {
            this.elements.chatMessages.scrollTop = this.elements.chatMessages.scrollHeight;
        }

        showTyping() {
            this.state.isTyping = true;
            this.elements.typingIndicator?.classList.add('active');
        }

        hideTyping() {
            this.state.isTyping = false;
            this.elements.typingIndicator?.classList.remove('active');
        }

        // View Methods
        handleViewChange(e) {
            const view = e.currentTarget.dataset.view;
            
            this.elements.viewBtns.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.view === view);
            });

            this.state.currentView = view;
            
            // Update results display
            const container = this.elements.resultsContainer;
            if (container) {
                container.className = view === 'list' ? 'results-list' : 'featured-grants';
            }
        }

        // Voice Input - Enhanced multi-browser support
        async startVoiceInput() {
            // ブラウザ対応チェック
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            
            if (!SpeechRecognition) {
                // フォールバック: サーバーサイド音声認識を試行
                this.startServerSideVoiceRecognition();
                return;
            }

            const recognition = new SpeechRecognition();
            recognition.lang = 'ja-JP';
            recognition.interimResults = true;
            recognition.continuous = false;
            recognition.maxAlternatives = 3;

            recognition.onstart = () => {
                this.elements.voiceBtn?.classList.add('recording');
                this.showVoiceStatus('音声入力中...', 'recording');
            };

            recognition.onresult = (event) => {
                let finalTranscript = '';
                let interimTranscript = '';
                
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const transcript = event.results[i][0].transcript;
                    
                    if (event.results[i].isFinal) {
                        finalTranscript += transcript;
                    } else {
                        interimTranscript += transcript;
                    }
                }
                
                // 中間結果をリアルタイム表示
                if (interimTranscript) {
                    this.elements.searchInput.value = finalTranscript + interimTranscript;
                    this.showVoiceStatus('認識中: ' + interimTranscript, 'processing');
                }
                
                // 最終結果で検索実行
                if (finalTranscript) {
                    this.elements.searchInput.value = finalTranscript;
                    this.showVoiceStatus('認識完了', 'success');
                    setTimeout(() => {
                        this.performSearch();
                    }, 500);
                }
            };

            recognition.onerror = (event) => {
                console.error('音声認識エラー:', event.error);
                
                let errorMessage = '音声認識エラーが発生しました';
                switch (event.error) {
                    case 'no-speech':
                        errorMessage = '音声が検出されませんでした。もう一度お試しください。';
                        break;
                    case 'audio-capture':
                        errorMessage = 'マイクにアクセスできません。ブラウザの設定を確認してください。';
                        break;
                    case 'not-allowed':
                        errorMessage = 'マイクの使用が許可されていません。設定で許可してください。';
                        break;
                    case 'network':
                        errorMessage = 'ネットワークエラーが発生しました。';
                        break;
                }
                
                this.showVoiceStatus(errorMessage, 'error');
            };

            recognition.onend = () => {
                this.elements.voiceBtn?.classList.remove('recording');
                setTimeout(() => {
                    this.hideVoiceStatus();
                }, 2000);
            };

            try {
                recognition.start();
            } catch (error) {
                console.error('音声認識開始エラー:', error);
                this.showVoiceStatus('音声入力を開始できませんでした', 'error');
            }
        }
        
        // サーバーサイド音声認識（フォールバック）
        async startServerSideVoiceRecognition() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.showVoiceStatus('このブラウザは音声入力をサポートしていません', 'error');
                return;
            }
            
            try {
                this.showVoiceStatus('マイクへのアクセスを許可してください', 'waiting');
                
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                
                this.showVoiceStatus('音声を録音中...', 'recording');
                this.elements.voiceBtn?.classList.add('recording');
                
                // MediaRecorder を使用して音声を録音
                const mediaRecorder = new MediaRecorder(stream);
                const audioChunks = [];
                
                mediaRecorder.ondataavailable = (event) => {
                    audioChunks.push(event.data);
                };
                
                mediaRecorder.onstop = async () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                    await this.sendAudioToServer(audioBlob);
                    
                    // ストリームを停止
                    stream.getTracks().forEach(track => track.stop());
                    
                    this.elements.voiceBtn?.classList.remove('recording');
                };
                
                mediaRecorder.start();
                
                // 5秒後に自動停止
                setTimeout(() => {
                    if (mediaRecorder.state === 'recording') {
                        mediaRecorder.stop();
                    }
                }, 5000);
                
            } catch (error) {
                console.error('音声録音エラー:', error);
                this.showVoiceStatus('マイクにアクセスできませんでした', 'error');
            }
        }
        
        // サーバーに音声ファイルを送信
        async sendAudioToServer(audioBlob) {
            this.showVoiceStatus('音声を解析中...', 'processing');
            
            try {
                const formData = new FormData();
                formData.append('action', 'gi_voice_to_text');
                formData.append('nonce', CONFIG.NONCE);
                formData.append('audio', audioBlob, 'voice.wav');
                formData.append('session_id', CONFIG.SESSION_ID);
                
                const response = await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.elements.searchInput.value = data.data.text;
                    this.showVoiceStatus('音声認識完了', 'success');
                    
                    // 認識結果で検索を実行
                    setTimeout(() => {
                        this.performSearch();
                    }, 500);
                } else {
                    this.showVoiceStatus('音声認識に失敗しました', 'error');
                }
                
            } catch (error) {
                console.error('音声送信エラー:', error);
                this.showVoiceStatus('音声の送信に失敗しました', 'error');
            }
        }
        
        // 音声入力状態の表示
        showVoiceStatus(message, type = 'info') {
            let statusElement = document.querySelector('.voice-status');
            
            if (!statusElement) {
                statusElement = document.createElement('div');
                statusElement.className = 'voice-status';
                this.elements.searchInput.parentNode.appendChild(statusElement);
            }
            
            statusElement.className = `voice-status voice-status-${type}`;
            statusElement.textContent = message;
            statusElement.style.display = 'block';
        }
        
        // 音声入力状態を隠す
        hideVoiceStatus() {
            const statusElement = document.querySelector('.voice-status');
            if (statusElement) {
                statusElement.style.display = 'none';
            }
        }

        // Loading States
        showLoading() {
            this.elements.resultsLoading?.classList.add('active');
            this.elements.resultsContainer?.classList.add('loading');
        }

        hideLoading() {
            this.elements.resultsLoading?.classList.remove('active');
            this.elements.resultsContainer?.classList.remove('loading');
        }

        showError(message) {
            const container = this.elements.resultsContainer;
            if (container) {
                container.innerHTML = `<div class="error-message">${message}</div>`;
            }
        }

        // Animation Methods
        initAnimations() {
            // Intersection Observer for scroll animations
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.grant-card').forEach(card => {
                observer.observe(card);
            });
        }

        animateCards() {
            const cards = document.querySelectorAll('.grant-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50);
            });
        }

        animateStats() {
            const statNumbers = document.querySelectorAll('.stat-number[data-count]');
            
            statNumbers.forEach(stat => {
                const target = parseInt(stat.dataset.count);
                this.animateNumber(stat, target);
            });
        }

        animateNumber(element, target) {
            const duration = 1500;
            const step = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current).toLocaleString();
            }, 16);
        }

        // Utility Methods
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new AISearchController();
        });
    } else {
        new AISearchController();
    }

})();
</script>

<?php
// AJAX Handlers - 実際のAI機能と統合
add_action('wp_ajax_gi_ai_search', 'gi_handle_enhanced_ai_search');
add_action('wp_ajax_nopriv_gi_ai_search', 'gi_handle_enhanced_ai_search');
add_action('wp_ajax_gi_ai_chat', 'gi_handle_real_ai_chat');
add_action('wp_ajax_nopriv_gi_ai_chat', 'gi_handle_real_ai_chat');
add_action('wp_ajax_gi_voice_to_text', 'gi_handle_voice_to_text');
add_action('wp_ajax_nopriv_gi_voice_to_text', 'gi_handle_voice_to_text');
add_action('wp_ajax_gi_save_search_session', 'gi_save_search_session');
add_action('wp_ajax_nopriv_gi_save_search_session', 'gi_save_search_session');

/**
 * 高度なAI検索処理（統合版）
 */
function gi_handle_enhanced_ai_search() {
    check_ajax_referer('gi_ai_search_nonce', 'nonce');
    
    $query = sanitize_text_field($_POST['query'] ?? '');
    $filter = sanitize_text_field($_POST['filter'] ?? 'all');
    $session_id = sanitize_text_field($_POST['session_id'] ?? '');
    $user_context = json_decode(stripslashes($_POST['user_context'] ?? '{}'), true);
    
    // AIコンセルジュのインスタンス取得
    if (class_exists('GI_AI_Concierge')) {
        $ai_concierge = GI_AI_Concierge::getInstance();
        
        // セマンティック検索を実行
        $semantic_results = $ai_concierge->perform_semantic_search($query, [
            'filter' => $filter,
            'user_context' => $user_context,
            'session_id' => $session_id
        ]);
        
        if ($semantic_results['success']) {
            wp_send_json_success($semantic_results['data']);
            return;
        }
    }
    
    // フォールバック: 従来の検索処理を使用
    $search_params = [
        'search' => $query,
        'categories' => $filter !== 'all' ? [$filter] : [],
        'nonce' => $_POST['nonce'],
        'page' => 1,
        'posts_per_page' => 12
    ];
    
    // 既存のgi_ajax_load_grants関数を利用
    $_POST = array_merge($_POST, $search_params);
    
    if (function_exists('gi_ajax_load_grants')) {
        gi_ajax_load_grants();
    } else {
        gi_fallback_search($query, $filter);
    }
}

/**
 * 実際のAIチャット処理
 */
function gi_handle_real_ai_chat() {
    check_ajax_referer('gi_ai_search_nonce', 'nonce');
    
    $message = sanitize_text_field($_POST['message'] ?? '');
    $session_id = sanitize_text_field($_POST['session_id'] ?? '');
    $conversation_history = json_decode(stripslashes($_POST['conversation_history'] ?? '[]'), true);
    
    if (empty($message)) {
        wp_send_json_error('メッセージが空です');
    }
    
    // AIコンセルジュでチャット処理
    if (class_exists('GI_AI_Concierge')) {
        $ai_concierge = GI_AI_Concierge::getInstance();
        
        $chat_result = $ai_concierge->process_chat_message([
            'message' => $message,
            'session_id' => $session_id,
            'conversation_history' => $conversation_history,
            'user_id' => get_current_user_id(),
            'context' => [
                'page' => 'search',
                'timestamp' => current_time('c')
            ]
        ]);
        
        if ($chat_result['success']) {
            wp_send_json_success($chat_result['data']);
            return;
        }
    }
    
    // フォールバック: 基本的なキーワードベースの回答
    $fallback_response = gi_generate_fallback_chat_response($message);
    
    wp_send_json_success([
        'response' => $fallback_response['message'],
        'related_grants' => $fallback_response['grants'],
        'suggestions' => $fallback_response['suggestions'],
        'session_id' => $session_id,
        'message_id' => uniqid('msg_')
    ]);
}

/**
 * 音声テキスト変換処理
 */
function gi_handle_voice_to_text() {
    check_ajax_referer('gi_ai_search_nonce', 'nonce');
    
    if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error('音声ファイルのアップロードに失敗しました');
    }
    
    // 音声認識API（OpenAI Whisper等）を使用
    if (class_exists('GI_AI_Concierge')) {
        $ai_concierge = GI_AI_Concierge::getInstance();
        
        $transcription = $ai_concierge->transcribe_audio($_FILES['audio']);
        
        if ($transcription['success']) {
            wp_send_json_success([
                'text' => $transcription['text'],
                'confidence' => $transcription['confidence'] ?? 0.95
            ]);
            return;
        }
    }
    
    wp_send_json_error('音声認識に失敗しました。もう一度お試しください。');
}

/**
 * 検索セッションの保存
 */
function gi_save_search_session() {
    check_ajax_referer('gi_ai_search_nonce', 'nonce');
    
    $session_data = [
        'session_id' => sanitize_text_field($_POST['session_id'] ?? ''),
        'search_query' => sanitize_text_field($_POST['search_query'] ?? ''),
        'filters_applied' => json_decode(stripslashes($_POST['filters_applied'] ?? '{}'), true),
        'results_count' => intval($_POST['results_count'] ?? 0),
        'user_interactions' => json_decode(stripslashes($_POST['user_interactions'] ?? '[]'), true),
        'timestamp' => current_time('c'),
        'user_id' => get_current_user_id(),
        'ip_address' => gi_get_user_ip()
    ];
    
    // データベースに保存
    global $wpdb;
    $table_name = $wpdb->prefix . 'gi_search_sessions';
    
    $result = $wpdb->insert(
        $table_name,
        [
            'session_id' => $session_data['session_id'],
            'user_id' => $session_data['user_id'],
            'search_data' => json_encode($session_data),
            'created_at' => current_time('mysql')
        ],
        ['%s', '%d', '%s', '%s']
    );
    
    if ($result !== false) {
        wp_send_json_success(['message' => 'セッションが保存されました']);
    } else {
        wp_send_json_error('セッションの保存に失敗しました');
    }
}

/**
 * フォールバック検索処理
 */
function gi_fallback_search($query, $filter) {
    $args = [
        'post_type' => 'grant',
        's' => $query,
        'posts_per_page' => 12,
        'post_status' => 'publish',
        'orderby' => 'relevance',
        'order' => 'DESC'
    ];
    
    if ($filter !== 'all') {
        $args['tax_query'] = [[
            'taxonomy' => 'grant_category',
            'field' => 'slug',
            'terms' => $filter
        ]];
    }
    
    $wp_query = new WP_Query($args);
    $grants = [];
    
    if ($wp_query->have_posts()) {
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            $post_id = get_the_ID();
            
            $grants[] = [
                'id' => $post_id,
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'excerpt' => get_the_excerpt(),
                'amount' => get_post_meta($post_id, 'max_amount', true),
                'deadline' => get_post_meta($post_id, 'deadline', true),
                'organization' => get_post_meta($post_id, 'organization', true),
                'success_rate' => get_post_meta($post_id, 'grant_success_rate', true),
                'featured' => get_post_meta($post_id, 'is_featured', true),
                'html' => function_exists('gi_render_card_unified') ? 
                         gi_render_card_unified($post_id, 'grid') : ''
            ];
        }
        wp_reset_postdata();
    }
    
    $ai_response = gi_generate_search_response($query, count($grants));
    
    wp_send_json_success([
        'grants' => $grants,
        'count' => $wp_query->found_posts,
        'ai_response' => $ai_response,
        'suggestions' => gi_generate_search_suggestions($query),
        'search_improvements' => gi_analyze_search_query($query)
    ]);
}

/**
 * フォールバックチャット応答生成
 */
function gi_generate_fallback_chat_response($message) {
    $message_lower = strtolower($message);
    
    // キーワードベースの応答パターン
    $response_patterns = [
        'IT' => [
            'message' => 'IT関連の補助金をお探しですね。IT導入補助金や小規模事業者持続化補助金などがおすすめです。',
            'keywords' => ['it', 'システム', 'デジタル', 'ソフトウェア']
        ],
        'ものづくり' => [
            'message' => 'ものづくり関連でしたら、ものづくり補助金が最適です。設備投資や技術開発に活用できます。',
            'keywords' => ['ものづくり', '製造', '設備', '機械']
        ],
        '創業' => [
            'message' => '創業支援の補助金ですね。創業支援補助金や小規模事業者持続化補助金をご検討ください。',
            'keywords' => ['創業', '起業', '開業', 'スタートアップ']
        ],
        '雇用' => [
            'message' => '雇用関連の支援制度については、雇用調整助成金や人材確保等支援助成金があります。',
            'keywords' => ['雇用', '採用', '人材', '労働']
        ]
    ];
    
    $matched_response = null;
    foreach ($response_patterns as $category => $pattern) {
        foreach ($pattern['keywords'] as $keyword) {
            if (strpos($message_lower, $keyword) !== false) {
                $matched_response = $pattern['message'];
                break 2;
            }
        }
    }
    
    if (!$matched_response) {
        $matched_response = "「{$message}」についてお調べします。より具体的な業種や目的をお教えいただけると、より適切な補助金をご提案できます。";
    }
    
    // 関連する補助金を検索
    $related_grants = [];
    if (function_exists('gi_ajax_load_grants')) {
        $search_args = [
            'post_type' => 'grant',
            's' => $message,
            'posts_per_page' => 3,
            'post_status' => 'publish'
        ];
        
        $query = new WP_Query($search_args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $related_grants[] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'permalink' => get_permalink()
                ];
            }
            wp_reset_postdata();
        }
    }
    
    return [
        'message' => $matched_response,
        'grants' => $related_grants,
        'suggestions' => [
            '申請の流れを教えて',
            '必要書類は何ですか？',
            '採択率の高い補助金は？',
            '締切が近い補助金を教えて'
        ]
    ];
}

/**
 * 検索応答の生成
 */
function gi_generate_search_response($query, $count) {
    if ($count === 0) {
        return "「{$query}」に該当する補助金が見つかりませんでした。キーワードを変更して再度検索してみてください。";
    } elseif ($count === 1) {
        return "「{$query}」について1件の補助金が見つかりました。詳細をご確認ください。";
    } else {
        return "「{$query}」について{$count}件の補助金が見つかりました。条件に合うものをお選びください。";
    }
}

/**
 * 検索提案の生成
 */
function gi_generate_search_suggestions($query) {
    $base_suggestions = [
        $query . ' 申請方法',
        $query . ' 必要書類',
        $query . ' 採択率',
        $query . ' 締切'
    ];
    
    // よく検索される関連キーワード
    $popular_terms = ['IT導入', 'ものづくり', '持続化', '事業再構築', '雇用調整'];
    
    return array_merge($base_suggestions, array_slice($popular_terms, 0, 3));
}

/**
 * 検索クエリの分析
 */
function gi_analyze_search_query($query) {
    $improvements = [];
    
    if (strlen($query) < 3) {
        $improvements[] = 'より具体的なキーワードで検索してみてください';
    }
    
    if (!preg_match('/[ぁ-んァ-ヶ一-龠]/u', $query)) {
        $improvements[] = '日本語のキーワードも試してみてください';
    }
    
    return $improvements;
}

/**
 * ユーザーIP取得
 */
function gi_get_user_ip() {
    $ip_fields = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ip_fields as $field) {
        if (!empty($_SERVER[$field])) {
            $ip = $_SERVER[$field];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}
?>