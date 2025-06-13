<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

require_once '../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <!-- Same navigation bar as dashboard.php -->
</nav>

<div class="container mt-4">
    <h2>Generate Reports</h2>
    <form method="POST" action="generate_report.php">
        <div class="row">
            <div class="col-md-4">
                <label>Report Type</label>
                <select class="form-control" name="report_type" required>
                    <option value="">Select Report</option>
                    <option value="sales">Sales Report</option>
                    <option value="users">User Activity Report</option>
                    <option value="products">Product Listing Report</option>
                    <option value="transactions">Transaction Report</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control">
            </div>
            <div class="col-md-4">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control">
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>