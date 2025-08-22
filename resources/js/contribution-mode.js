/**
 * Contribution Mode - Handles the interactive contribution system
 */
class ContributionMode {
    constructor() {
        this.isActive = false;
        this.contributableElements = [];
        this.currentContribution = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.identifyContributableElements();
    }

    bindEvents() {
        // Toggle contribution mode
        document.addEventListener('click', (e) => {
            if (e.target.matches('#contribution-toggle')) {
                e.preventDefault();
                this.toggle();
            }
        });

        // Handle ESC key to exit contribution mode
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.isActive) {
                    this.deactivate();
                } else if (!document.getElementById('contribution-modal').classList.contains('hidden')) {
                    this.closeModal();
                }
            }
        });

        // Handle contribution element clicks
        document.addEventListener('click', (e) => {
            if (this.isActive && e.target.matches('[data-contributable]')) {
                e.preventDefault();
                e.stopPropagation();
                this.openContributionModal(e.target);
            }
        });
    }

    identifyContributableElements() {
        // Find all elements that can accept contributions
        this.contributableElements = document.querySelectorAll('[data-contributable]');
    }

    toggle() {
        if (this.isActive) {
            this.deactivate();
        } else {
            this.activate();
        }
    }

    activate() {
        this.isActive = true;
        
        // Update toggle button
        const toggle = document.getElementById('contribution-toggle');
        toggle.textContent = 'Exit Contribution Mode';
        toggle.classList.remove('bg-blue-500', 'hover:bg-blue-600');
        toggle.classList.add('bg-red-500', 'hover:bg-red-600');

        // Show contribution instructions
        this.showInstructions();

        // Highlight contributable elements
        this.contributableElements.forEach(element => {
            element.classList.add('contributable-active');
        });

        // Add body class for styling
        document.body.classList.add('contribution-mode-active');
    }

    deactivate() {
        this.isActive = false;

        // Update toggle button
        const toggle = document.getElementById('contribution-toggle');
        toggle.textContent = 'Contribute';
        toggle.classList.remove('bg-red-500', 'hover:bg-red-600');
        toggle.classList.add('bg-blue-500', 'hover:bg-blue-600');

        // Hide instructions
        this.hideInstructions();

        // Remove highlights
        this.contributableElements.forEach(element => {
            element.classList.remove('contributable-active');
        });

        // Remove body class
        document.body.classList.remove('contribution-mode-active');
    }

    showInstructions() {
        const instruction = document.createElement('div');
        instruction.id = 'contribution-instructions';
        instruction.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        instruction.innerHTML = `
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Click on highlighted elements to contribute suggestions or improvements
                <button onclick="ContributionMode.deactivate()" class="ml-3 text-blue-200 hover:text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;
        document.body.appendChild(instruction);
    }

    hideInstructions() {
        const instruction = document.getElementById('contribution-instructions');
        if (instruction) {
            instruction.remove();
        }
    }

    async openContributionModal(element) {
        const objectType = element.dataset.contributable;
        const objectId = element.dataset.objectId;

        if (!objectType || !objectId) {
            console.error('Missing contribution data attributes');
            return;
        }

        try {
            // Fetch contribution options
            const response = await fetch(`/contribute/options?object_type=${objectType}&object_id=${objectId}`);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to load contribution options');
            }

            this.currentContribution = data;
            this.populateModal(data);
            this.showModal();

        } catch (error) {
            console.error('Error loading contribution options:', error);
            this.showError('Failed to load contribution options. Please try again.');
        }
    }

    populateModal(data) {
        // Set context information
        document.getElementById('contribution-context').textContent = 
            `${data.object_details.title}\n${data.object_details.subtitle}`;

        // Populate field type options
        const typeSelect = document.getElementById('contribution-type');
        typeSelect.innerHTML = '<option value="">Select contribution type...</option>';
        
        Object.entries(data.field_options).forEach(([value, label]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            typeSelect.appendChild(option);
        });

        // Set hidden fields
        document.getElementById('lesson-id').value = data.object_details.lesson_id || '';
        document.getElementById('object-type').value = data.object_type;
        document.getElementById('object-id').value = data.object_id;

        // Clear form
        document.getElementById('contribution-text').value = '';
        document.getElementById('contribution-error').classList.add('hidden');
    }

    showModal() {
        document.getElementById('contribution-modal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        
        // Focus on the contribution type select
        setTimeout(() => {
            document.getElementById('contribution-type').focus();
        }, 100);
    }

    closeModal() {
        document.getElementById('contribution-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        this.currentContribution = null;
    }

    async submitContribution() {
        const form = document.getElementById('contribution-form');
        const formData = new FormData(form);
        
        // Basic validation
        if (!formData.get('contribution_text').trim()) {
            this.showError('Please enter your contribution text.');
            return;
        }

        // Show loading state
        this.setSubmitLoading(true);

        try {
            const response = await fetch('/contribute/store', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to submit contribution');
            }

            // Success!
            this.closeModal();
            this.showSuccess(data.message);
            this.deactivate();

        } catch (error) {
            console.error('Error submitting contribution:', error);
            this.showError(error.message || 'Failed to submit contribution. Please try again.');
        } finally {
            this.setSubmitLoading(false);
        }
    }

    setSubmitLoading(loading) {
        const submitBtn = document.querySelector('button[onclick="ContributionMode.submitContribution()"]');
        const submitText = document.getElementById('submit-text');
        const submitSpinner = document.getElementById('submit-spinner');

        if (loading) {
            submitBtn.disabled = true;
            submitText.textContent = 'Submitting...';
            submitSpinner.classList.remove('hidden');
        } else {
            submitBtn.disabled = false;
            submitText.textContent = 'Submit Contribution';
            submitSpinner.classList.add('hidden');
        }
    }

    showError(message) {
        const errorDiv = document.getElementById('contribution-error');
        errorDiv.textContent = message;
        errorDiv.classList.remove('hidden');
    }

    showSuccess(message) {
        const successDiv = document.getElementById('contribution-success');
        successDiv.classList.remove('hidden');
        
        setTimeout(() => {
            successDiv.classList.add('hidden');
        }, 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.ContributionMode = new ContributionMode();
});

// Export for use in onclick handlers
window.ContributionMode = window.ContributionMode || {};
