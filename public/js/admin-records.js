// API endpoints - Admin version with full CRUD
const API_URL = '../../routes/api.php';
const MEDICINE_API_URL = '../../routes/medicine_api.php';

// DOM Elements
const form = document.getElementById('patientForm');
const tableBody = document.getElementById('patientTableBody');
const messageDiv = document.getElementById('message');
const formTitle = document.getElementById('formTitle');
const submitBtn = document.getElementById('submitBtn');
const cancelBtn = document.getElementById('cancelBtn');
const editIdInput = document.getElementById('editId');
const medicineSelect = document.getElementById('medicine');

// Load patients and medicines on page load
document.addEventListener('DOMContentLoaded', () => {
    loadPatients();
    loadMedicines();
    // Set today's date as default
    document.getElementById('date').valueAsDate = new Date();
});

// Load medicines for dropdown
async function loadMedicines() {
    try {
        const response = await fetch(`${MEDICINE_API_URL}?action=read`);
        const result = await response.json();

        if (result.success && result.data && result.data.length > 0) {
            // Clear existing options except the first one
            medicineSelect.innerHTML = '<option value="">Select Medicine</option>';
            
            // Add medicines to dropdown
            result.data.forEach(medicine => {
                const option = document.createElement('option');
                option.value = medicine.medicine_name;
                option.textContent = medicine.medicine_name;
                option.dataset.stock = medicine.current_stock;
                
                // Disable if out of stock
                if (parseInt(medicine.current_stock) === 0) {
                    option.disabled = true;
                    option.textContent += ' (Out of stock)';
                    option.style.color = '#999';
                }
                // Red text if low stock (less than 10)
                else if (parseInt(medicine.current_stock) < 10) {
                    option.style.color = '#dc2626';
                    option.style.fontWeight = 'bold';
                }
                
                medicineSelect.appendChild(option);
            });
        } else {
            medicineSelect.innerHTML = '<option value="">No medicines available</option>';
        }
    } catch (error) {
        console.error('Error loading medicines:', error);
        medicineSelect.innerHTML = '<option value="">Error loading medicines</option>';
    }
}

// Show message
function showMessage(message, type) {
    messageDiv.textContent = message;
    messageDiv.className = `message ${type}`;
    messageDiv.style.display = 'block';
    
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 5000);
}

// Load all patients
async function loadPatients() {
    try {
        const response = await fetch(`${API_URL}?action=read`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('API Response:', result);

        if (result.success && result.data && result.data.length > 0) {
            displayPatients(result.data);
        } else if (result.success && (!result.data || result.data.length === 0)) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="empty-state">
                        <div>No records found. Add your first patient record above!</div>
                    </td>
                </tr>
            `;
        } else {
            console.error('API Error:', result.error);
            showMessage(result.error || 'Failed to load records', 'error');
        }
    } catch (error) {
        console.error('Error loading patients:', error);
        showMessage('Failed to load patient records: ' + error.message, 'error');
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="empty-state">
                    <div>Error loading records. Check console for details.</div>
                </td>
            </tr>
        `;
    }
}

// Display patients in table - ADMIN VERSION with edit/delete buttons
function displayPatients(patients) {
    console.log('Displaying patients:', patients);
    tableBody.innerHTML = '';
    
    try {
        patients.forEach((patient) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${patient.record_id || 'N/A'}</td>
                <td>${patient.patient_name || 'N/A'}</td>
                <td>${patient.patient_type || 'N/A'}</td>
                <td>${patient.contact_no || 'N/A'}</td>
                <td>${patient.medicine_name || 'N/A'}</td>
                <td>${patient.quantity || 0}</td>
                <td>${patient.reason || 'N/A'}</td>
                <td>${formatDate(patient.date_given)}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-edit" onclick="editPatient(${patient.record_id})">Edit</button>
                        <button class="btn-delete" onclick="deletePatient(${patient.record_id})">Delete</button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
        });
    } catch (error) {
        console.error('Error displaying patients:', error);
        showMessage('Error displaying records: ' + error.message, 'error');
    }
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Invalid Date';
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: '2-digit', 
            day: '2-digit' 
        });
    } catch (e) {
        return 'N/A';
    }
}

// Handle form submission
form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    const isEdit = editIdInput.value !== '';
    const action = isEdit ? 'update' : 'create';

    try {
        const response = await fetch(`${API_URL}?action=${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showMessage(result.message, 'success');
            form.reset();
            resetForm();
            loadPatients();
            document.getElementById('date').valueAsDate = new Date();
        } else {
            showMessage(result.error || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
});

// Edit patient - ADMIN ONLY
async function editPatient(id) {
    try {
        const response = await fetch(`${API_URL}?action=read_one&id=${id}`);
        const result = await response.json();

        if (result.success) {
            const patient = result.data;
            
            // Populate form
            editIdInput.value = patient.record_id;
            document.getElementById('patient_id').value = patient.patient_id;
            document.getElementById('name').value = patient.patient_name;
            document.getElementById('patient_type').value = patient.patient_type;
            document.getElementById('contact_no').value = patient.contact_no;
            
            // Select the medicine in dropdown
            const medicineSelect = document.getElementById('medicine');
            const medicineOption = Array.from(medicineSelect.options).find(
                option => option.value === patient.medicine_name
            );
            if (medicineOption) {
                medicineSelect.value = patient.medicine_name;
            }
            
            document.getElementById('quantity').value = patient.quantity;
            document.getElementById('reason').value = patient.reason;
            document.getElementById('date').value = patient.date_given.split(' ')[0];

            // Update UI
            formTitle.textContent = 'Edit Patient Record';
            submitBtn.textContent = 'Update Record';
            cancelBtn.style.display = 'inline-block';

            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth' });
        } else {
            showMessage('Failed to load patient record', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Failed to load patient record', 'error');
    }
}

// Delete patient - ADMIN ONLY
async function deletePatient(id) {
    if (!confirm('Are you sure you want to delete this patient record?')) {
        return;
    }

    try {
        const response = await fetch(`${API_URL}?action=delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const result = await response.json();

        if (result.success) {
            showMessage(result.message, 'success');
            loadPatients();
        } else {
            showMessage(result.error || 'Delete failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Failed to delete patient record', 'error');
    }
}

// Cancel edit
cancelBtn.addEventListener('click', resetForm);

function resetForm() {
    editIdInput.value = '';
    document.getElementById('patient_id').value = '';
    formTitle.textContent = 'Add New Patient Record';
    submitBtn.textContent = 'Add Record';
    cancelBtn.style.display = 'none';
}
