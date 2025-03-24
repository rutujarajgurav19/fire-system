// JavaScript functionality for maintenance management
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId + '-tab').classList.add('active');
        });
    });
    
    // Log maintenance button functionality
    const logButtons = document.querySelectorAll('.log-btn');
    
    logButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Set the device in the form
            const deviceId = button.getAttribute('data-id');
            const deviceSelect = document.getElementById('device_id');
            deviceSelect.value = deviceId;
            
            // Switch to log tab
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            document.querySelector('[data-tab="log"]').classList.add('active');
            document.getElementById('log-tab').classList.add('active');
            
            // Scroll to form
            document.getElementById('log-tab').scrollIntoView({behavior: 'smooth'});
        });
    });
});
