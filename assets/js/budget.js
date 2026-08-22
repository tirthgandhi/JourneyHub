// Budget Management JavaScript
let currentBudgetData = {};

// DOM Elements
const budgetValue = document.getElementById('budget-value');
const editBudgetBtn = document.getElementById('edit-budget-btn');
const budgetEdit = document.getElementById('budget-edit');
const budgetInput = document.getElementById('budget-input');
const saveBudgetBtn = document.getElementById('save-budget-btn');
const cancelBudgetBtn = document.getElementById('cancel-budget-btn');

const totalSpent = document.getElementById('total-spent');
const remaining = document.getElementById('remaining');

const addExpenseBtn = document.getElementById('add-expense-btn');
const expenseForm = document.getElementById('expense-form');
const saveExpenseBtn = document.getElementById('save-expense-btn');
const cancelExpenseBtn = document.getElementById('cancel-expense-btn');

const expensesList = document.getElementById('expenses-list');

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadBudgetData();
    setupEventListeners();
});

function setupEventListeners() {
    editBudgetBtn.addEventListener('click', showBudgetEdit);
    saveBudgetBtn.addEventListener('click', saveBudget);
    cancelBudgetBtn.addEventListener('click', hideBudgetEdit);
    
    addExpenseBtn.addEventListener('click', showExpenseForm);
    saveExpenseBtn.addEventListener('click', saveExpense);
    cancelExpenseBtn.addEventListener('click', hideExpenseForm);
}

function loadBudgetData() {
    fetch(`../api/expenses/list.php?trip_id=${TRIP_ID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentBudgetData = data;
                renderBudgetData();
            } else {
                showError(data.error || 'Failed to load budget data');
            }
        })
        .catch(error => {
            console.error('Load budget error:', error);
            showError('Failed to load budget data');
        });
}

function renderBudgetData() {
    // Update budget display
    budgetValue.textContent = formatAmount(currentBudgetData.budget);
    totalSpent.textContent = '₹' + formatAmount(currentBudgetData.total_spent);
    
    // Update remaining amount with over-budget styling
    const remainingEl = document.getElementById('remaining');
    remainingEl.textContent = '₹' + formatAmount(currentBudgetData.remaining);
    
    if (currentBudgetData.is_over_budget) {
        remainingEl.classList.add('over-budget');
    } else {
        remainingEl.classList.remove('over-budget');
    }
    
    // Update category breakdown
    Object.entries(currentBudgetData.by_category).forEach(([category, amount]) => {
        const element = document.getElementById(`cat-${category}`);
        if (element) {
            element.textContent = '₹' + formatAmount(amount);
        }
    });
    
    // Render expenses list
    renderExpensesList();
}

function renderExpensesList() {
    if (!currentBudgetData.expenses || currentBudgetData.expenses.length === 0) {
        expensesList.innerHTML = '<p class="no-expenses">No expenses added yet</p>';
        return;
    }
    
    expensesList.innerHTML = currentBudgetData.expenses.map(expense => `
        <div class="expense-item" data-expense-id="${expense.id}">
            <div class="expense-details">
                <h4>${capitalizeFirst(expense.category)}</h4>
                <div class="expense-meta">
                    ${expense.description || 'No description'} • ${formatDate(expense.expense_date)}
                </div>
            </div>
            <div class="expense-right">
                <span class="expense-amount">₹${formatAmount(expense.amount)}</span>
                <div class="expense-actions">
                    <button class="btn btn-sm btn-secondary" onclick="editExpense(${expense.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteExpense(${expense.id})">Delete</button>
                </div>
            </div>
        </div>
    `).join('');
}

function showBudgetEdit() {
    budgetInput.value = currentBudgetData.budget || 0;
    budgetEdit.style.display = 'flex';
    editBudgetBtn.style.display = 'none';
    budgetInput.focus();
}

function hideBudgetEdit() {
    budgetEdit.style.display = 'none';
    editBudgetBtn.style.display = 'inline-block';
}

function saveBudget() {
    const budget = parseFloat(budgetInput.value);
    
    if (isNaN(budget) || budget < 0) {
        showError('Budget must be a valid number >= 0');
        return;
    }
    
    const formData = new FormData();
    formData.append('trip_id', TRIP_ID);
    formData.append('budget', budget);
    
    fetch('../api/expenses/set-budget.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentBudgetData.budget = parseFloat(data.budget);
            currentBudgetData.remaining = currentBudgetData.budget - currentBudgetData.total_spent;
            currentBudgetData.is_over_budget = currentBudgetData.remaining < 0;
            renderBudgetData();
            hideBudgetEdit();
        } else {
            showError(data.error || 'Failed to save budget');
        }
    })
    .catch(error => {
        console.error('Save budget error:', error);
        showError('Failed to save budget');
    });
}

function showExpenseForm() {
    expenseForm.style.display = 'block';
    addExpenseBtn.style.display = 'none';
    document.getElementById('expense-date').value = new Date().toISOString().split('T')[0];
}

function hideExpenseForm() {
    expenseForm.style.display = 'none';
    addExpenseBtn.style.display = 'inline-block';
    clearExpenseForm();
}

function clearExpenseForm() {
    document.getElementById('expense-category').value = '';
    document.getElementById('expense-amount').value = '';
    document.getElementById('expense-description').value = '';
    document.getElementById('expense-date').value = '';
}

function saveExpense() {
    const category = document.getElementById('expense-category').value;
    const amount = document.getElementById('expense-amount').value;
    const description = document.getElementById('expense-description').value;
    const expense_date = document.getElementById('expense-date').value;
    
    if (!category || !amount || !expense_date) {
        showError('Category, amount, and date are required');
        return;
    }
    
    const formData = new FormData();
    formData.append('trip_id', TRIP_ID);
    formData.append('category', category);
    formData.append('amount', amount);
    formData.append('description', description);
    formData.append('expense_date', expense_date);
    
    fetch('../api/expenses/add.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideExpenseForm();
            loadBudgetData(); // Reload to get updated totals
        } else {
            showError(data.error || 'Failed to add expense');
        }
    })
    .catch(error => {
        console.error('Add expense error:', error);
        showError('Failed to add expense');
    });
}

function deleteExpense(expenseId) {
    if (!confirm('Delete this expense?')) return;
    
    const formData = new FormData();
    formData.append('expense_id', expenseId);
    
    fetch('../api/expenses/delete.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadBudgetData(); // Reload to get updated totals
        } else {
            showError(data.error || 'Failed to delete expense');
        }
    })
    .catch(error => {
        console.error('Delete expense error:', error);
        showError('Failed to delete expense');
    });
}

function editExpense(expenseId) {
    // Simple implementation - just show alert for now
    // In a full implementation, would show inline edit form
    alert('Edit functionality - to be implemented');
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-msg';
    errorDiv.textContent = message;
    
    document.querySelector('.budget-main').insertBefore(errorDiv, document.querySelector('.budget-layout'));
    
    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 4000);
}

function formatAmount(amount) {
    return parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}