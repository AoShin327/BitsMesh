/**
 * BitsMesh Setting Page JavaScript
 *
 * Handles profile form submission, avatar cropping with Cropper.js,
 * and form interactions.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

(function() {
    'use strict';

    // Wait for DOM and Cropper.js to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initProfileForm();
        initAvatarCropper();
        initBioCounter();
    });

    /**
     * Initialize profile form submission
     */
    function initProfileForm() {
        const form = document.getElementById('bits-profile-form');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            saveProfile();
        });
    }

    /**
     * Save profile data via AJAX
     */
    function saveProfile() {
        const form = document.getElementById('bits-profile-form');
        const btn = document.getElementById('bits-save-profile');
        if (!form || !btn) return;

        // Disable button and show loading state
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="bits-spinner"></span> 保存中...';

        // Gather form data
        const formData = new FormData(form);

        // Send AJAX request
        fetch('/profile/setting/save', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.Success) {
                showToast(data.Message || '保存成功', 'success');
            } else {
                showToast(data.Error || '保存失败', 'error');
            }
        })
        .catch(error => {
            console.error('Save error:', error);
            showToast('网络错误，请稍后重试', 'error');
        })
        .finally(() => {
            // Restore button state
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    /**
     * Initialize Bio character counter
     */
    function initBioCounter() {
        const bioInput = document.getElementById('bits-bio');
        const bioCount = document.getElementById('bits-bio-count');
        if (!bioInput || !bioCount) return;

        bioInput.addEventListener('input', function() {
            const count = this.value.length;
            bioCount.textContent = count;

            // Visual feedback when approaching limit
            if (count > 230) {
                bioCount.style.color = '#ef4444';
            } else if (count > 200) {
                bioCount.style.color = '#f59e0b';
            } else {
                bioCount.style.color = '';
            }
        });
    }

    /**
     * Initialize avatar cropper functionality
     */
    function initAvatarCropper() {
        const avatarUpload = document.getElementById('bits-avatar-upload');
        const avatarModal = document.getElementById('bits-avatar-modal');
        const avatarInput = document.getElementById('bits-avatar-input');
        const cropperImage = document.getElementById('bits-cropper-image');
        const avatarPreview = document.getElementById('bits-avatar-preview');

        if (!avatarUpload || !avatarModal || !avatarInput || !cropperImage) return;

        let cropper = null;

        // Open modal when clicking avatar
        avatarUpload.addEventListener('click', function() {
            openModal();
        });

        // Close modal
        document.getElementById('bits-modal-close').addEventListener('click', closeModal);
        document.getElementById('bits-cancel-crop').addEventListener('click', closeModal);
        document.querySelector('.bits-modal-backdrop').addEventListener('click', closeModal);

        // File input change
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file type
            if (!file.type.match(/^image\/(jpeg|png|gif)$/)) {
                showToast('请选择 JPG、PNG 或 GIF 格式的图片', 'error');
                return;
            }

            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                showToast('图片大小不能超过 2MB', 'error');
                return;
            }

            // Read and display image
            const reader = new FileReader();
            reader.onload = function(event) {
                cropperImage.src = event.target.result;

                // Destroy existing cropper
                if (cropper) {
                    cropper.destroy();
                }

                // Initialize Cropper.js
                cropper = new Cropper(cropperImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    minCropBoxWidth: 100,
                    minCropBoxHeight: 100,
                    responsive: true,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false
                });
            };
            reader.readAsDataURL(file);
        });

        // Zoom controls
        document.getElementById('bits-zoom-in').addEventListener('click', function() {
            if (cropper) cropper.zoom(0.1);
        });

        document.getElementById('bits-zoom-out').addEventListener('click', function() {
            if (cropper) cropper.zoom(-0.1);
        });

        document.getElementById('bits-zoom-slider').addEventListener('input', function() {
            if (cropper) {
                const imageData = cropper.getImageData();
                const ratio = this.value;
                cropper.zoomTo(ratio);
            }
        });

        // Rotate controls
        document.getElementById('bits-rotate-left').addEventListener('click', function() {
            if (cropper) cropper.rotate(-90);
        });

        document.getElementById('bits-rotate-right').addEventListener('click', function() {
            if (cropper) cropper.rotate(90);
        });

        // Confirm crop
        document.getElementById('bits-confirm-crop').addEventListener('click', function() {
            if (!cropper) {
                showToast('请先选择图片', 'error');
                return;
            }

            // Get cropped canvas
            const canvas = cropper.getCroppedCanvas({
                width: 200,
                height: 200,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            if (!canvas) {
                showToast('裁剪失败', 'error');
                return;
            }

            // Convert to base64
            const avatarData = canvas.toDataURL('image/png');

            // Upload avatar
            uploadAvatar(avatarData, function(photoUrl) {
                // Update preview
                avatarPreview.src = photoUrl;

                // Close modal
                closeModal();

                // Destroy cropper
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }

                showToast('头像更新成功', 'success');
            });
        });

        function openModal() {
            avatarModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            avatarModal.classList.remove('active');
            document.body.style.overflow = '';

            // Reset file input
            avatarInput.value = '';

            // Destroy cropper
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }

            // Clear image src
            cropperImage.src = '';
        }
    }

    /**
     * Upload avatar via AJAX
     */
    function uploadAvatar(avatarData, onSuccess) {
        const btn = document.getElementById('bits-confirm-crop');
        if (!btn) return;

        // Disable button
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="bits-spinner"></span> 上传中...';

        // Get TransientKey
        const form = document.getElementById('bits-profile-form');
        const transientKey = form ? form.querySelector('input[name="TransientKey"]').value : '';

        // Send request
        fetch('/profile/setting/avatar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'Avatar=' + encodeURIComponent(avatarData) + '&TransientKey=' + encodeURIComponent(transientKey)
        })
        .then(response => response.json())
        .then(data => {
            if (data.Success) {
                if (onSuccess && data.PhotoUrl) {
                    onSuccess(data.PhotoUrl);
                }
            } else {
                showToast(data.Error || '上传失败', 'error');
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            showToast('网络错误，请稍后重试', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    /**
     * Show toast notification
     */
    function showToast(message, type) {
        const toast = document.getElementById('bits-toast');
        if (!toast) return;

        const messageEl = toast.querySelector('.bits-toast-message');
        if (messageEl) {
            messageEl.textContent = message;
        }

        // Set type class
        toast.className = 'bits-toast';
        if (type) {
            toast.classList.add('bits-toast-' + type);
        }

        // Show toast
        toast.classList.add('active');

        // Auto hide after 3 seconds
        setTimeout(function() {
            toast.classList.remove('active');
        }, 3000);
    }

    // Expose to global for debugging
    window.BitsSettings = {
        saveProfile: saveProfile,
        showToast: showToast
    };

})();
