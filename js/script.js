

// Simple interaction for buttons (shimmer effect)
document.querySelectorAll('.btn-action').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
        this.style.boxShadow = '0 0 25px rgba(198, 162, 96, 0.5), inset 0 0 15px rgba(198, 162, 96, 0.4)';
    });
    
    btn.addEventListener('mouseleave', function() {
        this.style.boxShadow = '0 0 15px rgba(0,0,0,0.8), inset 0 0 10px rgba(198, 162, 96, 0.2)';
    });
});


