<?php
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Handle the case when not logged in
    // Redirect to login page or treat as guest
    $user_id = null;
}

// Include session management
include('../includes/session.php');

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Include database connection
include('../config/db.php');

// Get user information
$userId = getUserId();
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user's links with click counts
$stmt = $pdo->prepare("
    SELECT links.*, 
           (SELECT COUNT(*) FROM clicks WHERE clicks.link_id = links.id) as click_count
    FROM links 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$links = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total clicks for all links
$totalClicks = 0;
foreach ($links as $link) {
    $totalClicks += $link['click_count'];
}

// Calculate total links
$totalLinks = count($links);

// Get recent clicks (last 10)
$stmt = $pdo->prepare("
    SELECT c.*, l.short_code, l.original_url 
    FROM clicks c
    JOIN links l ON c.link_id = l.id
    WHERE l.user_id = ?
    ORDER BY c.clicked_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$recentClicks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT DATE(c.clicked_at) AS click_date, COUNT(*) AS click_count
    FROM clicks c
    JOIN links l ON c.link_id = l.id
    WHERE l.user_id = ?
    GROUP BY DATE(c.clicked_at)
    ORDER BY click_date ASC");
$stmt->execute([$userId]);
$clicksOverTime = $stmt->fetchAll(PDO::FETCH_ASSOC);
$clicksOverTimeJson = json_encode($clicksOverTime);// for chronological order


// Fetch top links based on click count for Top Links chart
$stmt = $pdo->prepare("
    SELECT l.short_code, l.original_url, COUNT(c.id) AS click_count
    FROM links l
    LEFT JOIN clicks c ON l.id = c.link_id
    WHERE l.user_id = ?
    GROUP BY l.id
    ORDER BY click_count DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$topLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pass the data to JavaScript
$topLinksJson = json_encode($topLinks);
?>
<script>
    // Data for Clicks Over Time
    var clicksOverTimeData = <?php echo $clicksOverTimeJson; ?>;

    // Data for Top Links
    var topLinksData = <?php echo $topLinksJson; ?>;
</script>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | URL Shortener</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #1abc9c;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --info-color: #3498db;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .logo i {
            margin-right: 10px;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 20px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .dashboard {
            padding: 2rem 0;
        }
        
        .dashboard-header {
            margin-bottom: 2rem;
        }
        
        .dashboard-header h1 {
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .stat-card h3 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .stat-card p {
            color: #777;
            font-size: 0.9rem;
        }
        
        .chart-container {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .chart-title {
            color: var(--dark-color);
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .chart-period {
            display: flex;
            align-items: center;
        }
        
        .chart-period button {
            background: none;
            border: none;
            color: #777;
            cursor: pointer;
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s, color 0.3s;
        }
        
        .chart-period button.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .chart-period button:hover:not(.active) {
            background-color: #f1f1f1;
        }
        
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }
        
        .table-container {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            overflow-x: auto;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .table-title {
            color: var(--dark-color);
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            background-color: #f1f1f1;
            border-radius: 20px;
            padding: 5px 15px;
        }
        
        .search-box input {
            border: none;
            background: none;
            outline: none;
            padding: 5px;
            width: 200px;
        }
        
        .search-box i {
            color: #777;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f9f9f9;
            font-weight: 600;
            color: #555;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .links-table .original-url {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .links-table .short-url {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .links-table .actions {
            display: flex;
            gap: 10px;
        }
        
        .links-table .actions a {
            color: #777;
            font-size: 1.1rem;
            transition: color 0.3s;
        }
        
        .links-table .actions a:hover {
            color: var(--primary-color);
        }
        
        .links-table .actions a.delete {
            color: var(--danger-color);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            margin-top: 1rem;
        }
        
        .pagination li {
            margin: 0 5px;
        }
        
        .pagination a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            background-color: white;
            border-radius: 50%;
            color: #555;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .pagination a:hover, .pagination a.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.3s;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 0;
        }
        
        .empty-state i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            color: #777;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            color: #999;
            margin-bottom: 1.5rem;
        }
        
        .tooltip {
            position: relative;
            display: inline-block;
        }
        
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 200px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
        
        @media (max-width: 768px) {
            .charts-row {
                grid-template-columns: 1fr;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box {
                margin-top: 10px;
                width: 100%;
            }
            
            .search-box input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <i class="fas fa-link"></i>
                <span>URL Shortener</span>
            </div>
            <ul class="nav-links">
                <li><a href="../index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="index.php"><i class="fas fa-chart-bar"></i> Dashboard</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>Welcome, <?= htmlspecialchars($user['username']) ?></h1>
                <p>Here's an overview of your shortened links and their performance.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-link"></i>
                    <h3><?= $totalLinks ?></h3>
                    <p>Total Links</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-mouse-pointer"></i>
                    <h3><?= $totalClicks ?></h3>
                    <p>Total Clicks</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-chart-line"></i>
                    <h3><?= $totalLinks > 0 ? round($totalClicks / $totalLinks, 1) : 0 ?></h3>
                    <p>Average Clicks per Link</p>
                </div>
            </div>

            <div class="charts-row">
                <div class="chart-container">
                    <div class="chart-header">
                        <div class="chart-title">Clicks over time</div>
                        <div class="chart-period">
                            <button class="active" data-period="week">Week</button>
                            <button data-period="month">Month</button>
                            <button data-period="year">Year</button>
                        </div>
                    </div>
                    <canvas id="clicksChart"></canvas>
                </div>
                <div class="chart-container">
                    <div class="chart-header">
                        <div class="chart-title">Top Links</div>
                    </div>
                    <canvas id="topLinksChart"></canvas>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Your Links</div>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="linkSearch" placeholder="Search links...">
                    </div>
                </div>

                <?php if (count($links) > 0): ?>
    <div class="links-table">
        <table>
            <thead>
                <tr>
                    <th>Short URL</th>
                    <th>Original URL</th>
                    <th>Created</th>
                    <th>Clicks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($links as $link): ?>
                <tr>
                    <td class="short-url">
                        <a href="../shorten/redirect.php?code=<?= $link['short_code'] ?>" target="_blank">
                            <?= $_SERVER['HTTP_HOST'] ?>/<?= $link['short_code'] ?>
                        </a>
                    </td>
                    <td class="original-url">
                        <div class="tooltip">
                            <?= htmlspecialchars(substr($link['original_url'], 0, 50)) ?>
                            <?= strlen($link['original_url']) > 50 ? '...' : '' ?>
                            <span class="tooltiptext"><?= htmlspecialchars($link['original_url']) ?></span>
                        </div>
                    </td>
                    <td><?= date('M j, Y', strtotime($link['created_at'])) ?></td>
                    <td><?= $link['click_count'] ?></td>
                    <td class="actions">
                        <a href="analytics.php?link_id=<?= $link['id'] ?>" title="Analytics"><i class="fas fa-chart-bar"></i></a>
                        <a href="#" class="copy-link" data-url="<?= $_SERVER['HTTP_HOST'] ?>/<?= $link['short_code'] ?>" title="Copy"><i class="fas fa-copy"></i></a>
                        <a href="delete.php?id=<?= $link['id'] ?>" class="delete" title="Delete" onclick="return confirm('Are you sure you want to delete this link?')"><i class="fas fa-trash-alt"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-state">
        <i class="fas fa-link-slash"></i>
        <h3>No links found</h3>
        <p>You haven't created any shortened links yet.</p>
        <a href="../index.php" class="btn">Create your first link</a>
    </div>
<?php endif; ?>

            </div>

            <?php if (count($recentClicks) > 0): ?>
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Recent Clicks</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Short URL</th>
                            <th>Location</th>
                            <th>Browser</th>
                            <th>Clicked at</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentClicks as $click): ?>
                        <tr>
                            <td class="short-url"><?= $click['short_code'] ?></td>
                            <td><?= $click['country'] ? $click['country'] : 'India' ?></td>
                            <td><?= $click['browser'] ? $click['browser'] : 'Unknown' ?></td>
                            <td><?= date('M j, Y H:i', strtotime($click['clicked_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Fetch Clicks Over Time Data
    fetch('get_clicks_data.php')
        .then(response => response.json())
        .then(data => {
            const labels = data.map(row => row.click_date).reverse();
            const counts = data.map(row => row.click_count).reverse();

            const clicksCtx = document.getElementById('clicksChart').getContext('2d');
            const clicksChart = new Chart(clicksCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Clicks',
                        data: counts,
                        backgroundColor: 'rgba(52, 152, 219, 0.2)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(52, 152, 219, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(52, 152, 219, 1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: 0
                            }
                        }]
                    }
                }
            });

            // Chart period buttons (static adjustment)
            document.querySelectorAll('.chart-period button').forEach(button => {
                button.addEventListener('click', function () {
                    document.querySelector('.chart-period button.active').classList.remove('active');
                    this.classList.add('active');
                    // You can add filtering here later based on period (week/month/year)
                });
            });
        });

    // Fetch Top 5 Links Data
    fetch('get_top_links.php')
        .then(response => response.json())
        .then(data => {
            const labels = data.map(row => row.short_code);
            const counts = data.map(row => row.click_count);

            const topLinksCtx = document.getElementById('topLinksChart').getContext('2d');
            const topLinksChart = new Chart(topLinksCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Clicks',
                        data: counts,
                        backgroundColor: [
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(26, 188, 156, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(241, 196, 15, 0.7)',
                            'rgba(231, 76, 60, 0.7)'
                        ],
                        borderColor: [
                            'rgba(52, 152, 219, 1)',
                            'rgba(26, 188, 156, 1)',
                            'rgba(155, 89, 182, 1)',
                            'rgba(241, 196, 15, 1)',
                            'rgba(231, 76, 60, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: 0
                            }
                        }]
                    }
                }
            });
        });

    // Link search functionality
    document.getElementById('linkSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('.links-table tbody tr');

        rows.forEach(row => {
            const originalUrl = row.querySelector('.original-url').textContent.toLowerCase();
            const shortUrl = row.querySelector('.short-url').textContent.toLowerCase();

            if (originalUrl.includes(searchTerm) || shortUrl.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Copy link functionality
    document.querySelectorAll('.copy-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('data-url');
            navigator.clipboard.writeText(url).then(() => {
                alert('Link copied to clipboard!');
            });
        });
    });
});
</script>

<script>
    // Fetch data from PHP
    var clicksOverTimeData = <?php echo $clicksOverTimeJson; ?>;
    var topLinksData = <?php echo $topLinksJson; ?>;

    // 1. Collect all unique dates and organize data by short_code
    var allDatesSet = new Set();
    var dataByShortCode = {};

    clicksOverTimeData.forEach(function(item) {
        var date = item.click_date;
        var code = item.short_code;
        var count = parseInt(item.click_count);

        allDatesSet.add(date);

        if (!dataByShortCode[code]) {
            dataByShortCode[code] = {};
        }

        dataByShortCode[code][date] = count;
    });

    var allDates = Array.from(allDatesSet).sort();

    // 2. Create datasets for each short_code
    var datasets = [];

    Object.keys(dataByShortCode).forEach(function(code) {
        var dataPoints = allDates.map(function(date) {
            return dataByShortCode[code][date] || 0; // 0 if no data for that date
        });

        datasets.push({
            label: code,
            data: dataPoints,
            borderWidth: 2,
            fill: false,
            borderColor: getRandomColor(),
            tension: 0.3
        });
    });

    // 3. Render Clicks Over Time Chart
    var ctx1 = document.getElementById('clicksChart').getContext('2d');
    var clicksChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: allDates,
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Clicks Over Time (by Short Link)'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Clicks'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });

    // Random color generator for each line
    function getRandomColor() {
        const r = Math.floor(Math.random() * 200);
        const g = Math.floor(Math.random() * 200);
        const b = Math.floor(Math.random() * 200);
        return `rgba(${r}, ${g}, ${b}, 1)`;
    }

    // 4. Top Links Chart (no change needed)
    var topLinksLabels = [];
    var topLinksDataValues = [];
    topLinksData.forEach(function(item) {
        topLinksLabels.push(item.short_code);
        topLinksDataValues.push(item.click_count);
    });

    var ctx2 = document.getElementById('topLinksChart').getContext('2d');
    var topLinksChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: topLinksLabels,
            datasets: [{
                label: 'Click Count',
                data: topLinksDataValues,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>



</body>
</html>