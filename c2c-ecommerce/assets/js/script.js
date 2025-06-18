document.addEventListener('DOMContentLoaded', function() {
   
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
   
    document.querySelectorAll('.image-upload').forEach(function(input) {
        input.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                var preview = this.parentElement.querySelector('.image-preview');
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
   
    document.querySelectorAll('.confirm-before-delete').forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });
   
    document.querySelectorAll('.price-input').forEach(function(input) {
        input.addEventListener('blur', function() {
            var value = parseFloat(this.value);
            if (!isNaN(value)) {
                this.value = value.toFixed(2);
            }
        });
    });
});
