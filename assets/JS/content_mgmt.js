// Content Management Module
const CM = {
    currentPage: 'home',
    editors: {},
    apiBaseUrl: '/RADS-TOOLING/backend/api/content_mgmt.php',

    // Initialize the content management system
    init() {
        console.log('Initializing Content Management...');

        // Set up event listeners
        this.setupEventListeners();

        // Load initial content
        this.loadPageContent('home');

        console.log('CM initialized successfully');
    },

    // Set up all event listeners
    setupEventListeners() {
        // Tab switching
        document.querySelectorAll('.cm-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const page = tab.dataset.page;
                this.switchTab(page);
            });
        });

        // Edit button
        const btnEdit = document.getElementById('btnEditContent');
        if (btnEdit) {
            btnEdit.addEventListener('click', () => this.openEditModal());
        }

        // Modal controls
        const btnClose = document.getElementById('btnCloseModal');
        if (btnClose) {
            btnClose.addEventListener('click', () => this.closeEditModal());
        }

        const btnSaveDraft = document.getElementById('btnSaveDraft');
        if (btnSaveDraft) {
            btnSaveDraft.addEventListener('click', () => this.saveDraft());
        }

        const btnPublish = document.getElementById('btnPublish');
        if (btnPublish) {
            btnPublish.addEventListener('click', () => this.publish());
        }

        const btnReset = document.getElementById('btnReset');
        if (btnReset) {
            btnReset.addEventListener('click', () => this.resetToPublished());
        }

        // Close modal on outside click
        const modal = document.getElementById('editModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeEditModal();
                }
            });
        }
    },

    // Switch between content tabs
    switchTab(page) {
        console.log(`Switching to tab: ${page}`);

        // Update active tab
        document.querySelectorAll('.cm-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        const activeTab = document.querySelector(`.cm-tab[data-page="${page}"]`);
        if (activeTab) {
            activeTab.classList.add('active');
        }

        // Load content for the selected page
        this.currentPage = page;
        this.loadPageContent(page);
    },

    // Load content preview for a specific page
    loadPageContent(page) {
        console.log(`Loading content for page: ${page}`);
        const previewCard = document.getElementById('previewCard');

        if (!previewCard) {
            console.error('Preview card element not found');
            return;
        }

        // Show loading state
        previewCard.innerHTML = `
            <div class="preview-loading">
                <div class="spinner"></div>
                <p>Loading preview...</p>
            </div>
        `;

        // Fetch content from API
        fetch(`${this.apiBaseUrl}?action=get&page=${page}`)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                if (data.success) {
                    this.renderPreview(page, data.content);
                } else {
                    throw new Error(data.message || 'Failed to load content');
                }
            })
            .catch(error => {
                console.error('Error loading content:', error);
                previewCard.innerHTML = `
                    <div class="preview-error">
                        <span class="material-symbols-rounded" style="font-size: 48px; color: #e74c3c;">error</span>
                        <p>Error loading content</p>
                        <small>${error.message}</small>
                        <button onclick="CM.loadPageContent('${page}')" class="btn-retry">
                            Retry
                        </button>
                    </div>
                `;
            });
    },

    // Render content preview
    renderPreview(page, content) {
        const previewCard = document.getElementById('previewCard');

        if (!content) {
            previewCard.innerHTML = `
                <div class="preview-placeholder">
                    <p>No content available for this page</p>
                </div>
            `;
            return;
        }

        let html = '';

        switch (page) {
            case 'home':
                html = `
                    <div class="preview-content">
                        <h2>Homepage Preview</h2>
                        <div class="preview-section">
                            <h3>Hero Section</h3>
                            <div class="hero-preview">
                                ${content.hero_headline || '<p class="placeholder">No hero headline set</p>'}
                                ${content.hero_subtext || '<p class="placeholder">No hero subtext set</p>'}
                            </div>
                        </div>
                        <div class="preview-section">
                            <h3>Promo Strip</h3>
                            ${content.promo_text || '<p class="placeholder">No promo text set</p>'}
                        </div>
                    </div>
                `;
                break;

            case 'about':
                html = `
                    <div class="preview-content">
                        <h2>About Us Preview</h2>
                        <div class="preview-section">
                            <h3>Mission</h3>
                            ${content.about_mission || '<p class="placeholder">No mission statement set</p>'}
                        </div>
                        <div class="preview-section">
                            <h3>Vision</h3>
                            ${content.about_vision || '<p class="placeholder">No vision statement set</p>'}
                        </div>
                        <div class="preview-section">
                            <h3>Our Story</h3>
                            ${content.about_narrative || '<p class="placeholder">No story set</p>'}
                        </div>
                        <div class="preview-section">
                            <h3>Contact Information</h3>
                            <p><strong>Address:</strong> ${content.about_address || 'Not set'}</p>
                            <p><strong>Phone:</strong> ${content.about_phone || 'Not set'}</p>
                            <p><strong>Email:</strong> ${content.about_email || 'Not set'}</p>
                            <p><strong>Hours:</strong> ${content.about_hours_weekday || 'Not set'}</p>
                        </div>
                    </div>
                `;
                break;

            case 'privacy':
                html = `
                    <div class="preview-content">
                        <h2>Privacy Policy Preview</h2>
                        ${content.content || '<p class="placeholder">No privacy policy content set</p>'}
                    </div>
                `;
                break;

            case 'terms':
                html = `
                    <div class="preview-content">
                        <h2>Terms & Conditions Preview</h2>
                        ${content.content || '<p class="placeholder">No terms content set</p>'}
                    </div>
                `;
                break;

            case 'global':
                html = `
                    <div class="preview-content">
                        <h2>Navigation & Footer Preview</h2>
                        <div class="preview-section">
                            <h3>Navigation Labels</h3>
                            <ul>
                                <li>Home: ${content.nav_home || 'Home'}</li>
                                <li>About: ${content.nav_about || 'About'}</li>
                                <li>Products: ${content.nav_products || 'Products'}</li>
                            </ul>
                        </div>
                        <div class="preview-section">
                            <h3>Contact Information</h3>
                            <p>Phone: ${content.global_phone || 'Not set'}</p>
                            <p>Email: ${content.global_email || 'Not set'}</p>
                        </div>
                        <div class="preview-section">
                            <h3>Footer</h3>
                            <p>${content.footer_about || 'Not set'}</p>
                            <p>Copyright: ${content.footer_copyright || 'Not set'}</p>
                        </div>
                    </div>
                `;
                break;
        }

        previewCard.innerHTML = html;
    },

    // Open the edit modal
    openEditModal() {
        console.log('Opening edit modal...');
        const modal = document.getElementById('editModal');
        if (modal) {
            modal.classList.add('show');

            // Initialize Quill editors if not already initialized
            setTimeout(() => {
                if (Object.keys(this.editors).length === 0) {
                    this.initQuillEditors();
                } else {
                    // Load current content into editors
                    this.loadEditorContent();
                }
            }, 100);

            // Show the correct editor panel
            this.showEditorPanel(this.currentPage);
        }
    },

    // Close the edit modal
    closeEditModal() {
        const modal = document.getElementById('editModal');
        if (modal) {
            modal.classList.remove('show');
        }
    },

    // Initialize Quill editors
    initQuillEditors() {
        console.log('Initializing Quill editors...');

        // Find all elements with class 'wysiwyg-editor'
        const editorElements = document.querySelectorAll('.wysiwyg-editor');

        editorElements.forEach(element => {
            // Create a div container for Quill
            const quillContainer = document.createElement('div');
            quillContainer.id = element.id + '_quill';
            quillContainer.style.height = '200px';
            quillContainer.style.backgroundColor = '#fff';

            // Hide the original textarea
            element.style.display = 'none';

            // Insert Quill container after the textarea
            element.parentNode.insertBefore(quillContainer, element.nextSibling);

            // Initialize Quill
            const quill = new Quill('#' + quillContainer.id, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        ['link'],
                        ['clean']
                    ]
                },
                placeholder: 'Enter content here...'
            });

            // Store the Quill instance
            this.editors[element.id] = quill;

            // Set initial content if exists
            if (element.value) {
                quill.root.innerHTML = element.value;
            }

            console.log(`Quill editor initialized: ${element.id}`);
        });

        // Load content after all editors are ready
        if (Object.keys(this.editors).length === editorElements.length) {
            this.loadEditorContent();
        }
    },

    // Show the appropriate editor panel
    showEditorPanel(page) {
        console.log(`Showing editor panel for: ${page}`);

        // Hide all editor panels
        document.querySelectorAll('.cm-page-editor').forEach(panel => {
            panel.style.display = 'none';
        });

        // Show the selected panel
        const panel = document.getElementById(`editor-${page}`);
        if (panel) {
            panel.style.display = 'block';
        } else {
            console.error(`Panel not found: editor-${page}`);
        }

        // Update modal title
        const titles = {
            home: 'Edit Homepage',
            about: 'Edit About Us',
            privacy: 'Edit Privacy Policy',
            terms: 'Edit Terms & Conditions',
            global: 'Edit Navbar & Footer'
        };

        const modalTitle = document.getElementById('modalTitle');
        if (modalTitle) {
            modalTitle.textContent = titles[page] || 'Edit Content';
        }
    },

    // Load content into editors
    loadEditorContent() {
        console.log('Loading content into editors for page:', this.currentPage);

        fetch(`${this.apiBaseUrl}?action=get&page=${this.currentPage}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.content) {
                    console.log('Populating editors with:', data.content);

                    // Populate form fields with content
                    for (const [key, value] of Object.entries(data.content)) {
                        const field = document.getElementById(key);
                        if (field) {
                            if (this.editors[key]) {
                                // Quill editor
                                this.editors[key].root.innerHTML = value || '';
                            } else {
                                // Regular input field
                                field.value = value || '';
                            }
                        }
                    }

                    this.showToast('Content loaded', 'info');
                } else {
                    console.warn('No content to load');
                }
            })
            .catch(error => {
                console.error('Error loading editor content:', error);
                this.showToast('Error loading content', 'error');
            });
    },

    // Save as draft
    saveDraft() {
        this.saveContent('draft');
    },

    // Publish content
    publish() {
        if (confirm('Are you sure you want to publish these changes? This will make them visible to all users.')) {
            this.saveContent('published');
        }
    },

    // Save content (draft or published)
    saveContent(status) {
        console.log(`Saving content as ${status}...`);

        // Collect form data
        const formData = new FormData();
        formData.append('action', 'save');
        formData.append('page', this.currentPage);
        formData.append('status', status);

        // Get all editor values
        const panel = document.getElementById(`editor-${this.currentPage}`);
        if (panel) {
            panel.querySelectorAll('input, textarea, select').forEach(field => {
                if (field.id) {
                    let value;
                    if (this.editors[field.id]) {
                        // Get content from Quill editor
                        value = this.editors[field.id].root.innerHTML;
                    } else {
                        value = field.value;
                    }
                    formData.append(field.id, value);
                }
            });
        }

        // Send to API
        fetch(this.apiBaseUrl, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showToast(`Content ${status === 'draft' ? 'saved as draft' : 'published'} successfully!`, 'success');
                    if (status === 'published') {
                        this.loadPageContent(this.currentPage);
                        this.closeEditModal();
                    }
                } else {
                    throw new Error(data.message || 'Save failed');
                }
            })
            .catch(error => {
                console.error('Error saving content:', error);
                this.showToast('Error: ' + error.message, 'error');
            });
    },

    // Reset to published version
    resetToPublished() {
        if (confirm('Are you sure you want to discard all unsaved changes and reset to the published version?')) {
            this.loadEditorContent();
            this.showToast('Content reset to published version', 'info');
        }
    },

    // Show toast notification
    showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) {
            console.warn('Toast container not found');
            return;
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },

    // Add banner (placeholder)
    addBanner(sectionId) {
        console.log(`Add banner to ${sectionId}`);
        this.showToast('Banner upload functionality coming soon', 'info');
    },

    // Toggle version history drawer
    toggleVersionDrawer() {
        const drawer = document.getElementById('versionDrawer');
        if (drawer) {
            drawer.classList.toggle('show');

            if (drawer.classList.contains('show')) {
                this.loadVersionHistory();
            }
        }
    },

    // Load version history
    loadVersionHistory() {
        fetch(`${this.apiBaseUrl}?action=get_versions&page=${this.currentPage}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const versionList = document.getElementById('versionList');
                    if (versionList && data.versions) {
                        versionList.innerHTML = data.versions.map(v => `
                            <div class="version-item">
                                <div class="version-info">
                                    <strong>Version ${v.version}</strong>
                                    <span class="version-status ${v.status}">${v.status}</span>
                                </div>
                                <div class="version-meta">
                                    <small>By ${v.updated_by}</small>
                                    <small>${v.updated_at}</small>
                                </div>
                            </div>
                        `).join('');
                    }
                }
            })
            .catch(error => {
                console.error('Error loading version history:', error);
            });
    }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        // Small delay to ensure all resources are loaded
        setTimeout(() => CM.init(), 100);
    });
} else {
    setTimeout(() => CM.init(), 100);
}