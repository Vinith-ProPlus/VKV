@extends('layouts.admin')
@section('content')
@php
    $PageTitle = "Dashboard";
    $ActiveMenuName = 'Dashboard';
@endphp
 
<style>
    .dashboard-card {
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }
    .stat-card {
        padding: 15px;
        border-radius: 8px;
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .stat-card h6 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 10px;
    }
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        margin-top: auto;
    }
    .pichart-container {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        gap: 15px;
        margin-top: 15px;
    }
    .chart-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }
    .chart-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
    }
    .sidebar {
        position: sticky;
        top: 20px;
        height: calc(100vh - 40px);
        overflow-y: auto;
        padding-right: 10px;
    }
    .sidebar-widget {
        margin-bottom: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        padding: 15px;
    }
    .sidebar-widget h5 {
        font-size: 16px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
    
    /* Status cards colors */
    .card-projects {
        background-image: linear-gradient(to left bottom, #cdbbff, white);
        color: #8f67ff;
        box-shadow: 8px 7px;
    }
    .card-inprogress {
        background-image: linear-gradient(to left bottom, #ffdeb3, white);
        color: #ff8f00;
        box-shadow: 8px 7px;
    }
    .card-onhold {
        background-image: linear-gradient(to left bottom, #c5c5c5, white);
        color: #616161;
        box-shadow: 8px 7px;
    }
    .card-completed {
        background-image: linear-gradient(to left bottom, #b5dcb7, white);
        color: #2e7d32;
        box-shadow: 8px 7px;
    }

    .card-projects:hover, .card-inprogress:hover, .card-onhold:hover ,.card-completed:hover{
        cursor: pointer;
        transform: translate(5px, 2px);
        box-shadow: none;
    }
    
    @media (max-width: 992px) {
        .pichart-container {
            flex-direction: column;
        }
        .chart-box {
            width: 100%;
        }
        .sidebar {
            position: static;
            height: auto;
            margin-top: 20px;
        }
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Main Content (Charts) -->
        <div class="col-lg-9 col-md-8">
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card stat-card card-projects">
                        <h6>Total Projects</h6>
                        <div class="stat-value" id="projectsCount">0</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card stat-card card-inprogress">
                        <h6>In Progress</h6>
                        <div class="stat-value" id="inProgressCount">0</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card stat-card card-onhold">
                        <h6>On Hold</h6>
                        <div class="stat-value" id="onHoldCount">0</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card stat-card card-completed">
                        <h6>Completed</h6>
                        <div class="stat-value" id="completedCount">0</div>
                    </div>
                </div>
            </div>
            
            <!-- Project Trends -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card dashboard-card">
                        <h5 class="chart-title">Project Trends</h5>
                        <div id="projectTrendsChart"></div>
                    </div>
                </div>
            </div>

            <!-- Project Performance -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card dashboard-card">
                        <h5 class="chart-title">Project Performance Overview</h5>
                        <div class="pichart-container">
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-3 col-md-4">
            <div class="sidebar">
                <div class="card sidebar-widget">
                    <h5>Supervisors</h5>
                    <div id="recentActivities">
                        <p class="small text-muted mb-7">Total Supervisors count</p>
                        <div class="d-flex align-items-center mb-15">
                            <div class="me-3 bg-primary text-white rounded-circle p-2">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <p class="mb-0 small fw-bold" id="supervisorCount">-</p> 
                            </div>
                        </div>
                        <p class="small text-muted mb-7">CheckedIn Supervisors Count</p>
                        <div class="d-flex align-items-center mb-15">
                            <div class="me-3 bg-success text-white rounded-circle p-2">
                                <i class="fas fa-pause-circle"></i>
                            </div>
                            <div style="line-height: 5px;">
                                <p class="mb-0 small fw-bold" id="checkedInSupervisorCount">-</p> 
                            </div>
                        </div> 
                    </div>
                </div>
                
                <div class="card sidebar-widget overall-project-container">
                    {{-- <h5>Overall Project Status</h5>
                    <div id="statusDistributionChart"></div> --}}
                </div>
                
                <div class="card sidebar-widget d-none">
                    <h5>Today's Pending Tasks</h5>
                    <div id="upcomingDeadlines"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        
        // Common chart colors
        const chartColors = {
            primary: '#7c4dff',
            secondary: '#c6b3ff',
            success: '#4caf50',
            warning: '#ff9800',
            danger: '#f44336',
            info: '#2196f3',
            light: '#e9ecef',
            dark: '#343a40'
        };
        
        // Responsive options for all charts
        const chartResponsive = [{
            breakpoint: 992,
            options: {
                chart: {
                    width: '100%'
                }
            }
        }];

        // Project Trends Chart (Area)
        const projectTrendsOptions = {
            chart: {
                type: "area",
                height: 350,
                fontFamily: 'inherit',
                toolbar: { show: false },
                stacked: false
            },
            series: [
                {
                    name: "New Projects",
                    data: [15, 25, 20, 35, 40, 35, 50, 45, 60, 55, 70, 65]
                },
                {
                    name: "Completed Projects",
                    data: [10, 15, 15, 25, 30, 25, 40, 35, 50, 40, 60, 55]
                }
            ],
            stroke: {
                curve: "smooth",
                width: 3
            },
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    inverseColors: false,
                    opacityFrom: 0.45,
                    opacityTo: 0.1,
                    stops: [20, 100]
                }
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 4
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            dataLabels: { enabled: false },
            colors: [chartColors.primary, chartColors.success],
            responsive: chartResponsive
        };
        const projectTrendsChart = new ApexCharts(
            document.querySelector("#projectTrendsChart"), 
            projectTrendsOptions
        );
        projectTrendsChart.render();
 

        // Handle resize events for better responsiveness
        window.addEventListener('resize', function() {
            [
                // statusDistributionChart, 
                // monthlyProgressChart, 
                // teamPerformanceChart,
                projectTrendsChart
            ].forEach(chart => {
                if (chart) {
                    chart.render();
                }
            });
        });
    });
</script>
<script>
    $(document).ready(function() {
        let totalProjects = 0;
        let inProgressProjects = 0;
        let onHoldProjects = 0;
        let completedProjects = 0;

                
        // Common chart colors
        const chartColors = {
            primary: '#7c4dff',
            secondary: '#c6b3ff',
            success: '#4caf50',
            warning: '#ff9800',
            danger: '#f44336',
            info: '#2196f3',
            light: '#e9ecef',
            dark: '#343a40'
        };
        
        // Responsive options for all charts
        const chartResponsive = [{
            breakpoint: 992,
            options: {
                chart: {
                    width: '100%'
                }
            }
        }];
        // Function to fetch projects
        const getProjects = () => {
            $.ajax({
                url: "{{ route('projects.all') }}",
                type: "GET",
                dataType: "json",
                success: function(response) { 
                    let total = 0, inProgress = 0, onHold = 0, completed = 0;

                    response.forEach((item, index) => {
                        if(item.status === 'In-progress') inProgress++;
                        if(item.status === 'On-hold') onHold++;
                        if(item.status === 'Completed') completed++;
                        
                        total++;

                        // Generate a unique chart ID
                        let chartId = `project-chart-${index}`;
                        getProjectTasks(item.id);
                        projectPie(item.name, item.completion_percentage, chartId);
                    });

                    totalProjects = total;
                    inProgressProjects = inProgress;
                    onHoldProjects = onHold;
                    completedProjects = completed; 

                    // Start animations **after** data is fetched
                    animateCount("projectsCount", totalProjects);
                    animateCount("inProgressCount", inProgressProjects);
                    animateCount("onHoldCount", onHoldProjects);
                    animateCount("completedCount", completedProjects);

                    overallCalculation(inProgressProjects, onHoldProjects, completedProjects);
                    // alignment issue by triggering a reflow
                    setTimeout(() => {
                        window.dispatchEvent(new Event("resize")); // Force charts to align correctly
                    }, 300);

                },
                error: function(xhr, status, error) {
                    console.error("Error fetching projects:", xhr.responseText);
                }
            });
        };

        const getProjectTasks = (id) => {
            let total = 0;
            let completed = 0;
            let unCompletedPercentage = 0;
            let projectName = '';
            $.ajax({
                url: "{{ route('project.tasks') }}",
                type: "GET",
                dataType: "json",
                data:{'id':id},
                success: function(response) { 
                        response.forEach((item, index) => { 
                            projectName = item.project.name;
                            if(item.status === 'Completed') completed++;
                            total++;
                        });
                        unCompletedPercentage = ((total-completed)/total)*100;
                    if(unCompletedPercentage){
                        setPendingTasks(total, completed, unCompletedPercentage, projectName);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching projects:", xhr.responseText);
                }
            });
        }

        const getSupervisors = () => {
            let total = 0;
            $.ajax({
                url: "{{ route('getSupervisors') }}",
                type: "GET",
                dataType: "json", 
                success: function(response) {  
                    $('#supervisorCount').text(response.length);
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching projects:", xhr.responseText);
                }
            });
        }

        const getCheckedInSupervisors = () => {
            let total = 0;
            $.ajax({
                url: "{{ route('getCheckedInSupervisors') }}",
                type: "GET",
                dataType: "json", 
                success: function(response) {   
                    $('#checkedInSupervisorCount').text(response.length);
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching projects:", xhr.responseText);
                }
            });
        }

        getSupervisors();
        getCheckedInSupervisors();
        // Easing function
        function easeOutQuad(t) {
            return t * (2 - t);
        }

        // Animate Counter
        function animateCount(elementId, target, duration = 2500) {
            const element = document.getElementById(elementId);
            if (!element) return;

            let startTime = null;

            function updateCounter(timestamp) {
                if (!startTime) startTime = timestamp;
                const elapsed = timestamp - startTime;
                const progress = Math.min(elapsed / duration, 1); 

                element.textContent = Math.floor(target * easeOutQuad(progress));

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    element.textContent = target; // Ensure exact final value
                }
            }

            requestAnimationFrame(updateCounter);
        }

       // Function to create a pie chart for project
       function projectPie(name, value, chartId) {
            let container = $('.pichart-container');

            if(value.slice(-1)=='%'){
                value = value.slice(0, -1)
            }
            let html = `
                <div class="chart-box">
                    <h6>${name}</h6> 
                    <div id="${chartId}"></div>
                </div>`;
            
                container.append(html);

            setTimeout(() => {
                const teamPerformanceOptions = {
                    chart: {
                        type: "radialBar",
                        height: 215,
                        fontFamily: 'inherit',
                        toolbar: { show: false }
                    },
                    series: [value],
                    labels: ['Completed'],
                    colors: ["#008FFB"], // Replace with chartColors.primary if defined
                    plotOptions: {
                        radialBar: {
                            hollow: { size: '65%' },
                            dataLabels: {
                                show: true,
                                name: { show: true, fontSize: '14px', fontWeight: 600, offsetY: -10 },
                                value: {
                                    show: true,
                                    fontSize: '22px',
                                    fontWeight: 700,
                                    formatter: val => `${val}%`
                                }
                            }
                        }
                    }
                };

                let chart = new ApexCharts(document.querySelector(`#${chartId}`), teamPerformanceOptions);
                chart.render();
            }, 100); // Small delay to let DOM settle before rendering
        }
        function overallCalculation(inProgress, onHoldCount, completedProjects){
            let container = $('.overall-project-container');
 

            if (!Number.isFinite(inProgress) || !Number.isFinite(onHoldCount) || !Number.isFinite(completedProjects)) {
                console.error("Invalid data for chart:", { inProgress, onHoldCount, completedProjects });
                return;
            }

            // Clear previous content and add new HTML
            container.html(`
                <h5>Overall Project Status</h5>
                <div id="statusDistributionChart"></div>
            `);

            // Ensure element exists
            if (!document.getElementById("statusDistributionChart")) { 
                return;
            }

            const statusDistributionOptions = {
                chart: {
                    type: "donut",
                    height: 200,
                    fontFamily: 'inherit'
                },
                series: [inProgress, onHoldCount, completedProjects], // In Progress, On Hold, Completed
                labels: ['In Progress', 'On Hold', 'Completed'],
                colors: [chartColors.warning, chartColors.dark, chartColors.success],
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center'
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: { show: true },
                                value: { 
                                    show: true, 
                                    formatter: (val) => val 
                                },
                                total: {
                                    show: true,
                                    formatter: (w) => 
                                        w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                responsive: chartResponsive
            }; 

            // Destroy previous chart instance if it exists
            if (window.statusDistributionChart) {
                try {
                    window.statusDistributionChart.destroy(); 
                } catch (error) {
                    // console.error("Error destroying previous chart:", error);
                }
            }

            // Ensure chart element exists before rendering
            let chartElement = document.querySelector("#statusDistributionChart");
            if (!chartElement) {
                console.error("Chart container still missing!");
                return;
            }

            // Create new chart and store it in a global variable
            window.statusDistributionChart = new ApexCharts(chartElement, statusDistributionOptions);
            window.statusDistributionChart.render(); 
        }

        function setPendingTasks(total, completed, unCompletedPercentage, projectName){
            let container = $('#upcomingDeadlines');

            html = `<div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small">${projectName}</span>
                    <span class="badge bg-danger">${completed}/${total}</span>
                </div>
                <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: ${unCompletedPercentage}%"></div>
                </div>
            `;

            container.append(html);

            container.closest('.sidebar-widget').removeClass('d-none');
        }
        // Fetch projects and update counters
        getProjects();
        getProjectTasks();
    });
</script>

@endsection