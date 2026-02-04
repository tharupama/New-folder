// Inject modals into the page dynamically
function injectModals() {
  // Check if modals already exist to avoid duplicates
  if (document.getElementById('cartDrawer')) {
    return; // Modals already injected
  }

  // Create a temporary container for fetching
  const modalsContainer = document.createElement('div');
  
  // Load modals.html content
  fetch('modals.html')
    .then(response => response.text())
    .then(html => {
      // Find the body or main app container and insert modals
      const app = document.querySelector('.app');
      if (app) {
        // Insert modals after the app div
        app.insertAdjacentHTML('afterend', html);
      } else {
        // Fallback: insert before body close
        document.body.insertAdjacentHTML('beforeend', html);
      }
    })
    .catch(error => console.log('Note: Modals injected differently or already present'));
}

// Inject modals when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', injectModals);
} else {
  injectModals();
}
