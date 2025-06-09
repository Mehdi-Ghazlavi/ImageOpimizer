<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Image Optimizer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        secondary: '#8b5cf6',
                        dark: '#1e293b',
                        light: '#f8fafc'
                    }
                }
            }
        }
    </script>
    <style>
        .file-upload-area {
            transition: all 0.3s ease;
        }
        .file-upload-area.drag-over {
            border-color: #6366f1;
            background-color: #eef2ff;
        }
        .file-item {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        .progress-bar {
            transition: width 0.5s ease;
        }
        .preview-container {
            scrollbar-width: thin;
            scrollbar-color: #c7d2fe #f1f5f9;
        }
        .preview-container::-webkit-scrollbar {
            width: 6px;
        }
        .preview-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .preview-container::-webkit-scrollbar-thumb {
            background-color: #c7d2fe;
            border-radius: 4px;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-purple-100 min-h-screen p-4 md:p-8">
<div class="max-w-6xl mx-auto" x-data="imageUploader()" x-init="init()">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">
            <i class="fas fa-image text-primary mr-2"></i>Laravel Image Optimizer
        </h1>
        <p class="text-gray-600 max-w-2xl mx-auto">
            Upload images to optimize them with Laravel backend. Supports drag & drop, previews, and progress tracking.
        </p>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
        <div class="md:flex">
            <!-- Upload Section -->
            <div class="md:w-1/2 p-6 md:p-8 bg-gradient-to-br from-indigo-50 to-purple-50">
                <div
                    class="file-upload-area border-2 border-dashed border-gray-300 rounded-xl bg-white p-8 text-center cursor-pointer"
                    :class="{'drag-over': dragOver}"
                    @dragover.prevent="dragOver = true"
                    @dragleave="dragOver = false"
                    @drop.prevent="handleDrop($event)"
                >
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mb-4">
                            <i class="fas fa-cloud-upload-alt text-3xl text-primary"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Drag & Drop your images</h3>
                        <p class="text-gray-600 text-sm mb-4">or</p>
                        <input
                            type="file"
                            id="fileInput"
                            class="hidden"
                            multiple
                            accept="image/*"
                            @change="handleFileSelect"
                        >
                        <label for="fileInput" class="px-5 py-2.5 bg-gradient-to-r from-primary to-purple-600 text-white rounded-lg font-medium cursor-pointer hover:opacity-90 transition-opacity">
                            <i class="fas fa-folder-open mr-2"></i>Browse Files
                        </label>
                        <p class="text-xs text-gray-500 mt-4">Supports JPG, PNG, GIF up to 10MB</p>
                    </div>
                </div>

                <!-- File Info -->
                <div class="mt-6">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                            <div class="text-gray-600 text-sm mb-1">Files Selected</div>
                            <div class="text-2xl font-bold text-primary" x-text="files.length"></div>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                            <div class="text-gray-600 text-sm mb-1">Total Size</div>
                            <div class="text-2xl font-bold text-secondary" x-text="formatFileSize(totalSize)"></div>
                        </div>
                    </div>

                    <button
                        class="w-full py-3.5 bg-gradient-to-r from-primary to-secondary text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center"
                        :disabled="files.length === 0 || isUploading"
                        @click="uploadFiles"
                    >
                            <span x-show="!isUploading">
                                <i class="fas fa-bolt mr-2"></i>Optimize Images
                            </span>
                        <span x-show="isUploading">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                            </span>
                    </button>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="md:w-1/2 bg-white p-6 md:p-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-eye mr-2 text-primary"></i>Preview
                    </h2>
                    <button
                        class="text-sm text-red-500 hover:text-red-700 bg-red-50 px-3 py-1 rounded-lg transition-colors"
                        @click="files = []"
                        :disabled="isUploading"
                        x-show="files.length > 0"
                    >
                        <i class="fas fa-trash mr-1"></i> Clear All
                    </button>
                </div>

                <div class="preview-container space-y-4 max-h-[420px] overflow-y-auto pr-2">
                    <template x-for="(file, index) in files" :key="index">
                        <div
                            class="file-item bg-gradient-to-br from-gray-50 to-white rounded-lg border border-gray-200 p-4 flex items-center shadow-sm"
                            :class="{
                                    'border-green-200': file.progress === 100,
                                    'border-red-200': file.error,
                                    'border-indigo-200': file.progress > 0 && file.progress < 100
                                }"
                        >
                            <div class="flex-shrink-0 w-16 h-16 rounded-md overflow-hidden border-2 border-gray-100 shadow-inner">
                                <img :src="file.preview" class="object-cover w-full h-full" alt="Preview">
                            </div>
                            <div class="ml-4 flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 truncate max-w-[180px]" x-text="file.name"></div>
                                        <div class="text-xs text-gray-500 mt-0.5" x-text="formatFileSize(file.size)"></div>
                                    </div>
                                    <span
                                        class="text-xs px-2 py-1 rounded-full font-medium"
                                        :class="{
                                                'bg-yellow-100 text-yellow-800': file.progress === 0,
                                                'bg-blue-100 text-blue-800': file.progress > 0 && file.progress < 100,
                                                'bg-green-100 text-green-800': file.progress === 100,
                                                'bg-red-100 text-red-800': file.error
                                            }"
                                        x-text="file.error ? 'Error' : (file.progress === 100 ? 'Optimized' : (file.progress > 0 ? 'Processing' : 'Ready'))"
                                    ></span>
                                </div>

                                <!-- Progress Bar -->
                                <div class="mt-3" x-show="file.progress > 0 || file.error">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div
                                            class="progress-bar h-2 rounded-full"
                                            :class="{
                                                    'bg-gradient-to-r from-primary to-secondary': file.progress < 100 && !file.error,
                                                    'bg-gradient-to-r from-green-400 to-green-600': file.progress === 100,
                                                    'bg-gradient-to-r from-red-400 to-red-600': file.error
                                                }"
                                            :style="'width: ' + file.progress + '%'"
                                        ></div>
                                    </div>
                                    <div class="flex justify-between items-center mt-1.5">
                                            <span
                                                class="text-xs font-medium"
                                                :class="{
                                                    'text-gray-700': file.progress < 100 && !file.error,
                                                    'text-green-600': file.progress === 100,
                                                    'text-red-600': file.error
                                                }"
                                                x-text="file.error ? 'Error: ' + file.errorMessage : (file.progress === 100 ? 'Optimized successfully!' : file.progress + '% processed')"
                                            ></span>
                                        <span
                                            class="text-xs text-gray-500"
                                            x-text="file.error ? '' : (file.progress === 100 ? formatFileSize(file.optimizedSize || file.size) : '')"
                                        ></span>
                                    </div>
                                </div>
                            </div>
                            <button
                                class="ml-3 text-gray-400 hover:text-red-500 transition-colors"
                                @click="removeFile(index)"
                                :disabled="isUploading"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <div
                        class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50"
                        x-show="files.length === 0"
                    >
                        <i class="fas fa-images text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500 font-medium">No images selected</p>
                        <p class="text-gray-400 text-sm mt-1">Select or drop images to preview</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Progress -->
    <div
        class="bg-white rounded-2xl shadow-lg p-6 mb-8 transition-all duration-500"
        :class="{'opacity-100 translate-y-0': isUploading, 'opacity-0 translate-y-4 pointer-events-none': !isUploading}"
        x-show="isUploading"
        x-cloak
    >
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">
                    <i class="fas fa-bolt text-xl text-primary"></i>
                </div>
            </div>
            <div class="ml-4 flex-1">
                <h3 class="font-medium text-gray-900">Optimizing Images</h3>
                <p class="text-sm text-gray-500" x-text="files.length + ' files (' + formatFileSize(totalSize) + ')'"></p>
            </div>
            <div class="text-right">
                <div class="text-xl font-bold text-primary" x-text="totalProgress + '%'"></div>
            </div>
        </div>

        <!-- Overall progress -->
        <div class="mt-4">
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div
                    class="progress-bar bg-gradient-to-r from-primary to-secondary h-2.5 rounded-full"
                    :style="'width: ' + totalProgress + '%'"
                ></div>
            </div>
        </div>
    </div>

    <!-- Optimized Results -->
    <div
        class="bg-white rounded-2xl shadow-lg p-6 mb-8"
        x-show="optimizedResults.length > 0"
        x-transition
        x-cloak
    >
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>Optimization Results
            </h2>
            <div class="bg-green-50 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                <span x-text="optimizedResults.length"></span> images optimized
            </div>
        </div>
        <div class="flex justify-end mb-4" x-show="optimizedResults.length">
            <button
                @click="downloadAll"
                class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-indigo-600 transition-colors"
            >
                <i class="fas fa-download mr-2"></i>Download All
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="(result, index) in optimizedResults" :key="index">
                <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
                    <div class="h-48 overflow-hidden flex items-center justify-center bg-gray-100">
                        <img :src="result.url" class="object-contain max-h-full max-w-full" alt="Optimized image">
                    </div>
                    <div class="p-4">
                        <div class="text-sm font-medium text-gray-900 truncate" x-text="result.name"></div>
                        <div class="flex justify-between items-center mt-3">
                            <div>
                                <div class="text-xs text-gray-500">Original size</div>
                                <div class="text-xs font-medium text-gray-700" x-text="formatFileSize(result.originalSize)"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Optimized size</div>
                                <div class="text-xs font-medium text-green-600" x-text="formatFileSize(result.optimizedSize)"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Savings</div>
                                <div class="text-xs font-medium text-primary" x-text="result.savings + '%'"></div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div
                                    class="h-1.5 rounded-full"
                                    :class="result.savings > 0 ? 'bg-green-500' : 'bg-yellow-500'"
                                    :style="'width: ' + Math.abs(result.savings) + '%'"
                                ></div>
                            </div>
                        </div>

                        <!-- Download Button -->
                        <div class="mt-4 text-right">
                            <a
                                :href="result.url"
                                :download="result.name"
                                class="inline-flex items-center px-4 py-1.5 bg-primary text-white text-xs font-semibold rounded-lg hover:bg-indigo-600 transition-colors"
                            >
                                <i class="fas fa-download mr-2"></i>Download
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

<script>
    function imageUploader() {
        return {
            files: [],
            dragOver: false,
            isUploading: false,
            totalProgress: 0,

            optimizedResults: [],

            downloadAll() {
                this.optimizedResults.forEach(result => {
                    const a = document.createElement('a');
                    a.href = result.url;
                    a.download = result.name;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                });
            },

            init() {
                // Initialization if needed
            },

            handleFileSelect(e) {
                const selectedFiles = Array.from(e.target.files);
                this.processFiles(selectedFiles);
                e.target.value = ''; // Reset input to allow selecting same file again
            },

            handleDrop(e) {
                this.dragOver = false;
                const droppedFiles = Array.from(e.dataTransfer.files);
                this.processFiles(droppedFiles);
            },

            processFiles(fileList) {
                const imageFiles = fileList.filter(file => file.type.startsWith('image/'));

                imageFiles.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.files.push({
                            file: file,
                            name: file.name,
                            size: file.size,
                            type: file.type,
                            preview: e.target.result,
                            progress: 0,
                            error: false,
                            errorMessage: '',
                            optimizedSize: 0
                        });
                    };
                    reader.readAsDataURL(file);
                });
            },

            removeFile(index) {
                this.files.splice(index, 1);
            },

            formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];

            },

            get totalSize() {
                return this.files.reduce((total, file) => total + file.size, 0);
            },

            async uploadFiles() {
                if (this.files.length === 0) return;

                this.isUploading = true;
                this.optimizedResults = [];
                this.totalProgress = 0;

                // Reset file states
                this.files = this.files.map(file => ({
                    ...file,
                    progress: 0,
                    error: false,
                    errorMessage: ''
                }));

                const totalFiles = this.files.length;
                let completedFiles = 0;

                for (let index = 0; index < this.files.length; index++) {
                    const file = this.files[index];

                    const formData = new FormData();
                    formData.append('images[]', file.file, file.name);

                    try {
                        await new Promise((resolve, reject) => {
                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', '/optimize-images', true);
                            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);

                            xhr.upload.onprogress = (e) => {
                                if (e.lengthComputable) {
                                    const percent = Math.round((e.loaded / e.total) * 100);
                                    this.files[index].progress = percent;

                                    // Update total progress
                                    this.totalProgress = Math.round(
                                        this.files.reduce((sum, file) => sum + file.progress, 0) / totalFiles
                                    );
                                }
                            };

                            xhr.onload = () => {
                                if (xhr.status === 200) {
                                    const data = JSON.parse(xhr.responseText);
                                    if (data.success) {
                                        const result = data.images[0]; // adjust if multiple
                                        this.files[index].progress = 100;
                                        this.files[index].optimizedSize = result.optimizedSize;

                                        this.optimizedResults.push({
                                            name: result.name,
                                            url: result.url,
                                            originalSize: result.originalSize,
                                            optimizedSize: result.optimizedSize,
                                            savings: Math.round((1 - (result.optimizedSize / result.originalSize)) * 100)
                                        });
                                        resolve();
                                    } else {
                                        reject(new Error('Optimization failed'));
                                    }
                                } else {
                                    reject(new Error('Server error'));
                                }
                            };

                            xhr.onerror = () => reject(new Error('Upload failed'));
                            xhr.send(formData);
                        });
                    } catch (error) {
                        this.files[index].error = true;
                        this.files[index].errorMessage = error.message || 'Upload failed';
                    }
                }

                this.totalProgress = 100;
                this.isUploading = false;
            },

            // This simulates the upload to Laravel
            simulateUpload(index, formData) {
                return new Promise((resolve, reject) => {
                    // Simulate progress
                    const interval = setInterval(() => {
                        // Increment progress
                        this.files[index].progress += Math.floor(Math.random() * 15) + 5;

                        if (this.files[index].progress >= 100) {
                            clearInterval(interval);
                            this.files[index].progress = 100;

                            // Random chance to simulate failure (for demo purposes)
                            if (Math.random() < 0.1) { // 10% chance of error
                                reject(new Error('Server processing error'));
                            } else {
                                resolve();
                            }
                        }

                        // Update total progress
                        this.totalProgress = Math.round(
                            this.files.reduce((sum, file) => sum + file.progress, 0) / this.files.length
                        );
                    }, 300);
                });
            }
        }
    }
</script>
</body>
</html>
