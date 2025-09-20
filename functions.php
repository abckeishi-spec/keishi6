<?php
/**
 * Grant Insight Perfect - Functions File Loader
 * @package Grant_Insight_Perfect
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// テーマバージョン定数（重複チェック追加）
if (!defined('GI_THEME_VERSION')) {
    define('GI_THEME_VERSION', '6.2.2');
}
if (!defined('GI_THEME_PREFIX')) {
    define('GI_THEME_PREFIX', 'gi_');
}

// 機能ファイルの読み込み
$inc_dir = get_template_directory() . '/inc/';

// ファイル存在チェックを追加
$required_files = array(
    '1-theme-setup-optimized.php',    // テーマ基本設定、スクリプト（最適化版）
    '2-post-types.php',               // 投稿タイプ、タクソノミー
    '3-ajax-functions.php',           // AJAX関連
    '4-helper-functions.php',         // ヘルパー関数
    '5-template-tags.php',            // テンプレート用関数
    '6-admin-functions.php',          // 管理画面関連
    '7-acf-setup.php',                // ACF関連
    '8-acf-fields-setup.php',         // ACFフィールド定義
    '9-mobile-optimization.php',      // モバイル最適化機能
    '10-performance-helpers.php',     // パフォーマンス最適化ヘルパー
    '11-grant-card-renderer.php',     // グラントカードレンダラー
    '12-ai_concierge_function.php'    // AI Concierge 機能
);

// 各ファイルを安全に読み込み
foreach ($required_files as $file) {
    $file_path = $inc_dir . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        // デバッグモードの場合はエラーログに記録
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Grant Insight Theme: Required file not found - ' . $file_path);
        }
    }
}

/**
 * =============================================================================
 * データベーステーブル作成とデータ永続化機能
 * =============================================================================
 */

/**
 * データベーステーブル作成
 */
function gi_create_database_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // 1. 検索セッションテーブル
    $sessions_table = $wpdb->prefix . 'gi_search_sessions';
    $sessions_sql = "CREATE TABLE IF NOT EXISTS $sessions_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        session_id varchar(255) NOT NULL,
        user_id bigint(20) unsigned DEFAULT NULL,
        search_data longtext DEFAULT NULL,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY session_id (session_id),
        KEY user_id (user_id),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    // 2. AI会話履歴テーブル
    $conversations_table = $wpdb->prefix . 'gi_ai_conversations';
    $conversations_sql = "CREATE TABLE IF NOT EXISTS $conversations_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        session_id varchar(255) NOT NULL,
        user_id bigint(20) unsigned DEFAULT NULL,
        message_type enum('user','assistant','system') NOT NULL DEFAULT 'user',
        message longtext NOT NULL,
        context longtext DEFAULT NULL,
        emotion_score decimal(3,2) DEFAULT NULL,
        intent varchar(100) DEFAULT NULL,
        confidence decimal(3,2) DEFAULT NULL,
        response_time decimal(5,3) DEFAULT NULL,
        tokens_used int(11) DEFAULT NULL,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY session_id (session_id),
        KEY user_id (user_id),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    // 3. 検索分析テーブル
    $analytics_table = $wpdb->prefix . 'gi_search_analytics';
    $analytics_sql = "CREATE TABLE IF NOT EXISTS $analytics_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        date date NOT NULL,
        search_query varchar(255) DEFAULT NULL,
        results_count int(11) DEFAULT 0,
        user_id bigint(20) unsigned DEFAULT NULL,
        session_id varchar(255) DEFAULT NULL,
        response_time decimal(5,3) DEFAULT NULL,
        user_agent text DEFAULT NULL,
        ip_address varchar(45) DEFAULT NULL,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY date (date),
        KEY search_query (search_query),
        KEY user_id (user_id),
        KEY session_id (session_id)
    ) $charset_collate;";
    
    // 4. ユーザー設定テーブル
    $user_settings_table = $wpdb->prefix . 'gi_user_settings';
    $user_settings_sql = "CREATE TABLE IF NOT EXISTS $user_settings_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        user_id bigint(20) unsigned NOT NULL,
        setting_key varchar(100) NOT NULL,
        setting_value longtext DEFAULT NULL,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_setting (user_id, setting_key),
        KEY user_id (user_id)
    ) $charset_collate;";
    
    // 5. パフォーマンス統計テーブル
    $performance_table = $wpdb->prefix . 'gi_performance_stats';
    $performance_sql = "CREATE TABLE IF NOT EXISTS $performance_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        date date NOT NULL,
        metric_type varchar(50) NOT NULL,
        metric_value decimal(10,3) DEFAULT NULL,
        additional_data longtext DEFAULT NULL,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY date_metric (date, metric_type),
        KEY date (date),
        KEY metric_type (metric_type)
    ) $charset_collate;";
    
    // SQLを実行
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    $results = [];
    $results['sessions'] = dbDelta($sessions_sql);
    $results['conversations'] = dbDelta($conversations_sql);
    $results['analytics'] = dbDelta($analytics_sql);
    $results['user_settings'] = dbDelta($user_settings_sql);
    $results['performance'] = dbDelta($performance_sql);
    
    // バージョン情報を保存
    update_option('gi_database_version', GI_THEME_VERSION);
    update_option('gi_database_created_at', current_time('mysql'));
    
    return $results;
}

/**
 * テーマ有効化時の処理
 */
function gi_theme_activation() {
    // データベーステーブルを作成
    gi_create_database_tables();
    
    // 初期設定を保存
    $default_settings = [
        'gi_ai_enabled' => true,
        'gi_voice_enabled' => true,
        'gi_analytics_enabled' => true,
        'gi_cache_enabled' => true,
        'gi_search_suggestions_enabled' => true
    ];
    
    foreach ($default_settings as $key => $value) {
        if (!get_option($key)) {
            update_option($key, $value);
        }
    }
    
    // フラッシュリライトルール
    flush_rewrite_rules();
}

/**
 * テーマ無効化時の処理
 */
function gi_theme_deactivation() {
    // リライトルールをリセット
    flush_rewrite_rules();
    
    // 一時的なキャッシュを削除
    wp_cache_flush();
    
    // 期限切れのセッションデータを削除
    gi_cleanup_expired_sessions();
}

/**
 * データベースアップデート
 */
function gi_maybe_update_database() {
    $current_version = get_option('gi_database_version', '0.0.0');
    
    if (version_compare($current_version, GI_THEME_VERSION, '<')) {
        gi_create_database_tables();
    }
}

/**
 * 期限切れセッションデータのクリーンアップ
 */
function gi_cleanup_expired_sessions() {
    global $wpdb;
    
    // 30日以前のセッションデータを削除
    $sessions_table = $wpdb->prefix . 'gi_search_sessions';
    $conversations_table = $wpdb->prefix . 'gi_ai_conversations';
    $analytics_table = $wpdb->prefix . 'gi_search_analytics';
    
    $cleanup_date = date('Y-m-d H:i:s', strtotime('-30 days'));
    
    $wpdb->query($wpdb->prepare("DELETE FROM $sessions_table WHERE created_at < %s", $cleanup_date));
    $wpdb->query($wpdb->prepare("DELETE FROM $conversations_table WHERE created_at < %s", $cleanup_date));
    
    // 分析データは3ヶ月保持
    $analytics_cleanup_date = date('Y-m-d H:i:s', strtotime('-3 months'));
    $wpdb->query($wpdb->prepare("DELETE FROM $analytics_table WHERE created_at < %s", $analytics_cleanup_date));
}

/**
 * 日次メンテナンスタスク
 */
function gi_daily_maintenance_task() {
    gi_cleanup_expired_sessions();
    
    // パフォーマンス統計の更新
    gi_update_daily_performance_stats();
    
    // キャッシュのクリーンアップ
    gi_cleanup_cache();
}

/**
 * 日次パフォーマンス統計の更新
 */
function gi_update_daily_performance_stats() {
    global $wpdb;
    
    $today = date('Y-m-d');
    $analytics_table = $wpdb->prefix . 'gi_search_analytics';
    $performance_table = $wpdb->prefix . 'gi_performance_stats';
    
    // 今日の検索統計を計算
    $search_stats = $wpdb->get_row($wpdb->prepare("
        SELECT 
            COUNT(*) as total_searches,
            AVG(response_time) as avg_response_time,
            COUNT(DISTINCT user_id) as unique_users,
            COUNT(DISTINCT session_id) as unique_sessions
        FROM $analytics_table 
        WHERE DATE(created_at) = %s
    ", $today));
    
    if ($search_stats) {
        // 統計をパフォーマンステーブルに保存
        $metrics = [
            'total_searches' => $search_stats->total_searches,
            'avg_response_time' => $search_stats->avg_response_time,
            'unique_users' => $search_stats->unique_users,
            'unique_sessions' => $search_stats->unique_sessions
        ];
        
        foreach ($metrics as $metric_type => $metric_value) {
            $wpdb->replace(
                $performance_table,
                [
                    'date' => $today,
                    'metric_type' => $metric_type,
                    'metric_value' => $metric_value,
                    'created_at' => current_time('mysql')
                ],
                ['%s', '%s', '%f', '%s']
            );
        }
    }
}

/**
 * キャッシュクリーンアップ
 */
function gi_cleanup_cache() {
    // Transientキャッシュの削除
    $transients_to_delete = [
        'gi_search_stats',
        'gi_popular_grants',
        'gi_search_suggestions_cache'
    ];
    
    foreach ($transients_to_delete as $transient) {
        delete_transient($transient);
    }
}

/**
 * セッション分析データの記録
 */
function gi_record_search_analytics($search_query, $results_count, $response_time, $session_id) {
    global $wpdb;
    
    $analytics_table = $wpdb->prefix . 'gi_search_analytics';
    
    $wpdb->insert(
        $analytics_table,
        [
            'date' => current_time('mysql'),
            'search_query' => $search_query,
            'results_count' => $results_count,
            'user_id' => get_current_user_id(),
            'session_id' => $session_id,
            'response_time' => $response_time,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => gi_get_user_ip(),
            'created_at' => current_time('mysql')
        ],
        ['%s', '%s', '%d', '%d', '%s', '%f', '%s', '%s', '%s']
    );
}

// フック登録
add_action('after_switch_theme', 'gi_theme_activation');
add_action('switch_theme', 'gi_theme_deactivation');
add_action('admin_init', 'gi_maybe_update_database');
add_action('gi_daily_maintenance', 'gi_daily_maintenance_task');

// 日次メンテナンスのスケジュール設定
if (!wp_next_scheduled('gi_daily_maintenance')) {
    wp_schedule_event(time(), 'daily', 'gi_daily_maintenance');
}

// 統一カードレンダラーの読み込み（エラーハンドリング付き）
$card_renderer_path = get_template_directory() . '/inc/11-grant-card-renderer.php';
$card_unified_path = get_template_directory() . '/template-parts/grant-card-unified.php';

if (file_exists($card_renderer_path)) {
    require_once $card_renderer_path;
} else {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Grant Insight Theme: GrantCardRenderer class not found at ' . $card_renderer_path);
    }
}

if (file_exists($card_unified_path)) {
    require_once $card_unified_path;
} else {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Grant Insight Theme: grant-card-unified.php not found at ' . $card_unified_path);
    }
}

// グローバルで使えるヘルパー関数
if (!function_exists('gi_render_card')) {
    function gi_render_card($post_id, $view = 'grid') {
        if (class_exists('GrantCardRenderer')) {
            $renderer = GrantCardRenderer::getInstance();
            return $renderer->render($post_id, $view);
        }
        
        // フォールバック
        return '<div class="grant-card-error">カードレンダラーが利用できません</div>';
    }
}

/**
 * テーマの最終初期化
 */
function gi_final_init() {  // ✅ 修正
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Grant Insight Theme v' . GI_THEME_VERSION . ': Mobile optimization included, initialization completed successfully');
    }
}
add_action('wp_loaded', 'gi_final_init', 999);

// 以下のコードはそのまま...


/**
 * クリーンアップ処理
 */
function gi_theme_cleanup() {
    // オプションの削除
    delete_option('gi_login_attempts');
    
    // モバイル最適化キャッシュのクリア
    delete_option('gi_mobile_cache');
    
    // トランジェントのクリア
    delete_transient('gi_site_stats_v2');
    
    // オブジェクトキャッシュのフラッシュ（存在する場合のみ）
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
}
add_action('switch_theme', 'gi_theme_cleanup');

/**
 * スクリプトにdefer属性を追加（改善版）
 */
if (!function_exists('gi_add_defer_attribute')) {
    function gi_add_defer_attribute($tag, $handle, $src) {
        // 管理画面では処理しない
        if (is_admin()) {
            return $tag;
        }
        
        // WordPressコアスクリプトは除外
        if (strpos($src, 'wp-includes/js/') !== false) {
            return $tag;
        }
        
        // 既にdefer/asyncがある場合はスキップ
        if (strpos($tag, 'defer') !== false || strpos($tag, 'async') !== false) {
            return $tag;
        }
        
        // 特定のハンドルにのみdeferを追加
        $defer_handles = array(
            'gi-main-js',
            'gi-frontend-js',
            'gi-mobile-enhanced'
        );
        
        if (in_array($handle, $defer_handles)) {
            return str_replace('<script ', '<script defer ', $tag);
        }
        
        return $tag;
    }
}

// フィルターの重複登録を防ぐ
remove_filter('script_loader_tag', 'gi_add_defer_attribute', 10);
add_filter('script_loader_tag', 'gi_add_defer_attribute', 10, 3);

// モバイル専用テンプレート切り替えは削除（統合されました）

/**
 * モバイル用AJAX エンドポイント - さらに読み込み
 */
function gi_ajax_load_more_grants() {
    check_ajax_referer('gi_ajax_nonce', 'nonce');
    
    $page = intval($_POST['page'] ?? 1);
    $posts_per_page = 10;
    
    $args = [
        'post_type' => 'grant',
        'posts_per_page' => $posts_per_page,
        'post_status' => 'publish',
        'paged' => $page,
        'orderby' => 'date',
        'order' => 'DESC'
    ];
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        wp_send_json_error('No more posts found');
    }
    
    ob_start();
    
    while ($query->have_posts()): $query->the_post();
        echo gi_render_mobile_card(get_the_ID());
    endwhile;
    
    wp_reset_postdata();
    
    $html = ob_get_clean();
    
    wp_send_json_success([
        'html' => $html,
        'page' => $page,
        'max_pages' => $query->max_num_pages,
        'found_posts' => $query->found_posts
    ]);
}
add_action('wp_ajax_gi_load_more_grants', 'gi_ajax_load_more_grants');
add_action('wp_ajax_nopriv_gi_load_more_grants', 'gi_ajax_load_more_grants');

/**
 * テーマのアクティベーションチェック
 */
function gi_theme_activation_check() {
    // PHP バージョンチェック
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo 'Grant Insight テーマはPHP 7.4以上が必要です。現在のバージョン: ' . PHP_VERSION;
            echo '</p></div>';
        });
    }
    
    // WordPress バージョンチェック
    global $wp_version;
    if (version_compare($wp_version, '5.8', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p>';
            echo 'Grant Insight テーマはWordPress 5.8以上を推奨します。';
            echo '</p></div>';
        });
    }
    
    // 必須プラグインチェック（ACFなど）
    if (!class_exists('ACF') && is_admin()) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-info"><p>';
            echo 'Grant Insight テーマの全機能を利用するには、Advanced Custom Fields (ACF) プラグインのインストールを推奨します。';
            echo '</p></div>';
        });
    }
}
add_action('after_setup_theme', 'gi_theme_activation_check');

/**
 * エラーハンドリング用のグローバル関数
 */
if (!function_exists('gi_log_error')) {
    function gi_log_error($message, $context = array()) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_message = '[Grant Insight Error] ' . $message;
            if (!empty($context)) {
                $log_message .= ' | Context: ' . print_r($context, true);
            }
            error_log($log_message);
        }
    }
}

/**
 * テーマ設定のデフォルト値を取得
 */
if (!function_exists('gi_get_theme_option')) {
    function gi_get_theme_option($option_name, $default = null) {
        $theme_options = get_option('gi_theme_options', array());
        
        if (isset($theme_options[$option_name])) {
            return $theme_options[$option_name];
        }
        
        return $default;
    }
}

/**
 * テーマ設定を保存
 */
if (!function_exists('gi_update_theme_option')) {
    function gi_update_theme_option($option_name, $value) {
        $theme_options = get_option('gi_theme_options', array());
        $theme_options[$option_name] = $value;
        
        return update_option('gi_theme_options', $theme_options);
    }
}



/**
 * テーマのバージョンアップグレード処理
 */
function gi_theme_version_upgrade() {
    $current_version = get_option('gi_installed_version', '0.0.0');
    
    if (version_compare($current_version, GI_THEME_VERSION, '<')) {
        // バージョンアップグレード処理
        
        // 6.2.0 -> 6.2.1 のアップグレード
        if (version_compare($current_version, '6.2.1', '<')) {
            // キャッシュのクリア
            gi_theme_cleanup();
        }
        
        // 6.2.1 -> 6.2.2 のアップグレード
        if (version_compare($current_version, '6.2.2', '<')) {
            // 新しいメタフィールドの追加など
            flush_rewrite_rules();
        }
        
        // バージョン更新
        update_option('gi_installed_version', GI_THEME_VERSION);
        
        // アップグレード完了通知
        if (is_admin()) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>';
                echo 'Grant Insight テーマが v' . GI_THEME_VERSION . ' にアップグレードされました。';
                echo '</p></div>';
            });
        }
    }
}
add_action('init', 'gi_theme_version_upgrade');

/**
 * AJAXハンドラーの登録確認
 */