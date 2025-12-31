/**
 * Advanced File Upload Field Handler
 *
 * Provides drag-drop zone, preview, progress, and multiple file support.
 *
 * @package FormFlow
 * @since 2.8.0
 */

(function($) {
    'use strict';

    class AdvancedFileUpload {
        constructor(element) {
            this.$element = $(element);
            this.$input = this.$element.find('.isf-file-input');
            this.$dropZone = this.$element.find('.isf-file-dropzone');
            this.$fileList = this.$element.find('.isf-file-list');
            this.$browseBtn = this.$element.find('.isf-file-browse');

            this.files = [];
            this.maxSize = parseInt(this.$input.data('max-size')) || 5; // MB
            this.allowedTypes = (this.$input.data('allowed-types') || 'jpg,jpeg,png,pdf').split(',');
            this.multiple = this.$input.attr('multiple') !== undefined;
            this.maxFiles = parseInt(this.$input.data('max-files')) || (this.multiple ? 10 : 1);

            this.init();
        }

        init() {
            this.bindEvents();
            this.setupDragDrop();
        }

        bindEvents() {
            // Browse button click
            this.$browseBtn.on('click', (e) => {
                e.preventDefault();
                this.$input.trigger('click');
            });

            // File input change
            this.$input.on('change', (e) => {
                this.handleFiles(e.target.files);
            });

            // Remove file
            this.$fileList.on('click', '.isf-file-remove', (e) => {
                e.preventDefault();
                const index = $(e.currentTarget).data('index');
                this.removeFile(index);
            });

            // Click dropzone to browse
            this.$dropZone.on('click', (e) => {
                if (!$(e.target).hasClass('isf-file-remove')) {
                    this.$input.trigger('click');
                }
            });
        }

        setupDragDrop() {
            const $dropZone = this.$dropZone;

            // Prevent default drag behaviors
            $(document).on('dragover drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
            });

            // Highlight drop zone
            $dropZone.on('dragenter dragover', (e) => {
                e.preventDefault();
                e.stopPropagation();
                $dropZone.addClass('isf-dragover');
            });

            $dropZone.on('dragleave', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (!$dropZone[0].contains(e.relatedTarget)) {
                    $dropZone.removeClass('isf-dragover');
                }
            });

            // Handle drop
            $dropZone.on('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                $dropZone.removeClass('isf-dragover');

                const files = e.originalEvent.dataTransfer.files;
                this.handleFiles(files);
            });
        }

        handleFiles(fileList) {
            const files = Array.from(fileList);

            // Check file count limit
            if (!this.multiple && files.length > 1) {
                this.showError('Only one file is allowed');
                return;
            }

            const totalFiles = this.files.length + files.length;
            if (totalFiles > this.maxFiles) {
                this.showError(`Maximum ${this.maxFiles} files allowed`);
                return;
            }

            files.forEach((file) => {
                if (this.validateFile(file)) {
                    this.addFile(file);
                }
            });
        }

        validateFile(file) {
            // Check file size
            const fileSizeMB = file.size / (1024 * 1024);
            if (fileSizeMB > this.maxSize) {
                this.showError(`${file.name} exceeds maximum size of ${this.maxSize}MB`);
                return false;
            }

            // Check file type
            const extension = file.name.split('.').pop().toLowerCase();
            if (!this.allowedTypes.includes(extension)) {
                this.showError(`${file.name} is not an allowed file type. Allowed: ${this.allowedTypes.join(', ')}`);
                return false;
            }

            return true;
        }

        addFile(file) {
            const fileData = {
                file: file,
                id: Date.now() + Math.random(),
                progress: 0,
                uploaded: false,
                error: null
            };

            this.files.push(fileData);
            this.renderFile(fileData);
            this.uploadFile(fileData);
        }

        renderFile(fileData) {
            const isImage = fileData.file.type.startsWith('image/');
            const fileSize = this.formatFileSize(fileData.file.size);
            const index = this.files.indexOf(fileData);

            let preview = '<div class="isf-file-icon"><span class="dashicons dashicons-media-default"></span></div>';

            if (isImage) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.$fileList.find(`[data-file-id="${fileData.id}"] .isf-file-preview`).html(
                        `<img src="${e.target.result}" alt="${this.escapeHtml(fileData.file.name)}">`
                    );
                };
                reader.readAsDataURL(fileData.file);
            }

            const html = `
                <div class="isf-file-item" data-file-id="${fileData.id}">
                    <div class="isf-file-preview">${preview}</div>
                    <div class="isf-file-info">
                        <div class="isf-file-name">${this.escapeHtml(fileData.file.name)}</div>
                        <div class="isf-file-size">${fileSize}</div>
                        <div class="isf-file-progress">
                            <div class="isf-progress-bar">
                                <div class="isf-progress-fill" style="width: 0%"></div>
                            </div>
                            <div class="isf-progress-text">0%</div>
                        </div>
                    </div>
                    <button type="button" class="isf-file-remove" data-index="${index}" title="Remove">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
            `;

            this.$fileList.append(html);
            this.$dropZone.addClass('has-files');
        }

        uploadFile(fileData) {
            // Simulate upload progress
            // In a real implementation, this would use XMLHttpRequest or fetch with progress tracking
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 20;
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(interval);
                    this.onUploadComplete(fileData);
                }
                this.updateProgress(fileData, progress);
            }, 200);

            // In real implementation, replace with actual upload:
            /*
            const formData = new FormData();
            formData.append('file', fileData.file);
            formData.append('action', 'isf_upload_file');
            formData.append('nonce', ISFAdvancedUpload.nonce);

            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    this.updateProgress(fileData, percentComplete);
                }
            });

            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        fileData.uploaded = true;
                        fileData.url = response.data.url;
                        this.onUploadComplete(fileData);
                    } else {
                        this.onUploadError(fileData, response.data.message);
                    }
                } else {
                    this.onUploadError(fileData, 'Upload failed');
                }
            });

            xhr.addEventListener('error', () => {
                this.onUploadError(fileData, 'Network error');
            });

            xhr.open('POST', ISFAdvancedUpload.ajax_url);
            xhr.send(formData);
            */
        }

        updateProgress(fileData, progress) {
            fileData.progress = Math.min(100, Math.max(0, progress));
            const $item = this.$fileList.find(`[data-file-id="${fileData.id}"]`);
            $item.find('.isf-progress-fill').css('width', fileData.progress + '%');
            $item.find('.isf-progress-text').text(Math.round(fileData.progress) + '%');
        }

        onUploadComplete(fileData) {
            fileData.uploaded = true;
            const $item = this.$fileList.find(`[data-file-id="${fileData.id}"]`);
            $item.addClass('isf-file-uploaded');
            $item.find('.isf-file-progress').html('<span class="isf-upload-complete"><span class="dashicons dashicons-yes"></span> Uploaded</span>');
        }

        onUploadError(fileData, errorMessage) {
            fileData.error = errorMessage;
            const $item = this.$fileList.find(`[data-file-id="${fileData.id}"]`);
            $item.addClass('isf-file-error');
            $item.find('.isf-file-progress').html(`<span class="isf-upload-error"><span class="dashicons dashicons-warning"></span> ${this.escapeHtml(errorMessage)}</span>`);
        }

        removeFile(index) {
            const fileData = this.files[index];
            if (fileData) {
                this.$fileList.find(`[data-file-id="${fileData.id}"]`).remove();
                this.files.splice(index, 1);

                if (this.files.length === 0) {
                    this.$dropZone.removeClass('has-files');
                }

                // Reindex remaining files
                this.$fileList.find('.isf-file-remove').each((i, el) => {
                    $(el).attr('data-index', i);
                });
            }
        }

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        showError(message) {
            const $error = $('<div class="isf-file-error-message">' + this.escapeHtml(message) + '</div>');
            this.$element.append($error);
            setTimeout(() => $error.fadeOut(() => $error.remove()), 5000);
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Initialize advanced file uploads
    $(document).ready(function() {
        $('.isf-field-file-advanced').each(function() {
            new AdvancedFileUpload(this);
        });
    });

    // Expose class for external use
    window.ISFAdvancedFileUpload = AdvancedFileUpload;

})(jQuery);
