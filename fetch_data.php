document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.querySelector('.filter-form');
    const resultsContainer = document.querySelector('.crime-table-wrapper');
    const applyButton = document.querySelector('.primary-btn');

   
    if (filterForm && resultsContainer) {
        
        
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault(); 
            const originalBtnText = applyButton.innerHTML;
            applyButton.textContent = 'Filtering...';
            applyButton.disabled = true;
            
            resultsContainer.style.opacity = '0.5';

            const formData = new FormData(filterForm);

            fetch('fetch_data.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                resultsContainer.innerHTML = html;
                resultsContainer.style.opacity = '1';
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                resultsContainer.innerHTML = '<p style="color: red;">An error occurred while filtering. Please try again.</p>';
                resultsContainer.style.opacity = '1';
            })
            .finally(() => {
                applyButton.innerHTML = originalBtnText;
                applyButton.disabled = false;
            });
        });
    }
});