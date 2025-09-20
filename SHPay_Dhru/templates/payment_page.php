<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment | Binance Pay</title>
    <script src="/includes/js/tailwind.js"></script>
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-tertiary: #f1f5f9;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --accent-color: #3b82f6;
            --accent-hover: #2563eb;
            --success-color: #10b981;
            --error-color: #ef4444;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --shadow-medium: rgba(0, 0, 0, 0.15);
        }

        [data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-color: #475569;
            --accent-color: #60a5fa;
            --accent-hover: #3b82f6;
            --success-color: #34d399;
            --error-color: #f87171;
            --shadow-light: rgba(0, 0, 0, 0.3);
            --shadow-medium: rgba(0, 0, 0, 0.4);
        }

        * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        body {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            border-radius: 50px;
            padding: 8px;
            cursor: pointer;
            box-shadow: 0 4px 12px var(--shadow-light);
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px var(--shadow-medium);
        }

        .theme-toggle svg {
            width: 24px;
            height: 24px;
            color: var(--text-primary);
        }

        .container-main {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 40px var(--shadow-light);
        }

        .card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--shadow-medium);
        }

        .currency-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid var(--border-color);
            background: var(--bg-primary);
            color: var(--text-primary);
            position: relative;
            overflow: hidden;
        }

        .currency-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .currency-btn:hover::before {
            left: 100%;
        }

        .currency-btn.selected {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-hover) 100%);
            color: white;
            border-color: var(--accent-color);
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .currency-btn:not(.selected):hover {
            border-color: var(--accent-color);
            background: var(--bg-secondary);
            transform: translateY(-1px);
        }

        .currency-radio {
            display: none;
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            background: linear-gradient(135deg, var(--error-color) 0%, #dc2626 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn-cancel::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-cancel:hover::before {
            left: 100%;
        }

        .btn-cancel:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
            transform: translateY(-2px) scale(1.02);
        }

        .btn-cancel:active {
            transform: translateY(0) scale(0.98);
        }

        .success-animation {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            border-radius: 50%;
            box-shadow: 0 8px 30px rgba(16, 185, 129, 0.3);
            position: relative;
            animation: pop-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) 1;
        }

        @keyframes pop-in {
            0% { 
                transform: scale(0.3) rotate(-180deg); 
                opacity: 0; 
            }
            50% { 
                transform: scale(1.1) rotate(-90deg); 
                opacity: 0.8; 
            }
            100% { 
                transform: scale(1) rotate(0deg); 
                opacity: 1; 
            }
        }

        .checkmark {
            stroke: white;
            stroke-width: 4;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
            stroke-dasharray: 60;
            stroke-dashoffset: 60;
            animation: checkmark 0.8s 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        }

        @keyframes checkmark {
            to { stroke-dashoffset: 0; }
        }

        .gradient-bg {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-hover) 100%);
            color: white;
        }

        .copy-btn {
            background: var(--bg-primary);
            color: var(--accent-color);
            border: 1px solid var(--accent-color);
            transition: all 0.3s ease;
        }

        .copy-btn:hover {
            background: var(--accent-color);
            color: white;
            transform: scale(1.05);
        }

        .copy-btn.copied {
            background: var(--success-color);
            color: white;
            border-color: var(--success-color);
        }

        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .timer-progress {
            width: 100%;
            height: 6px;
            background: var(--bg-tertiary);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 8px;
        }

        .timer-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-color), var(--success-color));
            border-radius: 3px;
            transition: width 1s ease;
            position: relative;
        }

        .timer-progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .slide-in {
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Mobile optimizations */
        @media (max-width: 768px) {
            .theme-toggle {
                top: 10px;
                right: 10px;
                padding: 6px;
            }
            
            .theme-toggle svg {
                width: 20px;
                height: 20px;
            }

            .container-main {
                margin: 10px;
                border-radius: 16px;
            }

            .currency-btn {
                padding: 12px 16px;
                font-size: 16px;
            }

            .btn-cancel {
                padding: 12px 24px;
                font-size: 16px;
            }

            .success-animation {
                width: 80px;
                height: 80px;
            }
        }

        /* Touch-friendly interactions */
        @media (hover: none) {
            .currency-btn:hover,
            .btn-cancel:hover,
            .copy-btn:hover {
                transform: none;
            }
            
            .currency-btn:active {
                transform: scale(0.95);
            }
            
            .btn-cancel:active {
                transform: scale(0.95);
            }
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            :root {
                --border-color: #000000;
                --text-secondary: #000000;
            }
            
            [data-theme="dark"] {
                --border-color: #ffffff;
                --text-secondary: #ffffff;
            }
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <!-- Theme Toggle -->
    <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
        <svg id="sunIcon" class="hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <svg id="moonIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
    </button>

    <div class="container mx-auto max-w-6xl container-main rounded-2xl p-6 md:p-8 fade-in">
        <!-- Header -->
        <div class="flex items-center justify-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-center w-full slide-in">
                🔐 Crypto Payment
            </h1>
        </div>

        <!-- Two-Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column: Payment Details -->
            <div class="space-y-6">
                <!-- Currency Selection -->
                <div class="card rounded-xl p-6 fade-in">
                    <h3 class="text-xl font-semibold mb-4 flex items-center gap-2">
                        💰 Select Currency
                    </h3>
                    <div class="flex flex-col sm:flex-row gap-4" id="currencySelectGroup">
                        <label class="currency-btn selected flex items-center justify-center px-6 py-4 rounded-xl font-semibold text-lg cursor-pointer flex-1" data-currency="USDT">
                            <input type="radio" name="currency" value="USDT" class="currency-radio" checked>
                            <span>USDT</span>
                        </label>
                        
                    </div>
                </div>

                <!-- Amount Info -->
                <div class="gradient-bg p-6 rounded-xl shadow-lg fade-in">
                    <p class="text-lg font-semibold opacity-90">Send Exactly</p>
                    <p class="text-3xl md:text-4xl font-bold tracking-tight mt-2" id="amountDisplay">
                        <?php echo number_format($expectedAmount, 6); ?> <span id="currencyDisplay">USDT</span>
                    </p>
                    <div class="mt-4 text-sm opacity-90 space-y-1">
                        <p>📄 Invoice #<?php echo htmlentities($invoiceId); ?></p>
                        <p>💵 Amount in USD: $<?php echo number_format($amount, 2); ?></p>
                    </div>
                </div>

                <!-- Payment Note -->
                <div class="card rounded-xl p-6 fade-in">
                    <h3 class="text-xl font-semibold mb-4 flex items-center gap-2">
                        📝 Payment Note (Required)
                    </h3>
                    <div class="relative">
                        <div class="font-mono text-lg font-bold p-4 rounded-lg border-2 border-dashed" 
                             style="color: var(--accent-color); border-color: var(--accent-color); background: var(--bg-primary);" 
                             id="paymentNote">
                            <?php echo htmlentities($paymentNote); ?>
                        </div>
                        <button class="copy-btn absolute top-2 right-2 px-3 py-1 rounded-lg text-sm font-medium transition-all" id="copyNoteBtn">
                            📋 Copy
                        </button>
                    </div>
                    <p class="text-sm mt-3" style="color: var(--text-secondary);">
                        ⚠️ Include this exact code in your payment message/note
                    </p>
                </div>

                <!-- Payment Status -->
                <div class="card rounded-xl p-6 fade-in">
                    <div class="flex items-center justify-center gap-3" id="paymentSpinnerContainer">
                        <div class="spinner rounded-full h-6 w-6 border-2 border-b-transparent" 
                             style="border-color: var(--accent-color); border-bottom-color: transparent;" 
                             id="paymentSpinner"></div>
                        <span class="font-medium pulse">🔍 Verifying payment...</span>
                    </div>
                    <div id="paymentStatus" class="hidden mt-4"></div>
                    
                    <!-- Success Animation -->
                    <div id="paymentSuccessAnimation" class="hidden flex flex-col items-center justify-center mt-4 mb-4">
                        <div class="success-animation mb-4">
                            <svg width="60" height="60">
                                <polyline class="checkmark" points="16,32 28,44 44,20"></polyline>
                            </svg>
                        </div>
                        <div class="text-xl font-bold" style="color: var(--success-color);">
                            ✅ Payment Verified! Redirecting...
                        </div>
                    </div>
                    
                    <!-- Enhanced Timer Display -->
                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span style="color: var(--text-secondary);">⏱️ Time elapsed:</span>
                            <div class="flex items-center gap-2">
                                <span id="timeElapsed" class="font-mono text-lg font-bold" style="color: var(--accent-color);">00:00</span>
                                <span class="text-xs" style="color: var(--text-secondary);">(max 05:00)</span>
                            </div>
                        </div>
                        <div class="timer-progress">
                            <div class="timer-progress-bar" id="timerProgressBar" style="width: 0%"></div>
                        </div>
                        <div class="text-xs text-center" style="color: var(--text-secondary);" id="timerStatus">
                            Waiting for payment confirmation...
                        </div>
                    </div>
                </div>

                <!-- How to Pay -->
                <div class="card rounded-xl p-6 fade-in">
                    <h3 class="text-xl font-semibold mb-4 flex items-center gap-2">
                        📱 How to Pay
                    </h3>
                    <ol class="list-decimal pl-6 mb-4 space-y-3 text-sm md:text-base" style="color: var(--text-primary);">
                        <li>Open Binance app and go to <strong>Pay/Transfer</strong></li>
                        <li>Enter amount: <strong id="howToPayAmount"><?php echo number_format($expectedAmount, 6); ?> <span id="howToPayCurrency">USDT</span></strong></li>
                        <li>Add payment note <strong>exactly</strong> as shown above</li>
                        <li>Complete the transfer</li>
                    </ol>
                    <div class="p-4 rounded-lg border-l-4" 
                         style="background: var(--bg-secondary); border-color: #f59e0b; color: var(--text-primary);">
                        <strong>⚠️ Important:</strong> Payment verification may take <span id="verify-time">1–5 minutes</span>. 
                        Keep this page open and don't refresh.
                    </div>
                </div>
            </div>

            <!-- Right Column: QR Code and Actions -->
            <div class="flex flex-col items-center justify-center space-y-6">
                <div class="text-center space-y-4 fade-in">
                    <?php 
                    // QR kod kontrolü - Dhru Fusion gateway sistemi ile uyumlu
                    $qrImagePath = null;
                    $possiblePaths = [
                        'qr.png',
                        'qr.jpg', 
                        'qr.jpeg',
                        'gateway_qr.png',
                        'gateway_qr.jpg',
                        'binance_qr.png',
                        'binance_qr.jpg',
                        'payment_qr.png',
                        'payment_qr.jpg'
                    ];
                    
                    // Önce proje kök dizininde ara
                    foreach ($possiblePaths as $path) {
                        if (file_exists(ROOTDIR . '/' . $path)) {
                            $qrImagePath = '/' . $path;
                            break;
                        }
                    }
                    
                    // Dhru Fusion gateway icon kontrolü
                    if (!$qrImagePath && isset($GATEWAY['gateway_icon']) && !empty($GATEWAY['gateway_icon'])) {
                        $qrImagePath = $GATEWAY['gateway_icon'];
                    }
                    
                    // Dhru Fusion gateway logo kontrolü
                    if (!$qrImagePath && isset($GATEWAY['gateway_logo']) && !empty($GATEWAY['gateway_logo'])) {
                        $qrImagePath = $GATEWAY['gateway_logo'];
                    }
                    
                    // Gateway ayarlarından QR kod kontrolü
                    if (!$qrImagePath && isset($GATEWAY['qr_code_path']) && !empty($GATEWAY['qr_code_path'])) {
                        $qrImagePath = $GATEWAY['qr_code_path'];
                    }
                    ?>
                    
                    <?php if ($qrImagePath): ?>
                        <div class="p-4 rounded-2xl" style="background: var(--bg-primary); border: 2px solid var(--border-color);">
                            <img src="<?php echo htmlspecialchars($qrImagePath); ?>" alt="QR Code for Payment" 
                                 class="w-64 h-64 object-contain rounded-lg" loading="lazy" 
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                            <div style="display: none; padding: 2rem; text-align: center; color: var(--text-secondary);">
                                <p>QR kod yüklenemedi. Manuel ödeme talimatlarını takip edin.</p>
                            </div>
                        </div>
                        <p class="text-sm font-medium" style="color: var(--text-secondary);">
                            📱 Scan to pay with Binance
                        </p>
                    <?php else: ?>
                        <div class="p-8 rounded-2xl text-center" style="background: var(--bg-tertiary); border: 2px dashed var(--border-color);">
                            <div class="space-y-3">
                                <p class="text-sm" style="color: var(--text-secondary);">
                                    QR code not available. Please follow manual payment instructions.
                                </p>
                                <div class="text-xs" style="color: var(--text-secondary);">
                                    <p><strong>Debug Info:</strong></p>
                                    <p>• Checked paths: <?php echo implode(', ', $possiblePaths); ?></p>
                                    <p>• Gateway icon: <?php echo isset($GATEWAY['gateway_icon']) ? $GATEWAY['gateway_icon'] : 'Not set'; ?></p>
                                    <p>• Gateway logo: <?php echo isset($GATEWAY['gateway_logo']) ? $GATEWAY['gateway_logo'] : 'Not set'; ?></p>
                                    <p>• QR code path: <?php echo isset($GATEWAY['qr_code_path']) ? $GATEWAY['qr_code_path'] : 'Not set'; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="text-center space-y-4 fade-in">
                    <img src="https://shpay.org/pay.gif" alt="SHPay Payment" 
                         class="w-64 h-80 object-contain rounded-lg shadow-lg" loading="lazy" 
                         style="border: 1px solid var(--border-color);" />
                </div>

                <!-- Cancel Button -->
                <button class="btn-cancel mt-6" id="cancelBtn" type="button">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel Payment
                </button>

                <p class="text-sm text-center" style="color: var(--text-secondary);">
                    Powered by <a href="https://www.shpay.com" target="_blank" rel="noopener noreferrer" 
                                  class="font-medium hover:underline" style="color: var(--accent-color);">SHPay</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme management
            const themeToggle = document.getElementById('themeToggle');
            const sunIcon = document.getElementById('sunIcon');
            const moonIcon = document.getElementById('moonIcon');
            const html = document.documentElement;

            // Initialize theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            setTheme(savedTheme);

            function setTheme(theme) {
                html.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
                
                if (theme === 'dark') {
                    sunIcon.classList.remove('hidden');
                    moonIcon.classList.add('hidden');
                } else {
                    sunIcon.classList.add('hidden');
                    moonIcon.classList.remove('hidden');
                }
            }

            themeToggle.addEventListener('click', function() {
                const currentTheme = html.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                setTheme(newTheme);
            });

            // Cancel button logic
            const cancelBtn = document.getElementById('cancelBtn');
            const invoiceId = '<?php echo addslashes($invoiceId); ?>';
            cancelBtn.addEventListener('click', function() {
                window.location.href = `/main`;
            });

            // Currency button logic with enhanced animations
            const currencyGroup = document.getElementById('currencySelectGroup');
            const currencyBtns = Array.from(currencyGroup.querySelectorAll('.currency-btn'));
            const radios = Array.from(currencyGroup.querySelectorAll('input[type="radio"]'));

            currencyBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    currencyBtns.forEach(b => {
                        b.classList.remove('selected');
                        b.style.transform = 'scale(1)';
                    });
                    
                    this.classList.add('selected');
                    this.style.transform = 'scale(1.05)';
                    
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                    window.updateAmountDisplay(radio.value);
                    
                    // Add a subtle shake animation
                    this.style.animation = 'none';
                    setTimeout(() => {
                        this.style.animation = 'pulse 0.5s ease-in-out';
                    }, 10);
                });
            });

            // Enhanced copy note button
            const copyBtn = document.getElementById('copyNoteBtn');
            const noteText = document.getElementById('paymentNote').textContent.trim();
            copyBtn.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(noteText);
                    copyBtn.textContent = '✅ Copied!';
                    copyBtn.classList.add('copied');
                    
                    // Add success animation
                    copyBtn.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        copyBtn.style.transform = 'scale(1)';
                    }, 150);
                    
                    setTimeout(() => {
                        copyBtn.textContent = '📋 Copy';
                        copyBtn.classList.remove('copied');
                    }, 2000);
                } catch (err) {
                    console.error('Copy failed:', err);
                    copyBtn.textContent = '❌ Failed';
                    setTimeout(() => {
                        copyBtn.textContent = '📋 Copy';
                    }, 2000);
                }
            });

            // Update amount display based on currency selection
            window.updateAmountDisplay = function(currency) {
                const amount = '<?php echo addslashes($expectedAmount); ?>';
                document.getElementById('amountDisplay').innerHTML = `${amount} <span id="currencyDisplay">${currency}</span>`;
                document.getElementById('howToPayAmount').innerHTML = `${amount} <span id="howToPayCurrency">${currency}</span>`;
            };

            // Enhanced payment verification with better UI feedback
            const paymentNote = '<?php echo addslashes($paymentNote); ?>';
            const expectedAmount = '<?php echo addslashes($expectedAmount); ?>';
            const paymentSpinnerContainer = document.getElementById('paymentSpinnerContainer');
            const paymentStatus = document.getElementById('paymentStatus');
            const paymentSuccessAnimation = document.getElementById('paymentSuccessAnimation');
            let attempts = 0;
            const maxAttempts = 60; // ~9 minutes with exponential backoff (capped at 10s)
            const baseDelay = 2000; // Start with 2s delay

            // Enhanced timer with progress bar
            let elapsedSeconds = 0;
            const maxSeconds = 300; // 5 minutes
            const timeElapsedDisplay = document.getElementById('timeElapsed');
            const timerProgressBar = document.getElementById('timerProgressBar');
            const timerStatus = document.getElementById('timerStatus');
            let timerInterval = null;

            function updateTimeElapsedDisplay() {
                const m = String(Math.floor(elapsedSeconds / 60)).padStart(2, '0');
                const s = String(elapsedSeconds % 60).padStart(2, '0');
                timeElapsedDisplay.textContent = `${m}:${s}`;
                
                // Update progress bar
                const progress = (elapsedSeconds / maxSeconds) * 100;
                timerProgressBar.style.width = `${Math.min(progress, 100)}%`;
                
                // Update status message
                if (elapsedSeconds < 60) {
                    timerStatus.textContent = 'Waiting for payment confirmation...';
                } else if (elapsedSeconds < 180) {
                    timerStatus.textContent = 'Still checking for payment...';
                } else if (elapsedSeconds < 240) {
                    timerStatus.textContent = 'Payment verification in progress...';
                } else {
                    timerStatus.textContent = 'Almost timeout - please check your payment...';
                }
            }

            function startTimer() {
                updateTimeElapsedDisplay();
                timerInterval = setInterval(() => {
                    elapsedSeconds++;
                    updateTimeElapsedDisplay();
                    if (elapsedSeconds >= maxSeconds) {
                        clearInterval(timerInterval);
                    }
                }, 1000);
            }

            startTimer();

            async function showSuccessAndRedirect(redirectUrl, message = 'Payment Verified! Redirecting...') {
                paymentSuccessAnimation.classList.remove('hidden');
                paymentSuccessAnimation.querySelector('.text-xl').textContent = message;
                paymentSpinnerContainer.classList.add('hidden');
                
                // Clear timer
                clearInterval(timerInterval);
                timerStatus.textContent = 'Payment confirmed successfully!';
                timerProgressBar.style.width = '100%';
                
                setTimeout(() => {
                    window.location = redirectUrl;
                }, 2000);
            }

            async function showErrorAndRedirect(redirectUrl, message) {
                paymentSpinnerContainer.classList.add('hidden');
                paymentStatus.classList.remove('hidden');
                paymentStatus.innerHTML = `
                    <div class="p-4 rounded-lg border-l-4" style="background: var(--bg-secondary); border-color: var(--error-color); color: var(--text-primary);">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">❌</span>
                            <span class="font-medium">${message}</span>
                        </div>
                    </div>`;
                
                clearInterval(timerInterval);
                timerStatus.textContent = 'Payment verification failed.';
                
                setTimeout(() => {
                    window.location = redirectUrl;
                }, 3000);
            }

            async function verifyPayment() {
                const currency = currencyGroup.querySelector('input[type="radio"]:checked').value;
                const redirectUrl = `/settings/invoice`;
                
                if (attempts >= maxAttempts) {
                    clearInterval(timerInterval);
                    showErrorAndRedirect(redirectUrl, 'Payment verification timed out! Please contact support if you have paid.');
                    return;
                }

                try {
                    const resp = await fetch('verification.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            invoice_id: invoiceId,
                            payment_note: paymentNote,
                            expected_amount: expectedAmount,
                            currency: currency
                        }),
                        signal: AbortSignal.timeout(5000)
                    });
                    const data = await resp.json();

                    if (data.is_success || data.code === 405) {
                        clearInterval(timerInterval);
                        // Post verification result to shpay.php
                        const formData = new FormData();
                        formData.append('verification_result', JSON.stringify(data));
                        formData.append('invoice_id', invoiceId);
                        formData.append('currency', currency);
                        
                        try {
                            const shpayResp = await fetch('shpay.php', {
                                method: 'POST',
                                body: formData
                            });
                            const finalResp = await shpayResp.json();
                            
                            if (finalResp.isSuccess || finalResp.code === 405) {
                                showSuccessAndRedirect(redirectUrl, '✅ Payment Verified! Redirecting...');
                            } else {
                                showErrorAndRedirect(redirectUrl, finalResp.message || 'Payment processing failed. Please try again.');
                            }
                        } catch (err) {
                            showErrorAndRedirect(redirectUrl, 'Failed to process payment. Please try again.');
                        }
                    } else {
                        attempts++;
                        const delay = Math.min(baseDelay * Math.pow(2, attempts / 5), 10000);
                        setTimeout(verifyPayment, delay);
                    }
                } catch (err) {
                    attempts++;
                    const delay = Math.min(baseDelay * Math.pow(2, attempts / 5), 10000);
                    setTimeout(verifyPayment, delay);
                }
            }

            // Start payment verification after a short delay
            setTimeout(verifyPayment, 1000);

            // Add some interactive elements for better UX
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    timerStatus.textContent = 'Page hidden - verification paused';
                } else {
                    timerStatus.textContent = 'Page visible - verification resumed';
                }
            });

            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 't' || e.key === 'T') {
                    themeToggle.click();
                }
                if (e.key === 'c' || e.key === 'C') {
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        copyBtn.click();
                    }
                }
            });
        });
    </script>
</body>
</html>

