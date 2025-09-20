/**
 * Mobile Enhanced JavaScript
 * モバイル体験強化スクリプト
 * 
 * @version 3.0
 */

(function() {
    'use strict';

    // モバイル検出
    const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    /**
     * モバイル強化機能クラス
     */
    class MobileEnhancer {
        constructor() {
            this.initialized = false;
            this.touchStartY = 0;
            this.touchEndY = 0;
            this.isScrolling = false;
            this.pullToRefreshThreshold = 100;
            this.header = null;
            this.lastScrollY = 0;
            
            this.init();
        }

        /**
         * 初期化
         */
        init() {
            if (this.initialized) return;

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
                this.setupTouchOptimizations();
                this.setupSmartHeader();
                this.setupPullToRefresh();
                this.setupSwipeGestures();
                this.setupVibrationFeedback();
                this.setupOrientationHandling();
                
                this.initialized = true;
                console.log('Mobile Enhancer initialized');
            } catch (error) {
                console.error('Mobile Enhancer setup error:', error);
            }
        }

        /**
         * タッチ最適化
         */
        setupTouchOptimizations() {
            if (!isTouch) return;

            // タッチ可能要素にクラス追加
            const touchElements = document.querySelectorAll('button, .btn, a, .card, .gi-filter-chip');
            touchElements.forEach(element => {
                element.classList.add('touch-feedback');
                
                // タッチ開始
                element.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
                
                // タッチ終了
                element.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: true });
                
                // タッチキャンセル
                element.addEventListener('touchcancel', this.handleTouchEnd.bind(this), { passive: true });
            });

            // 長押し防止（コンテキストメニュー）
            document.addEventListener('contextmenu', (e) => {
                if (e.target.matches('button, .btn, a')) {
                    e.preventDefault();
                }
            });

            // ダブルタップズーム防止
            let lastTouchEnd = 0;
            document.addEventListener('touchend', (e) => {
                const now = new Date().getTime();
                if (now - lastTouchEnd <= 300) {
                    e.preventDefault();
                }
                lastTouchEnd = now;
            }, { passive: false });
        }

        /**
         * タッチ開始処理
         */
        handleTouchStart(e) {
            const element = e.currentTarget;
            element.classList.add('touching');
            
            // リップルエフェクト
            this.createRippleEffect(e, element);
            
            // バイブレーション
            this.triggerVibration('light');
        }

        /**
         * タッチ終了処理
         */
        handleTouchEnd(e) {
            const element = e.currentTarget;
            element.classList.remove('touching');
        }

        /**
         * リップルエフェクト作成
         */
        createRippleEffect(e, element) {
            const rect = element.getBoundingClientRect();
            const ripple = document.createElement('div');
            const size = Math.max(rect.width, rect.height);
            
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: translate(-50%, -50%) scale(0);
                animation: touchRipple 0.6s ease-out;
                pointer-events: none;
                z-index: 1000;
                width: ${size}px;
                height: ${size}px;
                left: ${e.touches[0].clientX - rect.left}px;
                top: ${e.touches[0].clientY - rect.top}px;
            `;

            // 相対位置指定がない場合は追加
            const elementStyle = window.getComputedStyle(element);
            if (elementStyle.position === 'static') {
                element.style.position = 'relative';
            }
            element.style.overflow = 'hidden';

            element.appendChild(ripple);
            
            setTimeout(() => {
                if (ripple.parentNode) {
                    ripple.parentNode.removeChild(ripple);
                }
            }, 600);
        }

        /**
         * スマートヘッダー
         */
        setupSmartHeader() {
            this.header = document.querySelector('.gi-mobile-header, .site-header, header');
            if (!this.header) return;

            let ticking = false;
            
            const updateHeader = () => {
                const currentScrollY = window.scrollY;
                const scrollDelta = Math.abs(currentScrollY - this.lastScrollY);
                
                if (scrollDelta < 5) return;

                if (currentScrollY > this.lastScrollY && currentScrollY > 100) {
                    // 下スクロール - ヘッダーを隠す
                    this.header.classList.add('header-hidden');
                } else {
                    // 上スクロール - ヘッダーを表示
                    this.header.classList.remove('header-hidden');
                }
                
                this.lastScrollY = currentScrollY;
            };

            window.addEventListener('scroll', () => {
                if (!ticking) {
                    requestAnimationFrame(() => {
                        updateHeader();
                        ticking = false;
                    });
                    ticking = true;
                }
            }, { passive: true });
        }

        /**
         * プルトゥリフレッシュ
         */
        setupPullToRefresh() {
            let startY = 0;
            let currentY = 0;
            let isRefreshing = false;

            document.addEventListener('touchstart', (e) => {
                if (window.scrollY === 0 && !isRefreshing) {
                    startY = e.touches[0].clientY;
                }
            }, { passive: true });

            document.addEventListener('touchmove', (e) => {
                if (window.scrollY === 0 && startY > 0) {
                    currentY = e.touches[0].clientY;
                    const pullDistance = currentY - startY;
                    
                    if (pullDistance > 50 && !isRefreshing) {
                        this.showPullRefreshIndicator(Math.min(pullDistance, this.pullToRefreshThreshold));
                    }
                }
            }, { passive: true });

            document.addEventListener('touchend', () => {
                const pullDistance = currentY - startY;
                
                if (pullDistance > this.pullToRefreshThreshold && !isRefreshing) {
                    this.triggerRefresh();
                } else {
                    this.hidePullRefreshIndicator();
                }
                
                startY = 0;
                currentY = 0;
            }, { passive: true });
        }

        /**
         * プルリフレッシュインジケーター表示
         */
        showPullRefreshIndicator(distance) {
            let indicator = document.querySelector('.pull-refresh-indicator');
            if (!indicator) {
                indicator = document.createElement('div');
                indicator.className = 'pull-refresh-indicator';
                indicator.innerHTML = `
                    <div class="pull-refresh-spinner">⟳</div>
                    <span class="pull-refresh-text">引っ張って更新</span>
                `;
                document.body.appendChild(indicator);
            }
            
            const progress = Math.min(distance / this.pullToRefreshThreshold, 1);
            indicator.style.transform = `translateY(${distance * 0.5}px) translateX(-50%)`;
            indicator.style.opacity = progress;
            
            if (progress >= 1) {
                indicator.querySelector('.pull-refresh-text').textContent = '離して更新';
            }
        }

        /**
         * プルリフレッシュインジケーター非表示
         */
        hidePullRefreshIndicator() {
            const indicator = document.querySelector('.pull-refresh-indicator');
            if (indicator) {
                indicator.style.transform = 'translateY(-100%) translateX(-50%)';
                setTimeout(() => {
                    if (indicator.parentNode) {
                        indicator.parentNode.removeChild(indicator);
                    }
                }, 300);
            }
        }

        /**
         * リフレッシュ実行
         */
        triggerRefresh() {
            this.showRefreshSpinner();
            
            // バイブレーション
            this.triggerVibration('medium');
            
            // 実際のリフレッシュ処理
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }

        /**
         * リフレッシュスピナー表示
         */
        showRefreshSpinner() {
            const indicator = document.querySelector('.pull-refresh-indicator');
            if (indicator) {
                indicator.querySelector('.pull-refresh-spinner').classList.add('pull-refresh-spin');
                indicator.querySelector('.pull-refresh-text').textContent = '更新中...';
            }
        }

        /**
         * スワイプジェスチャー
         */
        setupSwipeGestures() {
            let startX = 0;
            let startY = 0;
            let endX = 0;
            let endY = 0;

            document.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
            }, { passive: true });

            document.addEventListener('touchend', (e) => {
                endX = e.changedTouches[0].clientX;
                endY = e.changedTouches[0].clientY;
                
                this.handleSwipeGesture(startX, startY, endX, endY);
            }, { passive: true });
        }

        /**
         * スワイプジェスチャー処理
         */
        handleSwipeGesture(startX, startY, endX, endY) {
            const deltaX = endX - startX;
            const deltaY = endY - startY;
            const threshold = 50;

            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > threshold) {
                if (deltaX > 0) {
                    // 右スワイプ
                    this.onSwipeRight();
                } else {
                    // 左スワイプ
                    this.onSwipeLeft();
                }
            }
        }

        /**
         * 右スワイプイベント
         */
        onSwipeRight() {
            // 戻るジェスチャー
            if (window.history.length > 1) {
                this.triggerVibration('light');
                window.history.back();
            }
        }

        /**
         * 左スワイプイベント
         */
        onSwipeLeft() {
            // カスタムアクション（例：メニュー表示）
            const menu = document.querySelector('.mobile-menu');
            if (menu) {
                menu.classList.toggle('active');
                this.triggerVibration('light');
            }
        }

        /**
         * バイブレーション
         */
        setupVibrationFeedback() {
            // 機能は triggerVibration メソッドで実装済み
        }

        /**
         * バイブレーション実行
         */
        triggerVibration(type = 'light') {
            if (!navigator.vibrate) return;

            const patterns = {
                light: 10,
                medium: 50,
                heavy: [50, 50, 50]
            };

            navigator.vibrate(patterns[type] || 10);
        }

        /**
         * 画面向き変更対応
         */
        setupOrientationHandling() {
            window.addEventListener('orientationchange', () => {
                setTimeout(() => {
                    // リサイズイベントを発火
                    window.dispatchEvent(new Event('resize'));
                    
                    // スクロール位置を調整
                    window.scrollTo(0, window.scrollY);
                }, 100);
            });
        }
    }

    /**
     * パフォーマンスモニタリング
     */
    class PerformanceMonitor {
        constructor() {
            this.metrics = {
                touchLatency: [],
                scrollPerformance: [],
                animationFrames: 0
            };
        }

        measureTouchLatency(startTime) {
            const latency = performance.now() - startTime;
            this.metrics.touchLatency.push(latency);
            
            if (this.metrics.touchLatency.length > 100) {
                this.metrics.touchLatency.shift();
            }
        }

        getAverageLatency() {
            const sum = this.metrics.touchLatency.reduce((a, b) => a + b, 0);
            return sum / this.metrics.touchLatency.length || 0;
        }
    }

    // グローバル初期化
    window.MobileEnhancer = MobileEnhancer;
    window.PerformanceMonitor = PerformanceMonitor;

    // 自動初期化（モバイルデバイスのみ）
    if (isMobile || isTouch) {
        new MobileEnhancer();
        window.performanceMonitor = new PerformanceMonitor();
    }

})();