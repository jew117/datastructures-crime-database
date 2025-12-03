

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

    // Draw Chart with improved styling
    const drawChart = (type) => {
        if (!ctx) return;
        
        console.log("Switching chart to:", type);

        // If a chart already exists, destroy it before creating a new one
        if (crimeChart) {
            crimeChart.destroy();
        }

        // Enhanced color palette - 40 vibrant colors for all crime types
        const colorPalette = [
            { bg: 'rgba(59, 130, 246, 0.85)', border: 'rgba(59, 130, 246, 1)' },      // 1. Blue
            { bg: 'rgba(239, 68, 68, 0.85)', border: 'rgba(239, 68, 68, 1)' },        // 2. Red
            { bg: 'rgba(34, 197, 94, 0.85)', border: 'rgba(34, 197, 94, 1)' },        // 3. Green
            { bg: 'rgba(251, 146, 60, 0.85)', border: 'rgba(251, 146, 60, 1)' },      // 4. Orange
            { bg: 'rgba(168, 85, 247, 0.85)', border: 'rgba(168, 85, 247, 1)' },      // 5. Purple
            { bg: 'rgba(236, 72, 153, 0.85)', border: 'rgba(236, 72, 153, 1)' },      // 6. Pink
            { bg: 'rgba(14, 165, 233, 0.85)', border: 'rgba(14, 165, 233, 1)' },      // 7. Sky Blue
            { bg: 'rgba(245, 158, 11, 0.85)', border: 'rgba(245, 158, 11, 1)' },      // 8. Amber
            { bg: 'rgba(16, 185, 129, 0.85)', border: 'rgba(16, 185, 129, 1)' },      // 9. Emerald
            { bg: 'rgba(249, 115, 22, 0.85)', border: 'rgba(249, 115, 22, 1)' },      // 10. Dark Orange
            { bg: 'rgba(139, 92, 246, 0.85)', border: 'rgba(139, 92, 246, 1)' },      // 11. Violet
            { bg: 'rgba(6, 182, 212, 0.85)', border: 'rgba(6, 182, 212, 1)' },        // 12. Cyan
            { bg: 'rgba(234, 179, 8, 0.85)', border: 'rgba(234, 179, 8, 1)' },        // 13. Yellow
            { bg: 'rgba(147, 51, 234, 0.85)', border: 'rgba(147, 51, 234, 1)' },      // 14. Purple Variant
            { bg: 'rgba(220, 38, 38, 0.85)', border: 'rgba(220, 38, 38, 1)' },        // 15. Dark Red
            { bg: 'rgba(75, 85, 99, 0.85)', border: 'rgba(75, 85, 99, 1)' },          // 16. Gray
            { bg: 'rgba(20, 184, 166, 0.85)', border: 'rgba(20, 184, 166, 1)' },      // 17. Teal
            { bg: 'rgba(251, 191, 36, 0.85)', border: 'rgba(251, 191, 36, 1)' },      // 18. Gold
            { bg: 'rgba(167, 139, 250, 0.85)', border: 'rgba(167, 139, 250, 1)' },    // 19. Light Purple
            { bg: 'rgba(252, 165, 165, 0.85)', border: 'rgba(252, 165, 165, 1)' },    // 20. Light Red
            { bg: 'rgba(96, 165, 250, 0.85)', border: 'rgba(96, 165, 250, 1)' },      // 21. Light Blue
            { bg: 'rgba(248, 113, 113, 0.85)', border: 'rgba(248, 113, 113, 1)' },    // 22. Salmon
            { bg: 'rgba(134, 239, 172, 0.85)', border: 'rgba(134, 239, 172, 1)' },    // 23. Light Green
            { bg: 'rgba(253, 186, 116, 0.85)', border: 'rgba(253, 186, 116, 1)' },    // 24. Peach
            { bg: 'rgba(196, 181, 253, 0.85)', border: 'rgba(196, 181, 253, 1)' },    // 25. Lavender
            { bg: 'rgba(251, 207, 232, 0.85)', border: 'rgba(251, 207, 232, 1)' },    // 26. Light Pink
            { bg: 'rgba(125, 211, 252, 0.85)', border: 'rgba(125, 211, 252, 1)' },    // 27. Baby Blue
            { bg: 'rgba(253, 224, 71, 0.85)', border: 'rgba(253, 224, 71, 1)' },      // 28. Bright Yellow
            { bg: 'rgba(110, 231, 183, 0.85)', border: 'rgba(110, 231, 183, 1)' },    // 29. Mint
            { bg: 'rgba(251, 146, 60, 0.85)', border: 'rgba(251, 146, 60, 1)' },      // 30. Tangerine
            { bg: 'rgba(192, 132, 252, 0.85)', border: 'rgba(192, 132, 252, 1)' },    // 31. Medium Purple
            { bg: 'rgba(45, 212, 191, 0.85)', border: 'rgba(45, 212, 191, 1)' },      // 32. Turquoise
            { bg: 'rgba(244, 63, 94, 0.85)', border: 'rgba(244, 63, 94, 1)' },        // 33. Rose
            { bg: 'rgba(250, 204, 21, 0.85)', border: 'rgba(250, 204, 21, 1)' },      // 34. Marigold
            { bg: 'rgba(51, 65, 85, 0.85)', border: 'rgba(51, 65, 85, 1)' },          // 35. Slate
            { bg: 'rgba(244, 114, 182, 0.85)', border: 'rgba(244, 114, 182, 1)' },    // 36. Hot Pink
            { bg: 'rgba(34, 211, 238, 0.85)', border: 'rgba(34, 211, 238, 1)' },      // 37. Bright Cyan
            { bg: 'rgba(163, 230, 53, 0.85)', border: 'rgba(163, 230, 53, 1)' },      // 38. Lime
            { bg: 'rgba(251, 113, 133, 0.85)', border: 'rgba(251, 113, 133, 1)' },    // 39. Coral
            { bg: 'rgba(129, 140, 248, 0.85)', border: 'rgba(129, 140, 248, 1)' }     // 40. Indigo
        ];

        const backgroundColors = colorPalette.map(c => c.bg);
        const borderColors = colorPalette.map(c => c.border);

        // Base chart options
        let chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,  // Show legend for all chart types
                    position: type === 'line' ? 'top' : (type === 'bar' ? 'top' : 'right'),
                    align: type === 'line' ? 'center' : (type === 'bar' ? 'end' : 'center'),
                    labels: {
                        boxWidth: type === 'line' ? 40 : 15,
                        padding: 15,
                        usePointStyle: type === 'line',  // Use line style for line charts
                        font: {
                            size: type === 'line' ? 13 : 12,
                            weight: type === 'line' ? 'bold' : 'normal',
                            family: "'Titillium Web', sans-serif"
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            
                            // For multi-line charts, use dataset-based labels (crime types)
                            if (type === 'line' && data.datasets.length > 1) {
                                return data.datasets.map((dataset, i) => {
                                    return {
                                        text: dataset.label,  // This is the crime type name
                                        fillStyle: dataset.backgroundColor,
                                        strokeStyle: dataset.borderColor,
                                        lineWidth: 3,
                                        hidden: false,
                                        datasetIndex: i
                                    };
                                });
                            }
                            
                            // For other charts (bar, pie, doughnut), use custom labels with values
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const bgColor = Array.isArray(data.datasets[0].backgroundColor) 
                                        ? data.datasets[0].backgroundColor[i] 
                                        : data.datasets[0].backgroundColor;
                                    const borderColor = Array.isArray(data.datasets[0].borderColor)
                                        ? data.datasets[0].borderColor[i]
                                        : data.datasets[0].borderColor;
                                    
                                    return {
                                        text: `${label}: ${value}`,
                                        fillStyle: bgColor,
                                        strokeStyle: borderColor,
                                        lineWidth: 2,
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    },
                    onClick: function(e, legendItem, legend) {
                        // Get the chart
                        const chart = legend.chart;
                        
                        // For multi-line charts, toggle dataset visibility
                        if (legendItem.datasetIndex !== undefined) {
                            const index = legendItem.datasetIndex;
                            const meta = chart.getDatasetMeta(index);
                            meta.hidden = meta.hidden === null ? !chart.data.datasets[index].hidden : null;
                            chart.update();
                        } else {
                            // Default behavior for other charts
                            const index = legendItem.index;
                            const ci = legend.chart;
                            const meta = ci.getDatasetMeta(0);
                            meta.data[index].hidden = !meta.data[index].hidden;
                            ci.update();
                        }
                    }
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    titleFont: {
                        size: 14,
                        family: "'Titillium Web', sans-serif",
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13,
                        family: "'Titillium Web', sans-serif"
                    },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value;
                            
                            // Handle different chart types
                            if (type === 'pie' || type === 'doughnut') {
                                value = context.parsed; // For pie/doughnut, use context.parsed directly
                            } else if (context.parsed.y !== undefined && context.parsed.y !== null) {
                                value = context.parsed.y; // For bar/line charts
                            } else {
                                value = context.parsed;
                            }
                            
                            // Format the label
                            if (label) {
                                label += ': ';
                            }
                            
                            if (value !== undefined && value !== null) {
                                label += value + ' incident' + (value !== 1 ? 's' : '');
                            }
                            
                            return label;
                        }
                    }
                }
            },
            // Add hover effect for bar charts
            onHover: (event, activeElements) => {
                if (type === 'bar' && activeElements.length > 0) {
                    event.native.target.style.cursor = 'pointer';
                } else {
                    event.native.target.style.cursor = 'default';
                }
            }
        };

        // Add scales for bar and line charts
        if (type === 'bar' || type === 'line') {
            chartOptions.scales = {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 11,
                            family: "'Titillium Web', sans-serif"
                        },
                        color: '#6b7280'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    title: {
                        display: true,
                        text: 'Number of Incidents',
                        font: {
                            size: 12,
                            family: "'Titillium Web', sans-serif",
                            weight: 'bold'
                        },
                        color: '#374151'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 11,
                            family: "'Titillium Web', sans-serif"
                        },
                        color: '#6b7280',
                        maxRotation: 45,
                        minRotation: 0
                    },
                    grid: {
                        display: false,
                        drawBorder: false
                    }
                }
            };
        }

        // Line chart specific options
        if (type === 'line') {
            chartOptions.elements = {
                line: {
                    tension: 0.4,  // Smooth curves
                    borderWidth: 3
                },
                point: {
                    radius: 4,
                    hitRadius: 10,
                    hoverRadius: 6,
                    backgroundColor: 'white',
                    borderWidth: 2
                }
            };
        }

        // Bar chart specific options
        if (type === 'bar') {
            chartOptions.plugins.legend.display = true;
            chartOptions.barPercentage = 0.7;
            chartOptions.categoryPercentage = 0.8;
            
            // Add hover animation for bars
            chartOptions.animation = {
                duration: 300
            };
            
            chartOptions.hover = {
                mode: 'nearest',
                intersect: true,
                animationDuration: 200
            };
        }

        // Pie and Doughnut specific options
        if (type === 'pie' || type === 'doughnut') {
            chartOptions.plugins.legend.display = false;  // Hide default legend, we'll draw custom labels
            
            // Add hover effect - segments pop out
            chartOptions.hoverOffset = 15;
            
            // Smooth animation
            chartOptions.animation = {
                animateRotate: true,
                animateScale: true,
                duration: 500
            };
            
            chartOptions.hover = {
                mode: 'nearest',
                intersect: true
            };
            
            // Add padding for external labels
            chartOptions.layout = {
                padding: {
                    top: 40,
                    right: 140,
                    bottom: 40,
                    left: 40
                }
            };
            
            // Add percentage to tooltips
            chartOptions.plugins.tooltip.callbacks.label = function(context) {
                let label = context.label || '';
                let value = context.parsed;
                
                // Calculate percentage
                let sum = 0;
                let dataArr = context.chart.data.datasets[0].data;
                dataArr.forEach(data => {
                    sum += data;
                });
                let percentage = ((value / sum) * 100).toFixed(1);
                
                return `${label}: ${value} incidents (${percentage}%)`;
            };
            
            // Custom plugin to draw external labels with connector lines
            const externalLabelsPlugin = {
                id: 'externalLabels',
                afterDraw: (chart) => {
                    const ctx = chart.ctx;
                    const meta = chart.getDatasetMeta(0);
                    const chartArea = chart.chartArea;
                    
                    if (!meta || !meta.data) return;
                    
                    // Calculate total for percentages
                    let total = 0;
                    chart.data.datasets[0].data.forEach(val => {
                        total += val;
                    });
                    
                    ctx.save();
                    ctx.font = '12px "Titillium Web", sans-serif';
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';
                    
                    meta.data.forEach((segment, i) => {
                        if (!segment || segment.hidden) return;
                        
                        const value = chart.data.datasets[0].data[i];
                        const label = chart.data.labels[i];
                        const percentage = ((value / total) * 100).toFixed(2);
                        
                        // Get center of chart
                        const centerX = segment.x;
                        const centerY = segment.y;
                        const radius = segment.outerRadius;
                        const innerRadius = segment.innerRadius || 0;
                        
                        // Calculate angle for this segment (middle of the slice)
                        const angle = (segment.startAngle + segment.endAngle) / 2;
                        
                        // Point on outer edge of segment
                        const x1 = centerX + Math.cos(angle) * radius;
                        const y1 = centerY + Math.sin(angle) * radius;
                        
                        // Extended point (line goes out)
                        const lineLength = 25;
                        const x2 = centerX + Math.cos(angle) * (radius + lineLength);
                        const y2 = centerY + Math.sin(angle) * (radius + lineLength);
                        
                        // Determine if label should be on left or right
                        const isRightSide = x2 > centerX;
                        
                        // Horizontal line endpoint
                        const horizontalLength = 20;
                        const x3 = x2 + (isRightSide ? horizontalLength : -horizontalLength);
                        const y3 = y2;
                        
                        // Draw connector line
                        ctx.beginPath();
                        ctx.strokeStyle = borderColors[i] || 'rgba(0,0,0,0.3)';
                        ctx.lineWidth = 1.5;
                        
                        // Line from segment to extended point
                        ctx.moveTo(x1, y1);
                        ctx.lineTo(x2, y2);
                        
                        // Horizontal line
                        ctx.lineTo(x3, y3);
                        ctx.stroke();
                        
                        // Draw label text
                        ctx.fillStyle = '#374151';
                        ctx.textAlign = isRightSide ? 'left' : 'right';
                        
                        const textX = x3 + (isRightSide ? 5 : -5);
                        const labelText = `${label}: ${percentage}%`;
                        
                        ctx.fillText(labelText, textX, y3);
                    });
                    
                    ctx.restore();
                }
            };
            
            // Register the plugin for this chart
            if (!chartOptions.plugins.externalLabels) {
                chartOptions.plugins.externalLabels = externalLabelsPlugin;
            }
        }

        // Create the chart
        let datasets;
        
        if (currentChartData.multiLine && type === 'line') {
            // Multi-line chart: create a dataset for each crime type
            datasets = currentChartData.datasets.map((dataset, index) => {
                const color = colorPalette[index % colorPalette.length];
                return {
                    label: dataset.label,
                    data: currentChartData.labels.map(date => dataset.data[date] || 0),
                    backgroundColor: color.bg.replace('0.85', '0.1'),
                    borderColor: color.border,
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6
                };
            });
        } else {
            // Single dataset for bar/pie/doughnut or single-line chart
            datasets = [{
                label: currentChartData.labelText,
                data: currentChartData.data,
                backgroundColor: type === 'line' ? 'rgba(59, 130, 246, 0.1)' : backgroundColors,
                borderColor: type === 'line' ? 'rgba(59, 130, 246, 1)' : borderColors,
                borderWidth: type === 'pie' || type === 'doughnut' ? 2 : (type === 'line' ? 3 : 2),
                fill: type === 'line' ? true : false,
                tension: type === 'line' ? 0.4 : 0,
                hoverBackgroundColor: type === 'line' ? 'rgba(59, 130, 246, 0.2)' : backgroundColors,
                hoverBorderWidth: type === 'pie' || type === 'doughnut' ? 3 : 2,
                hoverBorderColor: borderColors
            }];
        }
        
        crimeChart = new Chart(ctx, {
            type: type,
            data: {
                labels: currentChartData.labels,
                datasets: datasets
            },
            options: chartOptions,
            plugins: (type === 'pie' || type === 'doughnut') && chartOptions.plugins.externalLabels ? [chartOptions.plugins.externalLabels] : []
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
            // Add chart type to form data
            const chartType = chartSelector ? chartSelector.value : 'bar';
            formData.append('chart_type', chartType);
            
            fetch('fetch_graph_data.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(json => {
                currentChartData.labels = json.labels;
                currentChartData.data = json.data;
                currentChartData.multiLine = json.multiLine || false;
                currentChartData.datasets = json.datasets || null;
                
                // Get all selected crime types
                const selectedCrimes = formData.getAll('crime_type[]').filter(c => c !== '');
                
                if (chartType === 'line') {
                    // Line chart shows time series
                    if (selectedCrimes.length > 0) {
                        if (selectedCrimes.length === 1) {
                            currentChartData.labelText = selectedCrimes[0] + " Over Time";
                        } else {
                            currentChartData.labelText = "Crime Trends Over Time";
                        }
                    } else {
                        currentChartData.labelText = "All Crimes Over Time";
                    }
                } else {
                    // Bar/pie/doughnut shows by crime type
                    if (selectedCrimes.length > 0) {
                        if (selectedCrimes.length === 1) {
                            currentChartData.labelText = "Incidents: " + selectedCrimes[0];
                        } else {
                            currentChartData.labelText = "Incidents: " + selectedCrimes.length + " crime types";
                        }
                    } else {
                        currentChartData.labelText = "# of Incidents by Type";
                    }
                }
                
                drawChart(chartType);
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
            // Refetch data when chart type changes (for line chart vs others)
            if (filterForm) {
                const formData = new FormData(filterForm);
                updateDashboard(formData);
            } else {
                // Fallback: just redraw if no form
                drawChart(this.value);
            }
        });
    }
});


// Global variables for modal state
let currentRecordId = null;
let currentRecordSource = null;

// Open observation modal
function openObservationModal(id, source) {
    console.log('Opening modal for ID:', id, 'Source:', source);
    
    currentRecordId = id;
    currentRecordSource = source;
    
    const modal = document.getElementById('observationModal');
    if (!modal) {
        console.error('Modal element not found!');
        alert('ERROR: Modal element not found. Please refresh the page.');
        return;
    }
    
    modal.style.display = 'block';
    document.getElementById('modalRecordId').textContent = id + ' (' + source + ')';
    document.getElementById('observationText').value = '';
    document.getElementById('observationMessage').innerHTML = '';
    
    // Load existing observations
    loadObservations(id, source);
}

// Close observation modal
function closeObservationModal() {
    console.log('Closing modal');
    const modal = document.getElementById('observationModal');
    if (modal) {
        modal.style.display = 'none';
    }
    currentRecordId = null;
    currentRecordSource = null;
}

// Load existing observations
function loadObservations(id, source) {
    const listContainer = document.getElementById('observationsList');
    listContainer.innerHTML = '<p class="loading">Loading observations...</p>';
    
    fetch(`get_observations.php?incident_id=${id}&source=${source}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.observations.length > 0) {
                let html = '';
                data.observations.forEach(obs => {
                    if (source === 'civilian') {
                        html += `<div class="observation-item">${obs.text || 'No observations yet'}</div>`;
                    } else {
                        html += `<div class="observation-item">
                            <div class="obs-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div>
                                    <strong>${obs.username}</strong>
                                    ${obs.updated_at ? '<span style="font-size: 0.75rem; color: #9ca3af;">(edited)</span>' : ''}
                                </div>
                                <span class="obs-date">${obs.created_at}</span>
                            </div>
                            <div class="obs-text">${obs.text}</div>`;
                        
                        // Only show edit/delete buttons if user owns observation OR is admin
                        if (isAdmin || obs.user_id == currentUserId) {
                            html += `<div class="obs-actions" style="margin-top: 0.75rem; display: flex; gap: 0.5rem;">
                                <button class="obs-action-btn obs-edit-btn" onclick="editObservationInModal(${obs.id}, ${obs.user_id}, '${id}', '${source}')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="obs-action-btn obs-delete-btn" onclick="deleteObservationInModal(${obs.id}, ${obs.user_id}, '${id}', '${source}')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>`;
                        }
                        
                        html += `</div>`;
                    }
                });
                listContainer.innerHTML = html;
            } else {
                listContainer.innerHTML = '<p class="no-data">No observations yet.</p>';
            }
        })
        .catch(err => {
            console.error('Error loading observations:', err);
            listContainer.innerHTML = '<p class="error">Error loading observations.</p>';
        });
}

// Submit observation
function submitObservation() {
    const observation = document.getElementById('observationText').value.trim();
    const messageDiv = document.getElementById('observationMessage');
    
    if (!observation) {
        messageDiv.innerHTML = '<p class="error">Please enter an observation.</p>';
        return;
    }
    
    const formData = new FormData();
    formData.append('incident_id', currentRecordId);
    formData.append('source', currentRecordSource);
    formData.append('observation', observation);
    
    messageDiv.innerHTML = '<p class="loading">Saving...</p>';
    
    fetch('add_observation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.innerHTML = '<p class="success">✓ Observation saved successfully!</p>';
            document.getElementById('observationText').value = '';
            
            // Reload observations
            loadObservations(currentRecordId, currentRecordSource);
            
            // Close modal after 1.5 seconds
            setTimeout(() => {
                closeObservationModal();
            }, 1500);
        } else {
            messageDiv.innerHTML = `<p class="error">Error: ${data.message}</p>`;
        }
    })
    .catch(err => {
        console.error('Error:', err);
        messageDiv.innerHTML = '<p class="error">Failed to save observation.</p>';
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('observationModal');
    if (event.target == modal) {
        closeObservationModal();
    }
}

// Toggle observations dropdown in table
function toggleObservations(rowId, recordId, source) {
    const obsRow = document.getElementById('obs-' + rowId);
    const icon = document.getElementById('icon-' + rowId);
    const content = document.getElementById('content-' + rowId);
    
    if (obsRow.style.display === 'none') {
        // Show observations
        obsRow.style.display = 'table-row';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
        
        // Load observations
        content.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Loading observations...</div>';
        
        fetch(`get_observations.php?incident_id=${recordId}&source=${source}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.observations.length > 0) {
                    let html = '<div class="observations-dropdown-list">';
                    html += '<h4><i class="fas fa-clipboard-list"></i> Observations:</h4>';
                    
                    data.observations.forEach((obs, index) => {
                        html += '<div class="observation-item-inline">';
                        html += '<div class="obs-number">#' + (index + 1) + '</div>';
                        html += '<div class="obs-content">';
                        
                        if (source === 'civilian') {
                            html += '<div class="obs-text-inline">' + (obs.text || 'No observation text') + '</div>';
                        } else {
                            html += '<div class="obs-header-inline">';
                            html += '<div><strong>' + obs.username + '</strong>';
                            if (obs.updated_at) {
                                html += ' <span style="font-size: 0.75rem; color: #9ca3af;">(edited)</span>';
                            }
                            html += '</div>';
                            html += '<span class="obs-date-inline">' + obs.created_at + '</span>';
                            html += '</div>';
                            html += '<div class="obs-text-inline">' + obs.text + '</div>';
                            
                            // Only show edit/delete buttons if user owns observation OR is admin
                            if (isAdmin || obs.user_id == currentUserId) {
                                html += '<div class="obs-actions" style="margin-top: 0.5rem;">';
                                html += '<button class="obs-action-btn obs-edit-btn" onclick="editObservation(' + obs.id + ', ' + obs.user_id + ', \'' + recordId + '\', \'' + source + '\')"><i class="fas fa-edit"></i> Edit</button>';
                                html += '<button class="obs-action-btn obs-delete-btn" onclick="deleteObservation(' + obs.id + ', ' + obs.user_id + ', \'' + recordId + '\', \'' + source + '\')"><i class="fas fa-trash"></i> Delete</button>';
                                html += '</div>';
                            }
                        }
                        
                        html += '</div></div>';
                    });
                    
                    html += '</div>';
                    content.innerHTML = html;
                } else {
                    content.innerHTML = '<div class="no-observations"><i class="fas fa-info-circle"></i> No observations yet for this record.</div>';
                }
            })
            .catch(err => {
                console.error('Error loading observations:', err);
                content.innerHTML = '<div class="error-loading"><i class="fas fa-exclamation-triangle"></i> Error loading observations.</div>';
            });
    } else {
        // Hide observations
        obsRow.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

// Explicitly attach functions to window object for guaranteed global access
window.openObservationModal = openObservationModal;
window.closeObservationModal = closeObservationModal;
window.loadObservations = loadObservations;
window.submitObservation = submitObservation;
window.toggleObservations = toggleObservations;
window.editObservation = editObservation;
window.deleteObservation = deleteObservation;
window.editObservationInModal = editObservationInModal;
window.deleteObservationInModal = deleteObservationInModal;

// Debug logging
console.log('Observation functions loaded and attached to window');
console.log('openObservationModal:', typeof window.openObservationModal);
console.log('closeObservationModal:', typeof window.closeObservationModal);
console.log('loadObservations:', typeof window.loadObservations);
console.log('submitObservation:', typeof window.submitObservation);
console.log('toggleObservations:', typeof window.toggleObservations);
console.log('editObservation:', typeof window.editObservation);
console.log('deleteObservation:', typeof window.deleteObservation);
console.log('editObservationInModal:', typeof window.editObservationInModal);
console.log('deleteObservationInModal:', typeof window.deleteObservationInModal);


// Edit observation (for dropdown in table)
function editObservation(observationId, observationUserId, recordId, source) {
    const newText = prompt('Edit your observation:');
    
    if (newText === null || newText.trim() === '') {
        return; // User cancelled or entered empty text
    }
    
    const formData = new FormData();
    formData.append('observation_id', observationId);
    formData.append('observation', newText.trim());
    
    fetch('edit_observation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Observation updated successfully!');
            // Reload the observations to show the updated text
            const rowId = 'row-' + recordId + '-' + source;
            // Close and reopen to refresh
            toggleObservations(rowId, recordId, source);
            setTimeout(() => {
                toggleObservations(rowId, recordId, source);
            }, 100);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Failed to update observation.');
    });
}

// Delete observation (for dropdown in table)
function deleteObservation(observationId, observationUserId, recordId, source) {
    if (!confirm('Are you sure you want to delete this observation? This action cannot be undone.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('observation_id', observationId);
    
    fetch('delete_observation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Observation deleted successfully!');
            // Reload the observations
            const rowId = 'row-' + recordId + '-' + source;
            // Close and reopen to refresh
            toggleObservations(rowId, recordId, source);
            setTimeout(() => {
                toggleObservations(rowId, recordId, source);
            }, 100);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Failed to delete observation.');
    });
}

// Edit observation (for modal)
function editObservationInModal(observationId, observationUserId, recordId, source) {
    const newText = prompt('Edit your observation:');
    
    if (newText === null || newText.trim() === '') {
        return; // User cancelled or entered empty text
    }
    
    const formData = new FormData();
    formData.append('observation_id', observationId);
    formData.append('observation', newText.trim());
    
    fetch('edit_observation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Observation updated successfully!');
            // Reload observations in modal
            loadObservations(recordId, source);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Failed to update observation.');
    });
}

// Delete observation (for modal)
function deleteObservationInModal(observationId, observationUserId, recordId, source) {
    if (!confirm('Are you sure you want to delete this observation? This action cannot be undone.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('observation_id', observationId);
    
    fetch('delete_observation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Observation deleted successfully!');
            // Reload observations in modal
            loadObservations(recordId, source);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Failed to delete observation.');
    });
}