// Bootstrap JS
import * as bootstrap from 'bootstrap';

// Hacer Bootstrap disponible globalmente
window.bootstrap = bootstrap;

// Sidebar toggle functionality
function initSidebar() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const contentWrapper = document.getElementById('contentWrapper');

    if (sidebarToggle && sidebar && contentWrapper) {
        // Cargar estado del sidebar desde localStorage
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            sidebar.classList.add('collapsed');
            contentWrapper.classList.add('sidebar-collapsed');
        }

        // Remover listener previo para evitar duplicados
        sidebarToggle.replaceWith(sidebarToggle.cloneNode(true));
        const newToggle = document.getElementById('sidebarToggle');

        newToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            contentWrapper.classList.toggle('sidebar-collapsed');

            // Guardar estado en localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }
}

// Inicializar en carga inicial
document.addEventListener('DOMContentLoaded', initSidebar);

// Re-inicializar después de navegación Livewire (wire:navigate)
document.addEventListener('livewire:navigated', initSidebar);
