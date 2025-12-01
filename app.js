document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.querySelector('.filter-form');
    const resultsContainer = document.querySelector('.crime-table-wrapper');
    const applyButton = document.querySelector('.primary-btn');
    const chartSelector = document.getElementById('chartTypeSelector');
    
    // Chart Variables
    let crimeChart = null;
    const ctx = document.getElementById('crimeChart');
    
    // Store data globally so we can switch chart types without re-fetching
    let currentChartData = { labels: [], data: [], labelText: '# of Incidents' };

    // Draw Chart
    const drawChart = (type) => {
        if (!ctx) return;
        
        console.log("Switching chart to:", type); // Debugging Log

        // If a chart already exists, destroy it before creating a new one
        if (crimeChart) {
            crimeChart.destroy();
        }

        let chartOptions = {
            responsive: true,
            maintainAspectRatio: false
        };

        if (type === 'bar' || type === 'line') {
            chartOptions.scales = {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            };
        }

        crimeChart = new Chart(ctx, {
            type: type, 
            data: {
                labels: currentChartData.labels, 
                datasets: [{
                    label: currentChartData.labelText, 
                    data: currentChartData.data, 
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)', // Blue
                        'rgba(255, 99, 132, 0.6)', // Red
                        'rgba(255, 206, 86, 0.6)', // Yellow
                        'rgba(75, 192, 192, 0.6)', // Green
                        'rgba(153, 102, 255, 0.6)', // Purple
                        'rgba(255, 159, 64, 0.6)'  // Orange
                    ],
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: chartOptions
        });
    };

    // Update Dashboard
    const updateDashboard = (formData) => {
        const originalBtnText = applyButton.innerHTML;
        applyButton.textContent = 'Updating...';
        applyButton.disabled = true;
        resultsContainer.style.opacity = '0.5';

        // Fetch Table Data
        fetch('fetch_data.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            resultsContainer.innerHTML = html;
            resultsContainer.style.opacity = '1';
        })
        .catch(err => {
            console.error('Table error:', err);
            resultsContainer.innerHTML = "<p style='color:red'>Error loading table.</p>";
        });

        // Fetch Graph Data
        if (ctx) {
            fetch('fetch_graph_data.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(json => {
                // Save data for later 
                currentChartData.labels = json.labels;
                currentChartData.data = json.data;
                
                // Determine Chart Mode
                const selectedCrime = formData.get('crime_type');
                
                if (selectedCrime) {
                    currentChartData.labelText = "Daily Incidents: " + selectedCrime;
                    if (chartSelector && chartSelector.value !== 'line') {
                        chartSelector.value = 'line';
                    }
                } else {
            
                    currentChartData.labelText = "# of Incidents by Type";
                    if (chartSelector && chartSelector.value === 'line') {
                        chartSelector.value = 'bar';
                    }
                }
                
                // Draw chart using currently selected chart type
                const type = chartSelector ? chartSelector.value : 'bar';
                drawChart(type);
            })
            .catch(err => console.error('Graph error:', err));
        }

        setTimeout(() => {
            applyButton.innerHTML = originalBtnText;
            applyButton.disabled = false;
        }, 300);
    };

    // Filter Handling
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault(); 
            const formData = new FormData(filterForm);
            updateDashboard(formData);
        });

        filterForm.dispatchEvent(new Event('submit'));
    }


    if (chartSelector) {
        chartSelector.addEventListener('change', function() {
            drawChart(this.value);
        });
    }
});