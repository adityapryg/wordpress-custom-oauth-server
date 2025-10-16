jQuery(document).ready(function($) {
    // Admin functionality
    $('.oauth-toggle-secret').click(function() {
        const button = $(this);
        const hidden = $('.oauth-secret-hidden');
        const visible = $('.oauth-secret-visible');
        
        if (hidden.is(':visible')) {
            hidden.hide();
            visible.show();
            button.text('Hide');
        } else {
            hidden.show();
            visible.hide();
            button.text('Show');
        }
    });
    
    // OAuth authorization page functionality
    const form = document.querySelector('.oauth-authorize-form');
    const buttons = document.querySelectorAll('.oauth-btn');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const clickedButton = e.submitter;
            if (clickedButton) {
                // Add loading state
                clickedButton.classList.add('loading');
                clickedButton.disabled = true;
                
                // Disable other buttons
                buttons.forEach(btn => {
                    if (btn !== clickedButton) {
                        btn.disabled = true;
                        btn.style.opacity = '0.5';
                    }
                });
            }
        });
    }
});
