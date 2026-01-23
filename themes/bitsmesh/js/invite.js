/**
 * BitsMesh Invite Page JavaScript
 *
 * @package BitsMesh
 * @since 1.0
 */

(function() {
    'use strict';

    // Wait for DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initInvitePage();
    });

    /**
     * Initialize invite page functionality.
     */
    function initInvitePage() {
        // Generate code button
        const generateBtn = document.getElementById('generate-code-btn');
        if (generateBtn) {
            generateBtn.addEventListener('click', handleGenerateCode);
        }

        // Copy buttons
        initCopyButtons();
    }

    /**
     * Handle generate code button click.
     */
    function handleGenerateCode() {
        const btn = document.getElementById('generate-code-btn');
        if (!btn || btn.disabled) return;

        const config = window.InviteConfig || {};
        const generateUrl = config.generateUrl || '/invite/generate';
        const transientKey = config.transientKey || '';
        const creditCost = config.creditCost || 1000;

        // Confirm
        if (!confirm('确定要消耗 ' + creditCost + ' 鸡腿生成邀请码吗？')) {
            return;
        }

        // Disable button
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<svg class="iconpark-icon spin" width="16" height="16"><use href="#loading-one"></use></svg> 生成中...';

        // Send request
        fetch(generateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'TransientKey=' + encodeURIComponent(transientKey)
        })
        .then(response => response.json())
        .then(data => {
            if (data.Success) {
                // Show success
                showNewCode(data.Code);

                // Update credits display
                const creditsEl = document.getElementById('current-credits');
                if (creditsEl && data.NewBalance !== undefined) {
                    creditsEl.textContent = numberFormat(data.NewBalance);

                    // Check if can still generate
                    if (data.NewBalance < creditCost) {
                        btn.disabled = true;
                        btn.className = 'btn btn-disabled btn-generate';
                        btn.innerHTML = '<svg class="iconpark-icon" width="16" height="16"><use href="#attention"></use></svg> 鸡腿不足';
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }

                // Reload page after 2 seconds to show new code in list
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                // Show error
                alert(data.Error || '生成失败，请重试');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Generate code error:', error);
            alert('网络错误，请重试');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    /**
     * Show newly generated code.
     */
    function showNewCode(code) {
        const display = document.getElementById('new-code-display');
        const codeValue = document.getElementById('new-code-value');

        if (display && codeValue) {
            codeValue.textContent = code;
            display.classList.remove('hidden');

            // Scroll to display
            display.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    /**
     * Initialize copy buttons.
     */
    function initCopyButtons() {
        // Copy by target ID
        document.querySelectorAll('[data-copy-target]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-copy-target');
                const target = document.getElementById(targetId);
                if (target) {
                    copyToClipboard(target.textContent || target.value, this);
                }
            });
        });

        // Copy by data-copy-text
        document.querySelectorAll('[data-copy-text]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const text = this.getAttribute('data-copy-text');
                if (text) {
                    copyToClipboard(text, this);
                }
            });
        });
    }

    /**
     * Copy text to clipboard.
     */
    function copyToClipboard(text, btn) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                showCopySuccess(btn);
            }).catch(function(err) {
                console.error('Copy failed:', err);
                fallbackCopy(text, btn);
            });
        } else {
            fallbackCopy(text, btn);
        }
    }

    /**
     * Fallback copy method.
     */
    function fallbackCopy(text, btn) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showCopySuccess(btn);
        } catch (err) {
            console.error('Fallback copy failed:', err);
        }
        document.body.removeChild(textarea);
    }

    /**
     * Show copy success feedback.
     */
    function showCopySuccess(btn) {
        if (!btn) return;

        btn.classList.add('copied');

        // Change icon temporarily
        const icon = btn.querySelector('.iconpark-icon use');
        if (icon) {
            const originalHref = icon.getAttribute('href');
            icon.setAttribute('href', '#check-one');

            setTimeout(function() {
                btn.classList.remove('copied');
                icon.setAttribute('href', originalHref);
            }, 1500);
        } else {
            setTimeout(function() {
                btn.classList.remove('copied');
            }, 1500);
        }
    }

    /**
     * Format number with commas.
     */
    function numberFormat(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
})();
