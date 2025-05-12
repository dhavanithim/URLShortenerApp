<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Analytics Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #e63946;
            --light: #f8f9fa;
            --dark: #212529;
            --background: #f7f8fc;
            --card: #ffffff;
            --text: #495057;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--background);
            color: var(--text);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .header h1 {
            color: var(--dark);
            font-size: 24px;
            font-weight: 600;
        }
        
        .header .link-id {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: var(--card);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card .icon {
            font-size: 32px;
            margin-bottom: 15px;
            display: inline-block;
        }
        
        .stat-card .title {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .stat-card .value {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
        }
        
        .chart-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            max-height: 800px; /* Limit chart height */
        }
        
        @media (max-width: 992px) {
            .chart-container {
                grid-template-columns: 1fr;
            }
        }
        
        .chart-card {
            background-color: var(--card);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .chart-card h3 {
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .table-container {
            background-color: var(--card);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }
        
        .table-container h3 {
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--dark);
            position: sticky;
            top: 0;
        }
        
        table tbody tr {
            border-bottom: 1px solid #e9ecef;
        }
        
        table tbody tr:hover {
            background-color: #f1f3f7;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        
        .badge.country {
            background-color: var(--info);
        }
        
        .badge.browser {
            background-color: var(--success);
        }
        
        .badge.ip {
            background-color: var(--secondary);
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 0;
        }
        
        .empty-state i {
            font-size: 48px;
            color: #dee2e6;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            font-size: 16px;
            color: #6c757d;
        }
        
        /* Animation classes */
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        
        .slide-in {
            animation: slideIn 0.5s ease forwards;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Loading animation */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100px;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
        
        /* Fix for long whitespace */
        .chart-card {
            min-height: 300px;
            max-height: 350px;
            display: flex;
            flex-direction: column;
        }
        
        .chart-card h3 {
            margin-bottom: 10px;
        }
        
        .chart-card > div {
            flex: 1;
            position: relative;
        }
        
        canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100% !important;
            height: 100% !important;
        }
    </style>
</head>
<body>
    <?php require_once '../config/db.php';
    
    // Check if link_id is provided
    if (!isset($_GET['link_id']) || !is_numeric($_GET['link_id'])) {
        echo "<div class='container empty-state fade-in'>
            <i class='fas fa-exclamation-circle'></i>
            <p>Invalid link ID. Please provide a valid link ID.</p>
        </div>";
        exit;
    }
    
    $link_id = $_GET['link_id'];
    
    // Prepare the SQL query to fetch analytics data for the specific link
    $sql = "SELECT * FROM clicks WHERE link_id = ? ORDER BY clicked_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$link_id]);
    
    $clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get the link details
    $linkSql = "SELECT * FROM links WHERE id = ?";
    $linkStmt = $pdo->prepare($linkSql);
    $linkStmt->execute([$link_id]);
    $link = $linkStmt->fetch(PDO::FETCH_ASSOC);
    
    // Prepare data for charts
    $countries = [];
    $browsers = [];
    $clicksByDate = [];
    
    foreach ($clicks as $click) {
        $country = $click['country'] ?: 'India';
        $browser = $click['browser'] ?: 'Unknown';
        
        // Count countries
        if (isset($countries[$country])) {
            $countries[$country]++;
        } else {
            $countries[$country] = 1;
        }
        
        // Count browsers
        if (isset($browsers[$browser])) {
            $browsers[$browser]++;
        } else {
            $browsers[$browser] = 1;
        }
        
        // Group clicks by date
        $date = date('Y-m-d', strtotime($click['clicked_at']));
        if (isset($clicksByDate[$date])) {
            $clicksByDate[$date]++;
        } else {
            $clicksByDate[$date] = 1;
        }
    }
    
    // Sort data
    arsort($countries);
    arsort($browsers);
    ksort($clicksByDate);
    
    // Prepare data for charts
    $countryLabels = array_keys(array_slice($countries, 0, 5));
    $countryData = array_values(array_slice($countries, 0, 5));
    
    $browserLabels = array_keys(array_slice($browsers, 0, 5));
    $browserData = array_values(array_slice($browsers, 0, 5));
    
    $dateLabels = array_keys($clicksByDate);
    $dateData = array_values($clicksByDate);
    
    ?>
    
    <div class="container">
        <div class="header fade-in">
            <h1>Link Analytics <span class="link-id">#<?= $link_id ?></span></h1>
            <?php if (!empty($link) && isset($link['original_url'])): ?>
                <span>
                    <?= isset($link['short_url']) ? htmlspecialchars($link['short_url']) : 'Short URL' ?> 
                    → <?= htmlspecialchars($link['original_url']) ?>
                </span>
            <?php endif; ?>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card slide-in" style="animation-delay: 0.1s">
                <i class="icon fas fa-mouse-pointer" style="color: var(--primary)"></i>
                <div class="title">Total Clicks</div>
                <div class="value"><?= count($clicks) ?></div>
            </div>
            
            <div class="stat-card slide-in" style="animation-delay: 0.2s">
                <i class="icon fas fa-globe" style="color: var(--info)"></i>
                <div class="title">Unique Countries</div>
                <div class="value"><?= count($countries) ?></div>
            </div>
            
            <div class="stat-card slide-in" style="animation-delay: 0.3s">
                <i class="icon fas fa-laptop" style="color: var(--success)"></i>
                <div class="title">Unique Browsers</div>
                <div class="value"><?= count($browsers) ?></div>
            </div>
            
            <div class="stat-card slide-in" style="animation-delay: 0.4s">
                <i class="icon fas fa-clock" style="color: var(--warning)"></i>
                <div class="title">Last Click</div>
                <div class="value"><?= !empty($clicks) ? date('M j', strtotime($clicks[0]['clicked_at'])) : 'N/A' ?></div>
            </div>
        </div>
        
        <?php if (count($clicks) > 0): ?>
            <!-- Charts -->
            <div class="chart-container">
                <div class="chart-card fade-in" style="animation-delay: 0.5s">
                    <h3>Clicks over Time</h3>
                    <div style="height: 250px;">
                        <canvas id="clicksTimeChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card fade-in" style="animation-delay: 0.6s">
                    <h3>Top Countries</h3>
                    <div style="height: 250px;">
                        <canvas id="countriesChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card fade-in" style="animation-delay: 0.7s">
                    <h3>Browser Distribution</h3>
                    <div style="height: 250px;">
                        <canvas id="browsersChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card fade-in" style="animation-delay: 0.8s">
                    <h3>Hourly Activity</h3>
                    <div style="height: 250px;">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Table -->
            <div class="table-container fade-in" style="animation-delay: 0.9s; max-height: 400px; overflow-y: auto;">
                <h3>Click Details</h3>
                <table>
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>Country</th>
                            <th>Browser</th>
                            <th>Click Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clicks as $click): ?>
                        <tr>
                            <td><span class="badge ip"><?= htmlspecialchars($click['ip_address'] ?? 'Unknown') ?></span></td>
                            <td><span class="badge country"><?= htmlspecialchars($click['country'] ?? 'India') ?></span></td>
                            <td><span class="badge browser"><?= htmlspecialchars($click['browser'] ?? 'Unknown') ?></span></td>
                            <td><?= isset($click['clicked_at']) ? date('M j, Y H:i:s', strtotime($click['clicked_at'])) : 'Unknown' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state fade-in">
                <i class="fas fa-chart-line"></i>
                <p>No clicks recorded for this link yet.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Show loading effect
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts if data exists
            <?php if (count($clicks) > 0): ?>
                // Prepare data
                const countryLabels = <?= json_encode($countryLabels) ?>;
                const countryData = <?= json_encode($countryData) ?>;
                
                const browserLabels = <?= json_encode($browserLabels) ?>;
                const browserData = <?= json_encode($browserData) ?>;
                
                const dateLabels = <?= json_encode($dateLabels) ?>;
                const dateData = <?= json_encode($dateData) ?>;
                
                // Set up colors
                const colorPalette = [
                    '#4361ee', '#3f37c9', '#4cc9f0', '#4895ef', '#f72585',
                    '#560bad', '#7209b7', '#b5179e', '#f72585', '#4361ee'
                ];
                
                // Time Series Chart
                new Chart(document.getElementById('clicksTimeChart'), {
                    type: 'line',
                    data: {
                        labels: dateLabels,
                        datasets: [{
                            label: 'Clicks',
                            data: dateData,
                            borderColor: '#4361ee',
                            backgroundColor: 'rgba(67, 97, 238, 0.1)',
                            tension: 0.3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 10,
                                titleFont: {
                                    size: 14
                                },
                                bodyFont: {
                                    size: 13
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    borderDash: [3, 3]
                                },
                                ticks: {
                                    precision: 0
                                }
                            }
                        },
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
                
                // Countries Pie Chart
                new Chart(document.getElementById('countriesChart'), {
                    type: 'pie',
                    data: {
                        labels: countryLabels,
                        datasets: [{
                            data: countryData,
                            backgroundColor: colorPalette,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    padding: 15
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 10
                            }
                        },
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 2000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
                
                // Browsers Chart
                new Chart(document.getElementById('browsersChart'), {
                    type: 'doughnut',
                    data: {
                        labels: browserLabels,
                        datasets: [{
                            data: browserData,
                            backgroundColor: colorPalette.slice(5),
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    padding: 15
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 10
                            }
                        },
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 2000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
                
                // Calculate hourly activity
                const hourlyData = Array(24).fill(0);
                <?php foreach ($clicks as $click): ?>
                    hourlyData[<?= date('G', strtotime($click['clicked_at'])) ?>]++;
                <?php endforeach; ?>
                
                // Hourly Activity Chart
                new Chart(document.getElementById('hourlyChart'), {
                    type: 'bar',
                    data: {
                        labels: Array.from({length: 24}, (_, i) => `${i}h`),
                        datasets: [{
                            label: 'Clicks',
                            data: hourlyData,
                            backgroundColor: '#4895ef',
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 10
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    borderDash: [3, 3]
                                },
                                ticks: {
                                    precision: 0
                                }
                            }
                        },
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart',
                            delay: function(context) {
                                return context.dataIndex * 50;
                            }
                        }
                    }
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>