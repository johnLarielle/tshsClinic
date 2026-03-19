// API endpoint - Admin version
const API_URL = '../../routes/medicine_api.php';

// DOM Elements
const form = document.getElementById('medicineForm');
const tableBody = document.getElementById('medicineTableBody');
const messageDiv = document.getElementById('message');
const formTitle = document.getElementById('formTitle');
const submitBtn = document.getElementById('submitBtn');
const cancelBtn = document.getElementById('cancelBtn');
const editIdInput = document.getElementById('editId');
const stockModal = document.getElementById('stockModal');
const stockForm = document.getElementById('stockForm');

// Load medicines on page load
document.addEventListener('DOMContentLoaded', () => {
    loadMedicines();
});

// Show message
function showMessage(message, type) {
    messageDiv.textContent = message;
    messageDiv.className = `message ${type}`;
    messageDiv.style.display = 'block';
    
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 5000);
}

// Load all medicines
async function loadMedicines() {
    try {
        const response = await fetch(`${API_URL}?action=read`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('API Response:', result);

        if (result.success && result.data && result.data.length > 0) {
            displayMedicines(result.data);
        } else if (result.success && (!result.data || result.data.length === 0)) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="empty-state">
                        <div>No medicines found. Add your first medicine above!</div>
                    </td>
                </tr>
            `;
        } else {
            console.error('API Error:', result.error);
            showMessage(result.error || 'Failed to load medicines', 'error');
        }
    } catch (error) {
        console.error('Error loading medicines:', error);
        showMessage('Failed to load medicines: ' + error.message, 'error');
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="empty-state">
                    <div>Error loading medicines. Check console for details.</div>
                </td>
            </tr>
        `;
    }
}

// Display medicines in table
function displayMedicines(medicines) {
    console.log('Displaying medicines:', medicines);
    tableBody.innerHTML = '';
    
    try {
        medicines.forEach((medicine) => {
            // Determine stock badge
            let stockBadge = '';
            const stock = parseInt(medicine.current_stock);
            if (stock === 0) {
                stockBadge = `<span class="stock-badge stock-low">Out of Stock</span>`;
            } else if (stock < 10) {
                stockBadge = `<span class="stock-badge stock-low">${stock} (Low)</span>`;
            } else if (stock < 50) {
                stockBadge = `<span class="stock-badge stock-medium">${stock} (Medium)</span>`;
            } else {
                stockBadge = `<span class="stock-badge stock-good">${stock} (Good)</span>`;
            }
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${medicine.medicine_id || 'N/A'}</td>
                <td><strong>${medicine.medicine_name || 'N/A'}</strong></td>
                <td>${medicine.description || 'No description'}</td>
                <td>${stockBadge}</td>
                <td>${formatDate(medicine.dateCreated)}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-stock" onclick="openStockModal(${medicine.medicine_id}, '${medicine.medicine_name}', ${medicine.current_stock})">Stock</button>
                        <button class="btn-edit" onclick="editMedicine(${medicine.medicine_id})">Edit</button>
                        <button class="btn-delete" onclick="deleteMedicine(${medicine.medicine_id})">Delete</button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
        });
    } catch (error) {
        console.error('Error displaying medicines:', error);
        showMessage('Error displaying medicines: ' + error.message, 'error');
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
            loadMedicines();
        } else {
            showMessage(result.error || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
});

// Edit medicine
async function editMedicine(id) {
    try {
        const response = await fetch(`${API_URL}?action=read_one&id=${id}`);
        const result = await response.json();

        if (result.success) {
            const medicine = result.data;
            
            // Populate form
            editIdInput.value = medicine.medicine_id;
            document.getElementById('medicine_name').value = medicine.medicine_name;
            document.getElementById('description').value = medicine.description || '';
            document.getElementById('current_stock').value = medicine.current_stock;

            // Update UI
            formTitle.textContent = 'Edit Medicine';
            submitBtn.textContent = 'Update Medicine';
            cancelBtn.style.display = 'inline-block';

            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth' });
        } else {
            showMessage('Failed to load medicine', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Failed to load medicine', 'error');
    }
}

// Delete medicine
async function deleteMedicine(id) {
    if (!confirm('Are you sure you want to delete this medicine?')) {
        return;
    }

    try {
        const response = await fetch(`${API_URL}?action=delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ medicine_id: id })
        });

        const result = await response.json();

        if (result.success) {
            showMessage(result.message, 'success');
            loadMedicines();
        } else {
            showMessage(result.error || 'Delete failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Failed to delete medicine', 'error');
    }
}

// Open stock modal
function openStockModal(medicineId, medicineName, currentStock) {
    document.getElementById('stock_medicine_id').value = medicineId;
    document.getElementById('stock_medicine_name').textContent = medicineName;
    document.getElementById('stock_current').textContent = currentStock;
    document.getElementById('stock_quantity').value = '';
    stockModal.style.display = 'block';
}

// Close stock modal
function closeStockModal() {
    stockModal.style.display = 'none';
    stockForm.reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target === stockModal) {
        closeStockModal();
    }
}

// Close button
document.querySelector('.close').onclick = closeStockModal;

// Handle stock form submission
stockForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const data = {
        medicine_id: document.getElementById('stock_medicine_id').value,
        action: document.getElementById('stock_action').value,
        quantity: document.getElementById('stock_quantity').value
    };

    try {
        const response = await fetch(`${API_URL}?action=update_stock`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showMessage(result.message, 'success');
            closeStockModal();
            loadMedicines();
        } else {
            showMessage(result.error || 'Stock update failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Failed to update stock', 'error');
    }
});

// Cancel edit
cancelBtn.addEventListener('click', resetForm);

function resetForm() {
    editIdInput.value = '';
    formTitle.textContent = 'Add New Medicine';
    submitBtn.textContent = 'Add Medicine';
    cancelBtn.style.display = 'none';
}
