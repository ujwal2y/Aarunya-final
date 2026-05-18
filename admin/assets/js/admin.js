/**
 * Aarunya Admin Panel - Consolidated JavaScript
 * Common functions and dashboard enhancements
 */

// Header Button Functionality
document.addEventListener('DOMContentLoaded', function() {
    const headerIcons = document.querySelectorAll('.header-icon');
    
    // Search button (first icon)
    if (headerIcons[0]) {
        headerIcons[0].style.cursor = 'pointer';
        headerIcons[0].setAttribute('title', 'Search');
        headerIcons[0].addEventListener('click', function() {
            showSearchModal();
        });
    }
    
    // Notification button (second icon)
    if (headerIcons[1]) {
        headerIcons[1].style.cursor = 'pointer';
        headerIcons[1].setAttribute('title', 'View Emergency Alerts');
        headerIcons[1].addEventListener('click', function() {
            window.location.href = 'emergency.php';
        });
    }
});

// Search Modal Function
function showSearchModal() {
    const searchQuery = prompt('🔍 Search for users, appointments, or doctors:');
    if (searchQuery && searchQuery.trim() !== '') {
        // Redirect to users page with search query
        window.location.href = 'users.php?search=' + encodeURIComponent(searchQuery.trim());
    }
}

// Notification Toast
function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `notification-toast ${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'rgba(34, 197, 94, 0.2)' : type === 'error' ? 'rgba(239, 68, 68, 0.2)' : 'rgba(59, 130, 246, 0.2)'};
        border: 1px solid ${type === 'success' ? 'rgba(34, 197, 94, 0.3)' : type === 'error' ? 'rgba(239, 68, 68, 0.3)' : 'rgba(59, 130, 246, 0.3)'};
        color: ${type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : '#3b82f6'};
        padding: 16px 24px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        backdrop-filter: blur(10px);
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
