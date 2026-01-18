/**
 * Nice Avatar - Registration-time avatar generation
 *
 * Generates avatar on registration form, converts to image, uploads as user photo.
 * After registration, avatar is a static file - no runtime rendering needed.
 *
 * Uses dom-to-image for PNG export (same as official react-nice-avatar demo)
 */

import React from 'react';
import ReactDOM from 'react-dom/client';
import Avatar, { genConfig } from 'react-nice-avatar';
import domtoimage from 'dom-to-image';

/**
 * Generate deterministic seed from email
 * @param {string} email
 * @returns {string} MD5-like seed
 */
function generateSeed(email) {
    // Simple hash function (same as PHP md5 for consistency)
    const str = email.toLowerCase().trim();
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        const char = str.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash;
    }
    // Convert to hex-like string
    return Math.abs(hash).toString(16).padStart(8, '0') +
           str.split('').reduce((a, c) => a + c.charCodeAt(0), 0).toString(16).padStart(8, '0');
}

/**
 * Render avatar to a container
 * @param {HTMLElement} container
 * @param {string} seed
 * @param {number} size
 * @returns {Promise<void>}
 */
function renderAvatar(container, seed, size = 200) {
    return new Promise((resolve) => {
        const config = genConfig(seed);
        const root = ReactDOM.createRoot(container);

        root.render(
            React.createElement(Avatar, {
                ...config,
                shape: 'square',
                style: { width: size, height: size }
            })
        );

        // Wait for React to render completely
        // React-nice-avatar uses multiple SVGs with position:absolute
        // Need enough time for all layers to render
        setTimeout(() => {
            resolve();
        }, 200);
    });
}

/**
 * Convert DOM element to PNG Blob using dom-to-image
 * This is the same approach used in the official react-nice-avatar demo
 * @param {HTMLElement} node - DOM element containing the avatar
 * @param {number} size - Original element size
 * @returns {Promise<Blob>}
 */
async function containerToPngBlob(node, size = 128) {
    // Use scale=3 for high-resolution output (128 * 3 = 384px)
    const scale = 3;

    const blob = await domtoimage.toBlob(node, {
        width: size * scale,
        height: size * scale,
        style: {
            transform: `scale(${scale})`,
            transformOrigin: 'top left',
            'border-radius': '0'
        }
    });

    return blob;
}

/**
 * Initialize avatar generation on registration form
 */
function initRegistrationForm() {
    // Find registration form
    const form = document.querySelector('form#Form_User_Register, form[action*="entry/register"]');
    if (!form) return;

    const emailInput = form.querySelector('input[name="Email"], input[type="email"]');
    if (!emailInput) return;

    // Create preview container
    const previewContainer = document.createElement('div');
    previewContainer.className = 'nice-avatar-preview';
    previewContainer.innerHTML = `
        <div class="nice-avatar-preview-wrapper" style="
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 12px 0;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        ">
            <div class="nice-avatar-preview-image" style="
                width: 128px;
                height: 128px;
                border-radius: 15%;
                overflow: hidden;
                background: linear-gradient(135deg, #e0ddff 0%, #ffedef 100%);
            "></div>
            <div class="nice-avatar-preview-text" style="font-size: 14px; color: #666;">
                输入邮箱后将自动生成专属头像
            </div>
        </div>
    `;

    // Insert preview after email field
    const emailGroup = emailInput.closest('.form-group, .P, li') || emailInput.parentElement;
    emailGroup.parentNode.insertBefore(previewContainer, emailGroup.nextSibling);

    const previewImage = previewContainer.querySelector('.nice-avatar-preview-image');
    const previewText = previewContainer.querySelector('.nice-avatar-preview-text');

    // Hidden input to store avatar data
    const avatarDataInput = document.createElement('input');
    avatarDataInput.type = 'hidden';
    avatarDataInput.name = 'NiceAvatarData';
    form.appendChild(avatarDataInput);

    let debounceTimer = null;
    let currentSeed = null;

    // Update preview when email changes
    async function updatePreview() {
        const email = emailInput.value.trim();
        if (!email || !email.includes('@')) {
            previewImage.innerHTML = '';
            previewText.textContent = '输入邮箱后将自动生成专属头像';
            avatarDataInput.value = '';
            currentSeed = null;
            return;
        }

        const seed = generateSeed(email);
        if (seed === currentSeed) return;
        currentSeed = seed;

        previewText.textContent = '正在生成头像...';

        try {
            // Render avatar for preview (larger size for better export quality)
            // Using 128px preview, will export at 3x scale = 384px
            previewImage.innerHTML = '';
            await renderAvatar(previewImage, seed, 128);

            // Wait for all SVG layers to fully render in the visible preview
            await new Promise(resolve => setTimeout(resolve, 400));

            // Export directly from the visible preview element
            // This works because the element is already rendered and visible
            const pngBlob = await containerToPngBlob(previewImage, 128);

            // Validate blob was created
            if (!pngBlob) {
                throw new Error('Failed to create PNG blob');
            }

            // Convert to base64 for form submission
            const reader = new FileReader();
            reader.onload = () => {
                avatarDataInput.value = reader.result;
                previewText.textContent = '✓ 头像已生成，注册后将自动使用';
            };
            reader.onerror = () => {
                console.error('FileReader error');
                previewText.textContent = '头像生成失败，将使用默认头像';
                avatarDataInput.value = '';
            };
            reader.readAsDataURL(pngBlob);

        } catch (error) {
            console.error('Avatar generation failed:', error);
            previewText.textContent = '头像生成失败，将使用默认头像';
            avatarDataInput.value = '';
        }
    }

    // Debounced email input handler
    emailInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(updatePreview, 500);
    });

    // Also trigger on blur
    emailInput.addEventListener('blur', updatePreview);

    // Initial check
    if (emailInput.value) {
        updatePreview();
    }
}

/**
 * Render explicit data-nice-avatar elements (for settings preview)
 */
function renderExplicitAvatars() {
    const elements = document.querySelectorAll('[data-nice-avatar]:not([data-nice-avatar-rendered])');

    elements.forEach(async (el) => {
        const seed = el.getAttribute('data-nice-avatar');
        const size = parseInt(el.getAttribute('data-size') || el.offsetWidth || '64', 10);

        if (seed) {
            await renderAvatar(el, seed, size);
            el.dataset.niceAvatarRendered = 'true';
        }
    });
}

/**
 * Initialize
 */
function init() {
    function onReady() {
        // Initialize registration form avatar
        initRegistrationForm();

        // Render explicit avatar elements (for settings page preview)
        renderExplicitAvatars();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }
}

// Auto-initialize
init();

// Export for manual use
export { generateSeed, renderAvatar, containerToPngBlob };
