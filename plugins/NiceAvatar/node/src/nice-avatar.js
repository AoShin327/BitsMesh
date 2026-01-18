/**
 * Nice Avatar - Registration-time avatar generation
 *
 * Generates avatar on registration form, converts to image, uploads as user photo.
 * After registration, avatar is a static file - no runtime rendering needed.
 */

import React from 'react';
import ReactDOM from 'react-dom/client';
import Avatar, { genConfig } from 'react-nice-avatar';
import html2canvas from 'html2canvas';

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
 * Render avatar to a container and return the SVG element
 * @param {HTMLElement} container
 * @param {string} seed
 * @param {number} size
 * @returns {Promise<SVGElement>}
 */
function renderAvatar(container, seed, size = 200) {
    return new Promise((resolve) => {
        const config = genConfig(seed);
        const root = ReactDOM.createRoot(container);

        root.render(
            React.createElement(Avatar, {
                ...config,
                shape: 'circle',
                style: { width: size, height: size }
            })
        );

        // Wait for React to render
        setTimeout(() => {
            const svg = container.querySelector('svg');
            resolve(svg);
        }, 100);
    });
}

/**
 * Convert container with avatar to PNG Blob using html2canvas
 * This captures the entire rendered avatar including all SVG layers
 * @param {HTMLElement} container - DOM element containing the avatar
 * @param {number} size - Output image size
 * @returns {Promise<Blob>}
 */
async function containerToPngBlob(container, size = 200) {
    // Use html2canvas to capture the rendered avatar
    const canvas = await html2canvas(container, {
        width: size,
        height: size,
        scale: 1,
        backgroundColor: '#ffffff',
        logging: false,
        useCORS: true,
        allowTaint: true
    });

    // Apply circular clip
    const finalCanvas = document.createElement('canvas');
    finalCanvas.width = size;
    finalCanvas.height = size;
    const ctx = finalCanvas.getContext('2d');

    // White background
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, size, size);

    // Circular clip
    ctx.beginPath();
    ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2);
    ctx.clip();

    // Draw the captured image
    ctx.drawImage(canvas, 0, 0, size, size);

    return new Promise((resolve, reject) => {
        finalCanvas.toBlob((blob) => {
            if (blob) {
                resolve(blob);
            } else {
                reject(new Error('Canvas toBlob failed'));
            }
        }, 'image/png', 0.95);
    });
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
                width: 64px;
                height: 64px;
                border-radius: 50%;
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
            // Render avatar
            previewImage.innerHTML = '';
            const svg = await renderAvatar(previewImage, seed, 64);

            if (!svg) {
                throw new Error('SVG not rendered');
            }

            // Generate PNG for upload (larger size for quality)
            const tempContainer = document.createElement('div');
            tempContainer.style.cssText = 'position:absolute;left:-9999px;width:200px;height:200px;';
            document.body.appendChild(tempContainer);

            await renderAvatar(tempContainer, seed, 200);
            const pngBlob = await containerToPngBlob(tempContainer, 200);

            document.body.removeChild(tempContainer);

            // Convert to base64 for form submission
            const reader = new FileReader();
            reader.onload = () => {
                avatarDataInput.value = reader.result;
                previewText.textContent = '✓ 头像已生成，注册后将自动使用';
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
